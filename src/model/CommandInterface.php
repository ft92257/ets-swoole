<?php
namespace Ets\model;

use Ets\pool\connector\MysqlConnector;

interface CommandInterface
{
    /**
     * @param MysqlConnector $db
     * @param string $sql
     * @return int
     */
    public function execute($db, string $sql): int;

    /**
     * @param MysqlConnector $db
     * @param string $sql
     * @return array
     */
    public function queryOne($db, string $sql): array;

    /**
     * @param MysqlConnector $db
     * @param string $sql
     * @return array
     */
    public function queryAll($db, string $sql): array;

    /**
     * @param MysqlConnector $db
     * @return int
     */
    public function getLastInsertId($db): int;

    /**
     * @param MysqlConnector $db
     * @return mixed
     */
    public function begin($db);

    /**
     * @param MysqlConnector $db
     * @return mixed
     */
    public function commit($db);

    /**
     * @param MysqlConnector $db
     * @return mixed
     */
    public function rollback($db);

}