<?php
/**
 * Created by PhpStorm.
 * User: zyw
 * Date: 2019/1/10
 * Time: 8:16
 */

namespace app\common\tools\validate;


class BaseValidate
{
    public function __call($name, $arguments)
    {
        return '没有此方法';
    }

    public static function __callStatic($name, $arguments)
    {
        return '没有此静态方法';
    }

    public static function phone($phone)
    {
        return (bool)preg_match("/^1[3456789]\d{9}$/", $phone);
    }

    public static function phoneV2($phone)
    {
        return (bool)preg_match("/^1\d{10}$/", $phone);
    }

    public static function email($email)
    {
        return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public static function int($num, $min = null, $max = null)
    {
        if (!is_numeric($num) || strpos($num, ".") !== false) {
            return false;
        }
        $flag = true;
        if (!is_null($min)) {
            $flag = $flag && ($num >= $min);
        }
        if (!is_null($max)) {
            $flag = $flag && ($num <= $max);
        }
        return $flag;
    }

    public static function string($str, $min = null, $max = null)
    {
        $flag = true;
        $len = mb_strlen($str);
        if (!is_null($min)) {
            $flag = ($flag && ($len >= $min));
        }
        if (!is_null($max)) {
            $flag = ($flag && ($len <= $max));
        }
        return $flag;
    }

    public static function float($number, $int_num = null, $float_num = null)
    {
        //if (!is_numeric($number) || !strpos($number, '.')) {
        if (!is_numeric($number)) {
            return false;
        }
        $flag = true;
        if (!is_null($int_num)) {
            $flag = (strpos($number, '.') <= $int_num);
        }
        if (!is_null($float_num)) {
            $flag = ($flag && ($float_num >= strlen(strstr($number, '.')) - 1));
        }
        return $flag;
    }


    public static function yearAndMonth($date)
    {
        return (bool)preg_match("/^[0-9]{4}-[0-9]{2}$/", $date);
    }

    //验证日期
    public static function date($date)
    {
        return (bool)preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $date);
    }

    //验证完整日期时间
    //任小龙 2018/9/6 修改
    public static function isDateTime($datetime)
    {
        $res = (bool)preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/', $datetime);
        if ($res === true) {
            $timeStr = strtotime($datetime);
            $datetime2 = date("Y-m-d H:i:s", $timeStr);
            if ($datetime === $datetime2) {
                return true;
            }
        }
        return false;
    }

    /**
     * 验证是否为url
     * @param $url
     * @return bool
     * @author huangzhicheng 2018年05月22日
     */
    public static function isUrl($url)
    {
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return true;
        } else {
            return false;
        }
    }

    public static function enum($needle, $list)
    {
        if (in_array($needle, $list)) {
            return true;
        } else {
            return false;
        }
    }

    public static function isArray($var)
    {
        return is_array($var);
    }

    public static function isIndexArray($var)
    {
        if (!self::isArray($var)) {
            return false;
        }
        $lastKey = 0;
        foreach ($var as $k => $v) {
            if ($lastKey !== $k) {
                return false;
            }
            $lastKey++;
        }
        return true;
    }

    public static function isExist($var)
    {
        try {
            is_null($var);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}