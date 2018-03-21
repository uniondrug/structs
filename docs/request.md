# 结构体

> 用于数据出、入的标准化转换

* 脚本位置: `app/Structs`
* 命名空间: `App\Structs`
* 用途分类: 当前保留二种
    1. 请求 - `App\Structs\Requests` - 将入参转换为标准结构体
    1. 返回 - `App\Structs\Results` - 将业务处理的结果转为标准结构体, 交由Response返回
* 应用分类: 当前分三种
    1. 标准 - `Struct`
    1. 列表 - `ListStruct`
    1. 分页 - `PaginatorStruct`


### 结构体定义

```php
class ExampleStruct extends Struct 
{
    /**
     * 表示可读/写的int类型属性, 默认值为1
     * @var int
     */
    public $id = 1;

    /**
     * 表示只读的int类型属性, 默认值为10
     * @var int
     */
    protected $pid = 10;

    /**
     * 表示可读/写的int类型数组属性, 默认空数组
     * @var int[]
     */
    public $ids = [];

    /**
     * 表示嵌套的结构体
     * @var InnerStruct
     */
    public $inner;

    /**
     * 表示嵌套的结构体列表
     * @var InnerStruct[]
     */
    public $inners;

    /**
     * 表示嵌套的子空间(目录)结构体
     * @var Depths\DepthStruct
     */
    public $depth;
    
    /**
     * 计算属性, 当已定义属性时, 则不走计算属性
     * 1. 入参为Object对象
     * 2. 已定义getStatusText()方法
     */
    public $statusText;
}
```


### 属性约束

> 属性约束指在解析和赋值时, 其值必须遵循约整限制, 如下示例如下:

```php
class ExampleStruct extends Struct 
{
    /**
     * @var int
     * @Validator(type=int,options={min:1900,max:2018},required=true)
     */
    public $year = 0;
}
```



### 约束参考

> 基本类型

1. Integer
    * `type` - 定义约整类型, 接受`int`或`integer`字符串
    * `options` - 约束选项
        * `max` - 最大值
        * `min` - 最小值
    * `required` - 指定是否为必须, 接受`true`、`false`值; 
1. Double
    * `type` - 定义约整类型, 接受`double`或`float`字符串
    * `options` - 约整选项
        * `max` - 最大值
        * `min` - 最小值
    * `required` - 指定是否为必须, 接受`true`、`false`值; 
1. String
    * `type` - 定义约整类型, 仅接受`string`字符串
    * `options` - 约整选项
        * `max` - 最大长度
        * `min` - 最小长度
    * `filters` - 过滤器, 限如下值
        * `string`
        * `int`
        * `integer`
        * `bool`
        * `boolean`
        * `float`
        * `double`
    * `required` - 指定是否为必须, 接受`true`、`false`值; 
    * `empty` - 是否允许为空, 接受`true`、`false`值; 



> 扩展类型

1. Email
    * `type` - 定义输出必须为Email地址, 限`email`选项
    > `@Validator(type=email)`
1. Mobile
    * `type` - 定义输出必须为手机号, 限`mobile`选项
    > `@Validator(type=mobile)`
1. Telphone
    * `type` - 定义输出必须为固定电话, 限`telphone`选项
    > `@Validator(type=telphone)`
1. Date
    * `type` - 定义输出必须为日期, 限`date`选项
    > `@Validator(type=date)`
1. Datetime
    * `type` - 定义输出必须为完整日期, 限`datetime`选项
    > `@Validator(type=datetime)`
1. Time
    * `type` - 定义输出必须为时间, 限`time`选项
    > `@Validator(type=time)`



> 混合类型

```text
/**
 * 联系电话, 可以是手机号或因定电话
 * @var string
 * @Validator(type={mobile,telphone},required=true,empty=false)
 */
 public $phone;
```












