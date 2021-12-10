<?php

namespace Ets\server\router;

use Ets\server\base\RequestInterface;

interface RouterInterface
{
    /**
     * @param RequestInterface $request
     * @return RouterResult
     */
    public function parse($request): RouterResult;

}

?>