<?php
namespace Ets\model;

interface CommandInterface
{
    public function execute($db, $sql): int;

    public function queryOne($db, $sql): array;

    public function queryAll($db, $sql): array;

    public function getLastInsertId($db): int;

    public function begin($db);

    public function commit($db);

    public function rollback($db);

}