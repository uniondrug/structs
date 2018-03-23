# 树形结构

> 多条记录, 依赖单条记录

* 命名: `Tree`
* 空间: `App\Structs\Results\XXX\Tree` - 其中 `XXX` 为实际的归属目录。


> 定义Struct结构体 `App\Structs\Results\Menu\Tree`, 用于控制返回结果。代码如下

```php
<?php
namespace App\Structs\Results\Menu;

use Uniondrug\Structs\ListStruct;

class Tree extends ListStruct
{
    /**
     * 定义树中的N个枝
     * @var Node[] 
     */
    public $body;
}
```

> 逻辑运算

```php
<?php
namespace App\Controllers;

use App\Controllers\Abstracts\Base;
use App\Structs\Results\Menu\Tree;

class ExampleController extends Base
{
    public function indexAction()
    {
        $struct = ExampleStruct::factory($this->request->getJsonRawBody());
        $lists = $this->menuService->getTree($struct);
        $result = Tree::factory($lists);
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
                "name" : "菜单名称-1", 
                "children" : [
                    {
                        "menuId" : 11, 
                        "name" : "菜单名称-11", 
                        "children" : [
                            {
                                "menuId" : 111, 
                                "name" : "菜单名称-111", 
                                "children" : []
                            }
                        ]
                    },
                    {
                        "menuId" : 12, 
                        "name" : "菜单名称-12",
                        "children" : []
                    }
                ]
            },
            {
                "menuId" : 2,
                "name" : "菜单名称-2",
                "children" : []
            }
        ]
    }
}
```