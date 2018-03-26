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
     * @validator(options={min:1})
     */
    protected $first = 1;

    /**
     * @var int
     * @validator(options={min:0})
     */
    protected $before;

    /**
     * @var int
     * @validator(options={min:0})
     */
    protected $current;

    /**
     * @var int
     * @validator(options={min:0})
     */
    protected $last;

    /**
     * @var int
     * @validator(options={min:0})
     */
    protected $next;

    /**
     * @var int
     * @validator(options={min:0})
     */
    protected $totalPages;

    /**
     * @var int
     * @validator(options={min:0})
     */
    protected $totalItems;
}
