<?php

namespace Ets\event;


use Ets\base\BaseObject;

abstract class BaseEvent extends BaseObject
{

    public static abstract function getName(): string;

}