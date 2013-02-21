<?php
/*
 * @version $Id: newthread.hack.php 627 2011-03-11 03:05:39Z yaoying $
 */
if( !defined('IS_IN_XWB_PLUGIN') ){
	exit('Access Denied!');
}
global $_G;


$tid = isset($GLOBALS['tid'])  ? (int)$GLOBALS['tid'] : 0;
$pid = isset($GLOBALS['pid'])  ? (int)$GLOBALS['pid'] : 0;

if( $tid >= 1 && $pid >= 1 ){
	if (XWB_plugin::V('p:syn')) {
		//由于采取register_shutdown_function，并且在模板输出后，output函数会赋值清空ftp的相关设置，故对相关变量进行保存
		$_G['xwb_ftp_remote_url'] = isset($_G['setting']['ftp']['attachurl']) ? $_G['setting']['ftp']['attachurl'] : '';
		$xp_publish = XWB_plugin::N('xwb_plugins_publish');
		register_shutdown_function(array(&$xp_publish, 'thread'), (int)$tid, (int)$pid, (string)$GLOBALS['subject'], (string)$GLOBALS['message']);

		
	}		
}