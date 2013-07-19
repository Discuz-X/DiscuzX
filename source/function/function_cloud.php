<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_cloud.php 29038 2012-03-23 06:22:39Z songlixin $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function openCloud() {
	global $_G;

	require_once libfile('function/cache');

	$result = DB::insert('common_setting', array('skey' => 'cloud_status', 'svalue' => '1'), false, true);
	updatecache('setting');

	if(!empty($_G['setting']['connectsiteid']) || !empty($_G['setting']['connectsitekey']) || !empty($_G['setting']['my_siteid_old']) || !empty($_G['setting']['my_sitekey_sign_old'])) {
		DB::delete('common_setting', "`skey` = 'connectsiteid' OR `skey` = 'connectsitekey' OR `skey` = 'my_siteid_old' OR `skey` = 'my_sitekey_sign_old'");
	}

	return $result;
}

function checkcloudstatus($showMessage = true) {
	global $_G;

	$res = false;

	$cloudStatus = $_G['setting']['cloud_status'];
	$site_id = $_G['setting']['my_siteid'];
	$site_key = $_G['setting']['my_sitekey'];

	if($site_id && $site_key) {
		switch($cloudStatus) {
		case 1:
			$res = 'cloud';
			break;
		case 2:
			$res = 'unconfirmed';
			break;
		default:
			$res = 'upgrade';
		}
	} elseif(!$cloudStatus && !$site_id && !$site_key) {
		$res = 'register';
	} elseif($showMessage) {
		if(defined('IN_ADMINCP')) {
			cpmsg_error('cloud_status_error');
		} else {
			showmessage('cloud_status_error');
		}
	}

	return $res;
}

function generateSiteSignUrl($params = array(), $isEncode = true, $isCamelCase = false) {
	global $_G;

	$ts = TIMESTAMP;
	$sId = $_G['setting']['my_siteid'];
	$sKey = $_G['setting']['my_sitekey'];
	$uid = $_G['uid'];

	if(!is_array($params)) {
		$params = array();
	}

	unset($params['sig'], $params['ts']);

	if ($isCamelCase) {
		$params['sId'] = $sId;
		$params['sSiteUid'] = $uid;
	} else {
		$params['s_id'] = $sId;
		$params['s_site_uid'] = $uid;
	}

	ksort($params);

	$str = buildArrayQuery($params, '', $isEncode);
	$sig = md5(sprintf('%s|%s|%s', $str, $sKey, $ts));

	$params['ts'] = $ts;
	$params['sig'] = $sig;

	$url = buildArrayQuery($params, '', $isEncode);
	return $url;
}

function registercloud($cloudApiIp = '') {
	global $_G;

	require_once DISCUZ_ROOT.'/api/manyou/Manyou.php';

	$cloudClient = new Discuz_Cloud_Client();
	$returnData = $cloudClient->register();

	if($cloudClient->errno == 1 && $cloudApiIp) {
		$cloudClient->setCloudIp($cloudApiIp);
		$returnData = $cloudClient->register();
		if (!$cloudClient->errno) {
			DB::query("REPLACE INTO ".DB::table('common_setting')." (`skey`, `svalue`)
						VALUES ('cloud_api_ip', '$cloudApiIp')");
		}
	}

	if($cloudClient->errno) {
		$result = array('errCode' => $cloudClient->errno, 'errMessage' => $cloudClient->errmsg);
	} else {
		$sId = intval($returnData['sId']);
		$sKey = $returnData['sKey'];

		if ($sId && $sKey) {
			DB::query("REPLACE INTO ".DB::table('common_setting')." (`skey`, `svalue`)
						VALUES ('my_siteid', '$sId'), ('my_sitekey', '$sKey'), ('cloud_status', '2')");
			updatecache('setting');

			$result = array('errCode' => 0);
		} else {
			$result = array('errCode' => 2);
		}
	}

	return $result;
}

function upgrademanyou($cloudApiIp = '') {
	global $_G;

	require_once DISCUZ_ROOT.'/api/manyou/Manyou.php';

	$cloudClient = new Discuz_Cloud_Client();
	$returnData = $cloudClient->sync();

	if($cloudClient->errno == 1 && $cloudApiIp) {
		$cloudClient->setCloudIp($cloudApiIp);
		$returnData = $cloudClient->sync();
		if (!$cloudClient->errno) {
			DB::query("REPLACE INTO ".DB::table('common_setting')." (`skey`, `svalue`)
						VALUES ('cloud_api_ip', '$cloudApiIp')");
		}
	}

	if($cloudClient->errno) {
		$result = array('errCode' => $cloudClient->errno, 'errMessage' => $cloudClient->errmsg);
	} else {
		$result = array('errCode' => 0);
	}

	return $result;
}

function getcloudapps($usecache = true) {
	global $_G;

	$apps = array();

	if($usecache) {
		$apps = $_G['setting']['cloud_apps'];
	} else {
		$apps = DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='cloud_apps'");
	}

	if($apps && !is_array($apps)) {
		$apps = unserialize($apps);
	}
	if(!$apps) {
		$apps = array();
	}

	return $apps;
}

function getcloudappstatus($appName, $usecache = true) {

	$res = false;

	$apps = getcloudapps($usecache);
	if($apps && $apps[$appName]) {
		$res = ($apps[$appName]['status'] == 'normal');
	}

	return $res;
}

function setcloudappstatus($appName, $status, $usecache = true, $updatecache = true) {

	$method = 'setcloudappstatus_'.$appName;
	if(!function_exists($method)) {
		return false;
	}

	if(!@call_user_func($method, $appName, $status)) {
		return false;
	}

	$apps = getcloudapps($usecache);

	$app = array('name' => $appName, 'status' => $status);

	$apps[$appName] = $app;
	$apps = addslashes(serialize($apps));

	$res = DB::insert('common_setting', array('skey' => 'cloud_apps', 'svalue' => $apps), false, true);

	if(!empty($updatecache)) {
		require_once libfile('function/cache');
		updatecache(array('plugin', 'setting', 'styles'));
	}

	return $res;
}

function setcloudappstatus_manyou($appName, $status) {

	$available = 0;
	if($status == 'normal') {
		$available = 1;
	}
	$res = DB::insert('common_setting', array('skey' => 'my_app_status', 'svalue' => $available), false, true);

	return $res;
}

function setcloudappstatus_connect($appName, $status) {

	$available = 0;
	if($status == 'normal') {
		$available = 1;
	}
	$connect_setting = DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='connect'");
	if($connect_setting && !is_array($connect_setting)) {
		$connect_setting = unserialize($connect_setting);
	}
	if(!$connect_setting) {
		$connect_setting = array();
	}
	$connect_setting['allow'] = $available;

	$connectnew = addslashes(serialize($connect_setting));
	$res = DB::insert('common_setting', array('skey' => 'connect', 'svalue' => $connectnew), false, true);

	if(!updatecloudpluginavailable('qqconnect', $available)) {
		return false;
	}

	return $res;
}

function setcloudappstatus_security($appName, $status) {
	$available = 0;
	if($status == 'normal') {
		$available = 1;
	}

	if(!updatecloudpluginavailable('security', $available)) {
		return false;
	}

	return true;
}

function setcloudappstatus_stats($appName, $status) {
	$available = 0;
	if($status == 'normal') {
		$available = 1;
	}

	if(!updatecloudpluginavailable('cloudstat', $available)) {
		return false;
	}

	return true;
}

function setcloudappstatus_search($appName, $status) {
	global $_G;
	$searchData = unserialize($_G['setting']['my_search_data']);
	if (!is_array($searchData)) {
		$searchData = array();
	}
	$searchData['isnew'] = 0;
	$searchData = addslashes(serialize(dstripslashes($searchData)));

	$available = 0;
	if($status == 'normal') {
		$available = 1;
	}
	$res = DB::insert('common_setting', array('skey' => 'my_search_data', 'svalue' => $searchData), false, true);
	if($available) {
		require_once DISCUZ_ROOT.'./api/manyou/Manyou.php';
		SearchHelper::allowSearchForum();
	}

	updatecloudpluginavailable('cloudsearch', $available);

	return true;
}

function setcloudappstatus_smilies($appName, $status) {

	$available = 0;
	if($status == 'normal') {
		$available = 1;
	}

	if(!updatecloudpluginavailable('soso_smilies', $available)) {
		return false;
	}

	return true;
}

function setcloudappstatus_storage($appName, $status) {
    $available = 0;
    if ($status == 'normal') {
        $available = 1;
    }

    if (!updatecloudpluginavailable('xf_storage', $available)) {
        return false;
    }
    return true;
}

function setcloudappstatus_qqgroup($appName, $status) {

	return true;
}

function setcloudappstatus_union($appName, $status) {

	return true;
}

function updatecloudpluginavailable($identifier, $available) {

	$available = intval($available);
	$identifier = addslashes(strval($identifier));
	$pluginId = DB::result_first("SELECT pluginid FROM ".DB::table('common_plugin')." WHERE identifier='$identifier'");
	if($pluginId) {
		DB::update('common_plugin', array('available' => $available), array('pluginid' => $pluginId));
	} else {
		return false;
	}

	return true;
}

function headerLocation($url) {
	ob_end_clean();
	ob_start();
	@header('location: '.$url);
	exit;
}

function buildArrayQuery($data, $key = '', $isEncode = false) {

	if ($key) {
		$query =  array($key => $data);
	} else {
		$query = $data;
	}

	if ($isEncode) {
		return cloud_http_build_query($query, '', '&');
	} else {
		return cloud_http_build_query($query);
	}
}

function cloud_http_build_query($data, $numeric_prefix='', $arg_separator='', $prefix='') {
	$render = array();
	if (empty($arg_separator)) {
		$arg_separator = @ini_get('arg_separator.output');
		empty($arg_separator) && $arg_separator = '&';
	}
	foreach ((array) $data as $key => $val) {
		if (is_array($val) || is_object($val)) {
			$_key = empty($prefix) ? "{$key}[%s]" : sprintf($prefix, $key) . "[%s]";
			$_render = cloud_http_build_query($val, '', $arg_separator, $_key);
			if (!empty($_render)) {
				$render[] = $_render;
			}
		} else {
			if (is_numeric($key) && empty($prefix)) {
				$render[] = urlencode("{$numeric_prefix}{$key}") . "=" . urlencode($val);
			} else {
				if (!empty($prefix)) {
					$_key = sprintf($prefix, $key);
					$render[] = urlencode($_key) . "=" . urlencode($val);
				} else {
					$render[] = urlencode($key) . "=" . urlencode($val);
				}
			}
		}
	}
	$render = implode($arg_separator, $render);
	if (empty($render)) {
		$render = '';
	}
	return $render;
}

function cloud_get_api_version() {
	return '0.4';
}

function cloud_init_uniqueid() {
	$siteuniqueid = DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='siteuniqueid'");
	if(empty($siteuniqueid) || strlen($siteuniqueid) < 16) {
		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
		$siteuniqueid = 'DX'.$chars[date('y')%60].$chars[date('n')].$chars[date('j')].$chars[date('G')].$chars[date('i')].$chars[date('s')].substr(md5($_G['clientip'].$_G['username'].TIMESTAMP), 0, 4).random(4);
		$unique = array(
			'skey' => 'siteuniqueid',
			'svalue' => $siteuniqueid
		);
		DB::insert('common_setting', $unique, false, true);
	}
}

function show() {
	global $_G;
	if ($_G['adminid'] != 1) {
		return false;
	}

	include_once DISCUZ_ROOT . '/source/discuz_version.php';
	$release = DISCUZ_RELEASE;
	$fix = defined(DISCUZ_FIXBUG) ? DISCUZ_FIXBUG : '';
	$cloudApi = cloud_get_api_version();
	include_once libfile('function/admincp');
	$isfounder = checkfounder($_G['member']);
	$sId = $_G['setting']['my_siteid'];
	$version = $_G['setting']['version'];
	$ts = TIMESTAMP;
	$sig = '';
	if ($sId) {
		$params = array(
			's_id' => $sId,
			'product_version' => $version,
			'product_release' => $release,
			'fix_bug' => $fix,
			'is_founder' => $isfounder,
			's_url' => $_G[siteurl],
			'last_send_time' => $_COOKIE['dctips'],
		);
		ksort($params);

		$str = buildArrayQuery($params, '', '&');
		$sig = md5(sprintf('%s|%s|%s', $str, $_G['setting']['my_sitekey'], $ts));
	}

	$jsCode = <<< EOF
		<div id="discuz_tips" style="display:none;"></div>
		<script type="text/javascript">
			var discuzSId = '$sId';
			var discuzVersion = '$version';
			var discuzRelease = '$release';
			var discuzApi = '$cloudApi';
			var discuzIsFounder = '$isfounder';
			var discuzFixbug = '$fix';
			var ts = '$ts';
			var sig = '$sig';
		</script>
		<script src="http://discuz.gtimg.cn/cloud/scripts/discuz_tips.js?v=1" type="text/javascript" charset="UTF-8"></script>
EOF;
	echo $jsCode;
}

function checkfounder($user = '') {
	global $_G;
	$user = empty($user) ? getglobal('member') : $user;

	$founders = str_replace(' ', '', $_G['config']['admincp']['founder']);
	if(!$user['uid'] || $user['groupid'] != 1 || $user['adminid'] != 1) {
		return false;
	} elseif(empty($founders)) {
		return true;
	} elseif(strexists(",$founders,", ",$user[uid],")) {
		return true;
	} elseif(!is_numeric($user['username']) && strexists(",$founders,", ",$user[username],")) {
		return true;
	} else {
		return FALSE;
	}
}

?>