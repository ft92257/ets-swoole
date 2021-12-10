<?php

namespace Ets\service\client;


use Ets\base\BaseObject;
use Ets\base\EtsException;
use Ets\Ets;
use Ets\helper\ToolsHelper;
use Swoole\Coroutine\Http\Client;

class HttpClient extends BaseObject
{
    const METHOD_POST = 'post';
    const METHOD_GET = 'get';
    const METHOD_XML_POST = 'xmlPost';
    const METHOD_JSON_POST = 'jsonPost';

    protected $host;

    protected $path;

    /**
     * @var array|string $params
     */
    protected $params;

    protected $headers;

    protected $timeout = 5;


    /**
     * @var array
     * ssl_cert_file
     * ssl_key_file
     */
    protected $cert = [];

    /**
     * @var array
     * path 本地文件路径
     * name 参数名
     * filename 文件名
     */
    protected $files = [];

    protected $logRate = 100;

    protected $checkStatusCode = true;

    protected $checkResult = true;

    // 使用xmlPost时，是否自动转换数组参数为xml格式
    protected $paramsToXml = true;

    // 使用xmlPost时转换的xml根节点
    protected $xmlRootTag = '';

    // 使用jsonPost时是否自动转换数组参数为json格式
    protected $paramsToJson = true;


    protected $result;

    protected $statusCode;

    protected $errorMsg;


    /**
     * @Override
     * @return array
     */
    protected function allowInitFields()
    {
        return [
            'host', 'path', 'params', 'headers', 'timeout',
            'cert', 'files', 'logRate', 'checkResult', 'checkStatusCode',
            'paramsToXml', 'paramsToJson', 'xmlRootTag'
        ];
    }

    public function getUrl()
    {
        return $this->host . $this->path;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function setHost($host)
    {
        $this->host = $host;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setHeader($key, $value)
    {
        $this->headers[$key] = $value;
    }

    protected function parseUrl($pathWithQuery = true)
    {
        $urlInfo = parse_url($this->getUrl());

        if (empty($urlInfo['scheme'])) {
            $urlInfo['scheme'] = 'http';
        }

        $urlInfo['isSsl'] = $urlInfo['scheme'] == 'https';

        if (empty($urlInfo['port'])) {
            $urlInfo['port'] = $urlInfo['scheme'] == 'https' ? 443 : 80;
        }

        if (empty($urlInfo['path'])) {
            $urlInfo['path'] = '/';
        }

        if(! empty($urlInfo['query'])) {
            $urlInfo['path'] .= '?'.$urlInfo['query'];
        }

        return $urlInfo;
    }

    public function post()
    {
        $urlInfo = $this->parseUrl();

        $cli = new Client($urlInfo['host'], $urlInfo['port'], $urlInfo['isSsl']);

        if (! empty($this->files)) {
            foreach ($this->files as $file) {
                if (file_exists($file['path'])) {
                    $cli->addFile($file['path'], $file['name'], null, $file['filename'] ?? null);
                }
            }
        }

        if (! empty($this->headers)) {
            $cli->setHeaders($this->headers);
        }

        if (!empty($this->timeout)) {
            $cli->set(['timeout' => $this->timeout]);
        }

        if (! empty($this->cert)) {
            $cli->set([
                'ssl_cert_file' => $this->cert['ssl_cert_file'],
                'ssl_key_file' => $this->cert['ssl_key_file'],
            ]);
        }

        // todo
        Ets::info([
            'url' => $this->getUrl(),
            'params' => $this->params,
            'headers' => $this->headers
        ], 'curl');

        $cli->post($urlInfo['path'], $this->params);

        $this->result = $cli->body;

        $this->errorMsg = $cli->errCode ? socket_strerror($cli->errCode) : '';

        $this->statusCode = $cli->statusCode;

        $cli->close();

        if ($this->checkStatusCode) {
            $this->checkStatusCode();
        }

        if ($this->checkResult) {
            $this->checkResult();
        }

        return $this->result;
    }

    public function xmlPost()
    {
        $this->setHeader('Content-type', 'application/xml');

        if ($this->paramsToXml) {
            $this->params = ToolsHelper::arrayToXml($this->params, $this->xmlRootTag);
        }

        return $this->post();
    }

    public function jsonPost()
    {
        $this->setHeader('Content-type', 'application/json');

        if ($this->paramsToJson) {
            $this->params = ToolsHelper::toJson($this->params);
        }

        return $this->post();
    }

    protected function checkResult()
    {
        if (! $this->result && $this->errorMsg) {

            throw new EtsException('HttpClient return error: ' . $this->errorMsg);
        }
    }

    protected function checkStatusCode()
    {
        if (intval($this->statusCode) != 200) {
            throw new EtsException('HttpClient return code error: ' . $this->statusCode);
        }
    }

    public function get()
    {
        $urlInfo = $this->parseUrl();

        $cli = new Client($urlInfo['host'], $urlInfo['port'], $urlInfo['isSsl']);
        if (! empty($this->headers)) {
            $cli->setHeaders($this->headers);
        }

        $cli->set([ 'timeout' => $this->timeout ]);
        $cli->get($urlInfo['path']);

        $this->result = $cli->body;
        $this->errorMsg = $cli->errCode ? socket_strerror($cli->errCode) : '';
        $this->statusCode = $cli->statusCode;

        $cli->close();

        if ($this->checkStatusCode) {
            $this->checkStatusCode();
        }

        if ($this->checkResult) {
            $this->checkResult();
        }

        return $this->result;
    }


}