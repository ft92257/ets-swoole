<?php
namespace Ets\model;


abstract class RelationBase
{
    public static abstract function getMainModelClass():string;

    public static abstract function getBelongModelClass(): string;

    public static abstract function getFieldMap(): array;
}