<?php

namespace Ets\event;


use Ets\base\BaseObject;
use Ets\base\Component;

abstract class BaseEvent extends Component
{

    /**
     * @var mixed $payload
     */
    protected $payload;

    public static abstract function getName(): string;

    /**
     * @param $payload
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;
    }

    /**
     * @return array
     */
    public function getPayload()
    {
        if (! is_array($this->payload)) {

            if (is_object($this->payload) && $this->payload instanceof BaseObject) {

                return $this->payload->toArray();
            }

            return (array) $this->payload;
        }

        return $this->payload;
    }

    public static function build($payload)
    {
        $event = new static();
        $event->setPayload($payload);

        return $event;
    }
}