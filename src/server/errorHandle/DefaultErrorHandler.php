<?php

namespace Ets\server\errorHandle;

use Ets\base\BizException;
use Ets\base\Component;
use Ets\consts\EtsConst;
use Ets\Ets;
use Ets\server\base\ResponseInterface;
use Ets\server\result\JsonResult;
use Ets\server\result\ResultInterface;

class DefaultErrorHandler extends Component implements ErrorHandlerInterface
{
    /**
     * @param \Throwable $e
     * @param ResponseInterface $response
     * @return ResultInterface
     */
    public function handleException(\Throwable $e, $response): ResultInterface
    {
        $msg = $e->getMessage() . (Ets::$app->isDebug() ? '#' . $e->getTraceAsString() : '');

        if ($e instanceof BizException) {
            $code = EtsConst::RESULT_CODE_BIZ_ERROR;
        } else {
            $code = EtsConst::RESULT_CODE_SYSTEM_ERROR;
        }

        return JsonResult::error($msg, $code);
    }
}
