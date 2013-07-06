<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: thread_album.php 28709 2012-11-08 08:53:48Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
require_once libfile('function/attachment');
$imglist = array();
foreach(C::t('forum_attachment_n')->fetch_all_by_id('tid:'.$_G['tid'], 'tid', $_G['tid'], 'aid') as $attach) {
	if($attach['uid'] != $_G['forum_thread']['authorid']) {
		continue;
	}
	$extension = strtolower(fileext($attach['filename']));
	$attach['ext'] = $extension;
	$attach['imgalt'] = $attach['isimage'] ? strip_tags(str_replace('"', '\"', $attach['description'] ? $attach['description'] : $attach['filename'])) : '';
	$attach['attachicon'] = attachtype($extension."\t".$attach['filetype']);
	$attach['attachsize'] = sizecount($attach['filesize']);
	if($attach['isimage'] && !$_G['setting']['attachimgpost']) {
		$attach['isimage'] = 0;
	}
	$attach['attachimg'] = $attach['isimage'] && (!$attach['readperm'] || $_G['group']['readaccess'] >= $attach['readperm']) ? 1 : 0;
	if(!$attach['attachimg']) {
		continue;
	}
	$attach['url'] = ($attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']).'forum/';
	$attach['dbdateline'] = $attach['dateline'];
	$attach['dateline'] = dgmdate($attach['dateline'], 'u');
	$imglist['aid'][] = $attach['aid'];
	$imglist['url'][] = $attach['url'].$attach['attachment'];
	$apids[] = $attach['pid'];
}
if(empty($imglist)) {
	showmessage('author_not_uploadpic');
}
foreach($postlist as $key=>$subpost) {
	if($subpost['first'] == 1 || in_array($subpost['pid'], $apids)) {
		unset($postlist[$key]);
	}
}
?>