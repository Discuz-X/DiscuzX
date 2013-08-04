<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: topicadmin_getip.php 33619 2013-07-17 06:18:28Z andyzheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['group']['allowviewip']) {
	showmessage('no_privilege_viewip');
}

$pid = $_GET['pid'];
$member = array();
$post = C::t('forum_post')->fetch('tid:'.$_G['tid'], $pid, false);
if($post && $post['tid'] == $_G['tid']) {
	$member = getuserbyuid($post['authorid']);
	$member = array_merge($post, $member);
}
if(!$member) {
	showmessage('thread_nonexistence', NULL);
} elseif(($member['adminid'] == 1 && $_G['adminid'] > 1) || ($member['adminid'] == 2 && $_G['adminid'] > 2)) {
	showmessage('admin_getip_nopermission', NULL);
}

$member['iplocation'] = convertip($member['useip']);
$portdata = C::t('common_remote_port')->fetch_by_id_idtype($pid, 'post');

include template('forum/topicadmin_getip');

?>