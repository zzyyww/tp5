<?php
/**
 * Created by PhpStorm.
 * User: zyw
 * Date: 2019/9/27
 * Time: 22:20
 */

namespace app\action;


class ImpressionId
{
    private static $impressionId = null;

    public static function get()
    {
        if (is_null(self::$impressionId)) {
            self::set();
        }
        return self::$impressionId;
    }

    private static function set()
    {
        self::$impressionId = md5(microtime() . mt_rand(10000, 99999));
    }
}