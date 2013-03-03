<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: register.php 27979 2012-02-20 06:01:40Z monkey $
 */
//note 更多more >> register(注册网页) @ Discuz! X2.5

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

include_once 'member.php';

class mobile_api {

	//note 程序模块执行前需要运行的代码
	function common() {
		global $_G;
		if(empty($_POST['regsubmit'])) {
			$_G['mobile_version'] = intval($_GET['version']);
		}
		require_once libfile('class/member');
		$ctl_obj = new register_ctl();
		$ctl_obj->setting = $_G['setting'];
		$ctl_obj->template = 'mobile:register';
		$ctl_obj->on_register();
		if(empty($_POST['regsubmit'])) {
			exit;
		}
	}

	//note 程序模板输出前运行的代码
	function output() {
		mobile_core::result(mobile_core::variable());
	}

}

?>