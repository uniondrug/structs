# Trait

> 定义返回结构体中的最小单位字段。

* NS: `App\Structs\Traits`
* NM: `NameTrait`


```text
trait MenuTrait
{
    /**
     * @var int
     */
    public $menuId = 0;
    
    /**
     * @var int
     */
    public $parentMenuId = 0;
}
```


### 用法

```text
class Row extends Struct
{
    use MenuTrait;
}
```