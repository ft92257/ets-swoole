<?php

namespace Ets\server\handle\request;

use Ets\server\base\RequestInterface;

interface RequestHandlerInterface
{

    /**
     * @param RequestInterface $request
     */
    public function beforeRequest($request);
}
