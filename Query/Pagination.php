<?php

namespace Netdudes\DataSourceryBundle\Query;

/**
 * Defines a status of pagination
 */
class Pagination
{
    /**
     * Default pagination count (elements per page)
     */
    const DEFAULT_COUNT = 20;

    /**
     * The page
     *
     * @var int
     */
    private $page;

    /**
     * Elements per page
     *
     * @var int
     */
    private $count;

    /**
     * Calculated: page * count
     *
     * @var int
     */
    private $offset;

    /**
     * @param $page  int 0-indexed page
     * @param $count int Items per page
     */
    public function __construct($page = 0, $count = self::DEFAULT_COUNT)
    {
        $this->page = $page;
        $this->count = $count > 0 ? $count : self::DEFAULT_COUNT;

        // The item offset form the beginning of the item collection
        $this->offset = $page * $count;
    }

    /**
     * @return mixed
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @return mixed
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param int $page
     */
    public function setPage($page)
    {
        $this->page = $page;
        $this->offset = $this->page * $this->count;
    }

    /**
     * @return mixed
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param int $count
     */
    public function setCount($count)
    {
        $this->count = $count > 0 ? $count : self::DEFAULT_COUNT;
        $this->offset = $this->page * $this->count;
    }
}
