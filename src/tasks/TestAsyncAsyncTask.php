<?php
/**
 * Created by PhpStorm.
 * User: alan
 * Date: 2018/8/23
 * Time: 10:26
 */

namespace yii2\swoole_async\tasks;

use yii2\swoole_async\basic\AsyncTask;

class TestAsyncAsyncTask extends AsyncTask
{

    public $rule = [
        'type' => 'async',
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
        sleep(1);
        return true;
    }
}