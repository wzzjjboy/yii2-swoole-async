Yii2 SWOOLE ASYNC
================================

1. 特性

2. 安装

   composer require alan/yii2-swoole-async:dev-master

3. 配置

     建议添加到common\config\main-local.php文件添加

     ```php
     //测试环境配置
     'dcache_data_center' => [
             'url' => 'http://10.154.157.157:10003',
             'key' => 'admvir8359MMjukd~644',
     ],
     //正式环境配置
     'dcache_data_center' => [
         'url' => 'https://kpc.wetax.com.cn',
         'key' => 'o4e0-hpoe875wimmv12@7',
     ],
     ```
     
    - 添加dcache表对应的配置(此配置用于查询表结构，用于ORM操作)

     ```php
    'db' => [
          'class' => 'yii\db\Connection',
          'dsn' => 'mysql:host=10.105.201.137;port=3307;dbname=providers',
          'username' => 'root',
          'password' => 'a6CFu3VH6OYW',
          'charset' => 'utf8',
          'tablePrefix' => 'gpi_',
          'commandClass' => 'yii2\swoole_async\components\DbCommand',
      ],
     'swoole' => [
         'class'            => 'yii2\swoole_async\basic\Swoole',
         'host'             => '127.0.0.1',
         'port'             => '9501',
         'taskWorkerNum'    => '10',
         'daemonize'        => false,
     ],
     ```
     
    - 添加task表

      ```mysql
      CREATE TABLE `gpi_async_tasks` (
        `task_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '任务ID',
        `task_name` varchar(20) NOT NULL DEFAULT '' COMMENT '任务名称',
        `task_class` varchar(100) NOT NULL DEFAULT '' COMMENT '任务类名',
        `task_data` varchar(500) NOT NULL DEFAULT '' COMMENT '任务数据JSON',
        `task_rule` varchar(500) NOT NULL DEFAULT '' COMMENT '任务规则JSON',
        `task_status` smallint(6) NOT NULL DEFAULT '0' COMMENT '状态 0|未完成 1|已完成',
        `run_count` int(11) NOT NULL DEFAULT '0' COMMENT '任务执行次数',
        `task_over` int(11) NOT NULL DEFAULT '0' COMMENT '任务结束 0|未结束 1|已结束',
        `output` varchar(200) NOT NULL DEFAULT '' COMMENT '执行中的输出',
        `finish_at` datetime DEFAULT NULL COMMENT '任务完成时间',
        `created_at` datetime NOT NULL COMMENT '创建时间',
        `updated_at` datetime NOT NULL COMMENT '更新时间',
        PRIMARY KEY (`task_id`),
        KEY `task_status` (`task_status`,`task_over`) USING BTREE
      ) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='定时任务记录表';
      ```
      

   -   添加启动脚本

     ```php
     use Yii;
     use yii\console\Controller;
     use yii2\swoole_async\basic\Swoole;
     use yii\base\InvalidConfigException;
     use yii2\swoole_async\tasks\TestAsyncAsyncTask;
     
     class SwooleController  extends Controller {
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
     ```

     

   - 启动

     ```php
     php yii swoole/start
     ```

     

   - 书写任务类

     ```php
     use yii2\swoole_async\basic\AsyncTask;
     
     class TestAsyncAsyncTask extends AsyncTask
     {
     
         public $rule = [
             'type' => 'async',
         ];
     
         /**
          * @param mixed $data
          * @return bool
          */
         public function consume($data): bool
         {
             // TODO: Implement consume() method.
             print_r(__METHOD__);
             print_r($data);
             return true;
         }
     }
     ```

     

   - 投递任务

     ```php
     TestAsyncAsyncTask::publish();
     ```

     