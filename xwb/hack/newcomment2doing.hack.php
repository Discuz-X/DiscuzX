<?php
/*
 * @version $Id: newcomment2doing.hack.php 519 2011-01-04 02:04:42Z yaoying $
 */
if( !defined('IS_IN_XWB_PLUGIN') ){
	exit('Access Denied!');
}
global $_G;
$op = isset($_G['gp_op']) ? (string)$_G['gp_op'] : '';
//dz在spacecp_comment.php处可能存在漏洞，用了$_POST
$doid = isset($_G['gp_doid']) ? (int)$_G['gp_doid'] : 0;
//评论的上一id
$up_id = isset($_G['gp_id']) ? (int)$_G['gp_id'] : 0;
$message = !empty($GLOBALS['message']) ? (string)$GLOBALS['message'] : '';
if( $doid > 0 && $up_id == 0 && $op == 'comment'){
	$xp_publish = XWB_plugin::N('xwb_plugins_publish');
	register_shutdown_function(array(&$xp_publish, 'doingCommentSync'), $doid, $message );
}
