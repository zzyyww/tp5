<?php
/**
 * Created by PhpStorm.
 * User: zyw
 * Date: 2019-04-25
 */

namespace app\common\output;


use app\action\ImpressionId;
use think\facade\Log;

class Json
{
    private static $extra = [];

    /**
     * @param $ret  int 返回码 1-正常,2-异常
     * @param $data array   要返回的数据
     * @param $exitStatus   int 是否截断
     * @return array|false|string
     */
    private static function export($ret, $data, $exitStatus)
    {
        $res = [
            'impression_id' => ImpressionId::get(),
            'ret'           => intval($ret),
        ];
        $res = array_merge($res, $data);
        $resLog = $res;
        if (isset($resLog['data'])) {
            $resLog['data'] = ['info' => '内容已省略'];
        }
//        unset($resLog['data']);
        $resJson = json($res);
        Log::record(json_encode($resLog, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'log');
        if ($exitStatus != 0) {
            header("Content-type: application/json");
            exit($resJson->getContent());
        }
        return $resJson;
    }

    /**
     * 错误输出
     * @param $code int 错误编码
     * @param $msg  string 错误信息说明
     * @param $extra mixed 需要输出的文案
     * @param $exitStatus int 结束程序运行标记 0-不结束,1-立即结束
     * @return array|string
     */
    public static function error($code, $msg, $extra = null, $exitStatus = 0)
    {
        if (is_null($extra)) {
            $extra = (object)[];
        }
        $data = [
            'code'  => intval($code),
            'msg'   => $msg,
            'extra' => $extra,
        ];
        return self::export(2, $data, $exitStatus);
    }

    /**
     * 正常输出返回
     * @param $data
     * @param int $exitStatus
     * @param array $extra
     * @return array|string
     */
    public static function success($data = null, $exitStatus = 0)
    {
        if (is_null($data)) {
            $data = (object)[];
        }
        if (empty(self::$extra)) {
            self::$extra = (object)[];
        }
        $res = [
            'data'  => $data,
            'extra' => self::$extra,
        ];

        return self::export(1, $res, $exitStatus);
    }

    public static function setExtra($param)
    {
        self::$extra = array_merge(self::$extra, $param);
    }
}
