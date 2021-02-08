<?php

declare(strict_types=1);

/**
 * 配置文件
 */

return [
    'service.host' => '127.0.0.1',
    'service.port' => (int)Env('RUN_PORT'),
    'smtp.host' => Env('SMTP_HOST'),
    'smtp.port' => (int)Env('SMTP_PORT'),
    'smtp.user' => Env('SMTP_USER'),
    'smtp.password' => Env('SMTP_PASSWORD'),
    'settings'=>function($c){
        return [
            'logger' =>[
                'name'=> 'message_center',
                'path'=>'runtime/log/'
            ],
            'redis' => [
                'default' => [
                    'driver' => 'redis',
                    'host' => Env('REDIS_HOST'),
                    'port' => (int)Env('REDIS_PORT'),
                    'password' => Env('REDIS_PASSWORD'),
                ],
            ]
        ];
    },
];
