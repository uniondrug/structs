# 只读属性

> 需以 `protected` 修饰符定义

```php
class ExampleStruct extends Struct 
{
    /**
     * 表示只读的int类型属性, 默认值为1
     * @var int
     */
    protected $id = 1;
}
```


### 用法

```php
$struct = ExampleStruct::factory(['id' => 10]);

// 1. 允许
echo $struct->id; // 10

// 2. 禁止
//    将抛出Exception异常
$struct->id = 100;
```