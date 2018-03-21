# 关于结构体

> 结构体Framework中, 工作流

1. `发起请求` - 浏览器/APP等发起一个网络请求
1. `网关分发` - 网关收到请求, 分发到控制器
1. `控 制 器` - 控制器收到网关的分发, 开始业务逻辑处理
    1. `起点` - 将入参转换成标准的Struct结构体
    1. `过程` - 执行业务逻辑过程, 并拿到业务处理结果
    1. `结束` - 将执行过程中拿到的结果(如Array, Model等)转换为结果Struct
    1. `返回` - 触发Response, 将结果Struct转换内容(如: JSON)并输出


### Requeset

1. [读写属性](./rq.rw.md) - 可读, 可写的结构体属性
1. [只读属性](./rq.ro.md) - 只读, 创建后不允许修改
1. [计算属性](./rq.get.md) - 计算, 值来自计算方法获取


### Result

1. [Row](./as.row1.md) - 单条记录, 来自单Model
1. [Row](./as.row2.md) - 单条记录, 来自多个Model(hasOne)
1. [Rows](./as.rows.md) - 记录列表
1. [Paging](./as.paging.md) - 分页列表
1. [Node](./as.node.md) - 树节点
1. [Tree](./as.tree.md) - 树形结构
