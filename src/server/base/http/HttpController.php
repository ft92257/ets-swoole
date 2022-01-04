<?php
namespace Ets\server\base\http;

use Ets\consts\EtsConst;
use Ets\server\base\Controller;
use Ets\server\result\JsonResult;

class HttpController extends Controller
{
    /**
     * @var HttpRequest
     */
    protected $request;

    /**
     * @var HttpResponse
     */
    protected $response;

    /**
     * @Override
     * @return array
     */
    protected function allowInitFields()
    {
        return ['request', 'response'];
    }

    /**
     * @param $data
     * @param string $msg
     * @param int $code
     */
    public function success($data = null, $msg = 'success', $code = EtsConst::RESULT_CODE_SUCCESS)
    {
        $this->response->setHeader('Content-type', 'application/json; charset=UTF-8');

        $result = JsonResult::success($data, $msg, $code);

        $this->response->finish($result->getOutputString());
    }

    /**
     * @param $msg
     * @param int $code
     * @param mixed $data
     */
    public function error($msg, $code = EtsConst::RESULT_CODE_BIZ_ERROR, $data = null)
    {
        $this->response->setHeader('Content-type', 'application/json; charset=UTF-8');

        $result = JsonResult::error($msg, $code, $data);

        $this->response->finish($result->getOutputString());
    }

    public function getPostParam($key, $default = null)
    {
        return $this->request->getPostParam($key, $default);
    }

    public function getJsonParam($key, $default = null)
    {
        return $this->request->getJsonParam($key, $default);
    }

    public function getQueryParam($key, $default = null)
    {
        return $this->request->getQueryParam($key, $default);
    }

    public function getHeader($key, $default = null)
    {
        return $this->request->getHeader($key, $default);
    }

}
