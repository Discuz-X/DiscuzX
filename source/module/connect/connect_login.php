<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: connect_login.php 29185 2012-03-28 07:01:36Z liudongdong $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/connect');

$op = !empty($_G['gp_op']) ? $_G['gp_op'] : '';
if(!in_array($op, array('init', 'callback', 'change'))) {
	showmessage('undefined_action');
}

$referer = dreferer();

if($op == 'init') {

	dsetcookie('con_request_token');
	dsetcookie('con_request_token_secret');

	$response = connect_get_request_token();
	if(!isset($response['status']) || $response['status'] !== 0) {
		if(!isset($response['status'])) {
			connect_errlog('100', lang('connect', 'connect_errlog_server_no_response'));
		} else {
			connect_errlog($response['status'], $response['result']);
		}
		showmessage('qqconnect:connect_get_request_token_failed', $referer);
	}

	$request_token = $response['result']['oauth_token'];
	$request_token_secret = $response['result']['oauth_token_secret'];

	dsetcookie('con_request_token', $request_token);
	dsetcookie('con_request_token_secret', $request_token_secret);

	$params = array(
		'oauth_token' => $request_token,
		'oauth_consumer_key' => $_G['setting']['connectappid'],
	);

	if($_G['gp_type']) {
		$params['type'] = $_G['gp_type'];
	}

	if(defined('IN_MOBILE')) {
		$params['source'] = 'mobile';
	}

	$redirect = $_G['connect']['url'] . '/oauth/authorize?'.cloud_http_build_query($params, '', '&');
	dheader('Location:' . $redirect);

} elseif($op == 'callback') {

	$params = $_GET;

	if(!connect_check_sig($params)) {
		connect_errlog('103', lang('connect', 'connect_errlog_sig_incorrect'));
		showmessage('qqconnect:connect_get_access_token_failed', $referer);
	}

	if(!isset($params['receive'])) {
		echo '<script type="text/javascript">setTimeout("window.location.href=\'connect.php?receive=yes&'.str_replace("'", "\'", cloud_http_build_query($_GET, '', '&')).'\'", 1)</script>';
		exit;
	}

	connect_params($params, $connect_params);

	$request_token = $connect_params['oauth_token'];
	$verify_code = $connect_params['oauth_verifier'];
	if($request_token && $verify_code) {
		$response = connect_get_access_token($request_token, $verify_code);
		if(!isset($response['status']) || $response['status'] != 0) {
			connect_errlog($response['status'], $response['result']);
			showmessage('qqconnect:connect_get_access_token_failed', $referer);
		}
		$conuin = $response['result']['oauth_token'];
		$conuinsecret = $response['result']['oauth_token_secret'];
		$conopenid = $response['result']['openid'];
		if(!$conuin || !$conuinsecret || !$conopenid) {
			connect_errlog('101', lang('connect', 'connect_errlog_access_token_incomplete'));
			showmessage('qqconnect:connect_get_access_token_failed', $referer);
		}
	} else {
		connect_errlog('102', lang('connect', 'connect_errlog_request_token_not_authorized'));
		showmessage('qqconnect:connect_get_request_token_failed', $referer);
	}

	loadcache('connect_blacklist');
	if(in_array($conopenid, $_G['cache']['connect_blacklist'])) {
		$params = array(
			'oauth_token' => $request_token,
			'oauth_consumer_key' => $_G['setting']['connectappid']
		);
		$change_qq_url = $_G['connect']['discuz_change_qq_url'];
		showmessage('qqconnect:connect_uin_in_blacklist', $referer, array('changeqqurl' => $change_qq_url));
	}

	$referer = $referer && (strpos($referer, 'logging') === false) && (strpos($referer, 'mod=login') === false) ? $referer : 'index.php';

	if($connect_params['uin']) {
		$old_conuin = $connect_params['uin'];
	}

	$is_notify = $connect_params['is_notify'] ? true : false;

	$conispublishfeed = $conispublisht = 0;

	$is_user_info = $connect_params['is_user_info'] ? $connect_params['is_user_info'] : 0;
	$is_feed = $connect_params['is_feed'] ? $connect_params['is_feed'] : 0;
	if($is_feed) {
		$conispublishfeed = $conispublisht = 1;
	}
	$user_auth_fields = connect_auth_field($is_user_info, $is_feed);

	$cookie_expires = 2592000;
	dsetcookie('client_created', TIMESTAMP, $cookie_expires);
	dsetcookie('client_token', $conopenid, $cookie_expires);

	$connect_member = array();
	if($old_conuin) {
		$connect_member = DB::fetch_first("SELECT uid, conuin, conuinsecret, conopenid FROM ".DB::table('common_member_connect')." WHERE conuin='$old_conuin'");
	}
	if(empty($connect_member)) {
		$connect_member = DB::fetch_first("SELECT uid, conuin, conuinsecret, conopenid FROM ".DB::table('common_member_connect')." WHERE conopenid='$conopenid'");
	}
	if($connect_member) {
		$member = DB::fetch_first("SELECT uid, conisbind FROM ".DB::table('common_member')." WHERE uid='$connect_member[uid]'");
		if($member) {
			if(!$member['conisbind']) {
				unset($connect_member);
			} else {
				$connect_member['conisbind'] = $member['conisbind'];
			}
		} else {
			DB::delete('common_member_connect', array('uid' => $connect_member['uid']));
			unset($connect_member);
		}
	}

	$connect_is_unbind = $connect_params['is_unbind'] == 1 ? 1 : 0;
	if($connect_is_unbind && $connect_member && !$_G['uid'] && $is_notify) {
		dsetcookie('connect_js_name', 'user_bind', 86400);
		dsetcookie('connect_js_params', base64_encode(serialize(array('type' => 'registerbind'))), 86400);
	}

	if($_G['uid']) {

		if($connect_member && $connect_member['uid'] != $_G['uid']) {
			showmessage('qqconnect:connect_register_bind_uin_already', $referer, array('username' => $_G['member']['username']));
		}

		$current_connect_member = DB::fetch_first("SELECT * FROM ".DB::table('common_member_connect')." WHERE uid='$_G[uid]'");
		if($current_connect_member) {
			if($current_connect_member['conuinsecret'] && $current_connect_member['conopenid'] != $conopenid) {
				showmessage('qqconnect:connect_register_bind_already', $referer);
			}
			DB::query("UPDATE ".DB::table('common_member_connect')." SET conuin='$conuin', conuinsecret='$conuinsecret', conopenid='$conopenid', conispublishfeed='$conispublishfeed', conispublisht='$conispublisht', conisregister='0', conisfeed='$user_auth_fields' WHERE uid='$_G[uid]'");
		} else {
			DB::query("INSERT INTO ".DB::table('common_member_connect')." (uid, conuin, conuinsecret, conopenid, conispublishfeed, conispublisht, conisregister, conisfeed) VALUES ('$_G[uid]', '$conuin', '$conuinsecret', '$conopenid', '$conispublishfeed', '$conispublisht', '0', '$user_auth_fields')");
		}
		DB::query("UPDATE ".DB::table('common_member')." SET conisbind='1' WHERE uid='$_G[uid]'");

		if($is_notify) {
			dsetcookie('connect_js_name', 'user_bind', 86400);
			dsetcookie('connect_js_params', base64_encode(serialize(array('type' => 'loginbind'))), 86400);
		}
		dsetcookie('connect_login', 1, 31536000);
		dsetcookie('connect_is_bind', '1', 31536000);
		dsetcookie('connect_uin', $conopenid, 31536000);
		dsetcookie('stats_qc_reg', 3, 86400);
		if($is_feed) {
			dsetcookie('connect_synpost_tip', 1, 31536000);
		}

		DB::query("INSERT INTO ".DB::table('connect_memberbindlog')." (uid, uin, type, dateline) VALUES ('$_G[uid]', '$conopenid', '1', '$_G[timestamp]')");

		showmessage('qqconnect:connect_register_bind_success', $referer);

	} else {

		if($connect_member) {
			DB::query("UPDATE ".DB::table('common_member_connect')." SET conuin='$conuin', conuinsecret='$conuinsecret', conopenid='$conopenid', conisfeed='$user_auth_fields' WHERE uid='$connect_member[uid]'");

			$params['mod'] = 'login';
			connect_login($connect_member);

			loadcache('usergroups');
			$usergroups = $_G['cache']['usergroups'][$_G['groupid']]['grouptitle'];
			$param = array('username' => $_G['member']['username'], 'usergroup' => $_G['group']['grouptitle']);

			DB::query("UPDATE ".DB::table('common_member_status')." SET lastip='".$_G['clientip']."', lastvisit='".time()."', lastactivity='".time()."' WHERE uid='$connect_member[uid]'");
			$ucsynlogin = '';
			if($_G['setting']['allowsynlogin']) {
				loaducenter();
				$ucsynlogin = uc_user_synlogin($_G['uid']);
			}

			dsetcookie('stats_qc_login', 3, 86400);
			showmessage('login_succeed', $referer, $param, array('extrajs' => $ucsynlogin));

		} else {

			$encode[] = authcode($conuin, 'ENCODE');
			$encode[] = authcode($conuinsecret, 'ENCODE');
			$encode[] = authcode($conopenid, 'ENCODE');
			$encode[] = authcode($user_auth_fields, 'ENCODE');
			$auth_hash = authcode(implode('|', $encode), 'ENCODE');
			dsetcookie('con_auth_hash', $auth_hash);

			unset($params['op']);
			$params['mod'] = 'register';
			$params['referer'] = $referer;
			$params['con_auth_hash'] = $auth_hash;
			unset($params['con_oauth_token']);
			unset($params['con_oauth_verifier']);

			$redirect = 'connect.php?'.cloud_http_build_query($params, '', '&');
			dheader("Location: $redirect");
		}
	}

} elseif($op == 'change') {

	dsetcookie('con_request_token');
	dsetcookie('con_request_token_secret');

	$response = connect_get_request_token();
	if(!isset($response['status']) || $response['status'] !== 0) {
		connect_errlog($response['status'], $response['result']);
		showmessage('qqconnect:connect_get_request_token_failed', $referer);
	}

	$request_token = $response['result']['oauth_token'];
	$request_token_secret = $response['result']['oauth_token_secret'];

	dsetcookie('con_request_token', $request_token);
	dsetcookie('con_request_token_secret', $request_token_secret);

	$params = array(
		'oauth_token' => $request_token,
		'oauth_consumer_key' => $_G['setting']['connectappid']
	);

	$redirect = $_G['connect']['url'] . '/discuz/login?'.cloud_http_build_query($params, '', '&');
	dheader('Location:' . $redirect);
}

function connect_login($connect_member) {
	global $_G;

	$member = DB::fetch_first("SELECT * FROM ".DB::table('common_member')." WHERE uid='$connect_member[uid]'");
	if(!$member) {
		return false;
	}

	require_once libfile('function/member');
	$cookietime = 1296000;
	setloginstatus($member, $cookietime);

	dsetcookie('connect_login', 1, $cookietime);
	dsetcookie('connect_is_bind', '1', 31536000);
	dsetcookie('connect_uin', $connect_member['conopenid'], 31536000);
	return true;
}

?>