<?php
/*
 * @version $Id: newdoing.hack.php 453 2010-12-23 04:36:02Z yaoying $
 */
if( !defined('IS_IN_XWB_PLUGIN') ){
	exit('Access Denied!');
}
global $_G;
$newdoid = isset($GLOBALS['newdoid']) ? (int)$GLOBALS['newdoid'] : 0;
$message = !empty($GLOBALS['message']) ? (string)$GLOBALS['message'] : '';
$p = XWB_plugin::O('xwbUserProfile');
$doing2weibo = (int)($p->get('doing2weibo',0));
if( $newdoid > 0 && !empty($message) && 1 == $doing2weibo ){
	$xp_publish = XWB_plugin::N('xwb_plugins_publish');
	register_shutdown_function(array(&$xp_publish, 'doingSync'), $newdoid, $message );
}
