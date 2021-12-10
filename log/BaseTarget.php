<?php

namespace Ets\log;

use Ets\base\BaseObject;
use Ets\Ets;
use Ets\helper\ToolsHelper;

abstract class BaseTarget extends BaseObject
{

    protected $messages = [];

    public function setMessages(array $messages)
    {
        $this->messages = $messages;

        return $this;
    }

    protected function getLogText()
    {
        $text = implode("\n", array_map([$this, 'formatMessage'], $this->messages)) . "\n";

        return $text;
    }

    protected function formatMessage($message)
    {
        $text = $message['text'];

        $msg = $message['timestamp'] . " [" . $message['level']. "][" . $message['category'] . "][".Ets::$app->getAppName(). "]\n"
            . (is_string($text) ? $text : ToolsHelper::toJson($text)) ;

        return $msg;
    }

    public abstract function export();

}

?>