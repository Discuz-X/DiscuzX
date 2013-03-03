<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: buyattachment.php 28205 2012-02-24 04:51:47Z monkey $
 */
//note 帖子thread >> buyattachment(购买附件) @ Discuz! X2.5

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'misc';
$_GET['action'] = 'attachpay';
include_once 'forum.php';

class mobile_api {

	//note 程序模块执行前需要运行的代码
	function common() {
	}

	//note 程序模板输出前运行的代码
	function output() {
		global $_G;
		$variable = array(
		    'filename' => $GLOBALS['attach']['filename'],
		    'description' => $GLOBALS['attach']['description'],
		    'authorid' => $GLOBALS['attach']['uid'],
		    'author' => $GLOBALS['attach']['author'],
		    'price' => $GLOBALS['attach']['price'],
		    'balance' => $GLOBALS['balance'],
		    'credit' => mobile_core::getvalues($_G['setting']['extcredits'][$_G['setting']['creditstransextra'][1]], array('title', 'unit')),
		);
		mobile_core::result(mobile_core::variable($variable));
	}

}

?>