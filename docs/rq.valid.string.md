# 字符串型限制

1. `type` - 定义约束整型, 接受
    1. `string`
1. `required` - 指定是否为必须, 接受
    1. `true`
    1. `false`
1. `filters` - 多值筛选, 限制字符串必须为指定值中的一个
1. `empty` - 是否允许为空
    1. `true`
    1. `false`



### 示例

```php
class ExampleStruct extends Struct 
{
    /**
     * @var string
     * @Validator(type=string,required=true)
     */
    public $column;

    /**
     * @var string
     * @Validator(type=string,filters={disable,enable},required=true,empty=false)
     */
    public $columnText;
}
```
