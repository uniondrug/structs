<?php
/**
 * StructManager.php
 *
 */

namespace Uniondrug\Structs;

use Uniondrug\Framework\Injectable;

/**
 * Class StructManager
 *
 * @package Uniondrug\Structs
 */
class StructManager extends Injectable
{
    /**
     * 结构体验证器注解的名称
     */
    const ANNOTATION_NAME = 'Validator';

    /**
     * 结构体的全部字段定义
     *
     * @var array
     */
    protected $definitions = [];

    /**
     * 结构体中受保护的字段，只读
     *
     * @var array
     */
    protected $protected = [];

    /**
     * 结构体的默认值
     *
     * @var array
     */
    protected $defaults = [];

    /**
     * 结构体的属性的验证规则
     *
     * @var array
     */
    protected $rules = [];

    /**
     * 结构体不可以用的字段
     *
     * @var array
     */
    protected $reserved = ['_variables', '_dependencyInjector', '_structManager'];

    /**
     * 类型过滤器，只支持如下类型，或者结构体，或者数组
     *
     * @var array
     */
    protected $filters = [
        'string'  => FILTER_SANITIZE_STRING,
        'int'     => FILTER_VALIDATE_INT,
        'integer' => FILTER_VALIDATE_INT,
        'bool'    => FILTER_VALIDATE_BOOLEAN,
        'boolean' => FILTER_VALIDATE_BOOLEAN,
        'float'   => FILTER_VALIDATE_FLOAT,
        'double'  => FILTER_VALIDATE_FLOAT,
    ];

    /**
     * @param \Uniondrug\Structs\StructInterface $struct
     *
     * @return mixed
     */
    public function getDefinitions(StructInterface $struct)
    {
        $className = strtolower(get_class($struct));
        if (isset($this->definitions[$className])) {
            return $this->definitions[$className];
        }
        throw new \RuntimeException($className . ' not initialized');
    }

    /**
     * @param \Uniondrug\Structs\StructInterface $struct
     *
     * @return mixed
     */
    public function getDefaults(StructInterface $struct)
    {
        $className = strtolower(get_class($struct));
        if (isset($this->defaults[$className])) {
            return $this->defaults[$className];
        }
        throw new \RuntimeException($className . ' not initialized');
    }

    /**
     * @param \Uniondrug\Structs\StructInterface $struct
     *
     * @return array|mixed
     */
    public function getProtected(StructInterface $struct)
    {
        $className = strtolower(get_class($struct));
        if (isset($this->protected[$className])) {
            return $this->protected[$className];
        }

        return [];
    }

    /**
     * @param \Uniondrug\Structs\StructInterface $struct
     *
     * @return array|mixed
     */
    public function getRules(StructInterface $struct)
    {
        $className = strtolower(get_class($struct));
        if (isset($this->rules[$className])) {
            return $this->rules[$className];
        }

        return [];
    }

    /**
     * @param \Uniondrug\Structs\StructInterface $struct
     * @param                                    $name
     *
     * @return bool
     */
    public function has(StructInterface $struct, $name)
    {
        $className = strtolower(get_class($struct));

        return isset($this->definitions[$className]) && array_key_exists($name, $this->definitions[$className]);
    }

    /**
     * @param \Uniondrug\Structs\StructInterface $struct
     * @param                                    $name
     *
     * @return bool
     */
    public function isProtected(StructInterface $struct, $name)
    {
        $className = strtolower(get_class($struct));

        return isset($this->protected[$className]) && in_array($name, $this->protected[$className]);
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function reserved($name)
    {
        return in_array($name, $this->reserved);
    }

    /**
     * @param \Uniondrug\Structs\StructInterface $struct
     * @param                                    $name
     *
     * @return mixed
     */
    public function getDefinition(StructInterface $struct, $name)
    {
        $className = strtolower(get_class($struct));
        if (isset($this->definitions[$className]) && array_key_exists($name, $this->definitions[$className])) {
            return $this->definitions[$className][$name];
        }

        throw new \RuntimeException($name . ' is not a valid property');
    }

    /**
     * @param \Uniondrug\Structs\StructInterface $struct
     * @param                                    $name
     *
     * @return mixed
     */
    public function getDefault(StructInterface $struct, $name)
    {
        $className = strtolower(get_class($struct));
        if (isset($this->defaults[$className]) && array_key_exists($name, $this->defaults[$className])) {
            return $this->defaults[$className][$name];
        }

        throw new \RuntimeException($name . ' is not a valid property');
    }

    /**
     * @param \Uniondrug\Structs\StructInterface $struct
     * @param                                    $name
     *
     * @return bool
     */
    public function getRule(StructInterface $struct, $name)
    {
        $className = strtolower(get_class($struct));
        if (isset($this->rules[$className]) && array_key_exists($name, $this->rules[$className])) {
            return $this->rules[$className][$name];
        }

        return false;
    }

    /**
     * 解析结构体定义
     *
     * @param \Uniondrug\Structs\StructInterface $struct
     */
    public function initialize(StructInterface $struct)
    {
        $className = strtolower(get_class($struct));

        if (!isset($this->definitions[$className])) {
            // 需要先行处理
            $this->rules[$className] = $this->_parseValidationRules(get_class($struct));

            // 初始化结构体
            $reflection = new \ReflectionObject($struct);
            $namespace = $reflection->getNamespaceName();
            $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED);

            $defaults = $reflection->getDefaultProperties();
            $protected = [];
            $definitions = [];

            // 默认值
            foreach ($this->reserved as $reserved) {
                unset($defaults[$reserved]);
            }

            // 属性类型
            foreach ($properties as $property) {
                if (!in_array($property->name, $this->reserved)) {
                    $definitions[$property->name] = $defaults[$property->name];

                    // 只读属性
                    if ($property->isProtected()) {
                        $protected[] = $property->name;
                    }

                    // 解析属性类型
                    $definitions[$property->name] = 'string'; // 默认类型
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
                                    if (array_key_exists($realType, $this->filters) || is_a($realType, StructInterface::class, true)) {
                                        $definitions[$property->name] = $type;
                                        break;
                                    }

                                    // Add Current NamespaceName
                                    $fullClass = $namespace . '\\' . $realType;
                                    if (is_a($fullClass, StructInterface::class, true)) {
                                        $definitions[$property->name] = $namespace . '\\' . $type;
                                        break;
                                    }

                                    throw new \RuntimeException("[" . get_class($struct) . "] Type '$type' not allowed in struct");
                                }
                            }
                        }
                    }
                }
            }

            $this->defaults[$className] = $defaults;
            $this->protected[$className] = $protected;
            $this->definitions[$className] = $definitions;
        }

        // 去除当前结构体对象的属性，让结构体的赋值取值使用__set/__get方法
        foreach ($this->definitions[$className] as $property => $definition) {
            unset($struct->{$property});
        }
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
        $structAnnotation = $this->annotations->get($className);
        foreach ($structAnnotation->getPropertiesAnnotations() as $property => $annotations) {
            /* @var \Phalcon\Annotations\Collection $annotations */
            if (static::reserved($property)) {
                continue;
            }

            if ($annotations->has(static::ANNOTATION_NAME)) {
                $validatorAnnotation = $annotations->get(static::ANNOTATION_NAME);
                $rules[$property] = $validatorAnnotation->getArguments();
            }
        }

        return $rules;
    }

    /**
     * 结构体构属性值造转换
     *
     * @param      $value
     * @param      $type
     *
     * @return array|float|int|string|boolean|\Uniondrug\Structs\Struct
     */
    public function convert($value, $type)
    {
        // 保存原始值
        $originValue = $value;

        // NULL 值直接返回
        if ($value === null) {
            return $value;
        }

        // 处理数组结构的属性
        if (substr($type, -2) == '[]') {
            if (is_array($value) || $value instanceof \Iterator || $value instanceof \ArrayAccess) {
                $subtype = substr($type, 0, -2);
                $data = [];
                foreach ($value as $v) {
                    $data[] = $this->convert($v, $subtype);
                }

                return $data;
            }
            throw new \RuntimeException("Type '$type' require value must be an array");
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

        // 标量转换
        $value = filter_var($value, $this->filters[$type]);
        if ($value === false && $type != 'bool' && $type != 'boolean') {
            $valueType = gettype($originValue);
            throw new \RuntimeException("Type '$type' required, but $valueType '" . $originValue . "' given");
        }

        return $value;
    }

    /**
     * 取值。如果值是结构体，返回数组结构
     *
     * @param $value
     *
     * @return mixed
     */
    public function value($value)
    {
        if ($value instanceof StructInterface) {
            return $value->toArray();
        }

        if (is_array($value)) {
            $data = [];
            foreach ($value as $v) {
                $data[] = $this->value($v);
            }

            return $data;
        }

        return $value;
    }
}
