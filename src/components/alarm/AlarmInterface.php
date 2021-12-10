<?php

namespace Ets\components\alarm;

interface AlarmInterface
{

    /**
     * 发送消息
     * @param string $title
     * @param string $content
     */
    public function send(string $title, string $content);

}