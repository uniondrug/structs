# 读写属性

> 需以 `public` 修饰符定义

```php
class ExampleStruct extends Struct 
{
    /**
     * 表示可读/写的int类型属性, 默认值为1
     * @var int
     */
    public $id = 1;
}
```


### 用法

```php
$struct = ExampleStruct::factory(['id' => 10]);

// 1. 允许
echo $struct->id; // 10

// 2. 允许
$struct->id = 100;
echo $struct->id; // 100
```