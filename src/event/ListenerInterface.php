<?php

namespace Ets\event;


interface ListenerInterface
{
    /**
     * @return array [event class => execute method]
     */
    public function getEventActionMap(): array;
}