<?php
/*
 * @version $Id: newcomment2blog.hack.php 836 2011-06-15 01:48:00Z yaoying $
 */
if( !defined('IS_IN_XWB_PLUGIN') ){
	exit('Access Denied!');
}
global $_G;
$blogid = isset($_G['gp_id']) ? (int)$_G['gp_id'] : 0;
//dz在spacecp_comment.php处可能存在漏洞，用了$_POST
$idtype = isset($_G['gp_idtype']) ? (string)$_G['gp_idtype'] : '';
$message = !empty($GLOBALS['message']) ? (string)$GLOBALS['message'] : '';
//评论的上一id
$up_cid = isset($_G['gp_cid']) ? (int)$_G['gp_cid'] : 0;
if( $blogid > 0 && $up_cid == 0 && $idtype == 'blogid'){
    $db = XWB_plugin::getDB();
    $query = $db->query("SELECT * FROM " . DB::table('home_blog') ." WHERE blogid='$blogid'");
	$blog = $db->fetch_array($query);
    if( !empty($blog)) {
        $xp_publish = XWB_plugin::N('xwb_plugins_publish');
        register_shutdown_function(array(&$xp_publish, 'blogCommentSync'), $blogid, $blog['uid'], (string)$message);
	}
}
