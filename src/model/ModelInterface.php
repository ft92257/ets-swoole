<?php
namespace Ets\model;


interface ModelInterface
{
    public function getDbComponent(): string;

    /**
     * 主键值，修改操作时需用到
     *
     * @return string|array
     */
    public function getPrimaryKey();

    public function getTableName(): string;

    public function getSlaveConfig(): array;

    public function getCreateTimeValue(): array;

    public function getUpdateTimeValue(): array;
}