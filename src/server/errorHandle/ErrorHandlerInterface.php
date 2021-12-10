<?php

namespace Ets\server\errorHandle;

use Ets\server\base\ResponseInterface;
use Ets\server\result\ResultInterface;

interface ErrorHandlerInterface
{
    /**
     * @param \Throwable $e
     * @param ResponseInterface $response
     * @return ResultInterface
     */
    public function handleException(\Throwable $e, $response): ResultInterface;
}
