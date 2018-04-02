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
        // 1. 必须结构检查
        parent::__construct(null, false);
        $this->hasListProperty();
        $this->hasPagingProperty();
        // 2. 数据源检查
        if (is_array($data)) {
            $this->__constructArray($data);
        } else if (is_object($data) && $data instanceof \stdClass) {
            $this->__constructObject($data);
        }
        // 3. End
        if ($end === true) {
            $this->endWith();
        }
    }

    /**
     * 数据组入参
     * @param array $data
     */
    private function __constructArray(array $data)
    {
        // 1. 分页
        if (isset($data[static::STRUCT_PAGING_COLUMN])) {
            $this->with([static::STRUCT_PAGING_COLUMN => $data[static::STRUCT_PAGING_COLUMN]]);
        } else {
            $this->with([static::STRUCT_PAGING_COLUMN => []]);
        }
        // 2. 数据
        if (isset($data[static::STRUCT_LIST_COLUMN])) {
            $this->with([static::STRUCT_LIST_COLUMN => $data[static::STRUCT_LIST_COLUMN]]);
        } else {
            $this->with([static::STRUCT_LIST_COLUMN => []]);
        }
    }

    /**
     * 对象入参
     * @param \stdClass $data
     */
    private function __constructObject(\stdClass $data)
    {
        // 1. 数据格式不合法
        if (!$this->isIteratorAble($data->items)) {
            throw new Exception("用于属性'{$this->getClassName()}::\$".static::STRUCT_LIST_COLUMN."'的数据源不是可迭格式");
        }
        // 2. 分页参数赋值
        $this->with([
            static::STRUCT_PAGING_COLUMN => [
                'first' => $data->first,
                'before' => $data->before,
                'current' => $data->current,
                'last' => $data->last,
                'next' => $data->next,
                'limit' => $data->limit,
                'totalPages' => $data->total_pages,
                'totalItems' => $data->total_items
            ]
        ]);
        // 3. 数据列表赋值
        $this->with([static::STRUCT_LIST_COLUMN => $data->items]);
    }
}
