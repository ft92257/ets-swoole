<?php
namespace Ets\consts;

class LogCategoryConst
{
    const ERROR_ALARM = 'email';
    const ERROR_REDIDS = 'email\redis';
    const ERROR_QUEUE = 'email\queue';
    const ERROR_NORMAL = 'application';

    const SQL_ERROR = 'sql\error';
    const SQL_EXECUTE = 'sql\execute';
    const SQL_QUERY = 'sql\query';

}