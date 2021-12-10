<?php

namespace Ets\components\alarm;

use Ets\base\Component;
use Ets\base\EtsException;
use Ets\components\lock\Lock;
use Ets\consts\LogCategoryConst;
use Ets\Ets;
use Ets\queue\jobs\EmailAlarmJob;
use Ets\queue\Queue;
use PHPMailer\PHPMailer\PHPMailer;

class EmailAlarm extends Component implements AlarmInterface
{

    protected $mailerFactoryComponent = MailerFactory::class;

    protected $from = [];

    protected $to = [];

    protected $queueComponent = Queue::class;

    protected $lockSecond = 60;

    protected $lockComponent = Lock::class;

    /**
     * @Override
     * @return array
     */
    protected function allowInitFields()
    {
        return ['mailerFactoryComponent', 'queueComponent', 'lockSecond', 'lockComponent'];
    }

    /**
     * @return PHPMailer
     */
    protected function getMailer()
    {
        /**
         * @var MailerFactory $factory
         */
        $factory = Ets::component($this->mailerFactoryComponent);

        return $factory->getMailer();
    }

    /**
     * @return Queue
     */
    protected function getQueue()
    {
        return Ets::component($this->queueComponent);
    }

    /**
     * @return Lock
     */
    protected function getLock()
    {
        return Ets::component($this->lockComponent);
    }


    /**
     * 发送报警消息
     *
     * @param string $title 标题
     * @param string $content 内容
     */
    public function send(string $title, string $content)
    {
        $key = 'EmailAlarm:' . $title;
        if ($this->lockSecond && ! $this->getLock()->addLock($key, $this->lockSecond)) {
            return;
        }

        if (empty($this->queueComponent)) {
            $this->syncSend($title, $content);
            return;
        }

        $job = new EmailAlarmJob([
            'title' => $title,
            'content' => $content,
        ]);

        $this->getQueue()->push($job);
    }

    /**
     * 发送消息
     * @param string $title
     * @param string $content
     * @throws EtsException
     */
    public function syncSend(string $title, string $content)
    {
        if (empty($this->from[0])) {
            throw new EtsException("邮件发送人不能为空");
        }
        if (empty($this->to)) {
            throw new EtsException("邮件接收人不能为空");
        }

        $mailer = $this->getMailer();

        try {

            $mailer->setFrom($this->from[0], $this->from[1] ?? '');

            foreach ($this->to as $to) {
                if (is_array($to)) {
                    $address = $to[0];
                    $name = $to[1] ?? '';
                } else {
                    $address = $to;
                    $name = '';
                }
                $mailer->addAddress($address, $name);
            }

            $mailer->Subject = $title;
            $mailer->Body = $content;

            $mailer->send();

        } catch (\Throwable $e) {

            Ets::error("邮件发送失败：" . $e->getMessage() . '#' . $mailer->ErrorInfo, LogCategoryConst::ERROR_NORMAL);
        }
    }

}