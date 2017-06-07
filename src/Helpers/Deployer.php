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
            'cd ' . config('laravel-deploy-helper.stages.' . $stage . '.remote.root'),
            'mkdir releases',
            'mkdir shared',
            'touch ldh.json'
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
    public static function doDeploy(Connection $connection, $stage, $branch, $ldh)
    {
        // Some stuff that does not change in runtime
        $releaseName = time();
        $home = config('laravel-deploy-helper.stages.' . $stage . '.remote.root');
        $shared = config('laravel-deploy-helper.stages.' . $stage . '.shared');
        $commands = config('laravel-deploy-helper.stages.' . $stage . '.commands');
        $versions = config('laravel-deploy-helper.stages.' . $stage . '.config.dependencies');
        $keep = config('laravel-deploy-helper.stages.' . $stage . '.config.keep');

        // Check what releases are old and can be removed
        ksort($ldh);
        $original = $ldh;
        $ldh = array_slice($ldh, -$keep, $keep, true);
        $toRemove = array_diff_key($original, $ldh);

        // Check versions
        verbose('[' . $stage . '] Checking dependencies. Migth take a minute.');
        foreach ($versions as $app => $version) {
            SSH::checkAppVersion($connection, $app, $version);
        }

        // Define the deploy
        $connection->define('deploy', [
            'mkdir ' . $home . '/releases/' . $releaseName,
            'cd ' . $home . '/releases/' . $releaseName,
            'git clone -b ' . $branch . ' ' . config('laravel-deploy-helper.stages.' . $stage . '.git.http') . ' .'
        ]);

        // Pre-flight for shared stuff
        $items['directories'] = [];
        foreach ($shared['directories'] as $share) {
            verbose('[' . $stage . '] About to share direcroty "' . $home . '/current/' . $share . '"');
            $items['directories'][] = '[ -e ' . $home . '/current/' . $share . ' ] && cp -R -p ' . $home . '/current/'
                . $share . ' ' . $home . '/shared/' . $share;
            $items['directories'][] = '[ -e ' . $home . '/shared/' . $share . ' ] && cp -R -p ' . $home . '/shared/' .
                $share . ' ' . $home . '/releases/' . $releaseName;
        }
        // Pre-flight for shared stuff
        $items['files'] = [];
        foreach ($shared['files'] as $share) {
            verbose('[' . $stage . '] About to share file "' . $home . '/current/' . $share . '"');
            $items['files'][] = '[ -e ' . $home . '/current/' . $share . ' ] && cp -p ' . $home . '/current/' . $share
                . ' ' . $home . '/shared/' . $share;
            $items['files'][] = '[ -e ' . $home . '/shared/' . $share . ' ] && cp -p ' . $home . '/shared/' . $share .
                ' ' . $home . '/releases/' . $releaseName . '/' . $share;
        }

        // Define shared files
        $connection->define('getSharedFiles', $items['files']);

        // Define shared directories
        $connection->define('getSharedDirectories', $items['directories']);

        $items = [];
        foreach ($commands as $command) {
            $items[] = 'cd ' . $home . '/releases/' . $releaseName . ' && ' . $command;
        }
        // Define commands
        $connection->define('definedCommands', $items);

        // Define post deploy actions
        $connection->define('postDeploy', [
            'ln -sfn ' . $home . '/releases/' . $releaseName . ' ' . $home . '/current',
            'rm -rf ' . $home . '/shared/*'
        ]);

        // Remove old deploys
        $items = [];
        foreach ($toRemove as $dir => $val) {
            $items[] = 'echo "Removing release ' . $dir . '" && rm -rf ' . $home . '/releases/' . $dir;
        }
        $connection->define('removeOld', $items);

        // Execute them!
        verbose('[' . $stage . '] Deploying ' . $branch . ' to server');
        $connection->task('deploy');
        verbose('[' . $stage . '] Handling shared files');
        $connection->task('getSharedFiles');
        verbose('[' . $stage . '] Handling shared directories');
        $connection->task('getSharedDirectories');
        verbose('[' . $stage . '] Handling commands');
        $connection->task('definedCommands');
        verbose('[' . $stage . '] Clean up and linking new instance');
        $connection->task('postDeploy');
        verbose('[' . $stage . '] Remove old deploys');
        $connection->task('removeOld');

        $ldh[$releaseName] = true;

        return $ldh;
    }
}
