<?php
require_once 'apiBase.php';
/**
 * API：加载器
 * @author junxiong<junxiong@staff.sina.com.cn>
 * @since 2011-03-04
 * @copyright Xweibo (C)1996-2099 SINA Inc.
 * @version $Id$
 *
 */
class apiLoader extends apiBase
{
    var $apiRoute = 'apiLoader';
    var $whileList = array('checkApi', 'switchMode');

    /// 初始化
    function apiLoad() {
        parent::apiBase();
    }
    
    function load($A, $P, $T, $F)
    {
        $switch = XWB_plugin::pCfg('switch_to_xweibo');
        list(, $method) = explode('.', $A);

        if( !$switch && !in_array($method, $this->whileList)) $this->_ERHelper('4010005', TRUE, 'load');;

        $this->_validate($A, $P, $T, $F); //检测验证

        ///处理参数集
        $PJDecode = (($tmp = json_decode(preg_replace('#(?<=[,\{\[])\s*("\w+"):(\d{6,})(?=\s*[,\]\}])#si', '${1}:"${2}"', $P), true)) && is_array($tmp)) ? $tmp : array();
        if('null' != strtolower($P) && !$PJDecode) {
            $this->_ERHelper('4030001', TRUE, 'load');
        }
        if( !XWB_plugin::_chkPath($A)) {
            $this->_ERHelper('4010001', TRUE, 'load');
        }

        ///分析路由
        $Route = XWB_plugin::_parseRoute($A);
        if(XWB_R_DEF_MOD_FUNC == $Route[3] || '_' == substr($Route[3], 0, 1)) {
            $this->_ERHelper('4010001', TRUE, 'load');
        }

        ///构建API文件路径
        $FilePath = XWB_P_ROOT . DIRECTORY_SEPARATOR. "xplugin_apis" . DIRECTORY_SEPARATOR . $Route[1] . $Route[2] . '.xapi.php';
        if( !file_exists($FilePath)) {
            $this->_ERHelper('4010002', TRUE, 'load');
        }

        ///引用API文件
        require_once $FilePath;
        if( !class_exists($Route[2])) {
            $this->_ERHelper('4010002', TRUE, 'load');
        }

        ///初始化API类
        $apiHandler = new $Route[2]();
        if( !is_object($apiHandler) || !method_exists($apiHandler, $Route[3])) {
            $this->_ERHelper('4010002', TRUE, 'load');
        }

        ///调用API方法
        $RT = call_user_func_array(array($apiHandler, $Route[3]), $PJDecode);

        return $RT;
    }

    ///验证
    function _validate($A, $P, $T, $F)
    {
        /// 超时检测
        if(XWB_REMOTE_API_TIME_VALIDATY < time() - $T) $this->_ERHelper('4010004');

        $secret = md5(sprintf("#%s#%s#%s#%s#%s#", XWB_APP_KEY, $A, $P, $T, XWB_plugin::pCfg('encrypt_key')));

        if(0 !== strcasecmp($F, $secret)) $this->_ERHelper('4010003', TRUE, 'load');

        return;
    }
}
?>
