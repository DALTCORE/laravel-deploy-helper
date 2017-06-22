<?php

namespace DALTCORE\LaravelDeployHelper\Helpers;

use Collective\Remote\Connection;

class Locker
{
    protected static $lockfile = '/.ldh-lockfile';

    /**
     * Check if the lockfile is set by LDH.
     *
     * @param Connection $ssh
     * @param string     $stage
     *
     * @return bool
     */
    public static function verify(Connection $ssh, $stage)
    {
        $home = config('laravel-deploy-helper.stages.'.$stage.'.remote.root');

        if ($ssh->exists($home.self::$lockfile)) {
            return true;
        }

        return false;
    }

    /**
     * Get lockfile path.
     *
     * @param Connection $ssh
     * @param string     $stage
     *
     * @return bool
     */
    public static function getLockPath(Connection $ssh, $stage)
    {
        $home = config('laravel-deploy-helper.stages.'.$stage.'.remote.root');

        if (self::verify($ssh, $stage) !== true) {
            error('Cannot open lockfile. Does not exist or does not have the rights.');

            return false;
        }

        $line = '';

        $ssh->run(['ls '.$home.self::$lockfile], function ($callback) use (&$line) {
            $line = str_replace("\n", '', $callback);
        });

        return $line;
    }

    /**
     * Put lockfile in system.
     *
     * @param Connection $ssh
     * @param string     $stage
     *
     * @return bool
     */
    public static function lock(Connection $ssh, $stage)
    {
        $home = config('laravel-deploy-helper.stages.'.$stage.'.remote.root');

        if (self::verify($ssh, $stage) === true) {
            error('Cannot lock deployment. Lockfile already in use!');

            return false;
        }

        $ssh->putString($home.self::$lockfile, time());

        return true;
    }

    /**
     * Destroy lock file.
     *
     * @param Connection $ssh
     * @param string     $stage
     *
     * @return bool
     */
    public static function unlock(Connection $ssh, $stage)
    {
        $home = config('laravel-deploy-helper.stages.'.$stage.'.remote.root');

        if (self::verify($ssh, $stage) !== true) {
            error('Cannot delete lockfile. Does not exist or does not have the rights.');

            return false;
        }

        $ssh->delete($home.self::$lockfile);

        return true;
    }
}
