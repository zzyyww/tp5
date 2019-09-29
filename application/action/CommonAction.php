<?php
/**
 * Created by PhpStorm.
 * User: zyw
 * Date: 2019/9/27
 * Time: 22:19
 */

namespace app\action;

use app\common\exception\AppException;
use app\data\export\InputExport;

class CommonAction
{
    private static $notNeedLoginList = [
        'Index/test',
        'Index/zyw',
    ];

    public function checkLogin()
    {
        $needLoginList = self::getNotNeedLoginList();
        $currentInfo = InputExport::getCurrentControllerAndAction();
        if (!in_array($currentInfo, $needLoginList)) {
            if (!(new OperatorAction())->isLogin()) {
                throw new AppException(180001, '登录验证未通过', '请登录后操作');
            }
        }
    }

    public static function getNotNeedLoginList()
    {
        $res = [];
        foreach (self::$notNeedLoginList as $value) {
            $res[] = strtolower($value);
        }
        return $res;
    }
}