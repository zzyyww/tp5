<?php
/**
 * Created by PhpStorm.
 * User: zyw
 * Date: 2019-04-25
 * Time: 16:49
 */

namespace app\data\export;


class InputExport
{

    public static function getHeader()
    {
        return $_SERVER;
    }

    public static function getRequestParam()
    {
        $get = request()->get();
        $post = request()->post();
        return $get+$post;
    }

    public static function getCurrentControllerAndAction()
    {
        $controller = request()->controller();
        $action = request()->action();

        return strtolower($controller.'/'.$action);
    }

    public static function getToken()
    {
        $token = isset($_SERVER['HTTP_TOKEN']) ? $_SERVER['HTTP_TOKEN'] : '';

        return $token;
    }

}