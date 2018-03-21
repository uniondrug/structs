# 单条记录

> 单条记录, 来自单个Model

* 命名: `Row`
* 空间: `App\Structs\Results\XXX\Row` - 其中 `XXX` 为实际的归属目录。


### 示例

> 定义Trait结构 `App\Structs\Traits\MenuTrait`, 其字段来自 `App\Models\Menu` 模型。代码如下

```php
<?php
namespace App\Structs\Traits;

/**
 * Menu Model Trait 
 */
trait MenuTrait 
{
    /**
     * @var int
     */
    public $menuId = 0;
    /**
     * @var string
     */
    public $name;
}
```

> 定义Struct结构体 `App\Structs\Results\Menu\Row`, 用于控制返回结果。代码如下

```php
<?php
namespace App\Structs\Results\Menu;

use Uniondrug\Structs\Struct;

class Row extends Struct
{
    use MenuTrait;
}
```

> 逻辑运算

```php
<?php
namespace App\Controllers;

use App\Controllers\Abstracts\Base;
use App\Structs\Results\Menu\Row;

class ExampleController extends Base
{
    public function indexAction()
    {
        // ... 省略
        $id = 1;
        $model = $this->menuService->getById($id);
        $result = Row::factory($model);
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
        "menuId" : 1, 
        "name" : "菜单名称"
    }
}
```