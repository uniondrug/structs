<?php
/**
 * 分页器结构体
 */

namespace Uniondrug\Structs;

/**
 * Class PagerStruct
 *
 * @property int $first
 * @property int $before
 * @property int $current
 * @property int $next
 * @property int $last
 * @property int $totalPages
 * @property int $totalItems
 */
final class PagingStruct extends Struct
{
    /**
     * @var int
     */
    protected $first = 1;

    /**
     * @var int
     */
    protected $before;

    /**
     * @var int
     */
    protected $current;

    /**
     * @var int
     */
    protected $last;

    /**
     * @var int
     */
    protected $next;

    /**
     * @var int
     */
    protected $totalPages;

    /**
     * @var int
     */
    protected $totalItems;
}
