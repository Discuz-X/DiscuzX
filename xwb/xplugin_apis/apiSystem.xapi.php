<?php
if( !class_exists('apiBase')) exit('Forbidden');
/**
 * API：模式[检查、开关]
 * @author junxiong<junxiong@staff.sina.com.cn>
 * @since 2011-01-21
 * @copyright Xweibo (C)1996-2099 SINA Inc.
 * @version $Id$
 *
 */
class apiSystem extends apiBase
{
    var $apiRoute = 'apiRelate';

    /// 初始化
    function apiSystem() {
        parent::apiBase();
    }

    /// 检查远程API
    function checkApi() {
        $this->rst = array('ver'=>XWB_P_VERSION, 'chatset'=>XWB_S_CHARSET, 'pro'=>XWB_P_PROJECT, 'switch'=>XWB_plugin::pCfg('switch_to_xweibo'));
        $this->_LogHelper($this->apiRoute . '/checkApi');
        return array('rst'=>$this->rst, 'errno'=>$this->errno, 'err'=>$this->err);
    }

    /// 开启或关闭与Xweibo标准版的关联
    function switchMode($LTXCfg = 1, $UTXCfg = '', $BTXCfg = '') {
        $FTParams = (1 == $LTXCfg) ? array('nbool_LTXCfg' => $LTXCfg, 'http_UTXCfg' => $UTXCfg, 'http_BTXCfg' => $BTXCfg) : array('nbool_LTXCfg' => $LTXCfg);
        if($this->_FTHelper($FTParams)) {
            $config = (1 == $LTXCfg) ? array('switch_to_xweibo' => (int)$LTXCfg, 'url_to_xweibo' => $UTXCfg, 'baseurl_to_xweibo' => $BTXCfg) : array('switch_to_xweibo' => (int)$LTXCfg);
            XWB_plugin::setPCfg($config) || $this->_ERHelper('4021001');
            $this->rst = TRUE;
        }
        $this->_LogHelper($this->apiRoute . '/switchMode');
        return array('rst'=>$this->rst, 'errno'=>$this->errno, 'err'=>$this->err);
    }

    /// 输出Dz或Dx的页header
    function outputHeader($ver = 'dx') {
        $FTParams = array('dzxver_ver' => $ver);
        if($this->_FTHelper($FTParams)) {
            if('dx' == $ver) {
                global $_G;
                ob_start();
                include XWB_P_ROOT . '/tpl/xwb_apihd.tpl.php';
                $buffer = ob_get_contents();
                @ob_end_clean();
                $this->rst = array('header' => $buffer);
            } elseif('dz' == $ver) {

            } else {
                $this->_ERHelper('4030001');
            }
        }
        $this->_LogHelper($this->apiRoute . '/outputHeader');
        return array('rst'=>$this->rst, 'errno'=>$this->errno, 'err'=>$this->err);
    }
}
?>
