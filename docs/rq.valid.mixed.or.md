# 合并限制

1. `type` - 定义约束整型, 接受多元素控制
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
     * 字段column的值是手机号或固定电话
     * @var string
     * @Validator(type={mobile,telphone},required=true)
     */
    public $column;
}
```
