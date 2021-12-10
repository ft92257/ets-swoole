<?php
namespace Ets\server\base\tcp;

use Ets\consts\EtsConst;
use Ets\server\base\Controller;
use Ets\server\result\JsonResult;

class TcpController extends Controller
{
    /**
     * @var TcpRequest
     */
    protected $request;

    /**
     * @var TcpResponse
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
    public function success($data, $msg = 'success', $code = EtsConst::RESULT_CODE_SUCCESS)
    {
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
        $result = JsonResult::success($data, $msg, $code);

        $this->response->finish($result->getOutputString());
    }

    public function getJsonParam($key, $default = null)
    {
        return $this->request->getJsonParam($key, $default);
    }

}
