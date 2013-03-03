<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: mobile_extends_check.php 31964 2012-10-26 07:27:36Z zhangjie $
 */

//$variable = array();
class mobile_api {

	var $variable = array();

	//note 程序模块执行前需要运行的代码
	function common() {
		//global $variable;

		//note获取已经启用的扩展数据模块
//		$extendlist = array();
//		foreach(C::t('#mobile#mobile_extendmodule')->fetch_all_used() as $module) {
//			unset($module['mid'], $module['available'], $module['modulefile'], $module['displayorder']);
//			$extendlist[] = $module;
//		}

		$this->variable = array(
			'extends' => array(
				'extendversion' => '1',
				'extendlist' => array(
					array(
						'identifier' => 'dz_newthread',
						'name' => lang('plugin/mobile', 'mobile_extend_newthread'),
						'icon' => '0',
						'islogin' => '0',
						'iconright' => '0',
						'redirect' => '',
					),
					array(
						'identifier' => 'dz_newreply',
						'name' => lang('plugin/mobile', 'mobile_extend_newreply'),
						'icon' => '0',
						'islogin' => '0',
						'iconright' => '0',
						'redirect' => '',
					),
					array(
						'identifier' => 'dz_digest',
						'name' => lang('plugin/mobile', 'mobile_extend_digest'),
						'icon' => '0',
						'islogin' => '0',
						'iconright' => '0',
						'redirect' => '',
					),
					array(
						'identifier' => 'dz_newpic',
						'name' => lang('plugin/mobile', 'mobile_extend_newpic'),
						'icon' => '0',
						'islogin' => '0',
						'iconright' => '0',
						'redirect' => '',
					),
				),
			)
		);
//		$this->variable = array(
//			'extends' => array(
//				'extendversion' => '1',
//				'extendlist' => $extendlist,
//			),
//		);
	}
	
	//note 程序模板输出前运行的代码
	function output() {
		//global $variable;
		mobile_core::result(mobile_core::variable($this->variable));
	}
}
?>