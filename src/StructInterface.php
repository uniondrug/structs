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
     * @param null|array|object $data
     * @return static
     */
    public static function factory($data = null);

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
}
