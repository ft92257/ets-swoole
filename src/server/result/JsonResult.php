<?php
namespace Ets\server\result;

use Ets\base\BaseObject;
use Ets\base\EtsException;
use Ets\consts\EtsConst;
use Ets\helper\ToolsHelper;

class JsonResult extends BaseObject implements ResultInterface
{

    protected $code;

    protected $msg;

    protected $data;

    /**
     * @Override
     * @return array
     */
    protected function allowInitFields()
    {
        return ['code', 'msg', 'data'];
    }

    public static function build(string $result)
    {
        $json = json_decode($result, true);
        if (empty($json)) {
            throw new EtsException('build result error: ' . $result, EtsConst::RESULT_CODE_SYSTEM_ERROR);
        }

        return new JsonResult([
            'data' => $json['data'],
            'msg' => $json['msg'],
            'code' => $json['code']
        ]);
    }

    public function getData()
    {
        return $this->data;
    }

    public function checkError($successCode = EtsConst::RESULT_CODE_SUCCESS)
    {
        if ($this->code != $successCode) {
            ToolsHelper::throws($this->msg);
        }

        return $this;
    }

    public static function success($data, $msg = 'success', $code = EtsConst::RESULT_CODE_SUCCESS)
    {
        return new JsonResult([
            'data' => $data,
            'msg' => $msg,
            'code' => $code
        ]);
    }

    public static function error($msg, $code = EtsConst::RESULT_CODE_BIZ_ERROR, $data = null)
    {
        return new JsonResult([
            'data' => $data,
            'msg' => $msg,
            'code' => $code
        ]);
    }

    /**
     * @Override
     * @return string
     */
    public function getOutputString(): string
    {
        return ToolsHelper::toJson(get_object_vars($this));
    }

}
