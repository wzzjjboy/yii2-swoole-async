<?php
return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=xxxxxxxxxx;port=3307;dbname=providers',
            'username' => 'root',
            'password' => 'a6CFu3VH6OYW',
            'charset' => 'utf8',
            'tablePrefix' => 'gpi_',
            'commandClass' => 'yii2\swoole_async\components\DbCommand',
        ],
        'redis' => [
            'class' => 'yii2\swoole_async\components\RedisConnection',
            'hostname' => 'xxxxxxxxxx',
            'port' => 7001,
            'database' => 1, //服务商
            'password' => 'xxxxxxxxxx'
        ],
        'invoiceRedisEvent' => [
            'class'         => 'common\mqTask\tasks\InvoiceRedisEvent',
            'host'          => 'xxxxxxxxx',
            'port'          => '5672',
            'username'      => 'xxxxxxxxxx',
            'password'      => 'xxxxxxxxxxxx',
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