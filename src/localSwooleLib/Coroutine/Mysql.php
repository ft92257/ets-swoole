<?php
namespace Swoole\Coroutine;

use Ets\base\EtsException;

class Mysql
{
    private $mysql;
    public $connected = false;
    public $errno;
    public $error;

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    function __call($name, $arguments)
    {
        $ret = call_user_func_array([$this->mysql, $name], $arguments);

        return $ret;
    }

    function __get($name)
    {
        if ($name == 'insert_id') {
            $res = $this->query('SELECT LAST_INSERT_ID() AS id');
            if($res === false) {
                return 0;
            }

            return $res[0]['id'] ?? 0;
        }
        if ($name == 'affected_rows') {
            return 1;
        }

        throw new EtsException($name . '属性不存在');
    }

    public function query($sql)
    {
        $result = $this->mysql->query($sql);

        if (is_bool($result)) {
            return $result;
        }

        $data = [];
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $data[] = $row;
        }

        return $data;
    }

    public function connect($config)
    {
        $mysql = new \mysqli(
            $config['host'],
            $config['user'],
            $config['password'],
            $config['database'],
            $config['port']
        );

        $this->mysql = $mysql;

        $this->connected = true;

        return $mysql;
    }
}
