<?php

namespace Ets\log;

use Ets\base\Component;
use Ets\Ets;
use Ets\helper\ToolsHelper;

class Logger extends Component
{
    const LEVEL_INFO = 'info';
    const LEVEL_ERROR = 'error';

    protected $messages = [];

    protected $targets = [
        [
            'class' => FileTarget::class,
            'levels' => ['info', 'error'],
            'categories' => ['application'],
            'logFileFunc' => 'logs/application.log',
        ],
        [
            'class' => EmailTarget::class,
            'levels' => ['error'],
            'categories' => ['email', 'email\*'],
            'title' => 'Error Alarm',
        ],
    ];

    protected $targetConfig = [];

    protected $isOpen = true;

    // 最大字符长度
    protected $maxLength = 2000;

    /**
     * @Override
     * @return array
     */
    protected function allowInitFields()
    {
        return ['targets', 'isOpen', 'maxLength'];
    }

    public function init()
    {
        parent::init();

        $targetConfig = [];
        foreach ($this->targets as $i => $target) {
            if (empty($target['categories'])) {
                $target['categories'] = ['ALL'];
            }

            foreach ($target['categories'] as $cate) {
                if (empty($target['levels'])) {
                    $target['levels'] = [self::LEVEL_INFO, self::LEVEL_ERROR];
                }
                foreach ($target['levels'] as $level) {
                    $targetConfig[$level][$cate][] = $i;
                }
            }
        }

        $this->targetConfig = $targetConfig;
    }

    public function log($message, $level, $category)
    {
        if (! $this->isOpen) {
            // 不记录日志
            return;
        }

        if (! is_string($message)) {
            $text = ToolsHelper::toJson($message);
        } else {
            $text = $message;
        }

        $text = substr($text, 0, $this->maxLength);

        $this->messages[] = [
            'level' => $level,
            'category' => $category,
            'text' => $text,
            'timestamp' => date('Y-m-d H:i:s'),
        ];
    }

    public function flush()
    {
        if (empty($this->messages)) {
            return;
        }

        $messages = $this->messages;
        $this->messages = [];

        foreach ($messages as $value) {
            $this->setTargetMessage($value);
        }

        foreach ($this->targets as $i => $target) {
            if (empty($target['messages'])) {
                continue;
            }

            // 锁定日志内容
            $targetMessages = $target['messages'];
            // 不影响其他协程, 重置内容
            $this->targets[$i]['messages'] = [];

            try {

                $this->getTarget($target, $i)->setMessages($targetMessages)->export();

            } catch (\Throwable $e) {
                //
            }
        }
    }

    /**
     * @param $target
     * @param $i
     * @return BaseTarget
     */
    protected function getTarget($target, $i)
    {
        return Ets::$app->getComponentByConfig($target, 'logTarget-' . $i);
    }

    protected function setTargetMessage($value)
    {
        // 根据消息获取日志记录器索引
        $idxAll = $this->targetConfig[$value['level']]['ALL'] ?? [];

        $pos = strpos($value['category'], '\\');
        $cate = $pos ? substr($value['category'], 0, $pos) . '\\*' : $value['category'];
        $idxCate = $this->targetConfig[$value['level']][$cate] ?? [];

        $idx = array_merge($idxAll, $idxCate);
        if (! empty($idx)) {
            foreach ($idx as $index) {
                $this->targets[$index]['messages'][] = $value;
            }
        }
    }
}