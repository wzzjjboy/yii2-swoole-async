<?php

namespace yii2\swoole_async\basic;

use common\base\BaseLog;
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

        return is_array($msg) ? json_encode($msg, JSON_UNESCAPED_UNICODE) : $msg;
    }

    /**
     * @inheritDoc
     * @param mixed $msg
     * @return void
     */
    public function error($msg):void
    {
        $msg = $this->logFormat($msg, 'error');
        BaseLog::error($msg);
    }

    /**
     * @inheritDoc
     * @param mixed $msg
     * @return void
     */
    public function info($msg):void
    {
        $msg = $this->logFormat($msg, 'info');
        BaseLog::info($msg);
    }

    /**
     * @inheritDoc
     * @param mixed $msg
     * @return void
     */
    public function warning($msg):void
    {
        $msg = $this->logFormat($msg, 'warning');
        BaseLog::warning($msg);
    }

    /**
     * @param mixed $msg
     */
    public function trace($msg): void
    {
        $msg = $this->logFormat($msg, 'trace');
        BaseLog::info("[Trace]".$msg);
    }
}
