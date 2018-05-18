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
 * @property int $limit
 * @property int $totalPages
 * @property int $totalItems
 * @package Uniondrug\Structs
 */
class PagingResult extends Struct
{
    /**
     * 第一页页码
     * @var int
     */
    protected $first;
    /**
     * 上一页页码
     * @var int
     */
    protected $before;
    /**
     * 下一页页码
     * @var int
     */
    protected $current;
    /**
     * 最后一页页码
     * @var int
     */
    protected $last;
    /**
     * 下一页页码
     * @var int
     */
    protected $next;
    /**
     * 每页数量
     * @var int
     */
    protected $limit = 0;
    /**
     * 总页数
     * @var int
     */
    protected $totalPages;
    /**
     * 总记录数
     * @var int
     */
    protected $totalItems;
}
