<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-03-26
 */
namespace Uniondrug\Structs;

/**
 * 分页请求
 * @package Uniondrug\Structs
 */
abstract class PagingRequest extends Struct
{
    /**
     * 请求页码
     * @var int
     * @Validator(options={min:1})
     */
    public $page = 1;

    /**
     * 每页数量
     * @var int
     * @Validator(options={min:1,max:1000})
     */
    public $limit = 10;
}
