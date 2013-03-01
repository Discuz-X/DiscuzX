<?php
/*
 * @version $Id: newreply.hack.php 598 2011-01-24 06:11:35Z yaoying $
 */
if( !defined('IS_IN_XWB_PLUGIN') ){
	exit('Access Denied!');
}
global $_G;
$tid = isset($_G['gp_tid']) ? (int)$_G['gp_tid'] : 0;
$pid = isset($GLOBALS['pid']) ? (int)$GLOBALS['pid'] : 0;

if( $tid >= 1 && $pid >= 1 ){
	$xp_publish = XWB_plugin::N('xwb_plugins_publish');
	register_shutdown_function(array(&$xp_publish, 'reply'), (int)$tid, (int)$pid, (string)$GLOBALS['message'] );
}
