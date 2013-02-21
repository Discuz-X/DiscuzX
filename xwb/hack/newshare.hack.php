<?php
/*
 * @version $Id: newshare.hack.php 459 2010-12-23 09:06:50Z yaoying $
 */
if( !defined('IS_IN_XWB_PLUGIN') ){
	exit('Access Denied!');
}
global $_G;
$arr = isset($GLOBALS['arr']) ? (array)$GLOBALS['arr'] : array();
$sid = isset($GLOBALS['sid']) ? (int)$GLOBALS['sid'] : 0;
$p = XWB_plugin::O('xwbUserProfile');
$share2weibo = (int)($p->get('share2weibo',0));
if( $sid > 0 && !empty($arr) && $share2weibo === 1 ){
	$xp_publish = XWB_plugin::N('xwb_plugins_publish');
	register_shutdown_function(array(&$xp_publish, 'shareSync'), $sid, $arr);
}