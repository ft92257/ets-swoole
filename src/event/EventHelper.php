<?php

namespace Ets\event;

use Ets\base\EtsException;
use Ets\Ets;
use Ets\helper\ToolsHelper;
use Ets\queue\BroadcastQueue;
use Ets\queue\jobs\EventJob;

class EventHelper
{

    /**
     * @param BaseEvent $event
     * @throws EtsException
     */
    public static function trigger($event)
    {
        if (! ($event instanceof BaseEvent)) {
            throw new EtsException("event参数需继承BaseEvent类");
        }

        $listeners = self::getListeners();

        foreach ($listeners as $listener) {
            $eventMap = $listener->getEventActionMap();
            if (empty($eventMap)) {
                continue;
            }

            foreach ($eventMap as $eventClass => $method) {
                if (get_class($event) == $eventClass) {
                    try {
                        $listener->$method($event);
                    } catch (\Throwable $e) {

                    }
                }
            }
        }
    }


    /**
     * @return BroadcastQueue
     */
    protected static function getQueueEvent()
    {
        return Ets::component(BroadcastQueue::class);
    }

    /**
     * @param BaseEvent $event
     * @param string $microServerRouteKeys
     */
    public static function microServerTrigger($event, $microServerRouteKeys = '')
    {
        $job = new EventJob([
            'name' => $event->getName(),
            'payloadString' => ToolsHelper::toJson($event->getPayload()),
        ]);

        self::getQueueEvent()->broadcast($job, $microServerRouteKeys);
    }

    /**
     * @return ListenerInterface[]
     */
    protected static function getListeners()
    {
        return Ets::$app->getComponentsBySuperClass(ListenerInterface::class);
    }

    /**
     * @param $eventName
     * @return string
     */
    public static function getClassByName($eventName): string
    {
        $listeners = self::getListeners();

        foreach ($listeners as $listener) {
            $eventMap = $listener->getEventActionMap();
            if (empty($eventMap)) {
                continue;
            }

            /**
             * @var BaseEvent $eventClass
             */
            foreach ($eventMap as $eventClass => $method) {
                if ($eventClass::getName() == $eventName) {
                    return $eventClass;
                }
            }
        }

        return '';
    }
}