<?php
/**
 * Created by PhpStorm.
 * User: zyw
 * Date: 2019/9/27
 * Time: 22:21
 */

namespace app\action;

use app\common\exception\AppException;
use app\data\export\OperatorExport;

class OperatorAction extends Base
{
    public function login()
    {
        $opInfo = OperatorExport::getPassByOpName($this->param['username']);
        if (empty($opInfo)) {
            throw new AppException(201, '用户信息不存在', '错误1');
        }
        $password = OperatorExport::getPassword($this->param['password']);
        if ($opInfo['password'] !== $password) {
            throw new AppException(201, '用户密码错误', '错误2');
        }
        $token = OperatorExport::createToken($opInfo['op_id']);
        $user_info = [
            'op_id' => $opInfo['op_id'],
            'username' => $opInfo['username'],
            'nickname' => $opInfo['nickname']
        ];
        OperatorExport::setToken($token, $user_info);
        OperatorExport::setLastLoginTime($opInfo['op_id']);
        $privilege = OperatorExport::getPrivilegeByOpId($opInfo['op_id']);

        return compact('token','user_info', 'privilege');
    }



    public function isLogin()
    {
        $uid = OperatorExport::getCurrentOpId();
        if (0 == $uid) {
            return false;
        } else {
            return true;
        }
    }
}