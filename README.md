# Laravel Deploy Helper

[![Dependency Status](https://www.versioneye.com/user/projects/593e38af0fb24f003de0c84c/badge.svg?style=flat-square)](https://www.versioneye.com/user/projects/593e38af0fb24f003de0c84c)
[![StyleCI](https://styleci.io/repos/93638212/shield?branch=master)](https://styleci.io/repos/93638212)
[![Packagist](https://img.shields.io/github/release/daltcore/laravel-deploy-helper.svg?style=flat-square)](https://packagist.org/packages/daltcore/laravel-deploy-helper)
[![Packagist](https://img.shields.io/packagist/dt/daltcore/laravel-deploy-helper.svg?style=flat-square)](https://packagist.org/packages/daltcore/laravel-deploy-helper)
[![license](https://img.shields.io/github/license/DALTCORE/laravel-deploy-helper.svg?style=flat-square)](https://github.com/DALTCORE/laravel-deploy-helper/blob/master/LICENSE)
[![Made by DALTCORE](https://img.shields.io/badge/MADE%20BY-DALTCORE-green.svg?style=flat-square)](https://github.com/DALTCORE)

Compatible with Laravel [5.1](https://github.com/DALTCORE/laravel-deploy-helper/tree/5.1), 5.4 and higher. 

**For Laravel 5.1 use branch 5.1 and tag v0.5.x**

LDH is a Laravel package that helps with deploying your website without the usage of FTP.  
The LDH packages uses SSH to build a deployment environment on the server for zero-downtime deployments  
and rollback functionality. 

**Everyone is allowed to help getting this package bigger and better! ;-)**

## Install

Via Composer

``` bash
$ composer require daltcore/laravel-deploy-helper v0.5.4
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

*Deploy to server*  
Deploy full instance to the remote server
```bash
php artisan ldh:deploy --stage=production --branch=develop
```

*Patch to server*  
Push a simple patch to the remote server (minor changes only)
```bash
php artisan ldh:patch --stage=production --branch=patch
```

*Rollback one instance*  
Something went horrably wrong, go back in history
```bash
php artisan ldh:rollback --stage=production
```

## Configuration

`stages`  
In the stages section you have to define your stages information.

`git`  
You can use git's http url with basic auth. Example: _https://username:password@github.com/repo/name.git_ .  
You can use git's ssh. Example: _git@github.com:repo/name.git_

`connection`  
In the connection section you can add your ssh details for deploying

`remote.root`  
Here you can put the root directory where LDH can set up it's directory structure

`commands`  
This is a array with commands that needs to be executed from the /current directory

`shared.directories`  
This is te section with directories that needs to be copied from the previous deploy to the next one

`shared.files`  
The same as with the directories, but then with files.
 
`config.dependencies`  
Here you can optionally put some dependencies (applications) that you want to use for deploy.  
You can use this for checking if everything on the server is setup correctly with the versions.  
You may want use 'true' for no version checking, but instead just checking if the application exists.

`config.keep`  
How many 'shadow' copies of the old deploys needs to exist. These come in handy for the rollback feature.


Config example:

```php
<?php

return [
    'stages' => [
        'production' => [
            'git' => '',

            'connection' => [
                'host'     => '',
                'username' => '',
                'password' => '',
                // 'key'       => '',
                // 'keytext'   => '',
                // 'keyphrase' => '',
                // 'agent'     => '',
                'timeout'  => 10,
            ],

            'remote' => [
                'root' => '/var/www',
            ],

            'commands' => [
                'composer install',
            ],

            'shared' => [
                'directories' => [
                    'public',
                    'storage',
                ],
                'files'       => [
                    '.env'
                ]
            ],

            'config' => [
                'dependencies' => [
                    'php' => '>=5.6',
                    'git' => true,
                ],
                'keep'         => 4,
            ],
        ]
    ]
];

```

## Directory structure
LDH deploys the following directory structure on first deploy
```text
.
├── current -> /var/www/vhosts/example.org/releases/1498833243
├── ldh.json
├── patches
│   └── 0001-Update-readme.md.patch
├── releases
│   └── 1498833243
└── shared
```

As you can see, LDH makes a static link from `/current` to `/releases/1496845077`.  
You you only have to point your vhost to the `/current/public` for your Laravel website to work.
