<?php

namespace Ets\log;


use Ets\components\alarm\EmailAlarm;
use Ets\Ets;
use Ets\components\alarm\AlarmInterface;

class EmailTarget extends BaseTarget
{

    protected $title;

    protected $titleWithEnv = true;

    protected $titleWithAppName = true;

    protected $alarmComponent = EmailAlarm::class;

    /**
     * @return AlarmInterface
     */
    public function getAlarm()
    {
        return Ets::component($this->alarmComponent, false);
    }

    /**
     * @Override
     * @return array
     */
    protected function allowInitFields()
    {
        return ['title', 'alarmComponentName', 'titleWithEnv', 'titleWithAppName'];
    }


    public function export()
    {
        if (! $this->getAlarm()) {
            return;
        }

        $title = '';
        if ($this->titleWithEnv) {
            $title .= strtoupper(Ets::$app->getEnv()) . '-';
        }
        if ($this->titleWithAppName) {
            $title .= Ets::$app->getAppName() . '-';
        }
        $title .= $this->title;

        $this->getAlarm()->send($title, $this->getLogText());
    }

}

?>