<?php
/**
 * 列表结构体基础。
 * 定义一个列表结构体时，必须定义一个属性`body`，类型是数组。
 */
namespace Uniondrug\Structs;

abstract class ListStruct extends Struct
{
    /**
     * override parent toArray()
     * @return array
     */
    public function toArray()
    {
        /**
         * @var array           $data
         * @var StructInterface $body
         */
        $data = [];
        foreach ($this->body as $body) {
            $data[] = $body->toArray();
        }
        return $data;
    }

    /**
     * @param array $data
     *
     * @return static
     */
    public static function factory($data = null)
    {
        // param format check
        $invalid = true;
        if (is_array($data)) {
            $invalid = false;
        } else if (is_object($data)) {
            if ($data instanceof \ArrayAccess || $data instanceof \Iterator) {
                $invalid = false;
            }
        }
        if ($invalid) {
            throw new \RuntimeException('data must be an array');
        }
        // create
        $struct = new static();
        if (!$struct->has('body')) {
            throw new \RuntimeException('Property \'body\' of \''.get_class($struct).'\' must be defined');
        }
        if (substr($struct->_definition['body'], -2) != '[]') {
            throw new \RuntimeException('Property \'body\' of \''.get_class($struct).'\' must be defined as an array (end with [])');
        }
        $dataType = substr($struct->_definition['body'], 0, -2);
        $isStruct = is_a($dataType, StructInterface::class, true);
        foreach ($data as $item) {
            if ($isStruct) {
                $struct->body[] = $dataType::factory($item);
            } else {
                $struct->body[] = $item;
            }
        }
        return $struct;
    }
}
