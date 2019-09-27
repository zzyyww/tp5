<?php
/**
 * Created by PhpStorm.
 * User: zyw
 * Date: 2019-04-26
 * Time: 17:44
 */

namespace app\data\export;



use app\common\enum\Param;
use app\data\mysql\operation\OperateLog;
use app\data\mysql\operation\Operator;
use app\data\mysql\operation\OperatorPrivilege;

class OperatorExport
{
    public static function createToken($opId)
    {
        $originToken = $opId . Param::APP_GLOBAL_SALT . microtime();
        return md5($originToken);
    }

    public static function setToken($token, $opInfo)
    {
        $op = new \app\data\redis\OperatorRedis();
        $op->setOpInfoByToken($token, $opInfo);
    }

    public static function setLastLoginTime($opId)
    {
        return (new Operator())->setLastLoginTime($opId);
    }

    public static function getCurrentOpId()
    {
        $tokenInfo = self::getTokenInfo();
        if (!isset($tokenInfo['op_id'])) {
            return 0;
        } else {
            return intval($tokenInfo['op_id']);
        }
    }

    public static function getTokenInfo()
    {
        $token = InputExport::getToken();
        if (empty($token)) {
            $info = [];
        } else {
            $info = (new \app\data\redis\OperatorRedis())->getTokenInfo($token);
        }

        return $info;
    }

    public static function getPassByOpName($opname)
    {
        return (new Operator())->getInfoByUseranme($opname);
    }

    public static function getPassword($originPassword)
    {
        return md5($originPassword . Param::APP_GLOBAL_SALT);
    }

    public static function getPrivilegeByOpId($opId)
    {
        return (new OperatorPrivilege())->getPrivilegeByUserId($opId);
    }

    public static function addOpLog($info)
    {
        $insertInfo = [
            'op_id' => self::getCurrentOpId(),
            'op_time' => time(),
        ];
        return (new OperateLog())->add(array_merge($info, $insertInfo));
    }
}