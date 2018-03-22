# 完整时间限制

1. `type` - 定义约束整型, 接受
    1. `datetime`
1. `options` - 约束选项
    1. `max` - 最大值
    1. `min` - 最小值
1. `required` - 指定是否为必须, 接受
    1. `true`
    1. `false`
1. `empty` - 是否允许为空
    1. `true`
    1. `false`



### 示例

```php
class ExampleStruct extends Struct 
{
    /**
     * @var string
     * @Validator(type=datetime,options={min:2018-01-01 00:00:00,max:2018-12-31 23:59:59},required=true,empty=false)
     */
    public $column;
}
```
