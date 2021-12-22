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
     * 触发本地事件
     *
     * @param BaseEvent $event
     * @throws EtsException
     */
    public static function localTrigger($event)
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
     * 跨服务发送事件
     *
     * @param BaseEvent $event
     * @param string $routingKey mq的routing key，默认全部广播模式
     */
    public static function broadcastTrigger($event, $routingKey = '')
    {
        $job = new EventJob([
            'name' => $event->getName(),
            'payloadString' => ToolsHelper::toJson($event->toArray()),
        ]);

        self::getQueueEvent()->broadcast($job, $routingKey);
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
             * @var BaseEvent|string $eventClass
             */
            foreach ($eventMap as $eventClass => $method) {

                if (ToolsHelper::classExists($eventClass) && $eventClass::getName() == $eventName) {
                    return $eventClass;
                }
            }
        }

        return '';
    }
}