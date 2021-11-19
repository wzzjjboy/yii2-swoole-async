<?php
/**
 * Created by PhpStorm.
 * User: alan
 * Date: 2018/8/23
 * Time: 10:26
 */

namespace yii2\swoole_async\tasks;

use yii2\swoole_async\basic\AsyncTask;


class TestTimedAsyncTask extends AsyncTask
{
    public $rule = [
        'type' => 'timed',
        'rule' => '*/30 * *'
    ];

    /**
     * @param mixed $data
     * @return bool
     */
    public function consume($data): bool
    {
        // TODO: Implement consume() method.
        print_r(__METHOD__);
        print_r($data);
        return false;
    }
}