<?php

namespace yii2\swoole_async\basic;

use Pheanstalk\Job;

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
}