<?php

namespace DALTCORE\LaravelDeployHelper;

use DALTCORE\LaravelDeployHelper\Console\Commands\Deploy;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

/**
 * Class PackageServiceProvider
 *
 * @package Daltcore\LaravelDeployHelper
 * @see http://laravel.com/docs/master/packages#service-providers
 * @see http://laravel.com/docs/master/providers
 */
class LdhServiceProvider extends BaseServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @see http://laravel.com/docs/master/providers#deferred-providers
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @see http://laravel.com/docs/master/providers#the-register-method
     * @return void
     */
    public function register()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Deploy::class,
            ]);
        }
    }

    /**
     * Application is booting
     *
     * @see http://laravel.com/docs/master/providers#the-boot-method
     * @return void
     */
    public function boot()
    {
        $this->registerConfigurations();
    }

    /**
     * Register the package configurations
     *
     * @see http://laravel.com/docs/master/packages#configuration
     * @return void
     */
    protected function registerConfigurations()
    {
        $this->mergeConfigFrom(
            $this->packagePath('config/ldh.php'), 'laravel-deploy-helper'
        );
        $this->publishes([
            $this->packagePath('config/ldh.php') => config_path('laravel-deploy-helper.php'),
        ], 'config');
    }


    /**
     * Loads a path relative to the package base directory
     *
     * @param string $path
     * @return string
     */
    protected function packagePath($path = '')
    {
        return sprintf("%s/../%s", __DIR__ , $path);
    }
}
