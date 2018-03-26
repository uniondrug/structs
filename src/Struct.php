<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-03-26
 */
namespace Uniondrug\Structs;

/**
 * @package Uniondrug\Structs
 */
abstract class Struct implements StructInterface
{
    /**
     * 分页与列表时的主数据字段名
     */
    const STRUCT_LIST_COLUMN = "body";

    /**
     * 分页时记录分页参数字段名
     */
    const STRUCT_PAGING_COLUMN = "paging";

    /**
     * 属性列表
     * 值true表示为只读属性, false表示读写属性
     * <code>
     * $_properties = [
     *     'NS\\ClassName' => [
     *         'public' => false,
     *         'protected' => true
     *     ]
     * ];
     * </code>
     * @var array
     */
    private static $_properties = [];

    /**
     * 反射记录
     * $_reflections = [
     *     'NS\\ClassName' => [
     *         'public' => Property{},
     *         'protected' => Property{}
     *     ]
     * ]
     * @var array
     */
    private static $_reflections = [];

    /**
     * 属性与值关系
     * $attributes = {
     *     'id' => 1,
     *     'sub' => StructInterface{
     *         'id' => 0
     *     }
     * }
     * @var array
     */
    private $attributes = [];

    /**
     * 反射类名
     * @var string
     */
    private $className;

    /**
     * 结构体静态构造方法
     * @param null|array|object $data 工厂模式入参
     * @return static
     */
    public static function factory($data = null)
    {
        return new static($data);
    }

    /**
     * 构造Struct结构体
     * @param null|array|object $data 工厂模式入参
     */
    public function __construct($data)
    {
        $this->initRefelection();
        $this->initDefaultValue();
        if ($data !== null) {
            $this->with($data);
        }
    }

    /**
     * @param string $name
     * @return mixed
     * @throws \Exception
     */
    public function & __get($name)
    {
        if (!$this->hasProperty($name)) {
            throw new \Exception("can not call undefined property '{$this->className}::\${$name}'");
        }
        return $this->attributes[$name];
    }

    /**
     * @param string $name
     * @param mixed  $value
     * @throws \Exception
     */
    public function __set($name, $value)
    {
        if (!$this->hasProperty($name)) {
            throw new \Exception("can not set undefined property '{$this->className}::\${$name}' value");
        }
        if ($this->isReadonlyProperty($name)) {
            throw new \Exception("can not set readonly property '{$this->className}::\${$name}' value");
        }
        $this->setValue($name, $value);
    }

    /**
     * 按属性名读取Property对象
     * @param string $name
     * @return Property
     */
    public function getProperty($name)
    {
        return self::$_reflections[$this->className][$name];
    }

    /**
     * 是否已定义列表必须定段
     * @throws \Exception
     */
    public function hasListProperty()
    {
        if (!isset(self::$_properties[$this->className][static::STRUCT_LIST_COLUMN])) {
            throw new \Exception("property '{$this->className}::\$".static::STRUCT_LIST_COLUMN."' is not defined");
        }
        /**
         * @var Property $property
         */
        $property = self::$_reflections[$this->className][static::STRUCT_LIST_COLUMN];
        if (!$property->isStruct()) {
            throw new \Exception("property '{$this->className}::\$".static::STRUCT_LIST_COLUMN."' must implement with StructInterface");
        }
        /**
         * comment format
         */
        if (!$property->isArray()) {
            throw new \Exception("property '{$this->className}::\$".static::STRUCT_LIST_COLUMN."' must be array, end with '[]'.");
        }
    }

    /**
     * 是否已定义分页必须定段
     * @throws \Exception
     */
    public function hasPagingProperty()
    {
        if (!isset(self::$_properties[$this->className][static::STRUCT_PAGING_COLUMN])) {
            throw new \Exception("property '{$this->className}::\$".static::STRUCT_PAGING_COLUMN."' is not defined");
        }
        /**
         * @var Property $property
         */
        $property = self::$_reflections[$this->className][static::STRUCT_PAGING_COLUMN];
        if (!$property->isStruct() || $property->isArray()) {
            throw new \Exception("property '{$this->className}::\$".static::STRUCT_PAGING_COLUMN."' must extends with PagingResult");
        }
    }

    /**
     * 检查指定字段是否已定义
     * @param string $name
     * @return bool
     */
    public function hasProperty($name)
    {
        return isset(self::$_properties[$this->className][$name]);
    }

    /**
     * 检查指定数据是否允许迭代
     * @param $data
     * @return bool
     */
    public function isIteratorAble($data)
    {
        if (is_array($data)) {
            return true;
        }
        if (is_object($data)) {
            if ($data instanceof \ArrayAccess) {
                return true;
            }
            if ($data instanceof \Iterator) {
                return true;
            }
        }
        return false;
    }

    /**
     * 检查属性是否为只读
     * @param string $name
     * @return bool
     */
    public function isReadonlyProperty($name)
    {
        return self::$_properties[$this->className][$name];
    }

    /**
     * 转为数组输出
     * @return array
     */
    public function toArray()
    {
        $data = $this->attributes;
        return $this->parseArray($data);
    }

    /**
     * 转JSON字符串
     * @param int $options
     * @param int $depth
     * @return string
     */
    public function toJson($options = 0, $depth = 512)
    {
        try {
            return \GuzzleHttp\json_encode($this->toArray(), $options, $depth);
        } catch(\Exception $e) {
            return '{}';
        }
    }

    /**
     * 绑定Struct数据
     * @param mixed $data
     */
    public function with($data)
    {
        $type = gettype($data);
        switch ($type) {
            case 'array' :
                $this->withArray($data);
                break;
            case 'object' :
                $this->withObject($data);
                break;
        }
    }

    /**
     * 使用数组赋值
     * @param array $data
     */
    private function withArray(array $data)
    {
        foreach (self::$_properties[$this->className] as $name => $readonly) {
            // unset
            if (!isset($data[$name])) {
                continue;
            }
            // setter
            $this->setValue($name, $data[$name]);
        }
    }

    /**
     * 使用对象赋值
     * @param object $data
     */
    private function withObject($data)
    {
        foreach (self::$_properties[$this->className] as $name => $readonly) {
            // 1. from property
            if (isset($data->{$name})) {
                $this->setValue($name, $data->{$name});
                continue;
            }
            // 2. from execute property
            $method = 'get'.ucfirst($name);
            if (method_exists($data, $method)) {
                $this->setValue($name, $data->{$method}());
                continue;
            }
            // 3. not support
        }
    }

    /**
     * 设置属性值
     * @param string $name
     * @param mixed  $value
     * @throws \Exception
     */
    private function setValue($name, $value)
    {
        /**
         * @var Property $property
         */
        $property = $this->getProperty($name);
        $propertyType = $property->getType();
        // 1. 数组字段赋值
        if ($property->isArray()) {
            // 1.2 不可迭代的数据类型
            if (!$this->isIteratorAble($value)) {
                throw new \Exception("value for '{$this->className}::{$name}' should be array");
            }
            // 1.3 结构体递归
            if ($property->isStruct()) {
                foreach ($value as $val) {
                    $this->attributes[$name][] = call_user_func_array("{$propertyType}::factory", [$val]);
                }
            } else {
                foreach ($value as $val) {
                    $property->validate($val);
                    $this->attributes[$name][] = $val;
                }
            }
            // 1.4 completed
            return;
        }
        // 2. 线性字段赋值
        if ($property->isStruct()) {
            $this->attributes[$name] = call_user_func_array("{$propertyType}::factory", [$value]);
        } else {
            $property->validate($value);
            $this->attributes[$name] = $value;
        }
    }

    /**
     * 设置各属性的默认值
     */
    private function initDefaultValue()
    {
        foreach (self::$_properties[$this->className] as $name => $readonly) {
            // 1. 清除属性定义, 让__get/__set生效
            unset($this->{$name});
            /**
             * @var Property $property
             */
            $property = self::$_reflections[$this->className][$name];
            $this->setValue($name, $property->getDefaultValue());
        }
    }

    /**
     * 初始化反射数据
     */
    private function initRefelection()
    {
        // 1. 当前Struct完整类名
        $this->className = get_class($this);
        // 2. 伪单例控制
        if (isset(self::$_reflections[$this->className])) {
            return;
        }
        // 3. 反射过程
        self::$_properties[$this->className] = [];
        self::$_reflections[$this->className] = [];
        $reflect = new \ReflectionClass($this);
        $namespace = $reflect->getNamespaceName();
        foreach ($reflect->getProperties() as $prop) {
            // 3.1 属性过滤/只记录Struct和子类的属性
            if (!is_a($prop->class, Struct::class, true)) {
                continue;
            }
            // 3.2 过滤非Public/Protected属性
            if (!$prop->isPublic() && !$prop->isProtected()) {
                continue;
            }
            // 3.3 加入反射记录
            self::$_properties[$this->className][$prop->name] = $prop->isProtected();
            self::$_reflections[$this->className][$prop->name] = new Property($prop, $namespace, $this->{$prop->name});
        }
    }

    /**
     * 以递归模式将结构转为数组
     * @param array $data
     * @return array
     */
    private function parseArray($data)
    {
        foreach ($data as $name => & $value) {
            if (is_array($value)) {
                $value = $this->parseArray($value);
            } else if (is_object($value) && method_exists($value, 'toArray')) {
                $value = $value->toArray();
            }
        }
        return $data;
    }
}
