<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_sitemanager.php 31327 2012-08-13 07:01:41Z liulanbo $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

if(!$operation || $operation=='todo'){
	shownav('sitemanager', 'menu_sitemanager_todo');
} elseif ($operation=='phpmyadmin'){
	if(empty($admincp) || !is_object($admincp) || !$admincp->isfounder) {
		exit('Access Denied');
	}
	shownav('sitemanager', 'menu_sitemanager_phpmyadmin');
	print_r('<iframe id="frame_content" src="/phpmyadmin" scrolling="yes" frameborder="0" style="position: absolute; left:0px; top:0px; width:100%; border:0px; height: 100%;"></iframe>');
} elseif ($operation=='filemanager'){
	if(empty($admincp) || !is_object($admincp) || !$admincp->isfounder) {
		exit('Access Denied');
	}
	shownav('sitemanager', 'menu_sitemanager_filemanager');
	print_r('<iframe id="frame_content" src="/WebFTP" scrolling="yes" frameborder="0" style="position: absolute; left:0px; top:0px; width:100%; border:0px; height: 100%;"></iframe>');
}
?>