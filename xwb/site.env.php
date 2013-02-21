<?php

/**
 * 插件附属站点的信息
 * @author yaoying <yaoying@staff.sina.com.cn>
 * @copyright SINA INC.
 * @version $Id: site.env.php 785 2011-05-25 06:18:31Z yaoying $
 *
 */

if( !defined('IS_IN_XWB_PLUGIN') ){
    exit('Access Denied!');
}

/// 附属站点的环境提取
if( !defined('IN_DISCUZ') ){
    require_once XWB_S_ROOT . '/source/class/class_core.php';
    require_once XWB_S_ROOT . '/source/function/function_forum.php';
    require_once XWB_S_ROOT . '/source/function/function_member.php';
    $discuz = & discuz_core::instance();
    //$discuz->init_cron = false;
    //$discuz->init_misc = false;
    $discuz->init();
    runhooks();
	//dx的设置文件中有个['output']['forceheader']，为1时会强制输出一个header编码，故只能如此处理，防止干扰插件，但不能做到100%完美
    if( 0 != $discuz->config['output']['forceheader'] && 'UTF-8' != strtoupper($discuz->config['output']['charset']) ){
    	@header("Content-type: text/html; charset=utf-8");
    }
    
//在钩子环境中，可能无法读取$discuz实例，因此要做如此处理
}elseif( !isset($discuz) || !is_a($discuz, 'discuz_core') ){
	$discuz = & discuz_core::instance();
}


$GLOBALS[XWB_SITE_GLOBAL_V_NAME]['site_db'] = & DB::object();

// 附属站点所用的字符集 UTF8 GBK BIG5
define('XWB_S_CHARSET',		str_replace("-","",strtoupper($discuz->config['output']['charset'])));
// 附属站点所用的表前缀
define('XWB_S_TBPRE', $discuz->config['db']['1']['tablepre']);
// 附属站点 的版本号
define('XWB_S_VERSION',		substr($discuz->var['setting']['version'], 1));

// 附属站点 的类型名称
define('XWB_S_NAME',		'DiscuzX');

// 附属站点 的标题名称
define('XWB_S_TITLE',		XWB_plugin::convertEncoding($discuz->var['setting']['bbname'], XWB_S_CHARSET, 'UTF-8'));
// 附属站点 的用户UID
define('XWB_S_UID',		(int)($discuz->var['uid']));

define('XWB_S_IS_ADMIN',	( ((int)($discuz->var['adminid']) == 1) ? true : false ));

if( !defined('CURSCRIPT') || CURSCRIPT == '' ){
	$XWB_S_CURSCRIPT = isset($_SERVER['SCRIPT_FILENAME']) ? substr( basename($_SERVER['SCRIPT_FILENAME']), 0, -4 ) : 'unknown';
	define('XWB_S_CURSCRIPT', $XWB_S_CURSCRIPT);
}else{
	define('XWB_S_CURSCRIPT', CURSCRIPT);
}
//echo '<pre>';print_r(get_defined_constants());echo '</pre>';exit;

define('XWB_PLUGIN_SITE_ENV_LOADED',		true);