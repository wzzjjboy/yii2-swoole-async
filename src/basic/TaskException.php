<?php
/**
 * Created by PhpStorm.
 * User: alan
 * Date: 2018/8/20
 * Time: 15:44
 */

namespace yii2\swoole_async\basic;



use yii\base\Exception;

class TaskException extends Exception
{
    public function getName()
    {
        return "task exception";
    }
}