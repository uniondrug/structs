<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-03-26
 */
namespace Uniondrug\Structs;

use Uniondrug\Validation\Param;

/**
 * @package Uniondrug\Structs
 */
class Property
{
    private static $commentRegexpGroup = "/([_a-z0-9]+)\s*=\s*\{([^\}]*)\}/i";

    private static $commentRegexpGroupItem = "/([_a-z0-9]+)\s*:\s*([_a-z0-9]+)/i";

    private static $commentRegexpSingle = "/([_a-z0-9]+)\s*[=]*\s*([_a-z0-9]*)/i";

    /**
     * 类型定义匹配
     * @var string
     */
    private static $commentRegexpType = "/@var\s+([_a-z0-9\\\\]+)\s*([\[\]]*)/i";

    /**
     * 验证器定义匹配
     * @var string
     */
    private static $commentRegexpValidator = "/@validator\(([^\)]*)\)/";

    /**
     * 默认值
     * @var mixed
     */
    private $defaultValue;

    /**
     * 是否为数组
     * @var bool
     */
    private $isArrayType = false;

    /**
     * 是否为结构体
     * @var bool
     */
    private $isStructType = false;

    /**
     * @var string
     */
    private $name;

    /**
     * 验证器规则
     * @var array
     */
    private $rule = [
        'type' => [],
        'options' => [],
        'filters' => [],
        'required' => false,
        'empty' => true
    ];

    /**
     * 类型名称
     * <code>
     * $type = 'integer';
     * $type = 'ExampleStruct';
     * </code>
     * @var string
     */
    private $type;

    /**
     * 是否为系统类型
     * @var bool
     */
    private $systemType = false;

    /**
     * Property constructor.
     * @param \ReflectionProperty $prop
     * @param string              $namespace
     * @param mixed               $defaultValue
     */
    public function __construct(\ReflectionProperty $prop, string $namespace, $defaultValue)
    {
        $this->name = $prop->name;
        $this->initComment($prop, $namespace);
        $this->initDefaultValue($defaultValue);
        $this->initStructType();
        $this->initDefaultValidator();
    }

    /**
     * 读取默认值
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * 读取类型名称
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * 是否为数组类型
     * @return bool
     */
    public function isArray()
    {
        return $this->isArrayType;
    }

    /**
     * 指定字段是否为必须
     * @return mixed
     */
    public function isRequired()
    {
        return $this->rule['required'];
    }

    /**
     * 是否为结构体类型
     * @return bool
     */
    public function isStruct()
    {
        return $this->isStructType;
    }

    /**
     * 数据验证
     */
    public function validate($value)
    {
        // 1. 非系统类型时忽略
        //    还不是最小化的字段
        if (!$this->systemType || count($this->rule['type']) === 0) {
            return;
        }
        // 2. validator
        Param::check([$this->name => $value], [$this->name => $this->rule]);
    }

    /**
     * 初始化注释
     * @param \ReflectionProperty $prop
     * @param string              $namespace
     */
    private function initComment(\ReflectionProperty $prop, string $namespace)
    {
        // 1. get comment
        //    return for empty or undefined
        $comment = $prop->getDocComment();
        if (!$comment) {
            return;
        }
        // 2. match type
        if (preg_match(self::$commentRegexpType, $comment, $m) > 0) {
            // 2.1 is array
            if ($m[2] === '[]') {
                $this->isArrayType = true;
            }
            // 2.2 type name
            if ($this->isSystemType($m[1])) {
                $this->type = $this->toSystemType($m[1]);
            } else {
                $this->type = $m[1][0] == '\\' ? $m[1] : '\\'.$namespace.'\\'.$m[1];
            }
        }
        // 3. match validator
        if (preg_match(self::$commentRegexpValidator, $comment, $m) > 0) {
            $this->initValidator($m[1]);
        }
    }

    /**
     * 初始化默认值
     * @param mixed $defaultValue
     */
    private function initDefaultValue($defaultValue)
    {
        // 1. set default value
        $this->defaultValue = $defaultValue;
        // 2. fixed undefined type
        //    default: string
        if ($this->type === null) {
            $setter = true;
            if ($this->defaultValue !== null) {
                $type = gettype($defaultValue);
                if ($this->isSystemType($type)) {
                    $setter = false;
                    $this->type = $type;
                    if ($this->type === 'array') {
                        $this->isArrayType = true;
                    }
                } else if ($type == 'object') {
                    $this->type = get_class($defaultValue);
                }
            }
            if ($setter) {
                $this->type = 'string';
            }
        }
        // 3. has default values
        if ($defaultValue !== null) {
            return;
        }
        // 4. set default values by system type
        if ($this->isArrayType) {
            $defaultValue = [];
        } else {
            switch ($this->type) {
                case 'boolean' :
                    $defaultValue = false;
                    break;
                case 'integer' :
                    $defaultValue = 0;
                    break;
                case 'float' :
                case 'double' :
                    $defaultValue = 0.0;
                    break;
                default :
                    $defaultValue = '';
                    break;
            }
        }
        $this->defaultValue = $defaultValue;
    }

    /**
     * 初始化是否为结构体
     */
    private function initStructType()
    {
        if ($this->systemType) {
            return;
        }
        $this->isStructType = is_a($this->type, StructInterface::class, true);
    }

    /**
     * 初始化验证器规则
     * @param string $comment
     * @example $this->initValidator("type=integer,options={min:1,max:10},required,empty")
     */
    private function initValidator(string $comment)
    {
        $obj = $this;
        // 1. collect group
        $comment = preg_replace_callback(static::$commentRegexpGroup, function($args) use (& $obj){
            $name = $args[1];
            $value = $args[2];
            if ($name == 'type' || $name == 'filters') {
                $obj->rule[$name] = explode(',', $value);
            } else if ($name === 'options') {
                $options = [];
                if (preg_match_all(static::$commentRegexpGroupItem, $value, $m) > 0) {
                    foreach ($m[1] as $i => $key) {
                        $options[$key] = $m[2][$i];
                    }
                }
                $obj->rule[$name] = $options;
            }
            return '';
        }, $comment);
        // 2. collect single
        if (preg_match_all(static::$commentRegexpSingle, $comment, $m) > 0) {
            foreach ($m[1] as $i => $name) {
                if ($name == 'type') {
                    $this->rule[$name] = [$m[2][$i]];
                } else if ($name == 'required' || $name == 'empty') {
                    $value = strtolower($m[2][$i]) === 'false' ? false : true;
                    $this->rule[$name] = $value;
                }
            }
        }
    }

    /**
     * 启用默认验证器
     * 强类型控制
     */
    private function initDefaultValidator()
    {
        if (count($this->rule['type']) === 0 && $this->type !== null) {
            $this->rule['type'][] = $this->type;
        }
    }

    /**
     * 是否为系统类型
     * @param string $type
     * @return bool
     */
    private function isSystemType(string $type)
    {
        $types = [
            'array',
            'bool',
            'boolean',
            'double',
            'float',
            'int',
            'integer',
            'string',
            'null'
        ];
        if (in_array($type, $types)) {
            $this->systemType = true;
            return true;
        }
        return false;
    }

    /**
     * 转标准类型名称
     * @param string $type
     * @return string
     */
    private function toSystemType(string $type)
    {
        switch ($type) {
            case 'bool' :
                $type = 'boolean';
                break;
            case 'float' :
                $type = 'double';
                break;
            case 'int' :
                $type = 'integer';
                break;
            case 'str' :
                $type = 'string';
                break;
        }
        return $type;
    }
}
