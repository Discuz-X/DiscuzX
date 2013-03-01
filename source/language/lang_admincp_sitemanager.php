<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_admincp_sitemanager.php 29721 2012-04-26 07:01:08Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$extend_lang = array
(
	'header_sitemanager' => '网站管理',
	'menu_sitemanager_filemanager' => '文件管理',
	'menu_sitemanager_todo' => '未来大致规划',
	'menu_sitemanager_links' => '链接资源',
	'menu_sitemanager_phpmyadmin' => 'phpMyAdmin',
	'menu_sitemanager_hooker'=> '嵌入点检查'
);


$GLOBALS['admincp_actions_normal'][] = 'sitemanager';

?>