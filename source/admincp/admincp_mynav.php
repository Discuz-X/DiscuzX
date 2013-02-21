<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_mynav.php 31327 2012-08-13 07:01:41Z liulanbo $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

if(!$operation || $operation=='mytest'){
	shownav('sitemanager', 'menu_sitemanager_todo');
	$links =   file_get_contents(DISCUZ_ROOT.'../message.txt');
	$links = ltrim($links);
	$name = substr($links, 0, strpos($links, "\n"));
	$links = str_replace($name, '', $links);
	$links = ltrim($links);
	$url = substr($links, 0, strpos($links, "\n"));;
	$links = str_replace($url, '', $links);
	$links = ltrim($links);
	print_r($links);
	file_put_contents(DISCUZ_ROOT.'../message.txt', $links);
	showformheader('flinks', '', 'usefullinksforum');
	showhiddenfields(array('groupid'=>8));
	showtableheader();
	showsetting('flinks_name', 'flinksnew[name]', $name, 'text');
	showsetting('flinks_url', 'flinksnew[url]', $url, 'text');
	showsubmit('flinkssubmit', 'submit', '');
	showtablefooter();
} elseif ($operation=='filemanager'){
	if(empty($admincp) || !is_object($admincp) || !$admincp->isfounder) {
		exit('Access Denied');
	}
	shownav('sitemanager', 'menu_sitemanager_filemanager');
	print_r('解压功能（临时）');
}
?>