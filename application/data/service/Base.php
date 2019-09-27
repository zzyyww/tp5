<?php
/**
 * Created by PhpStorm.
 * UserExport: rex . RenXiaolong
 * Date: 2019-04-26
 * Time: 17:22
 */

namespace app\data\service;


use app\action\Param;
use app\common\exception\AppException;
use app\common\output\Json;
use app\common\tools\curl\Curl;
use think\facade\Log;

class Base
{
    public function __construct()
    {
    }

    /**
     * @param $url
     * @param $param
     * @return mixed
     * @throws AppException
     */
    protected function getCall($url, $param)
    {
        $url .= '?' . $this->getParamString($param);
        Log::record($url, '==url==');
        $res = Curl::get($url);
        Log::record($res, '========result=========');
        return $this->handleCallRes($res);
    }

    protected function postCall($url, $param)
    {
        $param = $this->getParamList($param);
        Log::record([$url, $param], '==url==');
        $res = Curl::post($url, $param);

        return $this->handleCallRes($res);
    }

    private function getParamList($param)
    {
        $commonParam = (new Param())->getServiceCommonParam();
        $param = array_merge($param, $commonParam);

        return $param;
    }

    /**
     * @param $param
     * @return string
     * @throws AppException
     */
    private function getParamString($param)
    {
        $commonParam = (new Param())->getServiceCommonParam();
        $param = array_merge($param, (array)$commonParam);
        $str = '';
        foreach ($param as $key => $value) {
            $str .= '&' . $key . '=' . $value;
        }

        $str = trim($str, '&');

        return $str;
    }

    /**
     * @param $res
     * @return mixed
     * @throws AppException
     */
    private function handleCallRes($res)
    {
        $data = json_decode($res, true);
        if (empty($data)) {
            throw new AppException(111, $res, '服务端错误');
        }
        if (isset($data['status'])) {
            if (200 !== $data['status']) {
                throw new AppException($data['status'], $res, '');
            }
        } elseif (isset($data['code'])) {
            if ($data['code'] == 200) {
                return $data;
            } else {
                throw new AppException(333, $res, '服务端错误');
            }
        } else {
            throw new AppException(222, $res, '服务端错误');
        }
        if (isset($data['extra'])) {
            Json::setExtra($data['extra']);
        }
//        if (isset($data['bucket'])) {
//            Json::setExtra(['bucket'=>$data['bucket']]);
//        }
        return $data['contents'];
    }
}