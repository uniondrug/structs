<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-03-26
 */
namespace Uniondrug\Structs;

/**
 * 分页结构体
 * @property int $first
 * @property int $before
 * @property int $current
 * @property int $next
 * @property int $last
 * @property int $totalPages
 * @property int $totalItems
 * @package Uniondrug\Structs
 */
final class PagingResult extends Struct
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