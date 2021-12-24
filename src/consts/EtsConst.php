<?php
namespace Ets\consts;

class EtsConst
{
    // 协程变量
    const COROUTINE_POOL_WRAPPERS = 'PoolWrappers';
    const COROUTINE_MODEL_OBJECTS = 'EtsModelObjects';
    const COROUTINE_TRACE_ID = 'traceId';

    const HEADER_TRACE_ID = 'x-trace-id';

    // 返回结果常量
    const RESULT_CODE_SUCCESS = 0;
    const RESULT_CODE_BIZ_ERROR = -1;
    const RESULT_CODE_SYSTEM_ERROR = -2;
}