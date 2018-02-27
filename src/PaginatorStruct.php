<?php
/**
 * 分页数据结构体
 *
 */

namespace Uniondrug\Structs;

abstract class PaginatorStruct extends Struct
{
    /**
     * @var \Uniondrug\Structs\PagingStruct
     */
    public $paging;

    /**
     * @param $data
     *
     * @return static
     */
    public static function factory($data = null)
    {
        $struct = new static();

        if (!$struct->has('body')) {
            throw new \RuntimeException('Property \'body\' for \'' . get_class($struct) . '\' must be defined');
        }
        if (substr($struct->_definition['body'], -2) != '[]') {
            throw new \RuntimeException('Property \'body\' for \'' . get_class($struct) . '\' must be defined as an array (end with [])');
        }

        $struct->paging = PagingStruct::factory([
            'first'      => $data->first,
            'before'     => $data->before,
            'current'    => $data->current,
            'next'       => $data->next,
            'last'       => $data->last,
            'totalPages' => $data->total_pages,
            'totalItems' => $data->total_items,
        ]);

        $dataType = substr($struct->_definition['body'], 0, -2);
        foreach ($data->items as $item) {
            // 只获取一层数据
            $struct->body[] = $dataType::factory($item->toArray());
        }

        return $struct;
    }
}
