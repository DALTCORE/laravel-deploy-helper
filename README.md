# Laravel Deploy Helper

Laravel Deploy Helper package

## Install

Via Composer

``` bash
$ composer require daltcore/laravel-deploy-helper
```

## Usage

In your config/app.php
``` php
DALTCORE\LaravelDeployHelper\LdhServiceProvider::class,
```

Publish configuration
```bash
$ php artisan vendor:publish --tag=ldh-config
```
