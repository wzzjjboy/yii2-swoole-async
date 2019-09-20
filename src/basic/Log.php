<?php

namespace yii2\swoole_async\basic;

use yii\base\BaseObject;

class Log extends BaseObject implements ILog
{
    public $category = 'application';

    public $levels = ['error', 'warning', 'info', 'trace'];

    private function logFormat($msg, $level)
    {
        if (!in_array($level, $this->levels)){
            return null;
        }

        $msg = is_array($msg) ? json_encode($msg, JSON_UNESCAPED_UNICODE) : $msg;
        echo "[" . date('Y-m-d H:i:s') . "] [$level]: {$msg}" . PHP_EOL;
    }

    /**
     * @inheritDoc
     * @param mixed $msg
     * @return void
     */
    public function error($msg):void
    {
        $this->logFormat($msg, 'error');
    }

    /**
     * @inheritDoc
     * @param mixed $msg
     * @return void
     */
    public function info($msg):void
    {
        $this->logFormat($msg, 'info');
    }

    /**
     * @inheritDoc
     * @param mixed $msg
     * @return void
     */
    public function warning($msg):void
    {
        $this->logFormat($msg, 'warning');
    }

    /**
     * @param mixed $msg
     */
    public function trace($msg): void
    {
        $this->logFormat($msg, 'trace');
    }
}
