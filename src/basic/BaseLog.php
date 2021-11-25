<?php
namespace yii2\swoole_async\basic;

use Yii;

class BaseLog
{
    /**
     * YII Log category
     */
    const LOG_CATEGORY = "server";

    public static function info($msg, $category = self::LOG_CATEGORY){
        Yii::info(self::messageToArray($msg),$category);
    }

    public static function error($msg, $category = self::LOG_CATEGORY){
        Yii::error(self::messageToArray($msg),$category);
    }

    public static function warning($msg, $category = self::LOG_CATEGORY){
        Yii::warning(self::messageToArray($msg),$category);
    }

    public static function messageToArray($msg): array
    {
        if (!is_array($msg)) {
            return ['msg'=>(string)$msg];
        }
        if (!array_key_exists('msg', $msg) && !array_key_exists('message', $msg)) {
            $msg['msg'] = json_encode($msg);
        }
        return $msg;
    }
}