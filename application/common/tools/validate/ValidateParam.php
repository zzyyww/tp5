<?php
/**
 * Created by PhpStorm.
 * User: zyw
 * Date: 2019/1/23
 * Time: 14:18
 */

namespace app\common\tools\validate;


use app\common\exception\AppException;

class ValidateParam
{
    /**
     * @param $params
     * @param $rule
     * @return mixed
     * @throws AppException
     */
    public static function getFormatParam($params, $rule)
    {
        $object = new Param($params, $rule);
        list($error, $_, $params) = $object->getFormatParam();
        if (!empty($error)) {
            //TODO 下方的 error[0]以后要改为空值,未来不需要在在接口中输出
            throw new AppException(10008, $error, $error[0]);
        }
        return $params;
    }
}