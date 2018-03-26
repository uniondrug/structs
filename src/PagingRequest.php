<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-03-26
 */
namespace Uniondrug\Structs;

/**
 * 发送分页请求
 * @package Uniondrug\Structs
 */
abstract class PagingRequest extends Struct
{
    /**
     * 请求页码
     * @var int
     * @Validator(type=int,default=1,filter={int})
     */
    public $page = 1;

    /**
     * 每页数量
     * @var int
     * @Validator(type=int,default=10,filter={int})
     */
    public $limit = 10;
}
