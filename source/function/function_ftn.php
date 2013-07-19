<?php

/**
 *		[Discuz!] (C)2001-2099 Comsenz Inc.
 *		This is NOT a freeware, use is subject to license terms
 *
 *		$Id: function_ftn.php 29038 2012-03-23 06:22:39Z songlixin $
 *		旋风上传下载，需要的函数
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$_G['setting']['ftn_site_id'] = $_G['setting']['my_siteid'];
$_G['setting']['xf_storage_enc_key'] = $_G['setting']['xf_storage_enc_key'];

include_once libfile('function/cloud');

function ftn_formhash($specialadd = '') {
	global $_G;
	return substr(md5(substr($_G['timestamp'], 0, -4).$_G['username'].$_G['uid'].$_G['authkey'].$_G['setting']['xf_storage_enc_key'].$specialadd), 8, 8);
}

function make_ftn_sig($formhash){
	global $_G;
	$discuz_openid = xf_getOpenidByUid($_G['uid']);
	$ftnGetx = array('s_id' => $_G['setting']['ftn_site_id'], 's_site_uid' => $_G['uid'], 'ts' => $_G['timestamp'], 'discuz_form_hash' => $formhash, 'site_url' => $_G['siteurl'], 'discuz_openid' => $discuz_openid);
	$signGetx = $ftnGetx;
	ksort($signGetx);

	return _hash_hmac('sha1',cloud_http_build_query($signGetx, '', '&'), $_G['setting']['xf_storage_enc_key']);
}

function make_iframe_url($formhash){
	global $_G;
	$discuz_openid = xf_getOpenidByUid($_G['uid']);
	$ftnGetx = array('s_id' => $_G['setting']['ftn_site_id'], 's_site_uid' => $_G['uid'], 'ts' => $_G['timestamp'], 'discuz_form_hash' => $formhash, 'site_url' => $_G['siteurl'], 'discuz_openid' => $discuz_openid);
	$url = "http://cp.discuz.qq.com/storage/FTN?".cloud_http_build_query($ftnGetx, '', '&');
	$url = $url.'&sign='.make_ftn_sig($formhash);

	return $url;
}

function make_qqdl_url($sha,$filename) {
	global $_G;
	$filename = trim($filename);
	$filename = urlencode(diconv($filename,CHARSET,'UTF-8'));
	$url = $_G['siteurl'].$filename.'?&&txf_fid='.$sha.'&siteid='.$_G['setting']['ftn_site_id'];

	return 'qqdl://'.base64_encode($url);
}

function make_downloadurl($sha1,$filesize,$filename) {
	global $_G;


	$filename = trim($filename,' "'); // Discuz! 默认的filename两侧会加上 双引号
	$filename = diconv($filename,CHARSET,'UTF-8');
	$filename = str2hex($filename);

	$filename = strtolower($filename[1]);
	$post = 'http://dz.xf.qq.com/ftn.php?v=1&&';

	$k = _hash_hmac('sha1',sprintf('%s|%s|%s', $sha1, $_G['timestamp'], $_G['setting']['ftn_site_id']), $_G['setting']['xf_storage_enc_key']);

	$parm = array(
		'site_id' => $_G['setting']['ftn_site_id'],
		't' => $_G['timestamp'],
		'sha1' => $sha1,
		'filesize' => $filesize,
		'filename' => $filename,
		'k' => $k,
		'ip' => $_G['clientip']
	);

	return $post.cloud_http_build_query($parm,'','&&');
}

function _hash_hmac($algo, $data, $key, $raw_output = false) {

	if(function_exists('hash_hmac')) {
		return	hash_hmac($algo, $data, $key, $raw_output);
	} else {
		$algo = strtolower($algo);
		$pack = 'H'.strlen(call_user_func($algo, 'test'));
		$size = 64;
		$opad = str_repeat(chr(0x5C), $size);
		$ipad = str_repeat(chr(0x36), $size);

		if(strlen($key) > $size) {
			$key = str_pad(pack($pack, call_user_func($algo, $key)), $size, chr(0x00));
		} else {
			$key = str_pad($key, $size, chr(0x00));
		}

		for ($i = 0; $i < strlen($key) - 1; $i++) {
			$opad[$i] = $opad[$i] ^ $key[$i];
			$ipad[$i] = $ipad[$i] ^ $key[$i];
		}

		$output = call_user_func($algo, $opad.pack($pack, call_user_func($algo, $ipad.$data)));

		return ($raw_output) ? pack($pack, $output) : $output;
	}
}

function _join_parm($parm = array(),$joiner = '&'){
	$andflag = '';
	$return = '';
	foreach($parm as $key => $value){
		$value = urlencode($value);
		$return .= $andflag.$key.'='.$value;
		$andflag = $joiner;
	}

	return $return;
}

function str2hex($str){
	$length = strlen($str)*2;
	return unpack('H'.$length,$str);
}

function xf_getOpenidByUid($uid) {
	global $_G;

	$openid = 0;
	if (getcloudappstatus('connect')) {
		$openid = DB::result_first('SELECT conopenid FROM ' . DB::table('common_member_connect') . ' WHERE uid = ' . $uid );
	}

	return $openid;
}

function checkTableExist($tableName) {
	global $_G;
	if ($tableName == '') {
		return false;
	}
	$tableName = $_G['config']['db']['1']['tablepre'] . $tableName;
	$key = 'Tables_in_' . $_G['config']['db']['1']['dbname'];
	$query = DB::query('SHOW TABLES');
	while($data = DB::fetch($query)) {
		 $tableArray[] = $data[$key];
	}

	return in_array($tableName, $tableArray);
}


?>