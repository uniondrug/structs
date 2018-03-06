<?php
/**
 * 结构体接口
 *
 */

namespace Uniondrug\Structs;

/**
 * Interface StructInterface
 *
 * @package Uniondrug\Structs
 */
interface StructInterface
{
    /**
     * 结构体静态构造方法
     *
     * @param null|array|object $data
     *
     * @return static
     */
    public static function factory($data = null);

    /**
     * 检测一个属性是否是保留属性
     *
     * @param string $name
     *
     * @return bool
     */
    public static function reserved($name);

    /**
     * 初始化结构体
     *
     * @param null|array|object $data
     *
     * @return static
     */
    public function init($data);

    /**
     * 转换成数组结构
     *
     * @return array
     */
    public function toArray();

    /**
     * 转换成JSON字符串
     *
     * @param int $options
     * @param int $depth
     *
     * @return string
     */
    public function toJson($options = 0, $depth = 512);

    /**
     * 设置属性
     *
     * @param string $name
     * @param mixed $value
     *
     * @return $this
     */
    public function set($name, $value);

    /**
     * 设置属性
     *
     * @param string $name
     * @param mixed $value
     *
     * @return $this
     */
    public function setProperty($name, $value);

    /**
     * 获取属性值
     *
     * @param string $name
     *
     * @return mixed
     */
    public function get($name);

    /**
     * 获取属性值
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getProperty($name);

    /**
     * 检测一个属性是否存在
     *
     * @param string $name
     *
     * @return mixed
     */
    public function has($name);

    /**
     * 检测一个属性是否存在
     *
     * @param string $name
     *
     * @return mixed
     */
    public function hasProperty($name);

    /**
     * 返回所有属性
     *
     * @return array
     */
    public function getProperties();
}
