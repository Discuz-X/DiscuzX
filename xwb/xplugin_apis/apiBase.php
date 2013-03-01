<?php
/**
 * API：基类
 * @author junxiong<junxiong@staff.sina.com.cn>
 * @since 2011-03-03
 * @copyright Xweibo (C)1996-2099 SINA Inc.
 * @version $Id$
 *
 */
class apiBase
{
    var $rst = '';
    var $errno = '0';
    var $err = '';
    var $apiRoute = 'apiBase';

    function apiBase() {}
    
    /// 错误库助手
    function _ERHelper($errno, $halt = false, $funName = '') {
        $error = array(
            '4010001' => 'Routing invalid', //路由无效
            '4010002' => 'Request path is not correct', //请求路径不正确
            '4010003' => 'Signature is not correct', //验证失败
            '4010004' => 'Access time failure', //请求时间失效
            '4010005' => 'API has been closed', //API接口已关闭
            '4020001' => 'Account is binding', //帐号已绑定
            '4020002' => 'Account unbound', //帐号未绑定
            '4020003' => 'Users is not exist', //用户不存在
            '4021001' => 'Configuration file could not write', //配置文件无法写入
            '4030001' => 'Parameter illegal', //参数非法
            '4030002' => 'Illegal operation', //非法操作
            '4030003' => 'Database error', //数据库错误
        );
        $this->rst = false;
        $this->errno = $errno;
        $this->err = $error[$errno];
        
        if($halt) {
            $this->_LogHelper($this->apiRoute . '/' . $funName);
            exit(json_encode(array('rst'=>$this->rst, 'errno'=>$this->errno, 'err'=>$this->err)));
        } else {
            return false;
        }
    }

    /// 过滤助手
    function _FTHelper($params, $AllType= null) {
        foreach($params as $key => $param) {
            $Rx = '';
            $type = $AllType ? $AllType : array_shift(explode('_', $key));
            switch($type) {
                case 'num': $Rx = '#^\d+$#';break;
                case 'idtype': $Rx = '#^(sina_)*uid$#i';break;
                case 'strids': $Rx = '#^(\d+,){0,19}\d+$#';break;
                case 'str': $Rx = '#^[a-zA-Z_]+$#';break;
                case 'nstr': $Rx = '#^[a-zA-Z0-9]+$#';break;
                case 'http': $Rx = '#^http://[a-z0-9-\.\?\=&_@/%\#]*$#';break;
                case 'nbool': $Rx = '#^[0|1]$#';break;
                case 'dzxver': $Rx = '#^dz$|^dx$#';break;
                case 'tznum': $Rx = '#^[1|2]$#';break;
                case 'numid': $Rx = '#^[1-9][0-9]*$#';break;
            }
            if($Rx && !preg_match($Rx, $param)) return $this->_ERHelper('4030001');
        }
        return TRUE;
    }

    /// 数据库助手
    function _DBHelper($Query, $action) {
        $DBHandler = XWB_plugin::getDB();
        switch($action) {
            case 1:     //GET FIRST RECORD
                return $DBHandler->fetch_first($Query);break;
            case 2:     //INSERT, UPDATE, DELETE
                return $DBHandler->query($Query, 'UNBUFFERED') ? TRUE : $this->_ERHelper('4030003');break;
            case 3:     //SELECT
                $RT = $DBHandler->query($Query);
                $RS = array();
                while($row = $DBHandler->fetch_array($RT)) $RS[] = $row;
                return $RS;break;
            default:
                return $this->_ERHelper('4030002');
        }
    }

    /// API日志助手
    function _LogHelper($apiRoute) {
        if( !defined('XWB_LOCAL_API_LOG') || XWB_LOCAL_API_LOG != TRUE){
			return;
		}

        $data = array(
            "\r\n" . str_repeat('-', 45),
            "[REQUEST_URI]:\t\t" . ($_SERVER['REQUEST_URI'] ? $_SERVER['REQUEST_URI'] : '_UNKNOWN_'),
            "[API_ROUTE]:\t\t" . $apiRoute,
            "[ERROR_NO]:\t\t" . $this->errno,
            "[ERROR_MSG]:\t\t" . ($this->err ? $this->err : '_EMPTY_'),
            "[API_RESULT]:\t\t" . (($this->rst && !is_bool($this->rst))?"\r\n".print_r($this->rst, TRUE):(is_bool($this->rst)?($this->rst?'TRUE':'false'):'_EMPTY_')),
            str_repeat('-', 45) . "\r\n\r\n",
        );
        $logFile = XWB_P_DATA.'/api/api_local_log_'. date("Y-m-d_H"). '.txt';
        XWB_plugin::LOG(implode("\r\n", $data), $logFile);
        return;
    }
}
?>
