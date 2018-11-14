<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-03-26
 */
namespace Uniondrug\Structs;

/**
 * @package Uniondrug\Structs
 */
interface StructInterface
{
    /**
     * 结构体静态构造方法
     * @param null|array|object $data 入参数据类型
     * @param bool              $end  将入参赋值之后是否检查必须字段
     * @return static
     */
    public static function factory($data = null, $end = true);

    /**
     * 转换成数组结构
     * @return array
     */
    public function toArray();

    /**
     * 转换成JSON字符串
     * @param int $options
     * @param int $depth
     * @return string
     */
    public function toJson($options = 0, $depth = 512);

    /**
     * 入参是否传值
     * @param string $name
     * @return bool
     */
    public function isInput($name);
}
