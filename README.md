# Structs component for uniondrug/framework

结构体工具，用于构造数据结构，规范代码和服务之间的数据交互。

## 安装

```shell
$ cd project-home
$ composer require uniondrug/structs
```

## 使用

### 定义结构体

结构体定义了一个数据结构的构成，其属性支持的类型包括标量：`string`/`int`(`integer`)/`boolean`(`bool`)/`float`(`double`)，结构体，或者是由标量、结构体构成的数组。

1、通过属性注释的`@var`注解来定义一个属性的类型；

2、当属性是数组的时候，在类型后面加上`[]`标识；

3、当属性需要设置成只读的时候，使用修饰符`protected`，为了使IDE友好，可以在类注释中添加`@property`注解；

4、如果属性没有设置类型，默认是`string`类型；

> 注意：属性是数组时，其元素必须是一个结构体，或者是相同类型的标量。是标量类型则必须指定是何种类型，比如：`string[]`,`int[]`。当构造数据中有类型不符且不能转换时，会以`null`代替。


```php
<?php
use Uniondrug\Structs\Struct;

class OrderItemStruct extends Struct
{
    /**
     * @var string
     */
     public $name;

    /**
     * @var float
     */
     public $unitPrice;

    /**
     * @var int[]
     */
     public $cats;
}

class MemberStruct extends Struct
{
    /**
     * @var string
     */
     public $username;

    /**
     * @var string
     */
     public $mobile;
}

/**
 * @property float $price
 */
class OrderStruct extends Struct
{
    /**
     * @var string
     */
     public $orderNo;

    /**
     * @var float
     */
     protected $price = 0.0;

    /**
     * @var MemberStruct
     */
     public $customer;

    /**
     * @var OrderItemStruct[]
     */
     public $items;
}
```

### 使用结构体

1、实例化结构体

* 从数组实例化

> 注意：数据类型会在初始化的时候进行强制转换，如果数据源的数据类型没法转换成结构体定义的数据类型，会使用结构体定义的默认值，如果没有定义默认值，则是NULL。

```php
<?php
$orderItem = OrderItemStruct::factory([
    'name' => '板蓝根冲剂',
    'unitPrice' => '12.30',
]);
```

* 从Model的对象直接实例化

```php
<?php
$order = Order::fineFirst(1);
$orderStruct = OrderStruct::factory($order);
```

2、结构体输出

* 转换成数组：`toArray()` 方法
* 转换成JSON：`toJson()` 方法

### 预定义的结构体

* 分页器结构体

分页器结构体是用于直接将Phalcon的分页结果（通过 `getPaginate()` 方法分页查询的结果）直接转换成结构体。

需要为不同的分页数据定义一个`body`属性。

```php
<?php
namespace App\Structs;

class AreaPaginatorStruct extends PaginatorStruct
{
    /**
     * @var \App\Structs\AreaStruct[]
     */
    public $body = [];
}

....

        // 分页查询
        $queryBuilder = new QueryBuilder(
            [
                'builder' => $builder,
                'limit'   => $limit,
                'page'    => $page,
            ]
        );

        // 构造结构体
        return AreaPaginatorStruct::factory($queryBuilder->getPaginate());

```

输出结构，详见`PaginatiorStruct`和`PagingStruct`：

```php
[
    'body' => [
       ...
    ],
    'paging' => [
       ...
    ]
]
```

* 分页请求结构体

`PageRequestStruct`，主要用户分页请求。通常作为分页请求的参数的结构体的基类。
