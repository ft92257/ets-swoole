<?php

namespace Ets\queue\jobs;

use Ets\event\BaseEvent;
use Ets\event\EventHelper;
use Ets\helper\ToolsHelper;
use Ets\queue\BaseJob;

class EventJob extends BaseJob
{
    /**
     * @var string $name 事件名称
     */
    public $name;

    /**
     * @var string $payloadString json格式数据
     */
    public $payloadString;


    public function execute()
    {
        /**
         * @var $eventClass BaseEvent
         */
        $eventClass = EventHelper::getClassByName($this->name);
        if (! ToolsHelper::classExists($eventClass)) {
            return;
        }

        $payload = json_decode($this->payloadString, true);
        if (empty($payload)) {
            return;
        }

        EventHelper::localTrigger(new $eventClass($payload));
    }
}
