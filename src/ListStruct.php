<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-03-26
 */
namespace Uniondrug\Structs;

/**
 * 列表数据结构体
 * @package Uniondrug\Structs
 */
abstract class ListStruct extends Struct
{
    /**
     * @param null|array|object $data 入参数据类型
     * @param bool              $end  将入参赋值之后是否检查必须字段
     * @throws \Exception
     */
    public function __construct($data, $end = true)
    {
        // 1. 对象实例化
        parent::__construct(null, false);
        $this->hasListProperty();
        // 2. 数据格式不合法
        if (!$this->isIteratorAble($data)) {
            throw new \Exception("data for '".static::STRUCT_LIST_COLUMN."' can not iterator able");
        }
        // 3. 数据赋值
        $this->with([static::STRUCT_LIST_COLUMN => $data]);
        // 4. end
        if ($end === true){
            $this->endWith();
        }
    }
}
