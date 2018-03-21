<?php
/**
 * 分页结构体基类，用于输出分页数据。
 * 将 Phalcon 的 Paginator 结果转换成结构体。
 * 定义分页结构体时，必须定义一个属性`body`，类型为数组。内容是分页输出的格式。
 */

namespace Uniondrug\Structs;

abstract class PaginatorStruct extends Struct
{
    /**
     * @var \Uniondrug\Structs\PagingStruct
     */
    public $paging;

    /**
     * @param object $data Paginator Object
     *
     * @return static
     */
    public static function factory($data = null)
    {
        // if (!is_object($data) || !property_exists($data, 'items') || property_exists($data, 'first')) {
        //    throw new \RuntimeException('input data must be an Paginator object');
        // }
        $struct = new static();

        if (!$struct->has('body')) {
            throw new \RuntimeException('Property \'body\' for \'' . get_class($struct) . '\' must be defined');
        }
        if (substr($struct->getDefinition('body'), -2) != '[]') {
            throw new \RuntimeException('Property \'body\' for \'' . get_class($struct) . '\' must be defined as an array (end with [])');
        }

        // 分页结构
        $struct->paging = PagingStruct::factory([
            'first'      => $data->first,
            'before'     => $data->before,
            'current'    => $data->current,
            'next'       => $data->next,
            'last'       => $data->last,
            'totalPages' => $data->total_pages,
            'totalItems' => $data->total_items,
        ]);

        // 数据
        $dataType = substr($struct->getDefinition('body'), 0, -2);
        foreach ($data->items as $item) {
            $struct->body[] = $dataType::factory($item);
        }

        return $struct;
    }
}
