<?php
namespace Ets\server\base\console;

use Ets\base\Component;
use Ets\consts\EtsConst;
use Ets\server\result\JsonResult;

class ConsoleController extends Component
{
    /**
     * @var ConsoleRequest
     */
    protected $request;

    /**
     * @var ConsoleResponse
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
        $result = JsonResult::error($msg, $code, $data);

        $this->response->finish($result->getOutputString());
    }

    public function getCommandParam(int $index, $default = null)
    {
        return $this->request->getCommandParam($index, $default);
    }

}
