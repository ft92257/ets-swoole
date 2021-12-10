<?php
namespace Swoole\Coroutine\Http;

use Ets\base\BizException;

class Client
{
    public $body;
    public $errCode;
    public $statusCode;

    private $headers = [];
    private $options;
    private $host;
    private $port;
    private $ssl;

    public function __construct(string $host, int $port, bool $ssl = false)
    {
        $this->host = $host;
        $this->port = $port;
        $this->ssl = $ssl;
    }

    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }

    public function set($options)
    {
        $this->options = $options;
    }

    public function get(string $query)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->options['timeout']);
        unset($this->options['timeout']);

        $headers = $this->getFixedHeaders();
        if ( ! empty($headers) ){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $url = ($this->ssl ? 'https://' : 'http://') . $this->host . (in_array($this->port, [80,443]) ? '' : (':' . $this->port)) .  $query;

        if (stripos($url, "https://") !== false) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $options = $this->options;
        if (! empty($options)) {
            foreach ($options as $key => $value) {
                curl_setopt($ch, $key, $value);
            }
        }

        $sContent = curl_exec($ch);

        $aStatus = curl_getinfo($ch);
        curl_close($ch);

        $this->body = $sContent;
        $this->statusCode = intval($aStatus['http_code']);
    }

    public function getFixedHeaders()
    {
        $ret = [];
        foreach ($this->headers as $key => $head) {
            $ret[] = $key . ': ' . $head;
        }

        return $ret;
    }

    public function post(string $query, $data)
    {
        $ch = curl_init();

        // 设置超时时间
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->options['timeout']);
        unset($this->options['timeout']);

        $url = ($this->ssl ? 'https://' : 'http://') . $this->host . (in_array($this->port, [80,443]) ? '' : (':' . $this->port)) .  $query;

        // https请求跳过证书验证
        if (stripos($url, "https://") !== false) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $headers = $this->getFixedHeaders();
        if ( !empty($headers) ){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $options = $this->options;
        if (! empty($options)) {
            foreach ($options as $key => $value) {
                curl_setopt($ch, $key, $value);
            }
        }

        $sContent = curl_exec($ch);

        $errMsg = curl_error($ch);
        $aStatus = curl_getinfo($ch);
        curl_close($ch);

        $this->body = $sContent;
        $this->statusCode = intval($aStatus['http_code']);

        if (! $sContent && $errMsg) {
            $e = new BizException('调用第三方服务错误: '. $url . $errMsg);
            throw $e;
        }
    }

    public function close()
    {

    }

    public function addFile(string $path, string $name, string $filename = null, string $mimeType = null, int $offset = 0, int $length = 0)
    {
        //暂不支持
    }
}
