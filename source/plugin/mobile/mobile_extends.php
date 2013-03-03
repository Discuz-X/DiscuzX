<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: mobile_extends.php 31281 2012-08-03 02:29:27Z zhangjie $
 */

//note 本类用于对手机接口进行开放数据源扩展.
//note 在接口中使用: api/mobile/index.php?module=extends&ac=list获取扩展数据源列表
//note 查看数据源中的列表: api/mobile/index.php?module=extends&identifier=dz_newthread

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}
$_GET['identifier'] = !empty($_GET['identifier']) ? $_GET['identifier'] : '' ;
$_GET['check'] = !empty($_GET['check']) ? $_GET['check'] : '' ;
require_once './source/class/class_core.php';

if(empty($_GET['identifier']) && !empty($_GET['check'])) {
	require_once 'extends/mobile_extends_check.php';
} else {
	require_once 'extends/mobile_extends_list.php';
}


C::app()->init();
define('HOOKTYPE', 'hookscript');
hookscript('common', 'global');
hookscript('global', 'global');

?>