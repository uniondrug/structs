<?php
/**
 * 列表结构体基础。
 *
 * 定义一个列表结构体时，必须定义一个属性`body`，类型是数组。
 */

namespace Uniondrug\Structs;

abstract class ListStruct extends Struct
{
    /**
     * @param array $data
     *
     * @return static
     */
    public static function factory($data = null)
    {
        if (!is_array($data) || empty($data)) {
            throw new \RuntimeException('data must be an array');
        }

        $struct = new static();

        if (!$struct->has('body')) {
            throw new \RuntimeException('Property \'body\' of \'' . get_class($struct) . '\' must be defined');
        }
        if (substr($struct->_definition['body'], -2) != '[]') {
            throw new \RuntimeException('Property \'body\' of \'' . get_class($struct) . '\' must be defined as an array (end with [])');
        }

        $dataType = substr($struct->_definition['body'], 0, -2);
        $isStruct = is_a($dataType, StructInterface::class, true);
        foreach ($data as $item) {
            if ($isStruct) {
                $struct->body[] = $dataType::factory($item);
            } else {
                echo "a\n";
                $struct->body[] = $item;
            }
        }

        return $struct;
    }
}
