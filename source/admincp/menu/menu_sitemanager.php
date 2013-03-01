<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: menu_sitemanager.php 25593 2011-11-15 10:56:04Z yexinhao $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$topmenu['sitemanager'] = '';
	//前面为LANG标注，后面为admin.php?action=sitemanager&operation=filemanager
$menu['sitemanager'][] = array('menu_sitemanager_todo', 'sitemanager_todo');
$menu['sitemanager'][] = array('menu_sitemanager_filemanager', 'sitemanager_filemanager');
$menu['sitemanager'][] = array('menu_sitemanager_phpmyadmin', 'sitemanager_phpmyadmin');









?>