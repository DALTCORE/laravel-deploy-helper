<?php

namespace DALTCORE\LaravelDeployHelper\Helpers;

use DALTCORE\LaravelDeployHelper\Remote\RemoteManager;

class SSH
{
    public static function instance()
    {
        return new RemoteManager(app());
    }

    public static function home($stage)
    {
        return config('laravel-deploy-helper.stages.' . $stage . '.remote.root');
    }
}
