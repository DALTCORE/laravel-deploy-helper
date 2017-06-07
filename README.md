# Laravel Deploy Helper

Laravel >=5.4 compatible

## Install

Via Composer

``` bash
$ composer require daltcore/laravel-deploy-helper
```

In your config/app.php
``` php
DALTCORE\LaravelDeployHelper\LdhServiceProvider::class,
```

Publish configuration
```bash
$ php artisan vendor:publish --tag=ldh-config
```

## Usage

Deploy to server
```bash
php artisan ldh:deploy --stage=production --branch=develop
```

Rollback one instance
```bash
php artisan ldh:rollback --stage=production
```
