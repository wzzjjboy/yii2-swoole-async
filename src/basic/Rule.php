<?php

namespace yii2\swoole_async\basic;

use Yii;
use yii\base\Model;

/**
 *
 */
class Rule extends Model
{
//    /**
//     * @var Task
//     */
//    public $task;

    /**
     * @var string
     */
    public $type;

    /**
     * @var void
     */
    public $rule;


    private $allType = ["async", "delay", "timed"];


    public function rules(): array
    {
        return [
            [['type'], 'required'],
            [['type'], 'string', 'max' => 20],
            ['type', 'check']
        ];
    }

    public function check()
    {
        if (!in_array($this->type, $this->allType)){
            $this->addError("type", "无效的任务规则类型:{$this->type}");
            return false;
        }
    }

    public function attributeLabels(): array
    {
        return [
            'type' => '任务类型',
            'delayRule' => '延迟规则',
            'timedRule' => '定时规则',
        ];
    }


    /**
     * @param AsyncTask $task
     * @return array|bool
     */
    public function getInterval(AsyncTask $task):array
    {
        return $this->getParse($task)->parse();
    }


    public function isAsync(): bool
    {
        return $this->type == "async";
    }

    public function isDelay(): bool
    {
        return $this->type == "delay";
    }

    public function isTimed(): bool
    {
        return $this->type == "timed";
    }

    /**
     * @param $task
     * @return IParse
     */
    private function getParse($task): IParse
    {
        $class = (str_replace('\basic', '\parsers', __NAMESPACE__)) . '\\' . ucfirst($this->type) . "RuleParse";

        if (!class_exists($class)){
            AsyncTask::showError("无不存在的规则解析类:{$class}");
        }
        return Yii::createObject([
            'class' => $class,
            'rule' => $this->rule ?: [],
            'task' => $task,
        ]);
    }

    public function toJson()
    {
        return json_encode([
            'type' => $this->type,
            'class' => get_class($this),
            'rule' => $this->rule
        ], JSON_UNESCAPED_UNICODE);
    }
}
