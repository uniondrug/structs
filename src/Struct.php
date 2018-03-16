<?php
/**
 * 结构体，用于定于交互数据结构。
 */

namespace Uniondrug\Structs;

use Phalcon\Di;
use Uniondrug\Validation\Param;

abstract class Struct implements StructInterface
{
    const ANNOTATION_NAME = 'Validator';

    /**
     * 结构体的实际内容
     *
     * @var array
     */
    protected $_variables = [];

    /**
     * 受保护的结构体属性
     *
     * @var array
     */
    protected $_protected = [];

    /**
     * 结构体属性的类型定义，在实例化时通过反射自动创建
     *
     * @var array
     */
    protected $_definition = [];

    /**
     * 结构体属性的默认值，实例化的时候保存进去
     *
     * @var array
     */
    protected $_defaults = [];

    /**
     * 属性的验证规则
     *
     * @var array
     */
    protected $_rules = [];

    /**
     * 保留属性，不暴露
     *
     * @var array
     */
    protected static $_reserved = ['_variables', '_definition', '_protected', '_reserved', '_filters', '_defaults', '_validators', '_rules'];

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
        $this->_initialize();

        if ($data !== null) {
            $this->init($data);
        } else {
            $this->init([]);
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
     * 从一个对象或者数组初始化结构体
     *
     * @param array|object $data
     */
    public function init($data)
    {
        foreach ($this->_definition as $property => $type) {
            if (is_array($data) && isset($data[$property])) {
                $this->$property = $data[$property];
            } elseif (is_object($data) && property_exists($data, $property)) {
                $this->$property = $data->$property;
            } elseif (is_object($data) && method_exists($data, $property)) {
                $this->$property = $data->$property();
            } elseif (is_object($data) && method_exists($data, 'get' . $property)) {
                $method = 'get' . $property;
                $this->$property = $data->$method();
            } else {
                // 用默认值
                $this->$property = $this->_defaults[$property];
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
     * @throws \Uniondrug\Validation\Exceptions\ParamException
     */
    public function set($name, $value)
    {
        $this->__set($name, $value);

        return $this;
    }

    /**
     * 通过set()方法设置属性
     *
     * @param $name
     * @param $value
     *
     * @return $this
     * @throws \Uniondrug\Validation\Exceptions\ParamException
     */
    public function setProperty($name, $value)
    {
        $this->__set($name, $value);

        return $this;
    }

    /**
     * Alias of getProperty()
     *
     * @param $name
     *
     * @return mixed
     */
    public function get($name)
    {
        return $this->__get($name);
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
        return $this->__get($name);
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
        return array_key_exists($name, $this->_definition);
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
        return $this->has($name);
    }

    /**
     * 获取所有的公共属性
     *
     * @return array
     */
    public function getProperties()
    {
        return array_keys($this->_definition);
    }

    /**
     * 转换成数组
     *
     * @return array
     */
    public function toArray()
    {
        $data = [];
        foreach ($this->_variables as $name => $value) {
            $data[$name] = $this->_value($value);
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
     * @return string
     */
    final public function __toString()
    {
        return $this->toJson();
    }

    /**
     * @param $name
     *
     * @return bool
     */
    final public function __isset($name)
    {
        return isset($this->_variables[$name]);
    }

    /**
     * @param $name
     */
    final public function __unset($name)
    {
        if (!$this->has($name)) {
            throw new \RuntimeException('Property \'' . $name . '\' not exists');
        }
        throw new \RuntimeException('Property \'' . $name . '\' cannot be unset');
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    final public function & __get($name)
    {
        if (!$this->__isset($name)) {
            throw new \RuntimeException('Property \'' . $name . '\' not exists');
        }

        return $this->_variables[$name];
    }

    /**
     * @param $name
     * @param $value
     *
     * @return void
     */
    final public function __set($name, $value)
    {
        if (!$this->hasProperty($name)) {
            throw new \RuntimeException('Property \'' . $name . '\' not exists');
        }

        if ($this->_readonly($name)) {
            throw new \RuntimeException('Property \'' . $name . '\' is readonly');
        }

        // 验证器验证
        try {
            if (isset($this->_rules[$name])) {
                $rules = [$name => $this->_rules[$name]];
                if (!isset($rules[$name]['type']) && in_array($this->_definition[$name], ['string', 'int', 'integer', 'float', 'double'])) {
                    $rules[$name]['type'] = $this->_definition[$name];
                }

                $value = Param::check([$name => $value], $rules);
                $value = $value[$name];
            }

            $this->_variables[$name] = $this->_convert($value, $this->_definition[$name]);
        } catch (\Exception $e) {
            throw new \RuntimeException("Set property '$name' failed: " . $e->getMessage());
        }
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
        return in_array($name, $this->_protected);
    }

    /**
     * 通过反射获取定义的属性
     *
     * 结构体的属性类型只能是：
     *  标量：string/int/bool/float
     *  数组：单一结构，其组成也只能是同一种标量，或者结构体
     */
    protected function _initialize()
    {
        // 需要先行处理
        $this->_rules = $this->_parseValidationRules(get_class($this));

        // 初始化结构体
        $reflection = new \ReflectionObject($this);
        $namespace = $reflection->getNamespaceName();
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
        $defaults = $reflection->getDefaultProperties();

        $data = [];
        foreach ($properties as $property) {
            if (!in_array($property->name, static::$_reserved)) {

                // 定义的属性和默认值
                $this->_variables[$property->name] = $defaults[$property->name];
                $this->_defaults[$property->name] = $defaults[$property->name];

                $data[$property->name] = 'string'; // 默认类型
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

                                // Add Current NamespaceName
                                $fullClass = $namespace . '\\' . $type;
                                if (is_a($fullClass, StructInterface::class, true)) {
                                    $data[$property->name] = $fullClass;
                                    break;
                                }

                                throw new \RuntimeException('Type \'' . $type . '\' not allowed in struct');
                            }
                        }
                    }
                }

                // 去除该属性，走__set/__get方法
                unset($this->{$property->name});
            }
        }

        $this->_definition = $data;
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
     * 转换值
     *
     * @param      $value
     * @param      $type
     *
     * @return array|float|int|string|\Uniondrug\Structs\Struct
     */
    protected function _convert($value, $type)
    {
        $originValue = $value;

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
            if (is_object($value)) {
                if (get_class($value) == $type) {
                    return $value;
                } else {
                    throw new \RuntimeException('Type \'' . $type . '\' required, but \'' . get_class($value) . '\' given');
                }
            }

            // 构造实例化目标结构体
            $res = $type::factory($value);

            return $res;
        }

        // 标量转换
        $value = filter_var($value, static::$_filters[$type]);
        if ($value === false && $type != 'bool' && $type != 'boolean') {
            throw new \RuntimeException('Type \'' . $type . '\' required, but \'' . $originValue . '\' given');
        }

        return $value;
    }

    /**
     * 从注解中解析规则
     *
     * 支持如下注解：
     * @Validator(type=int,default=5,required=true,empty=true,filter={abc,def},options={min=5,max=10})
     *
     * @param string $className 结构体类名
     *
     * @return array
     */
    protected function _parseValidationRules($className)
    {
        /**
         * 从结构体字段中获取注解，完成规则定义
         *
         * @Validator(type=int,default=5,required=true,empty=true,filter={abc,def},options={min=5,max=10})
         *
         * 结构体的字段默认值会自动当做验证字段的默认值。
         *
         */

        /* @var \Phalcon\Annotations\Reflection $structAnnotation */
        $rules = [];
        $structAnnotation = Di::getDefault()->getShared('annotations')->get($className);
        foreach ($structAnnotation->getPropertiesAnnotations() as $property => $annotations) {
            /* @var \Phalcon\Annotations\Collection $annotations */
            if ($className::reserved($property)) {
                continue;
            }
            if ($annotations->has(static::ANNOTATION_NAME)) {
                $validatorAnnotation = $annotations->get(static::ANNOTATION_NAME);
                $rules[$property] = $validatorAnnotation->getArguments();
            }
        }

        return $rules;
    }
}
