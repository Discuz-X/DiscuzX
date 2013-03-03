<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forumupload.php 27451 2012-02-01 05:48:47Z monkey $
 */
//note 版块forum >> forumupload(版块列表) @ Discuz! X2.5

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'swfupload';
$_GET['action'] = 'swfupload';
$_GET['operation'] = 'upload';
include_once 'misc.php';

class mobile_api {

	//note 程序模块执行前需要运行的代码
	function common() {}

	//note 程序模板输出前运行的代码
	function output() {}

}

?>