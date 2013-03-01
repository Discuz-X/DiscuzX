<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_connect.php 27641 2012-02-08 09:51:14Z zhouxiaobo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/cloud');

function connect_output_javascript($jsurl) {
	return '<script type="text/javascript">_attachEvent(window, \'load\', function () { appendscript(\''.$jsurl.'\', \'\', 1, \'utf-8\') }, document);</script>';
}

function connect_output_php($url, $postData = '') {
	global $_G;

	$response = dfsockopen($url, 0, $postData, '', false, $_G['setting']['cloud_api_ip']);
	$result = (array) unserialize($response);
	return $result;
}

function connect_user_bind_js($params) {
	global $_G;

	$jsname = $_G['cookie']['connect_js_name'];
	if($jsname != 'user_bind') {
		return false;
	}

	$jsparams = unserialize(base64_decode($_G['cookie']['connect_js_params']));
	$jsurl = $_G['connect']['url'].'/notify/user/bind';

	if($jsparams) {
		$params = array_merge($params, $jsparams);
	}

	$func = 'connect_'.$jsname.'_params';
	$other_params = $func ();
	$params = array_merge($other_params, $params);
	$params['sig'] = connect_get_sig($params, connect_get_sig_key());

	$jsurl .= '?'.cloud_http_build_query($params, '', '&');

	dsetcookie('connect_js_name');
	dsetcookie('connect_js_params');
	return connect_output_javascript($jsurl);
}

function connect_load_qshare_js($appkey) {
	global $_G;

	$jsurl = $_G['siteurl'] . 'static/js/qshare.js';
	$sitename = isset($_G['setting']['bbname']) ? $_G['setting']['bbname'] : '';
	return '<script type="text/javascript" src="' . $jsurl . '"></script><script type="text/javascript">_share_tencent_weibo(null, $C("t_f", null, "td"), "' . $_G['siteurl'] . '", "' . $appkey . '", "' . $sitename . '");</script>';
}

function connect_check_token_js() {
	global $_G;

	$request_url = $_G['siteurl'] . 'connect.php?mod=check&op=token&_r=' . rand(1, 10000);
	$js = <<<EOF
<script type="text/javascript">
function connect_handle_check_token(response, ajax) {

    if (typeof(response) == "string" && response.indexOf("&") > 0) {

        var errCode = response.substring(0, response.indexOf("&"));
        errCode = errCode.substring(errCode.indexOf("=") + 1);

        var result = response.substring(response.indexOf("&") + 1);
        result = result.substring(result.indexOf("=") + 1);

        response = {"errCode" : errCode, "result" : result};
	} else {
		return false;
	}

	if (response.errCode == '0' && response.result == '2') {
		if (typeof(_is_token_outofdate) != "undefined") {
			_is_feed_auth = false;
			_is_token_outofdate = true;
			connect_post_init();
		}
		if (typeof(_is_token_outofdate_infloat) != "undefined") {
			_is_feed_auth_infloat = false;
			_is_token_outofdate_infloat = true;
			connect_post_init_infloat();
		}
		if (typeof(_share_buttons) != "undefined" && typeof(_is_oauth_user) != "undefined") {
			_is_oauth_user = false;
			_is_share_token_outofdate = true;
		}
	}
}

function connect_ajax_check_token() {
	var _check_token_ajax = Ajax("HTML", null);
	_check_token_ajax.get("{$request_url}", connect_handle_check_token);
}

_attachEvent(window, 'load', connect_ajax_check_token);
</script>
EOF;

	return $js;
}

function connect_cookie_login_report($loginTimes) {
	global $_G;

	$response = '';
	if ($loginTimes) {
		$api_url = $_G['connect']['api_url'].'/connect/discuz/batchCookieReport';
		$params = array (
			'oauth_consumer_key' => $_G['setting']['connectappid'],
			'login_times' => $loginTimes,
			'date' => dgmdate(TIMESTAMP - 86400, 'Y-m-d'),
			'ts' => TIMESTAMP,
		);
		$params['sig'] = connect_get_sig($params, connect_get_sig_key());

		$response = connect_output_php($api_url.'?', cloud_http_build_query($params, '', '&'));
	}

	return $response;
}

function connect_cookie_login_params() {
	global $_G;

    connect_merge_member();
	$oauthToken = $_G['member']['conuin'];
	$api_url = $_G['connect']['api_url'].'/connect/discuz/cookieReport';

	if($oauthToken) {
		$extra = array (
		    'oauth_token' => $oauthToken
		);

		$sig_params = connect_get_oauth_signature_params($extra);

		$oauth_token_secret = $_G['member']['conuinsecret'];
		$sig_params['oauth_signature'] = connect_get_oauth_signature($api_url, $sig_params, 'POST', $oauth_token_secret);
		$params = array (
			'client_ip' => $_G['clientip'],
			'u_id' => $_G['uid']
		);

		$params = array_merge($sig_params, $params);
		$params['response_type'] = 'php';

		return $params;
	} else {
		return false;
	}
}

function connect_cookie_login_js() {
	global $_G;

	$ajaxUrl = 'connect.php?mod=check&op=cookie';
	return '<script type="text/javascript">var cookieLogin = Ajax("TEXT");cookieLogin.get("' . $ajaxUrl . '", function() {});</script>';
}

function connect_user_unbind() {
	global $_G;

	$api_url = $_G['connect']['api_url'].'/connect/user/unbind';

	$extra = array (
		'oauth_token' => $_G['member']['conuin']
	);
	$sig_params = connect_get_oauth_signature_params($extra);
	$oauth_token_secret = $_G['member']['conuinsecret'];
	$sig_params['oauth_signature'] = connect_get_oauth_signature($api_url, $sig_params, 'POST', $oauth_token_secret);

	$params = array (
		'client_ip' => $_G['clientip']
	);
	$params = array_merge($sig_params, $params);
	$params['response_type'] = 'php';

	$response = connect_output_php($api_url.'?', cloud_http_build_query($params, '', '&'));
	return $response;
}

function connect_user_bind_params() {
	global $_G;

	connect_merge_member();
	getuserprofile('birthyear');
	getuserprofile('birthmonth');
	getuserprofile('birthday');
	switch ($_G['member']['gender']) {
		case 1 :
			$sex = 'male';
			break;
		case 2 :
			$sex = 'female';
			break;
		default :
			$sex = 'unknown';
	}

	$is_public_email = 2;
	$is_use_qq_avatar = $_G['member']['conisqzoneavatar'] == 1 ? 1 : 2;
	$birthday = sprintf('%04d', $_G['member']['birthyear']).'-'.sprintf('%02d', $_G['member']['birthmonth']).'-'.sprintf('%02d', $_G['member']['birthday']);

	$agent = md5(time().rand().uniqid());
	$inputArray = array (
		'uid' => $_G['uid'],
		'agent' => $agent,
		'time' => TIMESTAMP
	);
	require_once DISCUZ_ROOT.'./config/config_ucenter.php';
	$input = 'uid='.$_G['uid'].'&agent='.$agent.'&time='.TIMESTAMP;
	$avatar_input = authcode($input, 'ENCODE', UC_KEY);

	$params = array (
		'oauth_consumer_key' => $_G['setting']['connectappid'],
		'u_id' => $_G['uid'],
		'username' => $_G['member']['username'],
		'email' => $_G['member']['email'],
		'birthday' => $birthday,
		'sex' => $sex,
		'is_public_email' => $is_public_email,
		'is_use_qq_avatar' => $is_use_qq_avatar,
		's_id' => $_G['setting']['connectsiteid'],
		'avatar_input' => $avatar_input,
		'avatar_agent' => $agent,
		'site_ucenter_id' => UC_APPID
	);

	return $params;
}

function connect_feed_resend_js() {
	global $_G;

	$jsname = $_G['cookie']['connect_js_name'];
	if($jsname != 'feed_resend') {
		return false;
	}

	$params = unserialize(base64_decode($_G['cookie']['connect_js_params']));
	$params['sig'] = connect_get_sig($params, connect_get_sig_key());

	$jsurl = $_G['connect']['discuz_new_feed_url'];
	$jsurl .= '?' . cloud_http_build_query($params, '', '&');

	dsetcookie('connect_js_name');
	dsetcookie('connect_js_params');

	return connect_output_javascript($jsurl);
}

function connect_feed_remove($tid) {
	global $_G;

	$feedlog = DB :: fetch_first("SELECT * FROM ".DB :: table('connect_feedlog')." WHERE tid='$tid'");
	if(!$feedlog) {
		return false;
	}

	if(!getstatus($feedlog['status'], 4)) {
        $feedlog['status'] = setstatus(4, 1, $feedlog['status']);
		DB :: query("UPDATE ".DB :: table('connect_feedlog')." SET status='{$feedlog['status']}' WHERE tid='$tid'");
	}

	$params = array (
		'thread_id' => $tid,
		'ts' => TIMESTAMP
	);
	$params['sig'] = connect_get_sig($params, connect_get_sig_key());

	return sprintf('%s&%s', $_G['connect']['discuz_remove_feed_url'], cloud_http_build_query($params, '', '&'));
}

function connect_params($params, & $connect_params) {
	global $_G;

	if(!$params) {
		return false;
	}
	$connect_params = array ();
	foreach ($params as $key => $value) {
		if(substr($key, 0, 4) == 'con_') {
			$connect_params[substr($key, 4)] = $value;
		}
	}
}

function connect_check_sig($params) {
	global $_G;

	if(!$params) {
		return false;
	}

	$valid_params = array();
	foreach($params as $key => $value) {
		if(substr($key, 0, 4) == 'con_') {
			$valid_params[$key] = $value;
		}
	}
	$sig = $valid_params['con_sig'];
	unset($valid_params['con_sig']);
	ksort($valid_params);
	$str = '';
	foreach($valid_params as $k => $v) {
		if($v) {
			$str .= $k.'='.$v.'&';
		}
	}

	return $sig === md5($str.$_G['setting']['connectappkey']);
}

function connect_get_sig_key() {
	global $_G;

	return $_G['setting']['connectappid'] . '|' . $_G['setting']['connectappkey'];
}

function connect_get_sig($params, $app_key) {
	ksort($params);
	$base_string = '';
	foreach($params as $key => $value) {
		$base_string .= $key.'='.$value;
	}
	$base_string .= $app_key;
	return md5($base_string);
}

function connect_get_request_token() {
	global $_G;

	$api_url = $_G['connect']['api_url'].'/oauth/requestToken';

	$extra = array();
	$extra['oauth_callback'] = urlencode($_G['connect']['callback_url'] . '&referer=' . urlencode($_G['gp_referer']));
	$sig_params = connect_get_oauth_signature_params($extra);
	$sig_params['oauth_signature'] = connect_get_oauth_signature($api_url, $sig_params, 'POST');

	$params = array (
		'client_ip' => $_G['clientip']
	);

	$params['type'] = $_G['gp_type'];
	if(empty ($params['type'])) {
		$params['type'] = 'login';
	}
	if($_G['gp_statfrom']) {
		$params['statfrom'] = $_G['gp_statfrom'];
	}
	$params = array_merge($sig_params, $params);

	$response = connect_output_php($api_url.'?', cloud_http_build_query($params, '', '&'));
	return $response;
}

function connect_get_access_token($request_token, $verify_code) {
	global $_G;

	$api_url = $_G['connect']['api_url'].'/oauth/accessToken';

	$extra = array();
	$extra['oauth_token'] = $request_token;
	$extra['oauth_verifier'] = $verify_code;
	$sig_params = connect_get_oauth_signature_params($extra);
	$oauth_token_secret = $_G['cookie']['con_request_token_secret'];
	$sig_params['oauth_signature'] = connect_get_oauth_signature($api_url, $sig_params, 'POST', $oauth_token_secret);

	$params = array (
		'client_ip' => $_G['clientip']
	);
	$params = array_merge($sig_params, $params);

	dsetcookie('con_request_token');
	dsetcookie('con_request_token_secret');


	$response = connect_output_php($api_url.'?', cloud_http_build_query($params, '', '&'));
	return $response;
}

function connect_get_oauth_signature($url, $params, $method = 'POST', $oauth_token_secret = '') {

	global $_G;

	$method = strtoupper($method);
	if(!in_array($method, array ('GET', 'POST'))) {
		return FALSE;
	}

	$url = urlencode($url);

	$param_str = urlencode(cloud_http_build_query($params, '', '&'));

	$base_string = $method.'&'.$url.'&'.$param_str;

	$key = $_G['setting']['connectappkey'].'&'.$oauth_token_secret;

	if(function_exists('hash_hmac')) {
		$signature = hash_hmac('sha1', $base_string, $key);
	} else {
		$signature = connect_custom_hmac('sha1', $base_string, $key);
	}
	return $signature;
}

function connect_get_oauth_signature_params($extra = array ()) {
	global $_G;

	$params = array (
		'oauth_consumer_key' => $_G['setting']['connectappid'],
		'oauth_nonce' => connect_get_nonce(),
		'oauth_signature_method' => 'HMAC_SHA1',
		'oauth_timestamp' => TIMESTAMP
	);
	if($extra) {
		$params = array_merge($params, $extra);
	}
	ksort($params);

	return $params;
}

function connect_custom_hmac($algo, $data, $key, $raw_output = false) {
	$algo = strtolower($algo);
	$pack = 'H'.strlen($algo ('test'));
	$size = 64;
	$opad = str_repeat(chr(0x5C), $size);
	$ipad = str_repeat(chr(0x36), $size);

	if(strlen($key) > $size) {
		$key = str_pad(pack($pack, $algo ($key)), $size, chr(0x00));
	} else {
		$key = str_pad($key, $size, chr(0x00));
	}

	for ($i = 0; $i < strlen($key) - 1; $i++) {
		$opad[$i] = $opad[$i] ^ $key[$i];
		$ipad[$i] = $ipad[$i] ^ $key[$i];
	}

	$output = $algo ($opad.pack($pack, $algo ($ipad.$data)));

	return ($raw_output) ? pack($pack, $output) : $output;
}

function connect_get_nonce() {
	$mt = microtime();
	$rand = mt_rand();

	return md5($mt.$rand);
}

function connect_js_ouput_message($msg = '', $errMsg = '', $errCode = '') {
	$result = array (
		'result' => $msg,
		'errMessage' => $errMsg,
		'errCode' => $errCode
	);
	echo sprintf('con_handle_response(%s);', json_encode(connect_urlencode($result)));
	exit;
}

function connect_ajax_ouput_message($msg = '', $errCode = '') {

    @header("Content-type: text/html; charset=".CHARSET);

    echo "errCode=$errCode&result=$msg";
	exit;
}

function connect_urlencode($value) {

	if (is_array($value)) {
		foreach ($value as $k => $v) {
			$value[$k] = connect_urlencode($v);
		}
	} else if (is_string($value)) {
		$value = urlencode(str_replace(array("\r\n", "\r", "\n", "\"", "\/", "\t"), array('\\n', '\\n', '\\n', '\\"', '\\/', '\\t'), $value));
	}

	return $value;
}

function connect_merge_member() {
	global $_G;

	if (!$_G['member']['conisbind']) {
		return false;
	}

	$connect_member = DB::fetch_first("SELECT * FROM ".DB::table('common_member_connect')." WHERE uid='$_G[uid]'");
	if ($connect_member) {
		$_G['member'] = array_merge($_G['member'], $connect_member);
		$user_auth_fields = $connect_member['conisfeed'];
		if ($user_auth_fields == 0) {
			$_G['member']['is_user_info'] = 0;
			$_G['member']['is_feed'] = 0;
		} elseif ($user_auth_fields == 1) {
			$_G['member']['is_user_info'] = 1;
			$_G['member']['is_feed'] = 1;
		} elseif ($user_auth_fields == 2) {
			$_G['member']['is_user_info'] = 1;
			$_G['member']['is_feed'] = 0;
		} elseif ($user_auth_fields == 3) {
			$_G['member']['is_user_info'] = 0;
			$_G['member']['is_feed'] = 1;
		}
		unset($connect_member, $_G['member']['conisfeed']);
	}
}

function connect_auth_field($is_user_info, $is_feed) {
	if ($is_user_info && $is_feed) {
		return 1;
	} elseif (!$is_user_info && !$is_feed) {
		return 0;
	} elseif ($is_user_info && !$is_feed) {
		return 2;
	} elseif (!$is_user_info && $is_feed) {
		return 3;
	}
}

function connect_errlog($errno, $error) {
	return true;

	global $_G;
	writelog('errorlog', $_G['timestamp']."\t[QQConnect]".$errno." ".$error);
}

define('X_BOARDURL', $_G['setting']['discuzurl']);

function connect_parse_bbcode($bbcode, $fId, $pId, $isHtml, &$attachImages) {
	include_once libfile('function/discuzcode');

	$result = preg_replace('/\[hide(=\d+)?\].+?\[\/hide\](\r\n|\n|\r)/i', '', $bbcode);
	$result = preg_replace('/\[payto(=\d+)?\].+?\[\/payto\](\r\n|\n|\r)/i', '', $result);
	$result = discuzcode($result, 0, 0, $isHtml, 1, 2, 1, 0, 0, 0, 0, 1, 0);
	$result = preg_replace('/<img src="images\//i', "<img src=\"".$_G['siteurl']."images/", $result);
	$result = connect_parse_attach($result, $fId, $pId, $attachImages, $attachImageThumb);
	return $result;
}

function connect_parse_attach($content, $fId, $pId, &$attachImages) {
	global $_G;

	$permissions = connect_get_user_group_permissions(array(7), $fId);
	$visitorPermission = $permissions[7];

	$attachIds = array();
	$attachImages = array ();
	$query = DB :: query("SELECT aid, filename, isimage, readperm, price FROM ".DB :: table(getattachtablebypid($pId))." WHERE pid='$pId'");
	while ($attach = DB :: fetch($query)) {
		$aid = $attach['aid'];
		if($attach['isimage'] == 0 || $attach['price'] > 0 || $attach['readperm'] > $visitorPermission['readPermission'] || in_array($fId, $visitorPermission['forbidViewAttachForumIds']) || in_array($attach['aid'], $attachIds)) {
			continue;
		}

		$imageItem = array ();
		$thumbWidth = '100';
		$thumbHeight = '100';
		$bigWidth = '400';
		$bigHeight = '400';
		$key = md5($aid.'|'.$thumbWidth.'|'.$thumbHeight);
		$thumbImageURL = $_G['siteurl'] . 'forum.php?mod=image&aid='.$aid.'&size='.$thumbWidth.'x'.$thumbHeight.'&key='.rawurlencode($key).'&type=fixwr&nocache=1';
		$key = md5($aid.'|'.$bigWidth.'|'.$bigHeight);
		$bigImageURL = $_G['siteurl'] . 'forum.php?mod=image&aid='.$aid.'&size='.$bigWidth.'x'.$bigHeight.'&key='.rawurlencode($key).'&type=fixnone&nocache=1';
		$imageItem['aid'] = $aid;
		$imageItem['thumb'] = $thumbImageURL;
		$imageItem['big'] = $bigImageURL;

		$attachIds[] = $aid;
		$attachImages[] = $imageItem;
	}
	$content = preg_replace('/\[attach\](\d+)\[\/attach\]/ie', 'connect_parse_attach_tag(\\1, $attachNames)', $content);
	return $content;
}

function connect_parse_attach_tag($attachId, $attachNames) {
	include_once libfile('function/discuzcode');
	if(array_key_exists($attachId, $attachNames)) {
		return '<span class="attach"><a href="'.$_G['siteurl'].'/attachment.php?aid='.aidencode($attachId).'">'.$attachNames[$attachId].'</a></span>';
	}
	return '';
}

function connect_get_user_group_permissions($userGroupIds, $fId) {
	global $_G;

	$fields = array (
		'groupid' => 'userGroupId',
		'grouptitle' => 'userGroupName',
		'readaccess' => 'readPermission',
		'allowvisit' => 'allowVisit'
	);
	$userGroups = array ();
	$query = DB :: query("SELECT f.*,ff.* FROM ".DB :: table('common_usergroup')." f
			LEFT JOIN ".DB :: table('common_usergroup_field')." ff USING(groupid)
			WHERE f.groupid IN (".dimplode($userGroupIds).")");
	while ($row = DB :: fetch($query)) {
		foreach ($row as $k => $v) {
			if(array_key_exists($k, $fields)) {
				$userGroups[$row['groupid']][$fields[$k]] = $v;
			}
			$userGroups[$row['groupid']]['forbidForumIds'] = array ();
			$userGroups[$row['groupid']]['allowForumIds'] = array ();
			$userGroups[$row['groupid']]['specifyAllowForumIds'] = array ();
			$userGroups[$row['groupid']]['allowViewAttachForumIds'] = array ();
			$userGroups[$row['groupid']]['forbidViewAttachForumIds'] = array ();
		}
	}

	$row = DB :: fetch_first("SELECT ff.* FROM ".DB :: table('forum_forum')." f
			INNER JOIN ".DB :: table('forum_forumfield')." ff USING(fid) WHERE f.fid='$fId' AND f.status='1'");

	$allowViewGroupIds = array ();
	if($row['viewperm']) {
		$allowViewGroupIds = explode("\t", $row['viewperm']);
	}
	$allowViewAttachGroupIds = array ();
	if($row['getattachperm']) {
		$allowViewAttachGroupIds = explode("\t", $row['getattachperm']);
	}
	foreach ($userGroups as $gid => $_v) {
		if($row['password']) {
			$userGroups[$gid]['forbidForumIds'][] = $row['fid'];
			continue;
		}
		$perm = unserialize($row['formulaperm']);
		if(is_array($perm)) {
			if($perm[0] || $perm[1] || $perm['users']) {
				$userGroups[$gid]['forbidForumIds'][] = $row['fid'];
				continue;
			}
		}
		if(!$allowViewGroupIds) {
			$userGroups[$gid]['allowForumIds'][] = $row['fid'];
		}
		elseif(!in_array($gid, $allowViewGroupIds)) {
			$userGroups[$gid]['forbidForumIds'][] = $row['fid'];
		}
		elseif(in_array($gid, $allowViewGroupIds)) {
			$userGroups[$gid]['allowForumIds'][] = $row['fid'];
			$userGroups[$gid]['specifyAllowForumIds'][] = $row['fid'];
		}
		if(!$allowViewAttachGroupIds) {
			$userGroups[$gid]['allowViewAttachForumIds'][] = $row['fid'];
		}
		elseif(!in_array($gid, $allowViewAttachGroupIds)) {
			$userGroups[$gid]['forbidViewAttachForumIds'][] = $row['fid'];
		}
		elseif(in_array($gid, $allowViewGroupIds)) {
			$userGroups[$gid]['allowViewAttachForumIds'][] = $row['fid'];
		}
	}

	return $userGroups;
}

function connect_share_error($message, $type = 'alert') {
	echo "connect_share_loaded = 1;";
	echo "\n";
	echo "connect_show_dialog('', '$message', '$type');";
	exit;
}
?>