<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: search_qihoo.php 6752 2010-03-25 08:47:54Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(empty($srchtxt) && empty($srchuname)) {
	showmessage('search_invalid', 'forum.php?mod=search');
}

$keywordlist = '';
foreach(explode("\n", trim($qihoo_keyword)) as $key => $keyword) {
	$keywordlist .= $comma.trim($keyword);
	$comma = '|';
	if(strlen($keywordlist) >= 100) {
		break;
	}
}

if($orderby == 'lastpost') {
	$orderby = 'rdate';
} elseif($orderby == 'dateline') {
	$orderby = 'pdate';
} else {
	$orderby = '';
}

$stype = empty($stype) ? '' : ($stype == 2 ? 'author' : 'title');

$url = 'http://search.qihoo.com/usearch.html?site='.rawurlencode(site()).
	'&kw='.rawurlencode($srchtxt).
	'&ics='.CHARSET.
	'&ocs='.CHARSET.
	($orderby ? '&sort='.$orderby : '').
	($srchfid ? '&chanl='.rawurlencode($_G['cache']['forums'][$srchfid]['name']) : '').
	'&bbskw='.rawurlencode($keywordlist).
	'&summary='.$_G['setting']['qihoo']['summary'].
	'&stype='.$stype.
	'&count='.$_G['tpp'].
	'&fw=dz&SITEREFER='.rawurlencode($_G['siteurl']);

dheader("Location: $url");

?>