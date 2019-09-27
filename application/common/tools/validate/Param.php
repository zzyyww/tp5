<?php
/**
 * Created by PhpStorm.
 * User: zyw
 * Date: 2019/1/10
 * Time: 8:17
 */


namespace app\common\tools\validate;

/**
 * param 类:参数处理类
 *
 * 公共方法包括:
 * getFormatRule(); //获取格式化的规则列表:
 * getOriginParam(); //获取原始输入参数:
 * checkParamList(); //参数校验:
 * getFormatParam(); //获取经过根据规则补全格式化及校验后的参数集:
 *
 * 参数:
 * $rule: 规则集
 * $param: 待处理参数集
 *
 *
 * $rules 结构
 * [
 *    ['key','fun1:[p1,p2]|fun2:[p3,p4]','type','defaultValue','alias'],
 * ]
 * 参数说明:
 * key: 接收参数时的键名,接收参数为数组,键名与此key对应.
 * fun:[] 需要校验的参数方法,冒号前是方法名,冒号后的方括号及其中是此方法校验所需要的参数
 *        多个方法之间使用 | 分隔符分隔
 *        其中方法名及参数 与本类相同级文件夹中BaseValidate.php文件中方法对应
 *          eg: int:[1,10]|string:[14]  //方法名:int(),参数min=1,max=10;
 *          注意:设置参数时需要将原方法中第一个参数,即原始值省略掉,从第二个参数开始设置.
 * type: 参数需要转换的类型,取值范围参见下方私有属性 $availableType, 如果不需要转换请使用null
 * defaultValue: 参数默认值,即当无法获取参数值时,给赋的值,如果设置为null,则无默认值
 * alias: 输出时的参数名称,如果与输入需要的key值相同,则置空,或省略
 *
 * 使用举例:
 * $rule = [
 *      ['order_no','string:[14]|int','string',null],
 * ];
 * $param = [
 *      'order_no' => '201901100600032',
 * ];
 *
 * $param = new param($param,$rule);
 * $paramRes = $param->getFormatParam($param,$rule);//获取格式化后的参数
 *
 * $check = $param->checkParam();//校验参数
 */

class Param
{

    private $rules = null;//格式化后的能规则
    private $originParam = null;//原始参数
    private $param = null;//格式化后的参数
    private $errorArray = [];
    private $noticeArray = [];
    private $availableType = ['int', 'string', 'array', 'bool'];
    private $baseValidateClassName = 'app\common\tools\validate\BaseValidate';
    const ERROR_LEVEL_LOW = 'low';
    const ERROR_LEVEL_HIGH = 'high';
    const UNDEFINED_TYPE_INTO = 'undefined data type!!!!!';

    private function getError()
    {
        return $this->errorArray;
    }

    private function getParam()
    {
        return $this->param;
    }

    /**
     * 获取格式化后的处理规则
     * renxiaolong by 2019/1/10
     * @return null|array
     * @eg
     * 返回示例
     * $res = [
     *          '要返回参数的key' => ['取参数的key', '校验规则[array]', '参数类型', '默认值', '要求的返回键值'],
     *        ];
     */
    private function getFormatRule()
    {
        return $this->rules;
    }

    //以上为调试方法

    public function __construct($param = null, $rules = null)
    {
        $this->_init($param, $rules, false);
    }

    /**
     * 校验传来参数
     * @param null $param
     * @param null $rules 规则,具体说明见最上面
     * @param string $checkLevel 校验级别 ERROR_LEVEL_HIGH级为遇到错误就返回,ERROR_LEVEL_LOW级为将所有错误都汇总后返回
     * @return mixed
     * @eg
     * $res = [
     *          errorArray, noticeArray, []
     *        ]
     * 用户根据 $res[0],是否为空判断校验是否通过,errorArray中是具体的错误说明,noticeArray暂时没有使用
     */
    public function checkParamList($param = null, $rules = null, $checkLevel = self::ERROR_LEVEL_HIGH)
    {
        $this->_init($param, $rules, true);
        if (!empty($this->errorArray) && $checkLevel == self::ERROR_LEVEL_HIGH) return $this->_returnRes();
        $this->_checkParamByRule($checkLevel);

        return $this->_returnRes();
    }

    /**
     * 获取原始输入参数集
     * renxiaolong by 2019/1/10
     * @return null|array 返回值可能为null 或者 是一个关联数组
     */
    public function getOriginalParam()
    {
        return $this->originParam;
    }

    /**
     * 获取格式化后的参数
     * @param null $param
     * @param null $rules
     * @param string $checkLevel
     * @return array
     * @eg
     * $res = [
     *          errorArray, noticeArray, $dataArray
     *        ]
     * 用户根据 $res[0],是否为空判断获取的参数是否合法,errorArray中是具体的错误说明,$dataArray中是格式化后具体的参数值
     * 注意,如果规则有错误,则$dataArray中为空
     */
    public function getFormatParam($param = null, $rules = null, $checkLevel = self::ERROR_LEVEL_HIGH)
    {
        $this->_init($param, $rules, true);
        if (!empty($this->errorArray) && $checkLevel == self::ERROR_LEVEL_HIGH) {
            return $this->_returnRes($this->param);
        }

        $this->_checkParamByRule($checkLevel);

        if (!empty($this->errorArray)) {
            return $this->_returnRes($this->param);
        }

        return $this->_returnRes($this->param);
    }

    /**
     * 初始化参数及规则
     * renxiaolong by 2019/1/10
     * @param $param array 参数集合
     * @param $rules array 规则集合
     * @param $errorFlag bool 错误记录标识,当通过构造方法初始化参数时,规则可以为null,其他初始化操作如果规则为null则记录错误
     */
    private function _init($param, $rules, $errorFlag = true)
    {
        $this->_setRule($rules, $errorFlag);
        $this->_setOriginParam($param);
        $this->_setFormatParam();
    }

    /**
     * 设置处理规则
     * renxiaolong by 2019/1/10
     * @param $rules array
     * @param $errorFlag bool 错误记录标识,当通过构造方法初始化参数时,规则可以为null,其他初始化操作如果规则为null则记录错误
     */
    private function _setRule($rules, $errorFlag)
    {
        if (is_array($rules)) {
            $this->rules = $this->_getFormatRule($rules);
        } else {
            if (is_null($rules)) {
                if (is_null($this->rules)) {
                    $this->_addError('没有设置规则', $errorFlag);
                }
            } else {
                $this->_addError('规则不合法');
            }
        }
    }

    /**
     * 设置待处理参数
     * renxiaolong by 2019/1/10
     * @param $param
     */
    private function _setOriginParam($param)
    {
        if (!is_null($param)) {
            if ($this->_checkParamFormat($param)) {
                $this->originParam = $param;
            } else {
                $this->_addError('传来的参数不是数组 ' . serialize($param));
            }
        }
    }

    /**
     * 设置格式化后的参数
     * renxiaolong by 2019/1/10
     * @return bool
     */
    private function _setFormatParam()
    {
        if (!empty($this->errorArray) || is_null($this->rules)) {
            return false;
        }
        $param = [];
        foreach ($this->rules as $key => $rule) {
            $paramVal = isset($this->originParam[$rule['key']]) ? $this->originParam[$rule['key']] : $rule['default'];
            //格式化
            $paramVal = $this->_formatVal($paramVal, $rule['type']);
            $param[$key] = $paramVal;
        }
        $this->param = $param;
        return true;
    }

    /**
     * 根据已经设置好的规则进行参数校验
     * @param $checkLevel string 校验级别和前方法要求相同
     * @return bool
     */
    private function _checkParamByRule($checkLevel)
    {
        if (!in_array($checkLevel, [self::ERROR_LEVEL_HIGH, self::ERROR_LEVEL_LOW])) {
            $this->_addError('参数:checkLevel值传入错误');
            return false;
        }
        if (is_null($this->rules)) {
            return false;
        }

        foreach ($this->rules as $key => $val) {
            $waitCheckVal = $this->param[$key];

            if (is_null($waitCheckVal)) {
                $this->_addError($val['key'] . ' 是必传参数,必须有值');
                if ($checkLevel == self::ERROR_LEVEL_HIGH) return false;//如果是高级别,遇错误即时返回,下同
            }

            $funList = $val['fun'];
            $res = $this->_checkValueByFunList($key, $waitCheckVal, $funList, $checkLevel);
            if ($res === false && $checkLevel == self::ERROR_LEVEL_HIGH) return false;
        }

        if (empty($this->errorArray)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 根据传来的参数值和校验方法进行校验
     * renxiaolong by 2019/1/10
     * @param $key
     * @param $val
     * @param $funList
     * @param $checkLevel
     * @return bool
     */
    private function _checkValueByFunList($key, $val, $funList, $checkLevel)
    {
        foreach ($funList as $fun) {
            $funName = $fun[0];
            $funParam = array_merge([$val], $fun[1]);
            try {
                $res = call_user_func_array([new $this->baseValidateClassName, $funName], $funParam);
                if (!is_bool($res)) {
                    $this->_addError($res . ' ' . $funName);
                    if ($checkLevel == self::ERROR_LEVEL_HIGH) return false;
                }

                if ($res === false) {
                    $this->_addError('参数检验未通过: [' . $key . '=>' . $val . '] ' . $funName . '()');
                    if ($checkLevel == self::ERROR_LEVEL_HIGH) return false;
                }
            } catch (\Exception $e) {
                $this->_addError($e->getMessage());
                if ($checkLevel == self::ERROR_LEVEL_HIGH) return false;
            }
        }

        if (empty($this->errorArray)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 格式化单条规则
     * renxiaolong by 2019/1/10
     * @param $rules
     * @return array
     */
    private function _getFormatRule($rules)
    {
        /*
         * ['key','fun:[]','type','defaultValue','alias']
         */
        $res = [];
        foreach ($rules as $rule) {
            if (!is_array($rule)) {
                $this->_addError('规则条目不合法 ' . serialize($rule));
                continue;
            }
            $key = $this->_getRuleKey($rule);
            if (!is_null($key)) {
                $ruleKey = $this->_getRuleReturnName($rule, $key);
                $res[$ruleKey] = [
                    'key' => $key,
                    'fun' => $this->_getRuleFunCond($rule),
                    'type' => $this->_getRuleType($rule),
                    'default' => $this->_getRuleDefaultValue($rule),
                    'returnName' => $ruleKey
                ];
            }
        }

        return $res;
    }

    /**
     * 获取规则中的Key
     * @param $rule
     * @return null|string 如果正常返回string,如果不正常返回null
     */
    private function _getRuleKey($rule)
    {
        $key = isset($rule[0]) ? strval($rule[0]) : '';
        //数字不能开头
        if (!$this->_checkVarAndFunName($key)) {
            $key = '';
            $this->_addError($key . '规则中Key不合法');
        }
        if (empty($key)) {
            $key = null;
        }

        return $key;
    }

    /**
     * 从单个规则中格式化出条件参数
     * renxiaolong by 2019/1/10
     * @param $rule
     * @return array
     */
    private function _getRuleFunCond($rule)
    {
        /*
         * "fun1:[p1,p2]|fun2:[p3,p4]"
         */
        $funInfo = isset($rule[1]) ? $rule[1] : '';
        $resFun = [];
        if (empty($funInfo)) {
            $resFun = [];
        }

        if (is_string($funInfo)) {
            $funList = explode('|', $funInfo);
            foreach ($funList as $value) {
                $fun = explode(':', $value);
                if ($this->_checkVarAndFunName($fun[0])) {
                    $funName = $fun[0];
                    if (isset($fun[1])) {
                        $funParam = $this->_getFunParam($fun[1]);
                    } else {
                        $funParam = [];
                    }
                    $resFun[] = [$funName, $funParam];
                } else {
                    $this->_addError($value . ' 校验方法名错误');
                }
            }
        }

        return $resFun;
    }

    /**
     * 从单个规则中格式化出参数类型
     * renxiaolong by 2019/1/10
     * @param $rule
     * @return null|string
     */
    private function _getRuleType($rule)
    {
        $type = isset($rule[2]) ? strval($rule[2]) : null;
        if (is_null($type)) {
            return $type;
        }

        if (in_array($type, $this->availableType)) {
            return $type;
        } else {
            $this->_addError($type . ' 属于未知类型');
            return null;
        }
    }

    /**
     * 从单个规则中格式化出默认值
     * renxiaolong by 2019/1/10
     * @param $rule
     * @return null
     */
    private function _getRuleDefaultValue($rule)
    {
        $defaultValue = isset($rule[3]) ? $rule[3] : null;

        return $defaultValue;
    }

    /**
     * 从单个规则中格式化出返回键名
     * renxiaolong by 2019/1/10
     * @param $rule
     * @param $defaultName
     * @return string
     */
    private function _getRuleReturnName($rule, $defaultName)
    {
        $returnName = isset($rule[4]) ? strval($rule[4]) : '';

        if (!empty($returnName)) {
            if ($this->_checkVarAndFunName($returnName)) {
                return $returnName;
            } else {
                $this->_addError($returnName . ' 设置的返回键值不合法');
                return $defaultName;
            }
        } else {
            return $defaultName;
        }
    }

    /**
     * 检测变量命名及函数名命名
     * renxiaolong by 2019/1/10
     * @param $var
     * @return bool
     */
    private function _checkVarAndFunName($var)
    {
        if (intval($var) > 0) {
            return false;
        }

        if (empty($var)) {
            return false;
        }

        return true;
    }

    /**
     * 从条件中中格式化出校验函数的参数值
     * renxiaolong by 2019/1/10
     * @param $var
     * @return mixed|null
     */
    private function _getFunParam($var)
    {
        try {
            $res = json_decode($var, true);
        } catch (\Exception $e) {
            $this->_addError(serialize($var) . '校验参数错误');
            $res = [];
        }

        return $res;
    }

    /**
     * 添加错误信息
     * renxiaolong by 2019/1/10
     * @param $msg
     * @param bool $flag
     */
    private function _addError($msg, $flag = true)
    {
        if ($flag) {
            array_push($this->errorArray, $msg);
        }
    }

    /**
     * 添加提示信息
     * renxiaolong by 2019/1/10
     * @param $msg
     * @param bool $flag
     */
    private function _addNotice($msg, $flag = true)
    {
        if ($flag) {
            array_push($this->noticeArray, $msg);
        }
    }

    /**
     * 校验传入参数的基本类型
     * renxiaolong by 2019/1/10
     * @param $param
     * @return bool
     */
    private function _checkParamFormat($param)
    {
        if (!is_array($param)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 定义标准输出格式
     * renxiaolong by 2019/1/10
     * @param array $data
     * @return array
     */
    private function _returnRes($data = [])
    {
        return [$this->errorArray, $this->noticeArray, $data];
    }

    /**
     * 从默认地方设置待处理参数(当前默认值是http请求)
     * renxiaolong by 2019/1/10
     */
    private function _setParamDefault()
    {
        $param = $_SERVER;
        if (isset($_POST)) {
            $param += $_POST;
        }
        if (isset($_GET)) {
            $param += $_GET;
        }
        $this->originParam = $param;
    }

    /**
     * 将变量转换成要求的类型
     * renxiaolong by 2019/1/10
     * @param $content
     * @param $type
     * @return bool|float|int|mixed|null|string
     */
    private function _formatVal($content, $type)
    {
        //变量如果是null,但是要求的类型不是null的不能初始化
        if (is_null($content) && !is_null($type)) {
            return $content;
        }

        switch ($type) {
            case 'string':
                $content = strval(urldecode($content));
                break;
            case 'int':
                $content = intval($content);
                break;
            case 'double':
                $content = doubleval($content);
                break;
            case 'bool':
                $content = boolval($content);
                break;
            case 'null':
                $content = null;
                break;
            case 'array':
                $content = $this->_formatArray($content);
                break;
            case 'float':
                $content = floatval($content);
                break;
            default:
                $this->_addError(serialize($content) . ' , ' . self::UNDEFINED_TYPE_INTO);
                $content = self::UNDEFINED_TYPE_INTO;
        }
        return $content;
    }

    /**
     * 将索引格式的字符串变量格式化成索引数组
     * renxiaolong by 2019/1/10
     * @param $var
     * @return mixed|null
     */
    private function _formatArray($var)
    {
        try {
            if (is_array($var)) {
                $con = $var;
            } else {
                $con = json_decode($var);
            }
        } catch (\Exception $e) {
            //halt($e->getMessage());
            $this->_addError(serialize($var) . ' 格式化成数组时失败 ' . $e->getMessage());
            $con = null;
        }

        return $con;
    }
}