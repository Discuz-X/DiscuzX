<?php
/*
 * @version $Id: newblog.hack.php 453 2010-12-23 04:36:02Z yaoying $
 */
if( !defined('IS_IN_XWB_PLUGIN') ){
	exit('Access Denied!');
}
global $_G;
$blogid = isset($GLOBALS['newblog']['blogid']) ? (int)$GLOBALS['newblog']['blogid'] : 0;
if( $blogid > 0 && XWB_plugin::V('p:syn') ){
	$xp_publish = XWB_plugin::N('xwb_plugins_publish');
	register_shutdown_function(array(&$xp_publish, 'blogSync'), $blogid, (string)$GLOBALS['newblog']['subject']);
}
