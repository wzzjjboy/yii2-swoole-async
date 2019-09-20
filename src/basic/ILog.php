<?php

namespace yii2\swoole_async\basic;
/**
 *
 */
interface ILog
{
    /**
     * @param mixed $msg
     * @return void
     */
    public function error($msg):void;

    /**
     * @param mixed $msg
     * @return void
     */
    public function info($msg):void;

    /**
     * @param mixed $msg
     * @return void
     */
    public function warning($msg):void;

    /**
     * @param mixed $msg
     */
    public function trace($msg):void;
}
