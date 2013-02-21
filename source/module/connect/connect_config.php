<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: connect_config.php 27578 2012-02-06 07:13:45Z houdelei $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(empty($_G['uid'])) {
	showmessage('to_login', '', array(), array('showmsg' => true, 'login' => 1));
}

$op = !empty($_G['gp_op']) ? $_G['gp_op'] : '';
$referer = dreferer();

if(submitcheck('connectsubmit')) {

	if($op == 'config') {

		$ispublishfeed = !empty($_G['gp_ispublishfeed']) ? 1 : 0;
		$ispublisht = !empty($_G['gp_ispublisht']) ? 1 : 0;
		DB::query("UPDATE ".DB::table('common_member_connect')." SET conispublishfeed='$ispublishfeed', conispublisht='$ispublisht' WHERE uid='$_G[uid]'");
		if (!$ispublishfeed || !$ispublisht) {
			dsetcookie('connect_synpost_tip');
		}
		showmessage('qqconnect:connect_config_success', $referer);

	} elseif($op == 'unbind') {

		require_once libfile('function/connect');

		$connect_member = DB::fetch_first("SELECT * FROM ".DB::table('common_member_connect')." WHERE uid='$_G[uid]'");
		$_G['member'] = array_merge($_G['member'], $connect_member);

		if ($connect_member['conuinsecret']) {

			if($_G['member']['conisregister']) {
				if($_G['gp_newpassword1'] !== $_G['gp_newpassword2']) {
					showmessage('profile_passwd_notmatch', $referer);
				}
				if(!$_G['gp_newpassword1'] || $_G['gp_newpassword1'] != addslashes($_G['gp_newpassword1'])) {
					showmessage('profile_passwd_illegal', $referer);
				}
			}

			$response = connect_user_unbind();
			if (!isset($response['status']) || $response['status'] !== 0) {
				if(!isset($response['status'])) {
					connect_errlog('100', lang('connect', 'connect_errlog_server_no_response'));
				} else {
					connect_errlog($response['status'], $response['result']);
				}
			}

		} else {

			if($_G['member']['conisregister']) {
				if($_G['gp_newpassword1'] !== $_G['gp_newpassword2']) {
					showmessage('profile_passwd_notmatch', $referer);
				}
				if(!$_G['gp_newpassword1'] || $_G['gp_newpassword1'] != addslashes($_G['gp_newpassword1'])) {
					showmessage('profile_passwd_illegal', $referer);
				}
			}
		}

		DB::query("UPDATE ".DB::table('common_member_connect')." SET conuin='', conuinsecret='', conopenid='', conispublishfeed='0', conispublisht='0', conisregister='0', conisqzoneavatar='0', conisfeed='0' WHERE uid='$_G[uid]'");
		DB::query("UPDATE ".DB::table('common_member')." SET conisbind='0' WHERE uid='$_G[uid]'");
		DB::query("INSERT INTO ".DB::table('connect_memberbindlog')." (uid, uin, type, dateline) VALUES ('$_G[uid]', '{$_G[member][conopenid]}', '2', '$_G[timestamp]')");

		if($_G['member']['conisregister']) {
			loaducenter();
			uc_user_edit($_G['member']['username'], null, $_G['gp_newpassword1'], null, 1);
		}

		foreach($_G['cookie'] as $k => $v) {
			dsetcookie($k);
		}

		$_G['uid'] = $_G['adminid'] = 0;
		$_G['username'] = $_G['member']['password'] = '';

		showmessage('qqconnect:connect_config_unbind_success', 'member.php?mod=logging&action=login');
	}

} else {

	if($_G[inajax] && $op == 'synconfig') {
		DB::query("UPDATE ".DB::table('common_member_connect')." SET conispublishfeed='0', conispublisht='0' WHERE uid='$_G[uid]'");
		dsetcookie('connect_synpost_tip');

	} elseif($op == 'weibosign') {
		require_once libfile('function/connect');
		connect_merge_member();

		if($_G['member']['conuin'] && $_G['member']['conuinsecret']) {

			$arr = array();
			$arr['oauth_consumer_key'] = $_G['setting']['connectappid'];
			$arr['oauth_nonce'] = mt_rand();
			$arr['oauth_timestamp'] = TIMESTAMP;
			$arr['oauth_signature_method'] = 'HMAC_SHA1';
			$arr['oauth_token'] = $_G['member']['conuin'];
			ksort($arr);
			$arr['oauth_signature'] = connect_get_oauth_signature('http://api.discuz.qq.com/connect/getSignature', $arr, 'GET', $_G['member']['conuinsecret']);
			$result = connect_output_php('http://api.discuz.qq.com/connect/getSignature?' . cloud_http_build_query($arr, '', '&'));
			if ($result['status'] == 0) {
				connect_ajax_ouput_message('[wb=' . $result['result']['username'] . ']' . $result['result']['signature_url'] . '[/wb]', 0);
			} else {
				connect_ajax_ouput_message('connect_wbsign_no_account', $result['status']);
			}
		} else {
			connect_ajax_ouput_message('connect_wbsign_no_bind', -1);
		}

	} else {
		dheader('location: home.php?mod=spacecp&ac=plugin&id=qqconnect:spacecp');
	}
}
?>