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
     * @param $stage
     * @param $branch
     * @param $ldh
     *
     * @throws \Exception
     *
     * @return array
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
        verbose('['.$stage.'] Checking dependencies. Might take a minute.');
        foreach ($versions as $app => $version) {
            //            if (SSH::checkAppVersion($connection, $app, $version) == '-1') {
//                Locker::unlock($connection, $stage);
//                throw new \Exception('Version of ' . $app . ' does not match your requirements');
//            }
            SSH::checkAppVersion($connection, $app, $version);
        }

        // Define the deploy
        verbose('['.$stage.'] Creating new release directory and pulling from remote');
        // Fixes https://github.com/DALTCORE/laravel-deploy-helper/issues/6#issuecomment-315124310
        $url = config('laravel-deploy-helper.stages.'.$stage.'.git.http');
        if ($url === null) {
            $url = config('laravel-deploy-helper.stages.'.$stage.'.git');
        }

        SSH::execute($stage, ['mkdir '.$home.'/releases/'.$releaseName]);
        SSH::execute(
            $stage,
            [
                'cd '.$home.'/releases/'.$releaseName.'; '.
                'git clone -b '.$branch.' '."'".$url."'".' .',
            ]
        );

        // Pre-flight for shared stuff
        $items['directories'] = [];
        foreach ($shared['directories'] as $share) {
            verbose('['.$stage.'] About to share directory "'.$home.'/current/'.$share.'"');
            SSH::execute($stage, ['[ -e '.$home.'/current/'.$share.' ] && cp -R -p '.$home.'/current/'
                .$share.' '.$home.'/shared/'.$share, ]);
            SSH::execute(
                $stage,
                [$items['directories'][] = '[ -e '.$home.'/shared/'.$share.' ] && cp -R -p '.$home.
                    '/shared/'.$share.' '.$home.'/releases/'.$releaseName, ]
            );
        }
        // Pre-flight for shared stuff
        $items['files'] = [];
        foreach ($shared['files'] as $share) {
            verbose('['.$stage.'] About to share file "'.$home.'/current/'.$share.'"');
            SSH::execute($stage, ['[ -e '.$home.'/current/'.$share.' ] && cp -p '.$home.'/current/'.$share
                .' '.$home.'/shared/'.$share, ]);
            SSH::execute($stage, ['[ -e '.$home.'/shared/'.$share.' ] && cp -p '.$home.'/shared/'.$share.
                ' '.$home.'/releases/'.$releaseName.'/'.$share, ]);
        }

        // Define commands
        verbose('['.$stage.'] Executing custom commands');
        $items = [];
        foreach ($commands as $command) {
            verbose('Running command: '.$command);
            SSH::execute($stage, ['cd '.$home.'/releases/'.$releaseName.' && '.$command]);
        }

        // Define post deploy actions
        verbose('['.$stage.'] Linking new release to /current directory and removing temp');
        SSH::execute($stage, [
            'ln -sfn '.$home.'/releases/'.$releaseName.' '.$home.'/current &&'.
            'rm -rf '.$home.'/shared/*',
        ]);

        // Remove old deploys
        if (isset($toRemove) && is_array($toRemove)) {
            $items = [];
            verbose('['.$stage.'] Cleaning up old releases');
            foreach ($toRemove as $dir => $val) {
                SSH::execute($stage, ['echo "Removing release '.$dir.'" && rm -rf '.$home.'/releases/'.$dir]);
            }
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
