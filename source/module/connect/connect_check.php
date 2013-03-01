<?php

/**
 *	  [Discuz!] (C)2001-2009 Comsenz Inc.
 *	  This is NOT a freeware, use is subject to license terms
 *
 *	  $Id: connect_check.php 27643 2012-02-08 11:20:46Z zhouxiaobo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/connect');
require_once libfile('function/cloud');

$op = !empty($_G['gp_op']) ? $_G['gp_op'] : '';
if (!in_array($op, array('cookie'))) {
	connect_ajax_ouput_message('0', '1');
}

if ($op == 'cookie') {
	$settings = array();
	$query = DB::query("SELECT skey, svalue FROM ".DB::table('common_setting')." WHERE skey IN ('connect_login_times', 'connect_login_report_date')");
	while ($setting = DB::fetch($query)) {
		$settings[$setting['skey']] = $setting['svalue'];
	}

	if ($settings['connect_login_times'] && (empty($settings['connect_login_report_date']) || dgmdate(TIMESTAMP, 'Y-m-d') != $settings['connect_login_report_date'])) {
		if (!discuz_process::islocked('connect_login_report', 600)) {
			$result = connect_cookie_login_report($settings['connect_login_times']);
			if (isset($result['status']) && $result['status'] == 0) {
				DB::query("REPLACE INTO ".DB::table('common_setting')." (`skey`, `svalue`)
				VALUES ('connect_login_times', '0'), ('connect_login_report_date', '".dgmdate(TIMESTAMP, 'Y-m-d')."')");
			}
		}
		discuz_process::unlock('connect_login_report');
	}

}
?>