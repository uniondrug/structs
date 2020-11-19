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
    /**
     * 解析注释中的别名
     * @var string
     */
    private static $commentRegexpAlias = "/@alias\s+([_a-z][_a-z0-9]*)/i";
    /**
     * 解析注释中的分组
     * eg. name={values}
     * @var string
     */
    private static $commentRegexpGroup = "/([_a-z0-9]+)\s*=\s*\{([^\}]*)\}/i";
    /**
     * 解析注释分组中的元素
     * eg. name:value
     * @var string
     */
    private static $commentRegexpGroupItem = "/([_a-z0-9]+)\s*:\s*([:_a-z0-9\.\-\s\\\\]+)/i";
    /**
     * 单级解析
     * eg. name=value
     *     name
     * @var string
     */
    private static $commentRegexpSingle = "/([_a-z0-9]+)\s*[=]*\s*([:_a-z0-9\.\-\s\\\\]*)/i";
    /**
     * 类型定义匹配
     * @var string
     */
    private static $commentRegexpType = "/@var\s+([_a-z0-9\\\\]+)\s*([\[\]]*)/i";
    /**
     * 验证器定义匹配
     * @var string
     */
    private static $commentRegexpValidator = "/@validator\(([^\)]*)\)/i";
    /**
     * 自定义的默认值
     * @var mixed
     */
    private $defaultValue;
    /**
     * 是否为数组
     * @var bool
     */
    private $arrayType = false;
    private $booleanType = false;
    private $moneyType = false;
    /**
     * 是否为结构体
     * @var bool
     */
    private $structType = false;
    /**
     * @var string
     */
    public $name;
    public $aliasName = null;
    /**
     * @var string
     */
    private $className;
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
     * @param \ReflectionProperty $prop
     * @param string              $namespace
     * @param mixed               $defaultValue
     */
    public function __construct(\ReflectionProperty $prop, string $namespace, $defaultValue)
    {
        $this->name = $prop->name;
        $this->className = $prop->class;
        $this->initComment($prop, $namespace);
        $this->initDefaultValue($defaultValue);
        $this->initStructType();
        $this->initDefaultValidator();
    }

    public function formatMoney($value)
    {
        return sprintf("%.02f", $value);
    }

    /**
     * 读取默认值
     * @return mixed
     */
    public function getDefaultValue()
    {
        if ($this->moneyType) {
            return $this->formatMoney($this->defaultValue);
        }
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
     * 将指定值转为Boolean类型
     * @param mixed $value
     * @return bool
     */
    public function generateBoolean($value)
    {
        $t = strtolower(gettype($value));
        // 1. 已经是Boolean类型
        if ($t === 'bool' || $t === 'boolean') {
            return $value;
        }
        // 2. 是String类型
        if ($t === 'string' && preg_match("/^t|true|1|yes|y$/i", $value)) {
            return true;
        }
        // 3. 整型
        if ($t === 'integer') {
            return $t != 0;
        }
        // 4. false;
        return false;
    }

    /**
     * 属性是否为数组
     * @return bool
     */
    public function isArray()
    {
        return $this->arrayType;
    }

    public function isBoolean()
    {
        return $this->booleanType;
    }

    /**
     * 是否允许为空
     * @return bool
     */
    public function isEmpty()
    {
        return $this->rule['empty'];
    }

    /**
     * 是否为Money类型
     * @return bool
     */
    public function isMoney()
    {
        return $this->moneyType;
    }

    /**
     * 属性值是否必填
     * @return bool
     */
    public function isRequired()
    {
        return $this->rule['required'];
    }

    /**
     * 是否数据是否用于存储结构体
     * @return bool
     */
    public function isStruct()
    {
        return $this->structType;
    }

    /**
     * 属性数据类型是否为系统类型
     * @return bool
     */
    public function isSystemType()
    {
        return $this->systemType;
    }

    /**
     * 数据验证
     * @param mixed $value
     */
    public function validate(&$value)
    {
        // 1. 非系统类型时忽略
        //    还不是最小化的字段
        if (!$this->systemType || count($this->rule['type']) === 0) {
            return;
        }
        $value = $this->filterString($value);
        // 2. validator
        Param::check([$this->name => $value], [$this->name => $this->rule]);
    }

    /**
     * 字符串过滤
     * @param string $value
     * @return string
     */
    private function filterString($value)
    {
        if (is_string($value)) {
            return trim($value);
        }
        return $value;
    }

    /**
     * 初始化注释
     * @param \ReflectionProperty $prop
     * @param string              $namespace
     */
    private function initComment(\ReflectionProperty $prop, string $namespace)
    {
        // 0. get comment
        //    return for empty or undefined
        $comment = $prop->getDocComment();
        if (!$comment) {
            return;
        }
        // 1. match alias name
        if (preg_match(self::$commentRegexpAlias, $comment, $m) > 0) {
            $this->aliasName = $m[1];
        }
        // 2. match type
        if (preg_match(self::$commentRegexpType, $comment, $m) > 0) {
            // 2.1 is array
            if ($m[2] === '[]') {
                $this->arrayType = true;
            }
            // 2.2 type name
            if ($this->filterSystemType($m[1])) {
                if ($m[1] === 'array') {
                    throw new Exception("属性'{$this->className}::\${$this->name}'禁止使用'@var array'注解");
                }
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
                if ($this->filterSystemType($type)) {
                    $setter = false;
                    $this->type = $type;
                    if ($this->type === 'array') {
                        $this->arrayType = true;
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
        if ($this->arrayType) {
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
        $this->structType = is_a($this->type, StructInterface::class, true);
    }

    /**
     * 初始化验证器规则
     * @param string $comment
     * @example $this->initValidator("type=integer,options={min:1,max:10},required,empty")
     */
    private function initValidator(string $comment)
    {
        $obj = $this;
        $comment = preg_replace("/\"|'/", '', $comment);
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
                    if (strtolower($m[2][$i]) == 'money') {
                        $this->moneyType = true;
                    }
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
     * 过滤为系统类型
     * @link http://php.net/manual/zh/function.gettype.php
     * @param string $type
     * @return bool
     */
    private function filterSystemType(string $type)
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
        if (in_array(strtolower($type), $types)) {
            $this->systemType = true;
            return true;
        }
        return false;
    }

    /**
     * 简写类型转标准类型名称
     * @param string $type
     * @return string
     * @example $this->toSystemType('int'); // integer
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
            case 'money' :
            case 'str' :
                $type = 'string';
                break;
        }
        return $type;
    }
}
