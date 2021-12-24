<?php

namespace Ets\server\handle\error;

use Ets\base\BizException;
use Ets\base\Component;
use Ets\consts\EtsConst;
use Ets\consts\LogCategoryConst;
use Ets\Ets;
use Ets\server\base\http\HttpResponse;
use Ets\server\result\JsonResult;
use Ets\server\result\ResultInterface;

class HttpJsonErrorHandler extends Component implements ErrorHandlerInterface
{
    /**
     * 限http服务使用
     *
     * @param \Throwable $e
     * @param HttpResponse $response
     * @return ResultInterface
     */
    public function handleException(\Throwable $e, $response): ResultInterface
    {
        $msg = $e->getMessage() . (Ets::$app->isDebug() ? '#' . $e->getTraceAsString() : '');

        if ($e instanceof BizException) {
            $code = EtsConst::RESULT_CODE_BIZ_ERROR;
        } else {
            $code = EtsConst::RESULT_CODE_SYSTEM_ERROR;
            Ets::error("系统错误：" . $e->getMessage(). ' #' . $e->getTraceAsString(), LogCategoryConst::ERROR_ALARM);
        }

        $response->setHeader('Content-type', 'application/json; charset=UTF-8');

        return JsonResult::error($msg, $code);
    }
}
