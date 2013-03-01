<?php

/**
 *	  [Discuz!] (C)2001-2099 Comsenz Inc.
 *	  This is NOT a freeware, use is subject to license terms
 *
 *	  $Id: connect_feed.php 27623 2012-02-07 13:34:12Z zhouxiaobo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/connect');
require_once libfile('function/cloud');

$params = $_GET;
$op = !empty($_G['gp_op']) ? $_G['gp_op'] : '';
if (!in_array($op, array('new', 'remove'))) {
	connect_js_ouput_message('', 'undefined_action', 1);
}

$tid = trim(intval($_G['gp_thread_id']));
if (empty($tid)) {
	connect_js_ouput_message('', 'connect_thread_id_miss', 1);
}

if ($op == 'new') {

	connect_merge_member();

	$thread = DB::fetch_first("SELECT * FROM ".DB::table('forum_thread')." WHERE tid = '$tid' AND displayorder >= 0");
	$posttable = 'forum_post'.($thread['posttableid'] ? "_$thread[posttableid]" : '');
	$post = DB::fetch_first("SELECT * FROM ".DB::table($posttable)." WHERE tid = '$tid' AND first='1' AND invisible='0'");

	$f_type = trim(intval($_G['gp_type']));

	$api_url = $_G['connect']['api_url'] . '/connect/feed/new';

	$extra = array(
		'oauth_token' => $_G['member']['conuin'],
	);
	$sig_params = connect_get_oauth_signature_params($extra);
	$oauth_token_secret = $_G['member']['conuinsecret'];
	$sig_params['oauth_signature'] = connect_get_oauth_signature($api_url, $sig_params, 'POST', $oauth_token_secret);

	$params = array(
		'client_ip' => $_G['clientip'],
		'thread_id' => $tid,
		'author_id' => $thread['authorid'],
		'author' => $thread['author'],
		'forum_id' => $thread['fid'],
		'p_id' => $post['pid'],
		'u_id' => $_G['uid'],
		'subject' => $thread['subject'],
		'bbcode_content' => $post['message'],
		'read_permission' => $thread['readperm'],
		'f_type' => $f_type,
	);
	$params['html_content'] = connect_parse_bbcode($params['bbcode_content'], $params['forum_id'], $params['p_id'], $post['htmlon'], $attach_images);

	if($attach_images && is_array($attach_images)) {
		$attach_images = array_slice($attach_images, 0, 3);
		$feed_images = array();
		foreach ($attach_images as $attach_image) {
			$feed_images[] = $attach_image['big'];
		}
		$params['attach_images'] = implode('|', $feed_images);
		unset($feed_images);
	}

	$params = array_merge($sig_params, $params);
	$response = connect_output_php($api_url . '?', cloud_http_build_query($params, '', '&'));
	if(!isset($response['status']) || $response['status'] != 0) {
		if(in_array($response['status'], array('4019', '4021'))) {
			dsetcookie('connect_js_name', 'feed_resend');
			dsetcookie('connect_js_params', base64_encode(serialize(array('type' => $f_type, 'thread_id' => $tid, 'ts' => TIMESTAMP))), 86400);
		}
		connect_js_ouput_message('', $response['result'], $response['status']);
	} else {
		connect_js_ouput_message(lang('connect', 'feed_sync_success'), '', 0);
	}


} elseif ($op == 'remove') {

	$api_url = $_G['connect']['api_url'] . '/connect/feed/remove';

	$feedlog = DB :: fetch_first("SELECT * FROM ".DB :: table('connect_feedlog')." WHERE tid='$tid'");

	if ($feedlog) {
		$feedmember = DB::fetch_first("SELECT * FROM ".DB::table('common_member_connect')." WHERE uid='$feedlog[uid]'");
		if (empty($feedmember) || empty($feedmember['conopenid']) || empty($feedmember['conuinsecret'])) {
			connect_js_ouput_message('', lang('connect', 'deletethread_sync_failed'), '104');
		}
	} else {
		connect_js_ouput_message('', lang('connect', 'deletethread_sync_failed'), $response['status'], '105');
	}

	$extra = array();
	$extra['oauth_token'] = $feedmember['conuin'];
	$sig_params = connect_get_oauth_signature_params($extra);
	$oauth_token_secret = $feedmember['conuinsecret'];
	$sig_params['oauth_signature'] = connect_get_oauth_signature($api_url, $sig_params, 'POST', $oauth_token_secret);
	$params = array(
		'thread_id' => $tid,
		'client_ip' => $_G['clientip']
	);
	$params = array_merge($sig_params, $params);

	$response = connect_output_php($api_url . '?', cloud_http_build_query($params, '', '&'));
	if (!isset($response['status']) || $response['status'] != 0) {
		connect_errlog($response['status'], $response['result']);
		connect_js_ouput_message('', $response['result'], $response['status']);
	} else {
		connect_js_ouput_message(lang('connect', 'deletethread_sync_success'), '', 0);
	}
}
?>