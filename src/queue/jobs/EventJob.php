<?php

namespace Ets\queue\jobs;

use Ets\event\BaseEvent;
use Ets\event\EventHelper;
use Ets\queue\BaseJob;

class EventJob extends BaseJob
{
    /**
     * @var string $name 事件名称
     */
    protected $name;

    /**
     * @var string $payloadString json格式数据
     */
    protected $payloadString;


    public function execute()
    {
        /**
         * @var $eventClass BaseEvent
         */
        $eventClass = EventHelper::getClassByName($this->name);
        if (! class_exists($eventClass)) {
            return;
        }

        $payload = json_decode($this->payloadString, true);
        if (empty($payload)) {
            return;
        }

        EventHelper::trigger($eventClass::build($payload));
    }
}
