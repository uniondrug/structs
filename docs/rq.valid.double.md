# 浮点型限制

1. `type` - 定义约束整型, 接受
    1. `double`
    1. `float`
1. `options` - 约束选项
    1. `max` - 最大值
    1. `min` - 最小值
1. `required` - 指定是否为必须, 接受
    1. `true`
    1. `false`



### 示例

```php
class ExampleStruct extends Struct 
{
    /**
     * @var double
     * @Validator(type=double,options={min:1.0,max:100.0})
     */
    public $column;
}
```
