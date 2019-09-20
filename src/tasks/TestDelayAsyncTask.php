<?php
/**
 * Created by PhpStorm.
 * User: alan
 * Date: 2018/8/23
 * Time: 10:26
 */

namespace yii2\swoole_async\tasks;

use yii2\swoole_async\basic\AsyncTask;
use yii\base\InvalidParamException;


class TestDelayAsyncTask extends AsyncTask
{
    public $rule = [
        'type' => 'delay',
        'rule' => [
            '1s','5s',
        ],
    ];

    /**
     * @param mixed $data
     * @return bool
     */
    public function consume($data): bool
    {
        // TODO: Implement consume() method.
        echo __METHOD__ . PHP_EOL;
        return false;
    }
}