# 时间限制

1. `type` - 定义约束整型, 接受
    1. `time`
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
     * @Validator(type=datetime,options={min:08:00,max:21:30},required=true,empty=false)
     */
    public $column;
}
```
