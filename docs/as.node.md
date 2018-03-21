# 树节点

> 树形结构中的单个枝, 依赖单条记录, 一般不独立使用。

* 命名: `Node`
* 空间: `App\Structs\Results\XXX\Node` - 其中 `XXX` 为实际的归属目录。


> 定义Struct结构体 `App\Structs\Results\Menu\Node`, 用于控制返回结果。代码如下

```php
<?php
namespace App\Structs\Results\Menu;

class Node extends Row
{
    /**
     * 通过计算属性getChildren()方法读取子枝
     * @var Node[] 
     */
    public $children;
}
```