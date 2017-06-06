<?php
namespace DALTCORE\LaravelDeployHelper;

class Facade extends \Illuminate\Support\Facades\Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-deploy-helper';
    }
}
