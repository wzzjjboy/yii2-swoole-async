<?php


namespace yii2\swoole_async\basic;


trait ResponseTrait
{
    public function showRunning($pid)
    {
        $this->show("AsyncTask正在运行 pid:%s。。。", $pid);
    }

    public function showTerm()
    {
        $this->Show("AsyncTask未启动");
    }

    public function showSignTerm($pid)
    {

        $this->Show("pid:%s 收到SIGTERM信号，准备退出...", $pid);
    }

    public function  showInvalidArgument($tmp, ...$args)
    {
        $this->show($tmp, ...$args);
    }

    private function Show($tmp, ...$args)
    {
        echo sprintf($tmp . PHP_EOL, ...$args);
    }
}