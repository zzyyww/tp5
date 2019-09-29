<?php
/**
 * Created by PhpStorm.
 * User: zyw
 * Date: 2019/9/27
 * Time: 22:18
 */

namespace app\action;


class Base
{
    protected $param;

    public function __construct($param = [])
    {
        $this->param = $param;
    }
}