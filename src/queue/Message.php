<?php

namespace Ets\queue;

use Ets\base\BaseObject;

class Message extends BaseObject
{
    /**
     * @var array
     */
    protected $jobArrayData;

    /**
     * @var int
     */
    protected $attempt;

    protected function allowReadFields()
    {
        return ['attempt', 'jobArrayData'];
    }

    public function getJobArrayData()
    {
        return $this->jobArrayData;
    }

    /**
     * 当前已重试的次数
     *
     * @return int
     */
    public function getAttempt()
    {
        return $this->attempt;
    }

    /**
     * @param $jobArrayData
     * @param $attempt
     * @return static
     */
    public static function build(array $jobArrayData, $attempt)
    {
        return new static([
            'attempt' => $attempt,
            'jobArrayData' => $jobArrayData,
        ]);
    }
}
