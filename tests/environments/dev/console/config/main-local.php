<?php
return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=10.105.201.137;port=3307;dbname=providers',
            'username' => 'root',
            'password' => 'a6CFu3VH6OYW',
            'charset' => 'utf8',
            'tablePrefix' => 'gpi_',
            'commandClass' => 'yii2\swoole_async\components\DbCommand',
        ],
        'redis' => [
            'class' => 'yii2\swoole_async\components\RedisConnection',
            'hostname' => '10.105.201.137',
            'port' => 7001,
            'database' => 1, //服务商
            'password' => 'pUD85cOEvX22'
        ],
        'invoiceRedisEvent' => [
            'class'         => 'common\mqTask\tasks\InvoiceRedisEvent',
            'host'          => '10.154.33.130',
            'port'          => '5672',
            'username'      => 'rabbit',
            'password'      => 'aTjHMj7opZ3d5Kw6',
            'exchange_name' => 'invoice.event',
            'queue_name'    => 'invoice.event#from.redis',
            'routing_key'   => 'from.redis',
        ],
        'swoole' => [
            'class'            => 'yii2\swoole_async\basic\Swoole',
            'host'             => '127.0.0.1',
            'port'             => '9501',
            'taskWorkerNum'    => '10',
            'daemonize'        => false,
        ],
    ],
];