<?php
/**
 * 插件程序启动文件  For Discuz
 * @author yaoying
 * @author junxiong
 * @copyright SINA INC.
 * @version $Id: plugin.env.php 578 2011-01-19 02:34:16Z yaoying $
 *
 */

//插件常量定义
require_once dirname(__FILE__). '/common.cfg.php';

//插件appkey定义
if (file_exists(XWB_P_ROOT.'/app.cfg.php')) {
	require_once XWB_P_ROOT.'/app.cfg.php';
}

//插件通用库引入
require_once XWB_P_ROOT.'/lib/compat.inc.php';
require_once XWB_P_ROOT.'/lib/core.class.php';

//现阶段在本插件中主要用于存储db类。
//要注意附属站点的环境可能会会覆盖这个变量里面的内容
$GLOBALS[XWB_SITE_GLOBAL_V_NAME]	=  array();

/// 引入附属站点的环境
require_once XWB_P_ROOT.'/site.env.php';

//从common.cfg.php移出的内容
//要注意附属站点的环境可能会会覆盖这个变量里面的内容
//插件所需数据和php4兼容方案安全性初始化
$GLOBALS['__CLASS'] = array();
$GLOBALS['xwb_tips_type'] = '' ;

//初始单例化一个client user用户（兼初始化session）
$sess = XWB_plugin::getUser();

if ( !defined('IN_XWB_INSTALL_ENV') ){
	if( defined('XWB_S_UID') &&  XWB_S_UID ){
		$bInfo = XWB_plugin::getBindInfo ();
		if (!empty ($bInfo) && is_array ($bInfo)) {
			$keys = array ('oauth_token' => $bInfo ['token'], 'oauth_token_secret' => $bInfo ['tsecret'] );
			$sess->setInfo( 'sina_uid', $bInfo ['sina_uid'] );
			$sess->setOAuthKey( $keys, true );
		}
	}
	
	$GLOBALS['xwb_tips_type']  = $sess->getInfo('xwb_tips_type');
	if( $GLOBALS['xwb_tips_type'] ){
		$sess->delInfo('xwb_tips_type');
		setcookie ('xwb_tips_type', '', time () - 3600);
	}
	
	$xwb_token = $sess->getToken ();
	if ( empty($xwb_token) ) {
		$sess->clearToken ();
		setcookie ('xwb_tips_type', '', time () - 3600);
	}
}