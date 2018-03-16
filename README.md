# Structs component for uniondrug/framework

结构体工具，用于构造数据结构，规范代码和服务之间的数据交互。

## 使用场景

主要目的是保证在应用中间的业务逻辑层使用统一的结构化数据进行交互，并且保证数据安全可信。

* 控制器接收用户端的输入数据；

* 将数据库Model获取的数据转换为结构体，去掉Model本身的数据操作能力，避免误操作；

## 安装

```shell
$ cd project-home
$ composer require uniondrug/structs
```

## 使用

### 定义结构体

结构体定义了一个数据结构的构成，其属性支持的类型包括标量：`string`/`int`(`integer`)/`boolean`(`bool`)/`float`(`double`)、另一个结构体，或者是由同一类标量、结构体构成的数组。

1、通过属性注释的`@var`注解来定义一个属性的类型；

2、当属性是数组的时候，在类型后面加上`[]`标识；

3、当属性需要设置成只读的时候，使用修饰符`protected`，为了使IDE友好，可以在类注释中添加`@property`注解；

4、如果属性没有设置类型，默认是`string`类型；

> 注意：属性是数组时，其元素必须是一个结构体，或者是相同类型的标量。是标量类型则必须指定是何种类型，比如：`string[]`,`int[]`。

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


### 属性验证

结合`uniondrug/validation`，可以直接用结构体进行数据验证。

#### 验证规则定义

规则通过注解`@Validator`定义，说明如下：

`type`: 字符串或者数组，用到的验证器，可以是单个验证器的名称，也可以是一组验证器。
`required`: true/false，验证是否必填
`empty`: true/false，是否可以为空
`default`: mixed，默认值，如果传入的数据中这个字段为空，或者不存在，则使用该默认值
`options`: 数组，传给各个验证器的参数，具体根据各个验证器的不同而不同。如果是一组验证器，他们公用这个数组，从里面各取所需。
`filters`: 字符串或者数组，定义用到的过滤器。用来过滤输入数据。

```php
/**
 * @property float $price
 */
class OrderStruct extends Struct
{
    /**
     * @var string
     * @Validatior(type=string, required=true, options={min:10, max:20})
     */
     public $orderNo;

    /**
     * @var float
     * @Validator(type=float, required=true)
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

#### 使用结构体验证数据

```php
class IndexController extents Controller
{
    public function indexAction()
    {
        $input = $this->request->getJsonRawBody();

        $orderStruct = OrderStruct::factory($input);

        ....
    }
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

* 从对象直接实例化

首先检查对象是否有结构体需要的属性；

其次检查对象是否有结构体属性同名的方法，有则使用该方法为结构体属性赋值；

最后检查对象是否有`get`+属性名对应的方法，有则使用该方法为结构体属性赋值；

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
