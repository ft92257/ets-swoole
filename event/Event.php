<?php

namespace Ets\event;


use Ets\base\BaseObject;

class Event extends BaseObject
{

    public $name;

    public $sender;

    public $handled = false;

    public $data;
}