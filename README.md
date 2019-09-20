Yii2 SWOOLE ASYNC
================================

1. 安装

   composer require alan/yii2-swoole-async:dev-master

2. 配置

   - 添加dcache访问的http地址，本工具基于dcache的http访问接口编写

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
      //测试和正式环境只要表结构一致就只需要添加一处即可
      'db_tars_order' => [
          'class' => 'yii\db\Connection',
          'dsn' => 'mysql:host=10.105.1.106;port=3306;dbname=order_0',
          'username' => 'gordon',
          'password' => '4qYAEZ6scVNYPLTWRviT',
          'charset' => 'utf8',
          'enableSchemaCache' => true,
          'schemaCacheDuration' => 86400, // time in seconds
      ],
      'db_tars_relationship' => [
          'class' => 'yii\db\Connection',
          'dsn' => 'mysql:host=10.105.1.106;port=3306;dbname=relationship_0',
          'username' => 'gordon',
          'password' => '4qYAEZ6scVNYPLTWRviT',
          'charset' => 'utf8',
          'enableSchemaCache' => true,
          'schemaCacheDuration' => 86400, // time in seconds
      ],
      ```
