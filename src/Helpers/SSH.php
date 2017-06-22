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
        return config('laravel-deploy-helper.stages.'.$stage.'.remote.root');
    }

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
         * Check if Node exists on server with thuis version
         */
        if ((strtolower($app) == 'node' || strtolower($app) == 'nodejs') && $requestedVersion !== true) {
            $connection->run(Command::builder($app, ['--version']), function ($response) use (&$currVer) {
                preg_match('/v(?<version>.*?[^\s]+)/', $response, $match);
                $currVer = $match['version'];
            });
        }
        /*
         * Check if composer exists on server with thuis version
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
                    throw new \Exception('ERROR: '.$app.' is not installed on the server.');
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
                throw new \Exception('ERROR: '.$app.' is not installed on the server.');
            }
        });

        verbose("\t => Checking $app version $currVer $operator $version");

        return version_compare($currVer, $version, $operator);
    }
}
