<?php
/**
 * 结构体，用于定于交互数据结构。
 */

namespace Uniondrug\Structs;

use Uniondrug\Framework\Container;
use Uniondrug\Validation\Param;

abstract class Struct implements StructInterface, \Serializable, \JsonSerializable
{
    /**
     * 容器
     *
     * @var Container
     */
    protected $_dependencyInjector;

    /**
     * 结构体管理服务
     *
     * @var \Uniondrug\Structs\StructManager
     */
    protected $_structManager;

    /**
     * 结构体的实际内容
     *
     * @var array
     */
    protected $_variables = [];

    /**
     * 构造函数，初始化结构体。
     *
     * @param null|array|object $data
     */
    public function __construct($data = null)
    {
        $this->_dependencyInjector = Container::getDefault();
        $this->_structManager = $this->_dependencyInjector->getShared('structManager');
        if (!is_object($this->_structManager)) {
            throw new \RuntimeException('The injected service \'structManager\' is not valid');
        }

        $this->_structManager->initialize($this);
        $this->_variables = $this->getDefaults();

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
     * @deprecated
     */
    public static function reserved($name)
    {
        return false;
    }

    /**
     * 从一个对象或者数组初始化结构体
     *
     * @param array|object $data
     */
    public function init($data)
    {
        foreach ($this->getDefinitions() as $property => $type) {
            if (is_array($data) && isset($data[$property])) {
                $this->$property = $data[$property];
            } elseif (is_object($data) && property_exists($data, $property)) {
                $this->$property = $data->$property;
            } elseif (is_object($data) && method_exists($data, $property)) {
                $this->$property = $data->$property();
            } elseif (is_object($data) && method_exists($data, 'get' . $property)) {
                $method = 'get' . $property;
                $this->$property = $data->$method();
            } elseif (is_object($data)) {
                // 对于提供魔术方法 __get 的对象
                try {
                    $this->$property = $data->$property;
                } catch (\Throwable $e) {
                    $this->$property = $this->getDefault($property);
                }
            } else {
                $this->$property = $this->getDefault($property);
            }
        }
    }

    /**
     * 检测一个属性是否存在
     *
     * @param $name
     *
     * @return bool
     */
    public function has($name)
    {
        return $this->__isset($name);
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
        return $this->__isset($name);
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
     * 获取所有属性及其定义
     *
     * @return mixed
     */
    public function getDefinitions()
    {
        return $this->_structManager->getDefinitions($this);
    }

    /**
     * 获取指定属性的定义
     *
     * @param $name
     *
     * @return mixed
     */
    public function getDefinition($name)
    {
        return $this->_structManager->getDefinition($this, $name);
    }

    /**
     * 返回所有属性及其默认值
     *
     * @return mixed
     */
    public function getDefaults()
    {
        return $this->_structManager->getDefaults($this);
    }

    /**
     * 返回一个属性的默认值
     *
     * @param $name
     *
     * @return mixed
     */
    public function getDefault($name)
    {
        return $this->_structManager->getDefault($this, $name);
    }

    /**
     * 返回全部验证规则
     *
     * @return array|mixed
     */
    public function getRules()
    {
        return $this->_structManager->getRules($this);
    }

    /**
     * 返回属性的验证规则
     *
     * @param $name
     *
     * @return false|array
     */
    public function getRule($name)
    {
        return $this->_structManager->getRule($this, $name);
    }

    /**
     * 获取所有的公共属性
     *
     * @return array
     * @deprecated
     */
    public function getProperties()
    {
        return $this->_structManager->getDefinitions($this);
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
            $data[$name] = $this->_structManager->value($value);
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
        return $this->_structManager->has($this, $name);
    }

    /**
     * @param $name
     */
    final public function __unset($name)
    {
        if (!$this->__isset($name)) {
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
        if (!$this->__isset($name)) {
            throw new \RuntimeException("[" . get_class($this) . "] Property \'' . $name . '\' not exists");
        }

        if ($this->isProtected($name)) {
            throw new \RuntimeException("[" . get_class($this) . "] Property \'' . $name . '\' is readonly");
        }

        // 验证器验证
        try {
            $definition = $this->getDefinition($name);
            if ($rule = $this->getRule($name)) {
                $rules = [$name => $rule];
                if (!isset($rules[$name]['type']) && in_array($definition, ['string', 'int', 'integer', 'float', 'double'])) {
                    $rules[$name]['type'] = $definition;
                }

                $value = Param::check([$name => $value], $rules);
                $value = $value[$name];
            }

            $this->_variables[$name] = $this->_structManager->convert($value, $definition);
        } catch (\Exception $e) {
            throw new \RuntimeException("[" . get_class($this) . "] Set property '$name' failed: " . $e->getMessage());
        }
    }

    /**
     * 是否是只读属性
     *
     * @param $name
     *
     * @return bool
     */
    protected function isProtected($name)
    {
        return $this->_structManager->isProtected($this, $name);
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return $this->toJson();
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $this->init(json_decode($serialized));
    }

    /**
     * @return mixed|string
     */
    public function jsonSerialize()
    {
        return $this->toJson();
    }
}
