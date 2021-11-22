<?php

namespace yii2\swoole_async\basic;

use Pheanstalk\Job;
use yii2\swoole_async\models\DB;

class AsyncJob
{
    public static function getTaskId(Job $job) {
        list($taskId) = json_decode($job->getData(), true);
        return $taskId;
    }

    public static function getTaskName(Job $job) {
        list(, $taskName) = json_decode($job->getData(), true);
        return $taskName;
    }

    public static function getPutData(AsyncTask $task) {
        return json_encode([$task->taskBId, $task->getTaskName()]);
    }

    /**
     * @param DB $db
     * @return string
     */
    public static function getPutDataWithDB(DB $db){
        return json_encode([$db->getTaskBId(), $db->getName()]);
    }
}