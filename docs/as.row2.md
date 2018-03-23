# 单条记录

> 单条记录, 来自多个Model, 其它Model的数据通过主Model的计算属性获取，代码如下

* 命名: `Row`
* 空间: `App\Structs\Results\XXX\Row` - 其中 `XXX` 为实际的归属目录。

### 示例

> 定义Trait结构 `App\Structs\Traits\MerchantTrait`, 其字段来自 `App\Models\Menu` 模型。代码如下

```php
<?php
namespace App\Structs\Traits;

/**
 * Merchant Model Trait 
 */
trait MerchantTrait 
{
    /**
     * @var int
     */
    public $merchantId = 0;
}
```

```php
<?php
namespace App\Structs\Traits;

/**
 * Contact Model Trait 
 */
trait ContactTrait 
{
    /**
     * 联系电话
     * @var string
     */
    public $mobile;
}
```


> 定义Struct结构体 `App\Structs\Results\Merchant\Row` 和 `App\Structs\Results\Merchant\Contact\Row`, 用于控制返回结果。代码如下

```php
<?php
namespace App\Structs\Results\Merchant;

use Uniondrug\Structs\Struct;

class Row extends Struct
{
    use MerchantTrait;
    /**
     * 通过计算属性`getContact()`获取数据
     * 并将factory()结果赋值给$contact属性 
     * @var Contact\Row
     */
    public $contact;
}
```

```php
<?php
namespace App\Structs\Results\Merchant\Contact;

use Uniondrug\Structs\Struct;

class Row extends Struct
{
    use ContactTrrait;
}
```

> 逻辑运算

```php
<?php
namespace App\Controllers;

use App\Controllers\Abstracts\Base;
use App\Structs\Results\Merchant\Row;

class ExampleController extends Base
{
    public function indexAction()
    {
        // ... 省略
        $id = 1;
        $model = $this->merchantService->getById($id);
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
        "merchantId" : 1, 
        "contact" : {
            "mobile" : "13966013721"
        }
    }
}
```
