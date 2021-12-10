<?php

namespace yii2\swoole_async\basic;

use Yii;
use Pheanstalk\Job;
use Swoole\Process;
use yii\base\Component;
use Swoole\Process\Pool;
use Pheanstalk\Pheanstalk;
use yii\base\NotSupportedException;

/**
 *
 */
class Swoole extends Component implements IEngine
{
    use ResponseTrait;

    /**
     * @var ILog
     */
    public $log = 'yii2\swoole_async\basic\Log';

    /**
     * @var string
     */
    public $host = 'beanstalkd';

    /**
     * @var int
     */
    public $port = 11300;

    /**
     * @var int
     */
    public $workerNum = 1;

    /**
     * @var integer
     */
    private $runCount;

    /**
     * @var integer
     */
    public $maxRunCount = 10000;

    /**
     * @var AsyncTask
     */
    public $taskClass = 'yii2\swoole_async\basic\AsyncTask';
    /**
     * @var mixed
     */
    public $tube = 'default';

    /**
     * @var mixed|Pheanstalk
     */
    private $pheanstalk;

    /**
     * @var string
     */
    private $pidName = 'asyncTask';


    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        if (empty($this->log)) {
            $this->log = [
                'class' => Log::class,
            ];
        }
        if (is_string($this->log)){
            $this->log = [
                'class' => $this->log,
            ];
        }
        if (is_array($this->log)) {
            $this->log = Yii::createObject($this->log);
        }
        if (!$this->log instanceof ILog) {
            $this->showInvalidArgument("无效的log配置");
        }
        if (empty($this->tube)){
            $this->showInvalidArgument("无效的tube配置、不能为空");
        }
    }

    /**
     * @param AsyncTask $task
     * @return bool
     * @throws TaskException
     */
    public function publish($task): bool
    {
        if (!$task instanceof AsyncTask){
            $this->log->warning("invalid task". var_export($task, true));
            return false;
        }
        $pheanstalk = Pheanstalk::create($this->host, $this->port, 2);
        list($interval) = $task->getInterval();
        if (!$interval){
            $this->log->warning("get task:{$task->taskBId} interval get empty...");
            return false;
        }
        $taskData = AsyncJob::getPutData($task);
        $jobId = $pheanstalk->useTube($this->tube)->put($taskData, 1024, $interval, 60)->getId();
        $task->saveJobId($jobId);
        $this->log-> info("publish task:{$taskData} success.");
        return true;
    }

    /**
     * @inheritDoc
     */
    public function start():void
    {
        if (($pid = $this->isRunning())) {
            $this->showRunning($pid);
            return;
        }
        $pool = new Pool($this->workerNum);
        $pool->on("WorkerStart", function (Pool $pool, $workerId) {
            $running = true;
            /** @var Process $process */
            $process = $pool->getProcess($workerId);
            $this->log->info("onWorkerStart: workerId:{$workerId}  pid:{$process->pid}");
//            $this->savePid(posix_getppid());
            $this->pheanstalk = $pheanstalk = Pheanstalk::create($this->host);
            pcntl_signal(SIGTERM, function () use (&$running, $process) {
                $running = false;
                $this->log->info("pid:{$process->pid} 收到SIGTERM信号，准备退出...");
            });
            while ($running) {
                pcntl_signal_dispatch();
                $job = $pheanstalk->watch($this->tube)->ignore("default")->reserveWithTimeout(3);
                if (empty($job)){
                    continue;
                }
                $request = YII::$app->request;
                if ($request->hasMethod( 'setLogId')) {
                    $request->setLogId();
                }
                $this->onTask($job);
                if (($this->runCount++) > $this->maxRunCount) {
                    Process::kill($process->pid, SIGTERM);
                }
            }
            $this->log->trace("master process closed...");
            $pool->shutdown();
            $this->savePid(0);
        });
//        Process::daemon(true, false);
        $this->savePid(getmypid());
        $pool->start();
    }

    /**
     * @param AsyncTask $task
     * @param integer|false $first
     * @param null|integer|false $next
     */
    private function intervalLog(AsyncTask $task, $first, $next = null)
    {
        if ($task->rule->isTimed()){
            $msg = "calc timed task($task->taskBId) interval, first: $first, next: $next";
        } else {
            $taskType = $task->rule->isDelay() ? "delay" : "async";
            $msg = "calc {$taskType} task({$task->taskBId}) interval: $first";
        }
        $this->log->trace($msg);
    }


    public function onTask(Job $job): bool
    {
        $taskId = AsyncJob::getTaskId($job);
        $taskName = AsyncJob::getTaskName($job);
        $this->log->trace(["on task" => $job->getData()]);
        try{
            $task = $this->taskClass::findTask($taskId, $taskName);
            $task->run();
            if ($task->taskIsFinish()){
                $this->log->trace("task : ($task->taskBId) has finished " );
                $this->pheanstalk->delete($job);
                $this->log->trace("task : ($task->taskBId) has deleted... " );
                return true;
            }
            list($interval) = $task->getInterval();
            $this->intervalLog($task, $interval);
            if ($interval){
                $putData = AsyncJob::getPutData($task);
                $jobId = $this->pheanstalk->useTube($this->tube)->put($putData, 1024, $interval, 60)->getId();
                $task->saveJobId($jobId);
                $this->log->trace("task put again: ({$putData}) ... " );
            } elseif (false === $interval){
                $task->taskOver();
                $this->log->trace("task : ($task->taskBId) has over " );
            }
            $this->pheanstalk->delete($job);
            $this->log->trace("task : ($task->taskBId) has deleted... " );
            return true;
        }catch (TaskException $exception) {
            $this->handlerTaskException($exception);
            $this->pheanstalk->delete($job);
            $this->log->trace("task : ({$job->getData()}) has deleted... " );
            return false;
        }catch (\Exception $e){
            $this->handlerException($e);
            $this->pheanstalk->delete($job);
            $this->log->trace("task : ({$job->getData()}) has deleted... " );
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function stop():void
    {
        if (!($pid = $this->isRunning())) {
            $this->showTerm();
            return;
        }
        Process::kill($pid, SIGTERM);
    }

    /**
     * @inheritDoc
     */
    public function status():void
    {
        if (!($pid = $this->isRunning())) {
            $this->showTerm();
            return;
        }
        echo sprintf("MqTask正在运行：%s。。。\n", $pid);
    }

    /**
     *@inheritDoc
     */
    public function reload(): void
    {
        if (!($pid = $this->getPid())) {
            $this->showTerm();
            return;
        }
        Process::kill($pid, SIGUSR1);
    }

    /**
     * @throws NotSupportedException
     */
    public function restart():void
    {
        throw new NotSupportedException();
    }

    /**
     * @return int
     */
    private function isRunning(): int
    {
        if (!($pid = $this->getPid()) || (!Process::kill($pid, 0))) {
            return 0;
        }
        return $pid;
    }

    /**
     * @param $pid
     */
    private function savePid($pid)
    {
        file_put_contents($this->getPidFile(), $pid);
    }

    /**
     * @return int
     */
    private function getPid(): int
    {
        if (!file_exists($this->getPidFile())) {
            return 0;
        }
        return intval(file_get_contents($this->getPidFile()));
    }

    /**
     * @return string
     */
    private function getPidFile(): string
    {
        return Yii::$app->getRuntimePath() . DIRECTORY_SEPARATOR . $this->pidName .'.pid';
    }

    /**
     * @param TaskException $taskException
     */
    public function handlerTaskException(TaskException $taskException)
    {
        $this->log->warning(implode(PHP_EOL, [
            $taskException->getName(),
            $taskException->getMessage(),
            $taskException->getFile(),
            $taskException->getLine(),
            $taskException->getTraceAsString(),
        ]));
    }

    /**
     * @param \Exception $exception
     */
    public function handlerException(\Exception $exception)
    {
        $this->log->error(implode(PHP_EOL, [
            $exception->getMessage(),
            $exception->getLine(),
            $exception->getFile(),
            $exception->getTraceAsString(),
        ]));
    }
}
