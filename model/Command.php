<?php
namespace Ets\model;

use Ets\base\BaseObject;
use Ets\consts\LogCategoryConst;
use Ets\Ets;
use Ets\base\EtsException;
use Ets\pool\wrapper\MysqlWrapper;

class Command extends BaseObject
{
    protected $_sql;

    // 数据库连接
    /**
     * @var $db MysqlWrapper
     */
    protected $db;

    public $modelClass;

    public function getSql()
    {
        return $this->_sql;
    }

    public function setSql($sql)
    {
        $this->_sql = $sql;

        return $this;
    }

    public function execute()
    {
        $this->query();

        return $this->db->affected_rows ?: 1;
    }

    protected function query()
    {
        $ret = $this->db->query($this->getSql());

        if ($ret === false) {
            $error = "数据库执行错误#{$this->db->errno}: {$this->db->error}";
            throw new EtsException($error);
        }

        return $ret;
    }

    public function queryOne()
    {
        $res = $this->query();
        
        if($res === false) {    
            $this->errorLog();
            throw new EtsException('查询失败！');
        }

        return $res[0] ?? [];
    }

    public function queryOneBySql($sql)
    {
        $res = $this->db->query($sql);

        if($res === false) {
            $this->errorLog();
            throw new EtsException('查询失败！');
        }

        return $res[0] ?? [];
    }


    public function queryAll()
    {
        $res = $this->query();
        
        if($res === false) {    
            $this->errorLog();
            throw new EtsException('查询失败！');
        }

        return $res;
    }

    public function getLastInsertId()
    {
        return $this->db->insert_id;
    }

    protected function errorLog()
    {
        Ets::error("数据库错误：" . $this->_sql . '#' . $this->db->error . '#' . $this->db->errno, LogCategoryConst::ERROR_SQL) ;
    }

    public function begin()
    {
        return $this->db->begin();
    }

    public function commit()
    {
        return $this->db->commit();
    }

    public function rollback()
    {
        return $this->db->rollback();
    }

}