<?php

namespace Ets\queue;


class BroadcastQueue extends Queue
{

    /**
     * 广播
     *
     * @param BaseJob $job
     * @param $routeKeys
     */
    public function broadcast(BaseJob $job, $routeKeys)
    {
        // todo
    }


}