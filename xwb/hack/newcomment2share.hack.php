<?php
/*
 * @version $Id: newcomment2share.hack.php 519 2011-01-04 02:04:42Z yaoying $
 */
if( !defined('IS_IN_XWB_PLUGIN') ){
	exit('Access Denied!');
}
global $_G;
$sid = isset($_G['gp_id']) ? (int)$_G['gp_id'] : 0;
//dz在spacecp_comment.php处可能存在漏洞，用了$_POST
$idtype = isset($_G['gp_idtype']) ? (string)$_G['gp_idtype'] : '';
$message = !empty($GLOBALS['message']) ? (string)$GLOBALS['message'] : '';
//评论的上一id
$up_cid = isset($_G['gp_cid']) ? (int)$_G['gp_cid'] : 0;
if( $sid > 0 && $up_cid == 0 && $idtype == 'sid'){
	$xp_publish = XWB_plugin::N('xwb_plugins_publish');
	register_shutdown_function(array(&$xp_publish, 'shareCommentSync'), $sid, (string)$message);
}
