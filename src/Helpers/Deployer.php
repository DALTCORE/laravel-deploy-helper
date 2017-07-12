<?php

namespace DALTCORE\LaravelDeployHelper\Helpers;

use Collective\Remote\Connection;

class Deployer
{
    /**
     * @param \Collective\Remote\Connection $connection
     * @param                               $stage
     */
    public static function freshInit(Connection $connection, $stage)
    {
        // Init fresh remote repo
        $connection->define('init', [
            'cd '.config('laravel-deploy-helper.stages.'.$stage.'.remote.root'),
            'mkdir releases',
            'mkdir patches',
            'mkdir shared',
            'touch ldh.json',
        ]);
        $connection->task('init');
    }

    /**
     * @param \Collective\Remote\Connection $connection
     * @param                               $stage
     * @param                               $branch
     * @param                               $ldh
     *
     * @return int
     */
    public static function doDeploy($stage, $branch, $ldh)
    {
        // Some stuff that does not change in runtime
        $releaseName = time();
        $home = config('laravel-deploy-helper.stages.'.$stage.'.remote.root');
        $shared = config('laravel-deploy-helper.stages.'.$stage.'.shared');
        $commands = config('laravel-deploy-helper.stages.'.$stage.'.commands');
        $versions = config('laravel-deploy-helper.stages.'.$stage.'.config.dependencies');
        $keep = config('laravel-deploy-helper.stages.'.$stage.'.config.keep');

        // Check what releases are old and can be removed
        // Adding the array fixed #1
        if (is_array($ldh)) {
            ksort($ldh);
            $original = $ldh;
            $ldh = array_slice($ldh, -$keep, $keep, true);
            $toRemove = array_diff_key($original, $ldh);
        }

        // setup ssh connection to remote
        $connection = SSH::instance()->into($stage);

        // Check versions
        // Operators: http://php.net/manual/en/function.version-compare.php
        verbose('['.$stage.'] Checking dependencies. Migth take a minute.');
        foreach ($versions as $app => $version) {
            SSH::checkAppVersion($connection, $app, $version);
        }

        // Define the deploy
        verbose('['.$stage.'] Creating new release directory and pulling from remote');

        // Trying to escape special characters #6
        $git = addcslashes(config('laravel-deploy-helper.stages.'.$stage.'.git.http'), '$&');
        SSH::execute($stage, [
            'mkdir '.$home.'/releases/'.$releaseName,
            'cd '.$home.'/releases/'.$releaseName,
            'git clone -b '.$branch.' '.$git.' .',
        ]);

        // Pre-flight for shared stuff
        $items['directories'] = [];
        foreach ($shared['directories'] as $share) {
            verbose('['.$stage.'] About to share direcroty "'.$home.'/current/'.$share.'"');
            $items['directories'][] = '[ -e '.$home.'/current/'.$share.' ] && cp -R -p '.$home.'/current/'
                .$share.' '.$home.'/shared/'.$share;
            $items['directories'][] = '[ -e '.$home.'/shared/'.$share.' ] && cp -R -p '.$home.'/shared/'.
                $share.' '.$home.'/releases/'.$releaseName;
        }
        // Pre-flight for shared stuff
        $items['files'] = [];
        foreach ($shared['files'] as $share) {
            verbose('['.$stage.'] About to share file "'.$home.'/current/'.$share.'"');
            $items['files'][] = '[ -e '.$home.'/current/'.$share.' ] && cp -p '.$home.'/current/'.$share
                .' '.$home.'/shared/'.$share;
            $items['files'][] = '[ -e '.$home.'/shared/'.$share.' ] && cp -p '.$home.'/shared/'.$share.
                ' '.$home.'/releases/'.$releaseName.'/'.$share;
        }

        // Define shared files
        verbose('['.$stage.'] Syncing shared files');
        SSH::execute($stage, $items['files']);

        // Define shared directories
        verbose('['.$stage.'] Syncing shared directories');
        SSH::execute($stage, $items['directories']);

        $items = [];
        foreach ($commands as $command) {
            $items[] = 'cd '.$home.'/releases/'.$releaseName.' && '.$command;
        }
        // Define commands
        verbose('['.$stage.'] Executing custom commands');
        SSH::execute($stage, $items);

        // Define post deploy actions
        verbose('['.$stage.'] Linking new release to /current directory and removing temp');
        SSH::execute($stage, [
            'ln -sfn '.$home.'/releases/'.$releaseName.' '.$home.'/current',
            'rm -rf '.$home.'/shared/*',
        ]);

        // Remove old deploys
        if (is_array($toRemove)) {
            $items = [];
            foreach ($toRemove as $dir => $val) {
                $items[] = 'echo "Removing release '.$dir.'" && rm -rf '.$home.'/releases/'.$dir;
            }
            verbose('['.$stage.'] Cleaning up old releases');
            SSH::execute($stage, $items);
        }

        $ldh[$releaseName] = true;

        return $ldh;
    }

    /*
     * Rollback in case of error!
     */
    public static function doRollback(Connection $connection, $stage, $ldh, $dirs)
    {
        $home = config('laravel-deploy-helper.stages.'.$stage.'.remote.root');

        // Define post deploy actions
        $connection->define('preformRollback', [
            'ln -sfn '.$home.'/releases/'.$dirs[1].' '.$home.'/current',
            'rm -rf '.$home.'/releases/'.$dirs[0],
        ]);

        verbose("\t".'Hold my beer, We\'re rolling back');
        $connection->task('preformRollback');

        unset($dirs[0]);
        $ldhs = [];
        foreach ($dirs as $key => $ldh) {
            $ldhs[$ldh] = true;
        }

        krsort($ldhs);

        return $ldhs;
    }

    /**
     * @param $stage
     * @param $branch
     */
    public static function doPatch($stage, $branch)
    {
        $home = config('laravel-deploy-helper.stages.'.$stage.'.remote.root');

        // setup ssh connection to remote
        $connection = SSH::instance()->into($stage);

        $connection->define('preformPatch', [
            Command::builder('cd', [$home.'/current']),
            Command::builder('ls', ['-haml']),

            Command::builder('git', ['config', 'user.email', 'git+LDH@localhost.ext']),
            Command::builder('git', ['config', 'user.name', 'LDH']),

            Command::builder('git', ['fetch']),
            Command::builder('git',
                ['format-patch', '-1', 'origin/'.$branch, 'FETCH_HEAD', '-o', $home.'/patches']),
            'git apply --reject --whitespace=fix '.$home.'/patches/*',
            Command::builder('rm', ['-rf', $home.'/patches']),
        ]);

        verbose("\t".'Hold on tight, trying to patch!');
        $connection->task('preformPatch');
    }
}
