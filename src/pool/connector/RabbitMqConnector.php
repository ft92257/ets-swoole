<?php
namespace Ets\pool\connector;
use Ets\base\EtsException;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitMqConnector extends BasePoolConnector
{

    protected $host;

    protected $port;

    protected $username;

    protected $password;

    protected $vhost;

    /**
     * @Override
     * @return array
     */
    protected function getSetterFields()
    {
        return [
            'host', 'port', 'username', 'password', 'vhost'
        ];
    }

    protected function allowReadFields()
    {
        return [
            'host', 'port', 'username', 'password', 'vhost'
        ];
    }

    /**
     * 连接rabbitMq/重连
     *
     * @return AMQPChannel
     * @throws EtsException
     */
    protected function connect()
    {
        $conn = new AMQPStreamConnection(
            $this->host,
            $this->port,
            $this->username,
            $this->password,
            $this->vhost
        );

        $channel = $conn->channel();

        $channel->basic_qos(null, 1, null);

        return $channel;
    }

    /**
     * @Override
     * @return mixed
     */
    public function isConnected()
    {
        return $this->instance->is_open();
    }

    public function close()
    {
        $this->instance->close();
    }

}