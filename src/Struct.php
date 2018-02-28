<?php
/**
 * 结构体，用于定于交互数据结构。
 */

namespace Uniondrug\Structs;

abstract class Struct implements StructInterface
{
    /**
     * 避免重复解析
     *
     * @var bool
     */
    protected static $_initialized = false;

    /**
     * 保护属性列表，这些属性是只读的属性
     *
     * @var array
     */
    protected static $_protected = [];

    /**
     * 结构体定义，在实例化时通过反射自动创建
     *
     * @var array
     */
    protected static $_definition = [];

    /**
     * 结构体属性的默认值，实例化的时候保存进去
     *
     * @var array
     */
    protected static $_defaults = [];

    /**
     * 保留属性，不暴露
     *
     * @var array
     */
    protected static $_reserved = ['_initialized', '_definition', '_protected', '_reserved', '_filters', '_defaults'];

    /**
     * 类型过滤器，只支持如下类型，或者结构体，或者数组
     *
     * @var array
     */
    protected static $_filters = [
        'string'  => FILTER_SANITIZE_STRING,
        'int'     => FILTER_VALIDATE_INT,
        'integer' => FILTER_VALIDATE_INT,
        'bool'    => FILTER_VALIDATE_BOOLEAN,
        'boolean' => FILTER_VALIDATE_BOOLEAN,
        'float'   => FILTER_VALIDATE_FLOAT,
        'double'  => FILTER_VALIDATE_FLOAT,
    ];

    /**
     * 构造函数，初始化结构体。
     *
     * @param null|array|object $data
     */
    public function __construct($data = null)
    {
        if (!static::$_initialized) {
            static::_initialize();
        }

        if ($data !== null) {
            $this->init($data);
        }
    }

    /**
     * 静态构造方法
     *
     * @param null|array|object $data
     *
     * @return static
     */
    public static function factory($data = null)
    {
        return new static($data);
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public static function reserved($name)
    {
        return in_array($name, static::$_reserved);
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public static function defined($name)
    {
        return array_key_exists($name, static::$_definition);
    }

    /**
     * 从一个对象或者数组初始化结构体
     *
     * @param array|object $data
     */
    public function init($data)
    {
        foreach (static::$_definition as $property => $type) {
            if (is_array($data) && isset($data[$property])) {
                $this->$property = $this->_convert($data[$property], $type, static::$_defaults[$property]);
            } elseif (is_object($data) && property_exists($data, $property)) {
                $this->$property = $this->_convert($data->$property, $type, static::$_defaults[$property]);
            }
        }
    }

    /**
     * Alias of setProperty()
     *
     * @param $name
     * @param $value
     *
     * @return $this
     */
    public function set($name, $value)
    {
        return $this->setProperty($name, $value);
    }

    /**
     * 通过set()方法设置属性
     *
     * @param $name
     * @param $value
     *
     * @return $this
     */
    public function setProperty($name, $value)
    {
        if (!$this->hasProperty($name)) {
            throw new \RuntimeException('Property \'' . $name . '\' not exists');
        }

        if ($this->_readonly($name)) {
            throw new \RuntimeException('Property \'' . $name . '\' is readonly');
        }

        $this->$name = $this->_convert($value, static::$_definition[$name], static::$_defaults[$name]);

        return $this;
    }

    /**
     * Alis of getProperty()
     *
     * @param $name
     *
     * @return mixed
     */
    public function get($name)
    {
        return $this->getProperty($name);
    }

    /**
     * 通过get()方法获取属性
     *
     * @param $name
     *
     * @return mixed
     */
    public function getProperty($name)
    {
        if (!$this->hasProperty($name)) {
            throw new \RuntimeException('Property \'' . $name . '\' not exists');
        }

        return $this->$name;
    }

    /**
     * alias of hasProperty()
     *
     * @param $name
     *
     * @return bool
     */
    public function has($name)
    {
        return $this->hasProperty($name);
    }

    /**
     * 检测一个属性是否存在
     *
     * @param $name
     *
     * @return bool
     */
    public function hasProperty($name)
    {
        return static::defined($name);
    }

    /**
     * 获取所有的公共属性
     *
     * @return array
     */
    public function getProperties()
    {
        return array_keys(static::$_definition);
    }

    /**
     * 转换成数组
     *
     * @return array
     */
    public function toArray()
    {
        $data = [];
        foreach ($this->getProperties() as $property) {
            $data[$property] = $this->_value($this->$property);
        }

        return $data;
    }

    /**
     * 转换成JSON字符串
     *
     * @param int $options
     * @param int $depth
     *
     * @return string
     */
    public function toJson($options = 0, $depth = 512)
    {
        return json_encode($this->toArray(), $options, $depth);
    }

    /**
     * 禁止访问非公共属性
     *
     * @param $name
     *
     * @return mixed
     */
    final public function __get($name)
    {
        if ($this->hasProperty($name)) {
            return $this->$name;
        }

        throw new \RuntimeException('Property \'' . $name . '\' not exists');
    }

    /**
     * 禁止访问非公共属性
     *
     * @param $name
     * @param $value
     */
    final public function __set($name, $value)
    {
        if (!$this->hasProperty($name)) {
            throw new \RuntimeException('Property \'' . $name . '\' not exists');
        }
        if ($this->_readonly($name)) {
            throw new \RuntimeException('Property \'' . $name . '\' is readonly');
        }

        $this->$name = $this->_convert($value, static::$_definition[$name]);
    }

    /**
     * 是否是只读属性
     *
     * @param $name
     *
     * @return bool
     */
    protected function _readonly($name)
    {
        return in_array($name, static::$_protected);
    }

    /**
     * 通过反射获取定义的属性
     *
     * 结构体的属性类型只能是：
     *  标量：string/int/bool/float
     *  数组：单一结构，其组成也只能是标量，或者结构体
     */
    protected static function _initialize()
    {
        $reflection = new \ReflectionClass(static::class);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED);
        $defaults = $reflection->getDefaultProperties();

        $data = [];
        foreach ($properties as $property) {
            if (!in_array($property->name, static::$_reserved)) {
                static::$_defaults[$property->name] = $defaults[$property->name];
                $data[$property->name] = 'string';
                $doc = $property->getDocComment();
                if (!empty($doc)) {
                    $docLines = explode("\n", $doc);
                    foreach ($docLines as $line) {
                        if (preg_match('/@var\s+([^\s]*)(\s+.*)?$/', $line, $match)) {
                            array_shift($match);
                            $type = array_shift($match);
                            if ($type) {
                                if (substr($type, -2) == '[]') {
                                    $realType = substr($type, 0, -2);
                                } else {
                                    $realType = $type;
                                }

                                // 标量类型
                                if (array_key_exists($realType, static::$_filters) || is_a($realType, StructInterface::class, true)) {
                                    $data[$property->name] = $type;
                                    break;
                                }

                                throw new \RuntimeException('Type \'' . $type . '\' not allowed in struct');
                            }
                        }
                    }
                }

                // 标记为只读
                if ($property->isProtected()) {
                    static::$_protected[] = $property->name;
                }
            }
        }

        static::$_initialized = true;
        static::$_definition = $data;
    }

    /**
     * 取值。如果值是结构体，返回数组结构
     *
     * @param $value
     *
     * @return mixed
     */
    protected function _value($value)
    {
        if ($value instanceof Struct) {
            return $value->toArray();
        }

        if (is_array($value)) {
            $data = [];
            foreach ($value as $v) {
                $data[] = $this->_value($v);
            }

            return $data;
        }

        return $value;
    }

    /**
     * @param      $value
     * @param      $type
     *
     * @param null $default
     *
     * @return array|float|int|string|\Uniondrug\Structs\Struct
     */
    protected function _convert($value, $type, $default = null)
    {
        // 处理数组结构的属性
        if (substr($type, -2) == '[]') {
            if (is_array($value) || $value instanceof \Iterator || $value instanceof \ArrayAccess) {
                $subtype = substr($type, 0, -2);
                $data = [];
                foreach ($value as $v) {
                    $data[] = $this->_convert($v, $subtype);
                }

                return $data;
            }

            throw new \RuntimeException('Type \'' . $type . '\' require value must by an array');
        }

        // 结构体
        if (is_a($type, StructInterface::class, true)) {
            // 已经是结构体
            if (is_object($value) && get_class($value) == $type) {
                return $value;
            }

            // 构造实例化目标结构体
            $res = $type::factory($value);

            return $res;
        }

        // 标量
        return filter_var($value, static::$_filters[$type], [
            'options' => [
                'default' => $default,
            ],
        ]);
    }
}
