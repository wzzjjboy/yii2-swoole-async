<?php
/**
 * Created by PhpStorm.
 * User: alan
 * Date: 17-12-14
 * Time: 下午3:15
 */

namespace console\controllers;


use Yii;

use yii\console\Controller;
use yii2\swoole_async\basic\Swoole;
use yii\base\InvalidConfigException;
use yii2\swoole_async\tasks\TestAsyncAsyncTask;

class TestController  extends Controller {
    public function actionHello() {
        print_r("hello");
    }

    /**
     * @return Swoole
     * @throws InvalidConfigException
     */
    public function getSwoole(){
        /** @var Swoole $swoole */
        return Yii::$app->get("swoole");
    }

    /**
     * 启动swoole
     * @throws InvalidConfigException
     */
    public function actionStart() {
        $this->getSwoole()->start();
    }

    /**
     * 停止
     * @throws InvalidConfigException
     */
    public function actionStop()
    {
        $this->getSwoole()->stop();
    }

    /**
     * 查询状态
     * @throws InvalidConfigException
     */
    public function actionStatus()
    {
        $this->getSwoole()->status();
    }

    /**
     * 重启扫热服务
     * @throws InvalidConfigException
     */
    public function actionReload()
    {
        $this->getSwoole()->reload();
    }

    /**
     * 重启服务
     * @throws InvalidConfigException
     */
    public function actionRestart()
    {
        $this->getSwoole()->restart();
    }


    public function actionAsyncTask()
    {
        TestAsyncAsyncTask::publish();
    }

}


