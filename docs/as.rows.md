# 记录列表

> 多条记录, 依赖单条记录

* 命名: `Rows`
* 空间: `App\Structs\Results\XXX\Rows` - 其中 `XXX` 为实际的归属目录。


> 定义Struct结构体 `App\Structs\Results\Menu\Rows`, 用于控制返回结果。代码如下

```php
<?php
namespace App\Structs\Results\Menu;

use Uniondrug\Structs\ListStruct;

class Rows extends ListStruct
{
    /**
     * 定义结构体中的每条记录的结构
     * @var Row[] 
     */
    public $body;
}
```

> 逻辑运算

```php
<?php
namespace App\Controllers;

use App\Controllers\Abstracts\Base;
use App\Structs\Results\Menu\Rows;

class ExampleController extends Base
{
    public function indexAction()
    {
        $struct = ExampleStruct::factory($this->request->getJsonRawBody());
        $lists = $this->menuService->getList($struct);
        $result = Rows::factory($lists);
        return $this->serviceServer->withObject($result)->response();
    }
}
```

> 返回结果

```json
{
    "errno" : "0", 
    "error" : "", 
    "data" : {
        "body" : [
            {
                "menuId" : 1,
                "name" : "菜单名称-1"
            },
            {
                "menuId" : 2,
                "name" : "菜单名称-2"
            }
        ]
    }
}
```