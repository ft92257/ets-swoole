<?php
namespace Ets\model;

use Ets\base\BaseObject;

class Pagination extends BaseObject
{
    protected $page;

    protected $size;

    protected $isGetCount;

    /**
     * @param $page
     * @param int $size
     * @param bool $isGetCount
     * @return static
     */
    public static function build($page, $size = 10, $isGetCount = true)
    {
        $pagination = new static([
            'page' => $page,
            'size' => $size,
            'isCount' => $isGetCount,
        ]);

        return $pagination;
    }

    public function isGetCount()
    {
        return $this->isGetCount;
    }

    public function getOffset(): int
    {
        $this->page = max(1, $this->page);

        return ($this->page - 1) * $this->size;
    }

    public function getLimit(): string
    {
        return $this->size;
    }

    public function getResult($data, $count = 0)
    {
        return [
            'page' => $this->page,
            'size' => $this->size,
            'maxPage' => ceil($count / $this->size),
            'count' => $count,
            'data' => $data
        ];
    }

}