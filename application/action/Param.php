<?php
/**
 * Created by PhpStorm.
 * User: zyw
 * Date: 2019/9/27
 * Time: 22:23
 */

namespace app\action;

use app\common\tools\validate\ValidateParam;
use app\data\export\InputExport;
use app\data\export\OperatorExport;

class Param
{
    private $headerParamKeyRule = [
        ['token', 'string:[0]', 'string', null, 'token'],
    ];
    private static $requestCommonParam = null;
    private static $serviceCommonParam = null;
    private static $requestParam = null;
    private $signature;
    private $requestTimestamp;

    public function __construct()
    {
        //保存请求的公共参数
        $this->setRequestCommonParam();
        //验证并设置请求子服务时的公共参数
        $this->setServiceCommonParam();
        //接口参数设置
        $this->setRequestParam();
    }

    /**
     * 设置请求公共参数
     */
    private function setRequestCommonParam()
    {
        if (is_null(self::$requestCommonParam)) {
            $headerList = InputExport::getHeader();
            $param = [];
            foreach ($this->headerParamKeyRule as $value) {
                $key = 'HTTP_' . strtoupper($value[0]);
                if (isset($headerList[$key])) {
                    $param[$value[0]] = $headerList[$key];
                }
            }

            self::$requestCommonParam = $param;
        }
    }

    private function setServiceCommonParam()
    {
        $controller = request()->controller();
        if ($controller == 'Index') {
            $rule = [
                ['token', 'string:[0]', 'string', '', 'token'],
            ];
        } else {
            $rule = $this->headerParamKeyRule;
        }
        $param = ValidateParam::getFormatParam(self::$requestCommonParam, $rule);
        $param['user_id'] = OperatorExport::getCurrentOpId();
        $param['impression_id'] = ImpressionId::get();
        unset($param['token']);
        self::$serviceCommonParam = $param;
    }

    private function setRequestParam()
    {
        if (is_null(self::$requestParam)) {
            $param = InputExport::getRequestParam();
            if (isset($param['sign'])) {
                $this->signature = $param['sign'];
                unset($param['sign']);
            }
            if (isset($param['time'])) {
                $this->requestTimestamp = $param['time'];
                unset($param['time']);
            }
            self::$requestParam = $param;
        }
    }

    /**
     * @return null
     * @throws \app\common\exception\AppException
     */
    public function getServiceCommonParam()
    {
        return self::$serviceCommonParam;
    }

    /**
     * 检验参数签名
     * TODO
     */
    public function checkSignature()
    {

    }

    public function getRequestParam($rule)
    {
        $param = ValidateParam::getFormatParam(self::$requestParam, $rule);

        return $param;
    }
}