<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-03-26
 */
namespace Uniondrug\Structs;

/**
 * 分页数据结构体
 * @package Uniondrug\Structs
 */
abstract class PaginatorStruct extends Struct
{
    /**
     * @var \Uniondrug\Structs\PagingResult
     */
    public $paging;

    /**
     * @param array|null|object $data
     * @throws \Exception
     */
    public function __construct($data)
    {
        // 1. 对象实例化
        parent::__construct(null);
        $this->hasListProperty();
        $this->hasPagingProperty();
        // 2. 数据格式不合法
        if (!property_exists($data, 'items') || !$this->isIteratorAble($data->items)) {
            throw new \Exception("data for '".static::STRUCT_LIST_COLUMN."' can not iterator able");
        }
        // 3. 分页参数赋值
        $this->paging = PagingResult::factory([
            'first' => $data->first,
            'before' => $data->before,
            'current' => $data->current,
            'next' => $data->next,
            'last' => $data->last,
            'totalPages' => $data->total_pages,
            'totalItems' => $data->total_items
        ]);
        // 4. 数据列表赋值
        $this->with([static::STRUCT_LIST_COLUMN => $data->items]);
    }
}
