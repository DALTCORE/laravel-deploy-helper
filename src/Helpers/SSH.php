<?php

namespace DALTCORE\LaravelDeployHelper\Helpers;

use Collective\Remote\Connection;
use Composer\Semver\Semver;
use DALTCORE\LaravelDeployHelper\Remote\RemoteManager;

class SSH
{
    /**
     * @return \DALTCORE\LaravelDeployHelper\Remote\RemoteManager
     */
    public static function instance()
    {
        return new RemoteManager(app());
    }

    /**
     * @param $stage
     *
     * @return mixed
     */
    public static function home($stage)
    {
        return config('laravel-deploy-helper.stages.' . $stage . '.remote.root');
    }

    /**
     * @param \Collective\Remote\Connection $connection
     * @param                               $app
     * @param                               $requestedVersion
     *
     * @return bool|mixed
     */
    public static function checkAppVersion(Connection $connection, $app, $requestedVersion)
    {
        $currVer = null;

        preg_match('/([>=|<=|>=|==|!=|<>|>|<|=]+)(.*)/', $requestedVersion, $match);
        $operator = isset($match[1]) ? $match[1] : '=';
        $version = isset($match[2]) ? $match[2] : 0;

        /*
         * Check if PHP Exists on server WITH this version
         */
        if (strtolower($app) == 'php' && $requestedVersion !== true) {
            $connection->run(Command::builder($app, ['-v']), function ($response) use (&$currVer) {
                preg_match('/PHP (?<version>.*?[^\s]+)/', $response, $match);
                $currVer = $match['version'];
            });
        }

        /*
         * Check if Node exists on server with this version
         */
        if ((strtolower($app) == 'node' || strtolower($app) == 'nodejs') && $requestedVersion !== true) {
            $connection->run(Command::builder($app, ['--version']), function ($response) use (&$currVer) {
                preg_match('/v(?<version>.*?[^\s]+)/', $response, $match);
                $currVer = $match['version'];
            });
        }

        /*
        * Check if NPM exists on server with this version
        */
        if ((strtolower($app) == 'npm') && $requestedVersion !== true) {
            $connection->run(Command::builder($app, ['-v']), function ($response) use (&$currVer) {
                preg_match('/(?<version>.*?[^\s]+)/', $response, $match);
                $currVer = $match['version'];
            });
        }

        /*
         * Check if composer exists on server with this version
         */
        if (strtolower($app) == 'composer' && $requestedVersion !== true) {
            $connection->run(Command::builder($app, ['--version']), function ($response) use (&$currVer) {
                preg_match('/version (?<version>.*?[^\s]+)/', $response, $match);
                $currVer = $match['version'];
            });
        }

        /*
         * Check if application just exists on server
         */
        if ($requestedVersion === true) {
            $connection->run(Command::builder($app, []), function ($response) use ($app) {
                if (stripos($response, 'command not found') !== false) {
                    throw new \Exception('ERROR: ' . $app . ' is not installed on the server.');
                }
            });
            verbose("\t => Checking $app is available");

            return true;
        }

        /*
         * Check if exists
         */
        $connection->run(Command::builder($app, []), function ($response) use ($app) {
            if (stripos($response, 'command not found') !== false) {
                throw new \Exception('ERROR: ' . $app . ' is not installed on the server.');
            }
        });

        verbose("\t => Checking $app version $currVer $operator $version");

        return (!empty(Semver::satisfies($version, $requestedVersion)));
    }

    /**
     * Use a fresh connection to prevent.
     *
     * @param $stage
     * @param $commands
     */
    public static function execute($stage, $commands)
    {
        $connection = self::instance()->into($stage);
        $connection->run($commands);
        unset($connection);
    }

    /**
     * Pre flight checks before the deploy happens.
     *
     * @param $instance
     * @param $stage
     * @param $branch
     *
     * @throws \Exception
     *
     * @return bool|mixed
     */
    public static function preFlight($instance, $stage, $branch = false)
    {
        cli_header();

        // Pre flight checking
        if ($stage === null) {
            throw new \Exception('The argument "--stage=" is required!', 128);
        } else {
            if (!is_array(config('laravel-deploy-helper.stages.' . $stage))) {
                throw new \Exception('The stage "' . $stage
                    . '" does not exist!', 128);
            }
        }

        if ($branch != false) {
            if ($branch === null) {
                throw new \Exception('The argument "--branch=" is required!', 128);
            }

            if (in_array($branch, Git::getBranches()) == false) {
                throw new \Exception('The branch "' . $branch
                    . '" does not exists locally? Please `git pull`!', 128);
            }
        }

        // Connecting to remote server
        verbose('[' . $stage . '] Trying to login into remote SSH');
        $ssh = self::instance()->into($stage);

        // Check for lockfile
        if (Locker::lock($ssh, $stage) === false) {
            // Cannot create lock file, stop the process!
            exit(1);
        }

        // Trying to read file
        verbose('[' . $stage . '] Reading config file from remote server');
        $config = $ssh->exists(self::home($stage) . '/ldh.json');

        // Check if config exists
        if ($config == false) {
            error('[' . $stage . '] ldh.json does not exists.');
            if ($instance->confirm('Do you want to initialize LDH here?')) {
                Deployer::freshInit($ssh, $stage);
            } else {
                return false;
            }
        } else {
            verbose('[' . $stage . '] Found config. Checking directories.');
            $config = $ssh->getString(self::home($stage) . '/ldh.json');
            if ($config == false) {
                error('[' . $stage . '] Config file is empty... Something is wrong.');

                return false;
            }

            return json_decode($config, true);
        }
    }

    /**
     * End of command.
     *
     * @param $stage
     */
    public static function performLanding($stage)
    {
        $ssh = self::instance()->into($stage);
        Locker::unlock($ssh, $stage);
        verbose('[' . $stage . '] Changes are successfull!');
    }
}
