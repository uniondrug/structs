<?php
/**
 * 分页数据结构体
 *
 */

namespace Uniondrug\Structs;

abstract class PaginatorStruct extends Struct
{
    /**
     * @var \Uniondrug\Structs\PagerStruct
     */
    public $page;

    /**
     * @param $data
     *
     * @return static
     */
    public static function factory($data = null)
    {
        $struct = new static();

        if (!$struct->has('data')) {
            throw new \RuntimeException('Property \'data\' for \'' . get_class($struct) . '\' must be defined');
        }
        if (substr($struct->_definition['data'], -2) != '[]') {
            throw new \RuntimeException('Property \'data\' for \'' . get_class($struct) . '\' must be defined as an array (end with [])');
        }

        $struct->page = PagerStruct::factory([
            'first'      => $data->first,
            'before'     => $data->before,
            'current'    => $data->current,
            'next'       => $data->next,
            'last'       => $data->last,
            'totalPages' => $data->total_pages,
            'totalItems' => $data->total_items,
        ]);

        $dataType = substr($struct->_definition['data'], 0, -2);
        foreach ($data->items as $item) {
            // 只获取一层数据
            $struct->data[] = $dataType::factory($item->toArray());
        }

        return $struct;
    }
}
