<?php

namespace DALTCORE\LaravelDeployHelper\Helpers;

use Collective\Remote\Connection;
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

    public static function checkAppVersion(Connection $connection, $app, $requestedVersion)
    {
        $currVer = null;


        if (strtolower($app) == 'php') {
            $connection->run(Command::builder($app, ['-v']), function ($response) use (&$currVer) {
                preg_match('/PHP (?<version>.*?[^\s]+)/', $response, $match);
                $currVer = $match['version'];
            });
        }

        if (strtolower($app) == 'node' || strtolower($app) == 'nodejs') {
            $connection->run(Command::builder($app, ['--version']), function ($response) use (&$currVer) {
                preg_match('/v(?<version>.*?[^\s]+)/', $response, $match);
                $currVer = $match['version'];
            });
        }

        if ($requestedVersion === true) {
            $connection->run(Command::builder($app, []), function ($response) use ($app) {
                if (stripos($response, 'command not found') !== false) {
                    throw new \Exception('ERROR: ' . $app . ' is not installed on the server.');
                }
            });
            verbose("Checking $app is available");

            return true;
        }

        $connection->run(Command::builder($app, []), function ($response) use ($app) {
            if (stripos($response, 'command not found') !== false) {
                throw new \Exception('ERROR: ' . $app . ' is not installed on the server.');
            }
        });

        list($operator, $version) = explode('|', $requestedVersion);
        verbose("Checking $app version $currVer $operator $version");

        return version_compare($currVer, $version, $operator);
    }
}
