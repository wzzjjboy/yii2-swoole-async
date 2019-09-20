<?php


namespace yii2\swoole_async\components;

use yii\db\Exception;
use yii\helpers\Inflector;
use  yii\redis\Connection;

class RedisConnection extends Connection {
    /**
     * Allows issuing all supported commands via magic methods.
     *
     * ```php
     * $redis->hmset('test_collection', 'key1', 'val1', 'key2', 'val2')
     * ```
     *
     * @param string $name name of the missing method to execute
     * @param array $params method call arguments
     * @return mixed
     */
    public function __call($name, $params)
    {
        $redisCommand = strtoupper(Inflector::camel2words($name, false));
        if (in_array($redisCommand, $this->redisCommands)) {
            try{
                return $this->executeCommand($redisCommand, $params);
            }catch (Exception $exception){
                $this->_socket = false;
                return $this->executeCommand($redisCommand, $params);
            }

        } else {
            return parent::__call($name, $params);
        }
    }
}