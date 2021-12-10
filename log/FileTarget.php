<?php

namespace Ets\log;

use Ets\helper\FileHelper;
use Swoole\Coroutine;

class FileTarget extends BaseTarget
{
    /**
     * @var $logFileFunc callable
     */
    protected $logFileFunc;

    protected $dirMode = 0775;

    /**
     * @Override
     * @return array
     */
    protected function allowInitFields()
    {
        return ['logFileFunc'];
    }

    public function export()
    {
        $text = $this->getLogText();

        $logFile = call_user_func($this->logFileFunc);

        $logPath = dirname($logFile);
        if (! is_dir($logPath)) {
            FileHelper::createDirectory($logPath, $this->dirMode, true);
        }

        Coroutine::writeFile($logFile, $text, FILE_APPEND);
    }

}

?>