<?php
/**
 * Created by PhpStorm.
 * User: zyw
 * Date: 2019-05-13
 * Time: 15:31
 */

namespace app\index\controller;


use think\App;
use think\Controller;

class Base extends Controller
{
    public function __construct(App $app = null)
    {
        parent::__construct($app);
        //检查登录权限
        (new \app\action\CommonAction())->checkLogin();
    }

}