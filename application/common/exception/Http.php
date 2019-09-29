<?php
/**
 * Created by PhpStorm.
 * User: zyw
 * Date: 2019/9/27
 * Time: 14:34
 */

namespace app\common\exception;


use app\common\output\Json;
use Exception;
use think\exception\Handle;

class Http extends Handle
{
    public function render(Exception $e)
    {
        $message = $e->getMessage();
        $code = $e->getCode();
        try {
            $info = unserialize($message);
            $msg = $info['show'];
        } catch (\Exception $e) {
            $msg = '';
        }
        return Json::error($code, $msg);
    }
}