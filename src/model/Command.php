<?php
namespace Ets\model;

use Ets\base\Component;
use Ets\base\EtsException;
use Ets\consts\LogCategoryConst;
use Ets\Ets;
use Ets\event\EventHelper;
use Ets\event\events\SqlErrorEvent;
use Ets\pool\connector\MysqlConnector;

class Command extends Component implements CommandInterface
{
    // 是否记录执行日志
    protected $isLogExecute = false;

    // 是否记录查询日志
    protected $isLogQuery = false;

    /**
     * @override
     * @return array
     */
    protected function allowInitFields()
    {
        return ['isLogExecute', 'isLogQuery'];
    }

    /**
     * @param MysqlConnector $db
     * @param $sql
     * @return int
     */
    public function execute($db, string $sql): int
    {
        $this->query($db, $sql);

        if ($this->isLogExecute) {
            Ets::info($sql, LogCategoryConst::SQL_EXECUTE);
        }

        return $db->affected_rows ?: 1;
    }

    protected function query($db, string $sql)
    {
        $ret = $db->query($sql);

        if ($ret === false) {
            $error = "数据库执行错误#{$db->errno}: {$db->error}";

            Ets::error([$sql, $error], LogCategoryConst::SQL_ERROR);

            EventHelper::localTrigger(new SqlErrorEvent(['sql' => $sql, 'error' => $error]));

            throw new EtsException($error);
        }

        if ($this->isLogQuery) {
            Ets::info($sql, LogCategoryConst::SQL_QUERY);
        }

        return $ret;
    }

    public function queryOne($db, string $sql): array
    {
        $res = $this->query($db, $sql);
        
        if($res === false) {    
            throw new EtsException('查询失败！');
        }

        return $res[0] ?? [];
    }

    public function queryAll($db, string $sql): array
    {
        $res = $this->query($db, $sql);
        
        if($res === false) {    
            throw new EtsException('查询失败！');
        }

        return $res;
    }

    public function getLastInsertId($db): int
    {
        return $db->insert_id;
    }

    public function begin($db)
    {
        return $db->begin();
    }

    public function commit($db)
    {
        return $db->commit();
    }

    public function rollback($db)
    {
        return $db->rollback();
    }

}