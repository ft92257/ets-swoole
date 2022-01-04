<?php
namespace Swoole\Coroutine;


class Client
{
    private $connection;

    private $timeout;

    private $packageEof;

    public function __construct($mode)
    {
    }

    /**
     * @param $host
     * @param $port
     * @param $timeout
     * @return bool
     */
    public function connect($host, $port, $timeout)
    {
        $fp = stream_socket_client($host . ':' . $port, $errNo, $errStr, $timeout);

        $this->connection = $fp;

        return $fp;
    }

    public function set($config)
    {
        $this->timeout = $config['timeout'] ?? 5;

        $this->packageEof = $config['package_eof'] ?? "\r\n\r\n";
    }

    public function send($body)
    {
        fwrite($this->connection, $body);
    }

    /**
     * @return string
     */
    public function recv()
    {
        $resp = '';
        $start = time();
        $timeout = $this->timeout;
        $lineLen = 16384;
        $ending = $this->packageEof;

        stream_set_timeout($this->connection, $timeout);
        while (! feof($this->connection) && (time() < $start + $timeout)) {
            $line = stream_get_line($this->connection, $lineLen, $ending);
            $resp .= $line;
            if ($ending !== null && strlen($line) < $lineLen) {
                break;
            }
        }

        return $resp;
    }

    public function close()
    {
        fclose($this->connection);
    }
}
