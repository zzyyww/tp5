<?php
/**
 * Created by PhpStorm.
 * User: zyw
 * Date: 2019-04-26
 * Time: 13:20
 */

namespace app\common\exception;



class AppException extends \Exception
{
    public function __construct($code, $msg, $showMsg = '')
    {
        $message = $this->getMsg($msg, $showMsg);
        parent::__construct($message, $code);
    }

    private function getMsg($msg, $showMsg)
    {
        $message = serialize(['show'=>$showMsg, 'msg'=>$msg]);

        return $message;
    }
}