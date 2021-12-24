<?php

namespace Ets\log;

use Ets\base\Component;
use Ets\consts\EtsConst;
use Ets\coroutine\CoroutineVar;
use Ets\Ets;
use Ets\helper\ToolsHelper;

abstract class BaseTarget extends Component
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
        $traceId = CoroutineVar::getObject(EtsConst::COROUTINE_TRACE_ID);

        $msg = $message['timestamp'] . " [" . $message['level']. "][" . $message['category'] . "][".Ets::$app->getAppName(). "][traceId:".$traceId."]\n"
            . (is_string($text) ? $text : ToolsHelper::toJson($text)) ;

        return $msg;
    }

    public abstract function export();

}

?>