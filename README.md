Yii2 SWOOLE ASYNC
================================

1. 特性

2. 安装

   composer require alan/yii2-swoole-async:v2.0.0

3. 配置

     > 1.0.x有消费者卡死的Bug, 解决的办法是升级了swoole到4.5.*的版本，使用Pool代替原来的swoole_service。
   >
     > 关于1.0.0升级到2.0的配置的变更，由于2.x采用Yii2框架自身的日志组件又要在消费任务的时候刷新LogId, 所以需要替换文件类，配置层级 composents => logs => targets => class: yii2\mq_task\components\FileTarget
     
     建议添加到common\config\main-local.php文件添加
     
     ```php
     //测试环境配置
     'dcache_data_center' => [
             'url' => 'http://xxxxxxxxxxxxx',
             'key' => 'xxxxxxxxx',
     ],
     //正式环境配置
     'dcache_data_center' => [
         'url' => 'https://xxxxxxxxxxx',
         'key' => 'xxxxxxxxxxxxxxxxx',
   ],
    ```
    
    - 添加dcache表对应的配置(此配置用于查询表结构，用于ORM操作)
    
     ```php
    ['db' => [
          'class' => 'yii\db\Connection',
          'dsn' => 'mysql:host=xxxxxxxxx;port=3307;dbname=xxxxxxxxx',
          'username' => 'xxxxxxxxxx',
          'password' => 'xxxxxxxxxxxx',
          'charset' => 'utf8',
          'tablePrefix' => 'xxxxxx',
          'commandClass' => 'xxxxxxxxxxxxxxx',
      ],
     'swoole' => [
         'class'            => 'yii2\swoole_async\basic\Swoole',
         'host'             => '127.0.0.1', //beanstalkd host
         'port'             => '11300', //beanstalkd port
     ],
    
   'request' => [ //添加行为
        'as beforeAction' => [
            'class' => \yii2\swoole_async\components\LogIDBehavior::class, //替换
            'name'  => 'swooleTask',
        ],
    ],  
    
    'components' => [ //替换console的日志类
    	'log' => [
        	[
            'class' => 'yii2\swoole_async\components\FileTarget', //替换
            'levels' => ['warning', 'info','error'],
            'categories' =>['server'],
            'exportInterval' => 1, //这里注重，太大日志则不能及时刷入文件，太小会影响性能
            'logVars' => [],
            'logFile' => __DIR__ . '/../runtime/logs/server_'. date("ymd") .'.log',
            'maxFileSize' => 1024 * 500,//以kb为单位的
            'maxLogFiles' =>5
          ],
      ],
   ]]
    ```

   - 添加task表
   
     ```mysql
     CREATE TABLE `gpi_async_tasks` (
       `task_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '任务ID',
       `job_id` varchar(100) NOT NULL DEFAULT '' COMMENT 'Job ID',
       `task_b_id` varchar(100) NOT NULL DEFAULT '' COMMENT '任务业务ID',
       `task_type` int(11) NOT NULL DEFAULT 0 COMMENT '任务类型：1|Timed 2|Delay 3|Async',
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
       KEY `idx_job_not_run` (`task_type`, `created_at`, `run_count`) USING BTREE,
       KEY `idx_job_status` (`task_type`, `created_at`, `task_status`, `task_over`) USING BTREE
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
   
     