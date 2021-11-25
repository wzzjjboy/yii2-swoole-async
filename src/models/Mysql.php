<?php
/**
 * Created by PhpStorm.
 * User: alan
 * Date: 2018/8/20
 * Time: 15:07
 */

namespace yii2\swoole_async\models;

use Pheanstalk\Job;
use yii\db\ActiveRecord;
use yii\db\BatchQueryResult;
use yii\behaviors\TimestampBehavior;
use yii2\swoole_async\basic\AsyncJob;

/**
 * Class Mysql
 * @package common\tasks
 * @property integer $task_id
 * @property string $task_b_id
 * @property integer $task_type
 * @property string $job_id
 * @property string $task_class
 * @property string $task_name
 * @property string $task_data
 * @property string $task_rule
 * @property integer $task_status
 * @property integer $run_count
 * @property integer $task_over
 * @property string $output
 * @property string $finish_at
 * @property string $created_at
 * @property string $updated_at
 *
 */
class Mysql extends ActiveRecord implements DB
{
    const STATUS_UNFINISHED = 0;

    const STATUS_FINISHED = 1;

    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%async_tasks_new}}';
    }

    /**
     * @return BatchQueryResult
     */

    public static function findAllForLoading()
    {
        return self::find()->where(['task_status' => self::STATUS_UNFINISHED, 'task_over' => 0])->each();
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['task_data', 'task_name', 'task_b_id', 'task_type'], 'required'],
            [['task_status', 'run_count'], 'integer'],
            [['task_name'], 'string' ,'max' => 100],
            [['task_b_id'], 'string' ,'max' => 100],
            [['output', 'task_class'], 'string' ,'max' => 200],
            [['task_rule', 'task_data'], 'string' ,'max' => 500],
            [['finish_at', 'created_at', 'updated_at'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'task_b_id' => '业务ID',
            'task_type' => '任务类型：1|Timed 2|Delay 3|Async',
            'task_name' => '任务名称',
            'data' => '任务数据',
            'output' => '执行中的输出',
            'status' => '任务状态0|未完成 1|已完成',
            'count' => '任务执行次数',
            'finish_at' => '任务完成时间',
            'created_at' => '任务创建时间',
            'updated_at' => '任务更新时间',
        ];
    }

    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => function(){
                    return date("Y-m-d H:i:s");
                },
            ]
        ];
    }

    /**
     * @return string
     */
    public function getTaskBId(): string
    {
        return $this->task_b_id;
    }

    /**
     * @return int
     */
    public function getTaskId(): int
    {
        // TODO: Implement getTaskId() method.
        return $this->task_id;
    }

    /**
     * @return string
     */
    public function getRule(): string
    {
        // TODO: Implement getRule() method.
        return $this->task_rule;
    }

    /**
     * @return string
     */
    public function getData(): string
    {
        return $this->task_data;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->task_name;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->task_status;
    }

    /**
     * @return int
     */
    public function getRunCount(): int
    {
        return intval($this->run_count);
    }

    /**
     * @return string
     */
    public function getCreatedAt(): string
    {
        return $this->created_at;
    }

    /**
     * @return string
     */
    public function getUpdatedAt(): string
    {
        return $this->updated_at;
    }

    /**
     * @param int $taskId
     * @return self|null
     */
    public static function findTask(string $taskBId, string $taskName): ?DB
    {
        return self::find()->where(['task_b_id' => $taskBId, 'task_name' =>$taskName])->one();
    }

    /**
     * @param string $bid
     * @return self|null
     */
    public static function findByBId(string $bid): ?DB
    {
        return self::find()->where(['task_b_id' => $bid])->one();
    }

    public static function generate($data, &$msg = null)
    {
        $m = new self();
        $m->load($data, '');
        if (!$m->save()){
            $msg = current($m->getFirstErrors());
            return false;
        }

        return $m;
    }

    public function taskFinished(): bool
    {
        $this->run_count += 1;
        $this->task_status = self::STATUS_FINISHED;
        $this->finish_at = date("Y-m-d H:i:s");
        return $this->save();
    }

    public function taskContinue()
    {
        $this->task_status = self::STATUS_UNFINISHED;
        $this->run_count += 1;
        return $this->save();
    }

    public function isFinish(): bool
    {
        return $this->task_status == self::STATUS_FINISHED;
    }

    public function getTaskClass(): string
    {
        return $this->task_class;
    }

    public function taskOver(): bool
    {
        $this->task_status = self::STATUS_UNFINISHED;
        $this->task_over = 1;
        return $this->save();
    }

    public function isOver(): bool
    {
        return $this->task_over == 1;
    }

    public function saveJob(string $jobId): bool
    {
       $this->job_id = $jobId;
       return $this->save(false);
    }
}