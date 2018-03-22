# 只读属性

> 请以 `protected` 修饰符, 同时需满足如下条件

1. static::factory()的入参必须为object对象实例
1. object对像无和其它相对应的同名属性
1. object对象中定义了`getter`方法, 如`getStatusText()`


```php
class ExampleStruct extends Struct 
{
    /**
     * @var int
     */
    protected $status;
    /**
     * @var string
     */
    protected $statusText;
}
```


### 用法

```php
class Example extends Model
{
    public function getStatusText()
    {
        return "禁用";
    }
}
```

```php
$model = new Example();
$struct = ExampleStruct::factory($model);

echo $struct->status;     // 0
echo $struct->statusText; // 禁用
```