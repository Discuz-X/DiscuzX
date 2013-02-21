<?php
/*
 * @version $Id: newarticle.hack.php 453 2010-12-23 04:36:02Z yaoying $
 */
if( !defined('IS_IN_XWB_PLUGIN') ){
	exit('Access Denied!');
}
global $_G;
$aid = isset($GLOBALS['aid']) ? (int)$GLOBALS['aid'] : 0;
$subject = isset($_POST['title']) ? (string)$_POST['title'] : '';
if( $aid >= 1 ){
	if (XWB_plugin::V('p:syn')) {
		$xp_publish = XWB_plugin::N('xwb_plugins_publish');
		register_shutdown_function(array(&$xp_publish, 'articleSync'), (int)$aid, $subject);
	}		
}

