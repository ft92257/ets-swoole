<?php

namespace Ets\server\handle\request;

use Ets\base\Component;
use Ets\consts\EtsConst;
use Ets\coroutine\CoroutineVar;
use Ets\server\base\console\ConsoleRequest;
use Ets\server\base\http\HttpRequest;
use Ets\server\base\RequestInterface;

class DefaultRequestHandler extends Component implements RequestHandlerInterface
{
    /**
     * @param RequestInterface $request
     */
    public function beforeRequest($request)
    {
        if ($request instanceof HttpRequest) {
            $traceId = $request->getHeader(EtsConst::HEADER_TRACE_ID);
            if (empty($traceId)) {
                $traceId = uniqid();
            }

            CoroutineVar::setObject(EtsConst::COROUTINE_TRACE_ID, $traceId);
        }

        if ($request instanceof ConsoleRequest) {
            $traceId = uniqid();
            CoroutineVar::setObject(EtsConst::COROUTINE_TRACE_ID, $traceId);

        }
    }
}
