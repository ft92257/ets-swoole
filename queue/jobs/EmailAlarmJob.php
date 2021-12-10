<?php

namespace Ets\queue\jobs;

use Ets\components\alarm\EmailAlarm;
use Ets\Ets;
use Ets\queue\BaseJob;

class EmailAlarmJob extends BaseJob
{
    protected $title;

    protected $content;

    protected $alarmComponent = EmailAlarm::class;

    /**
     * @return EmailAlarm
     */
    protected function getAlarm()
    {
        return Ets::component($this->alarmComponent);
    }

    public function execute()
    {
        $this->getAlarm()->syncSend($this->title, $this->content);
    }
}
