<?php

namespace Ets\components\alarm;

use Ets\base\Component;
use PHPMailer\PHPMailer\PHPMailer;

class MailerFactory extends Component
{

    protected $host;

    protected $username;

    protected $password;

    protected $port;

    protected $encryption = PHPMailer::ENCRYPTION_SMTPS;

    /**
     * @var $mailer PHPMailer
     */
    protected $mailer;

    /**
     * @Override
     * @return array
     */
    protected function allowInitFields()
    {
        return ['host', 'username', 'password', 'port', 'encryption'];
    }

    public function init()
    {
        parent::init();

        $mailer = new PHPMailer(true);
        $mailer->isSMTP();
        $mailer->SMTPAuth   = true;

        $mailer->Host       = $this->host;
        $mailer->Username   = $this->username;
        $mailer->Password   = $this->password;
        $mailer->Port       = $this->port;
        $mailer->SMTPSecure = $this->encryption;

        $this->mailer = $mailer;
    }

    public function getMailer()
    {
        return $this->mailer;
    }

}