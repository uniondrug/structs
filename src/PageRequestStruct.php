<?php
/**
 * 分页请求的基础结构
 */

namespace Uniondrug\Structs;

class PageRequestStruct extends Struct
{
    /**
     * 请求页码
     *
     * @var int
     * @Validator(type=int,default=1,filter={int})
     */
    public $page = 1;

    /**
     * 每页数量
     *
     * @var int
     * @Validator(type=int,default=10,filter={int})
     */
    public $limit = 10;
}
