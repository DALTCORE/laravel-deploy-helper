<?php

return [
    'default' => [
        'stage' => 'production',
    ],

    'stages' => [
        'production' => [
            'git' => '',

            'branch' => '',

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
                    '.env',
                ],
            ],

            'config' => [
                'dependencies' => [
                    'php' => '>=5.6',
                    'git' => true,
                ],
                'keep'         => 4,
            ],
        ],
    ],
];
