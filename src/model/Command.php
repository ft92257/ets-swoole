<?php
namespace Ets\model;

use Ets\base\BaseObject;
use Ets\base\EtsException;

class Command extends BaseObject implements CommandInterface
{
    public function execute($db, $sql): int
    {
        $this->query($db, $sql);

        return $db->affected_rows ?: 1;
    }

    protected function query($db, $sql)
    {
        $ret = $db->query($sql);

        if ($ret === false) {
            $error = "数据库执行错误#{$db->errno}: {$db->error}";
            throw new EtsException($error);
        }

        return $ret;
    }

    public function queryOne($db, $sql): array
    {
        $res = $this->query($db, $sql);
        
        if($res === false) {    
            throw new EtsException('查询失败！');
        }

        return $res[0] ?? [];
    }

    public function queryAll($db, $sql): array
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