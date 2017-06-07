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

## Configuration

`stages`  
In the stages section you have to define your stages information.

`git.http`  
For now I'm using git's http url with basic auth _http://user:pass@git.server/repo.git_

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
            'git' => [
                'http' => ''
            ],

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