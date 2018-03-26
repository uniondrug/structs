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
     * @param null|array|object $data 入参数据类型
     * @param bool              $end  将入参赋值之后是否检查必须字段
     * @throws Exception
     */
    public function __construct($data, $end = true)
    {
        // 1. 对象实例化
        parent::__construct(null, false);
        $this->hasListProperty();
        $this->hasPagingProperty();
        // 2. 数据格式不合法
        if (!property_exists($data, 'items') || !$this->isIteratorAble($data->items)) {
            throw new Exception("用于属性'{$this->getClassName()}::\$".static::STRUCT_LIST_COLUMN."'的数据源不是可迭格式");
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
        // 5. End
        if ($end === true) {
            $this->endWith();
        }
    }
}
