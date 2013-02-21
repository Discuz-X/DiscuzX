<?php

/**
 *		[Discuz!] (C)2001-2099 Comsenz Inc.
 *		This is NOT a freeware, use is subject to license terms
 *
 *		$Id: Manyou.php 28952 2012-03-20 09:18:17Z songlixin $
 */

define('MY_FRIEND_NUM_LIMIT', 2000);

class Manyou {

	var $siteId;
	var $siteKey;

	var $timezone;
	var $version;
	var $charset;
	var $language;

	var $myAppStatus;
	var $mySearchStatus;

	var $errno = 0;
	var $errmsg = '';

	function manyou($siteId, $siteKey, $timezone, $version, $charset, $language, $myAppStatus, $mySearchStatus) {
		$this->siteId = $siteId;
		$this->siteKey = $siteKey;
		$this->timezone = $timezone;
		$this->version = $version;
		$this->charset = $charset;
		$this->language = $language;
		$this->myAppStatus = $myAppStatus;
		$this->mySearchStatus = $mySearchStatus;
	}

	function run() {
		$result = $this->checkRequest();
		if($result) {
			$response = $result;
		} else {
			$response = $this->_processServerRequest();
		}
		@ob_end_clean();
		if(function_exists('ob_gzhandler')) {
			@ob_start('ob_gzhandler');
		} else {
			@ob_start();
		}
		echo serialize($this->_formatLocalResponse($response));
		exit;
	}

	function checkRequest() {
		$siteuniqueid = DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='siteuniqueid'");
		if (empty($siteuniqueid)) {
			return new ErrorResponse('11', 'Client SiteKey NOT Exists');
		} elseif (empty($this->siteKey)) {
			return new ErrorResponse('12', 'My SiteKey NOT Exists');
		}
		return false;
	}

	function getApplicationIframe($aId, $uId) {
		$timestamp = time();
		$suffix = base64_decode(urldecode($_GET['suffix']));
		$extra = $_GET['my_extra'];
		$currentUrl = $this->_getCurrentUrl();
		$prefix = dirname($currentUrl) . '/';

		$url = 'http://apps.manyou.com/' . $aId;
		if ($suffix) {
			$url .= '/' . ltrim($suffix, '/');
		}
		$separator = strpos($suffix, '?') ? '&' : '?';
		$url .= $separator . 'my_uchId=' . $uId . '&my_sId=' . $this->siteId;
		$url .= '&my_prefix=' . urlencode($prefix) . '&my_suffix=' . urlencode($suffix);
		$url .= '&my_current=' . urlencode($currentUrl);
		$url .= '&my_extra=' . urlencode($extra);
		$url .= '&my_ts=' . $timestamp;
		$hash = md5($this->siteId . '|' . $uId . '|' . $aId . '|' . $currentUrl . '|' . $extra . '|' . $timestamp . '|' . $this->siteKey);
		$url .= '&my_sig=' . $hash;
		return <<<EOT
<script type="text/javascript" src="http://static.manyou.com/scripts/my_iframe.js"></script>
<script language="javascript">
var server = new MyXD.Server("ifm0");
server.registHandler("iframeHasLoaded");
server.start();
function iframeHasLoaded(ifm_id) {
	MyXD.Util.showIframe(ifm_id);
	document.getElementById("loading").style.display = "none";
}
</script>
<div id="loading" style="display:block; padding:100px 0; text-align:center; color:#999999; font-size:12px;">Loading...</div>
<iframe id="ifm0" frameborder="0" width="810" height="810" scrolling="no" style="position:absolute; top:-5000px; left:-5000px;" src="$url"></iframe>
EOT;
	}

	function getCpIframe($uId) {
		$prefix = 'http://uchome.manyou.com';
		$timestamp = time();
		$currentUrl = $this->_getCurrentUrl();
		$request = $_GET;
		unset($request['my_suffix']);
		$params = $request ? $request : array('ac' => 'userapp');
		$queryParams = array();
		foreach ($params as $key => $value) {
			$queryParams[] = $key . '=' . urlencode($value);
		}
		$pageUrl = dirname($currentUrl) . '/' . basename($_SERVER['SCRIPT_URL']) . '?' . join('&', $queryParams);
		if(!$_GET['my_suffix']) {
			$appId = intval($_GET['appid']);
			if ($appId) {
				$mode = $_GET['mode'];
				if ($mode == 'about') {
					$suffix = '/userapp/about?appId=' . $appId;
				} else {
					$suffix = '/userapp/privacy?appId=' . $appId;
				}
			} else {
				$suffix = '/userapp/list';
			}
		} else {
			$suffix = $_GET['my_suffix'];
		}
		$my_extra = $_GET['my_extra'] ? $_GET['my_extra'] : '';
		$delimiter = strrpos($suffix, '?') ? '&' : '?';
		$myUrl = $prefix . urldecode($suffix . $delimiter . 'my_extra=' . $my_extra);
		$hash = md5($this->siteId . '|' . $uId . '|' . $this->siteKey . '|' . $timestamp);
		$delimiter = strrpos($myUrl, '?') ? '&' : '?';
		$url = $myUrl . $delimiter . 's_id=' . $this->siteId . '&uch_id=' . $uId . '&uch_url=' . urlencode($pageUrl) . '&my_suffix=' . urlencode($suffix) . '&timestamp=' . $timestamp . '&my_sign=' . $hash;
		return <<<EOT
<script type="text/javascript" src="http://static.manyou.com/scripts/my_iframe.js"></script>
<script language="javascript">
var server = new MyXD.Server("ifm0");
server.registHandler("iframeHasLoaded");
server.start();
function iframeHasLoaded(ifm_id) {
	MyXD.Util.showIframe(ifm_id);
	document.getElementById("loading").style.display = "none";
}
</script>
<div id="loading" style="display:block; padding:100px 0; text-align:center; color:#999999; font-size:12px;">Loading...</div>
<iframe id="ifm0" frameborder="0" width="810" scrolling="no" height="810" style="position:absolute; top:-5000px; left:-5000px;" src="$url"></iframe>
EOT;
	}

	function _call($method, $params) {
		list($module, $method) = explode('.', $method);
		$response = $this->_callServerMethod($module, $method, $params);
		return $this->_formatServerResponse($response);
	}

	function _processServerRequest() {
		$request = $_POST;
		$module = $request['module'];
		$method = $request['method'];
		$params = $request['params'];

		if (!$module || !$method) {
			return new ErrorResponse('1', 'Invalid Method: ' . $method);
		}

		$params = stripslashes($params);
		$siteKey = $this->siteKey;
		if ($request['ptnId']) {
			$siteKey = md5($this->siteId . $this->siteKey . $request['ptnId'] . $request['ptnMethods']);
		}
		$sign = $this->_generateSign($module, $method, $params, $siteKey);

		if ($sign != $request['sign']) {
			return new ErrorResponse('10', 'Error Sign');
		}

		if ($request['ptnId']) {
			if ($allowMethods = explode(',', $request['ptnMethods'])) {
				if (!in_array(ManyouHelper::getMethodCode($module, $method), $allowMethods)) {
					return new ErrorResponse('13', 'Method Not Allowed');
				}
			}
		}

		$params = unserialize($params);
		$params = $this->_myAddslashes($params);

		return $this->_callLocalMethod($module, $method, $params);
	}

	function _formatLocalResponse($data) {

		require_once libfile('function/cloud');
		$res = array(
					 'my_version' => cloud_get_api_version(),
					 'timezone' => $this->timezone,
					 'version' => $this->version,
					 'charset' => $this->charset,
					 'language' => $this->language
					 );
		if (strtolower(get_class($data)) == 'response') {
			if (is_array($data->result) && $data->getMode() == 'Batch') {
				foreach($data->result as $result) {
					if (strtolower(get_class($result)) == 'response') {
						$res['result'][]  = $result->getResult();
					} else {
						$res['result'][] = array('errno' => $result->getErrno(),
												 'errmsg' =>  $result->getErrmsg()
												);
					}
				}
			} else {
				$res['result']	= $data->getResult();
			}
		} else {
			$res['errCode'] = $data->getErrno();
			$res['errMessage'] = $data->getErrmsg();
		}
		return $res;
	}

	function _callLocalMethod($module, $method, $params) {
		if ($module == 'Batch' && $method == 'run') {
			$response = array();
			foreach($params as $param) {
				$response[] = $this->_callLocalMethod($param['module'], $param['method'], $param['params']);
			}
			return new Response($response, 'Batch');
		}

		$methodName = $this->_getMethodName($module, $method);
		if (method_exists($this, $methodName)) {
			$result = @call_user_func_array(array($this, $methodName), $params);
			if (is_object($result) && is_a($result, 'ErrorResponse')) {
				return $result;
			}
			return new Response($result);
		} else {
			return new ErrorResponse('2', 'Method not implemented: ' . $methodName);
		}
	}

	function _getMethodName($module, $method) {
		return 'on' . ucfirst($module) . ucfirst($method);
	}

	function _generateSign($module, $method, $params, $siteKey) {
		return md5($module . '|' . $method . '|' . $params . '|' . $siteKey);
	}

	function _getCurrentUrl() {
		$protocal = $_SERVER['HTTPS'] ? 'https' : 'http';
		$currentUrl = $protocal . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
		if ($_SERVER['QUERY_STRING']) {
			$currentUrl .= '?' . $_SERVER['QUERY_STRING'];
		}
		return $currentUrl;
	}

	function _myAddslashes($string) {
		if(is_array($string)) {
			foreach($string as $key => $val) {
				$string[$key] = $this->_myAddslashes($val);
			}
		} else {
			$string = ($string === null) ? null : addslashes($string);
		}
		return $string;
	}

	function onUsersGetInfo($uIds, $fields = array(), $isExtra = false) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onUsersGetFriendInfo($uId, $num = MY_FRIEND_NUM_LIMIT, $isExtra = false) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onUsersGetExtraInfo($uIds) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onUsersGetFormHash($uId, $userAgent) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onFriendsGet($uIds, $friendNum = MY_FRIEND_NUM_LIMIT) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onFriendsAreFriends($uId1, $uId2) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onUserApplicationAdd($uId, $appId, $appName, $privacy, $allowSideNav, $allowFeed, $allowProfileLink,  $defaultBoxType, $defaultMYML, $defaultProfileLink, $version, $displayMethod, $displayOrder = null, $userPanelArea = null, $canvasTitle = null,	$isFullscreen = null , $displayUserPanel = null, $additionalStatus = null) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onUserApplicationRemove($uId, $appIds) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onUserApplicationUpdate($uId, $appIds, $appName, $privacy, $allowSideNav, $allowFeed, $allowProfileLink, $version, $displayMethod, $displayOrder = null, $userPanelArea = null, $canvasTitle = null,  $isFullscreen = null, $displayUserPanel = null) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onUserApplicationGetInstalled($uId) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onUserApplicationGet($uId, $appIds) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onSiteGetUpdatedUsers($num) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onSiteGetUpdatedFriends($num) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onSiteGetAllUsers($from, $num, $friendNum = MY_FRIEND_NUM_LIMIT) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onSiteGetStat($beginDate = null, $num = null, $orderType = 'ASC') {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onFeedPublishTemplatizedAction($uId, $appId, $titleTemplate, $titleData, $bodyTemplate, $bodyData, $bodyGeneral = '', $image1 = '', $image1Link = '', $image2 = '', $image2Link = '', $image3 = '', $image3Link = '', $image4 = '', $image4Link = '', $targetIds = '', $privacy = '', $hashTemplate = '', $hashData = '', $specialAppid=0) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onNotificationsSend($uId, $recipientIds, $appId, $notification) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onNotificationsGet($uId) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onApplicationUpdate($appId, $appName, $version, $displayMethod, $displayOrder = null, $userPanelArea = null, $canvasTitle = null,	$isFullscreen = null, $displayUserPanel = null, $additionalStatus = null) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onApplicationRemove($appIds) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onApplicationSetFlag($applications, $flag) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onCreditGet($uId) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onCreditUpdate($uId, $credits, $appId, $note) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onRequestSend($uId, $recipientIds, $appId, $requestName, $myml, $type) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onVideoAuthSetAuthStatus($uId, $status) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onVideoAuthAuth($uId, $picData, $picExt = 'jpg', $isReward = false) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onSearchGetUserGroupPermissions($userGroupIds) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onSearchGetUpdatedPosts($num, $lastPostIds = array()) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onSearchRemovePostLogs($pIds) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onSearchGetPosts($pIds) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onSearchGetNewPosts($num, $fromPostId = 0) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onSearchGetAllPosts($num, $pId = 0, $orderType = 'ASC') {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onSearchRecyclePosts($pIds) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onSearchGetUpdatedThreads($num, $lastThreadIds = array(), $lastForumIds = array(), $lastUserIds = array()) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onSearchRemoveThreadLogs($lastThreadIds = array(), $lastForumIds = array(), $lastUserIds = array()) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onSearchGetThreads($tIds) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onSearchRecycleThreads($tIds) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onSearchGetNewThreads($num, $tId = 0) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onSearchGetAllThreads($num, $tId = 0, $orderType = 'ASC') {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onSearchGetForums($fIds = array()) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onSearchSetConfig($data = array()) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onSearchGetConfig($data = array()) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onSearchSetHotWords($hotWords = array()) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onCommonGetNav($type = '') {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onCloudGetApps($appName = '') {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onCloudSetApp($app) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onCloudOpenCloud() {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onCloudGetStats() {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onConnectSetConfig($data = array()) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

	function onUnionAddAdvs($advs = array()) {
		return new ErrorResponse('2', 'Method not implemented.');
	}

}

class ErrorResponse {

	var $errno = 0;
	var $errmsg = '';

	function ErrorResponse($errno, $errmsg) {
		$this->errno = $errno;
		$this->errmsg = $errmsg;
	}

	function getErrno() {
		return $this->errno;
	}

	function getErrmsg() {
		return $this->errmsg;
	}

	function getResult() {
		return null;
	}

}

class Response {

	var $result;
	var $mode;

	function Response($res, $mode = null) {
		$this->result = $res;
		$this->mode = $mode;
	}

	function getResult() {
		return $this->result;
	}

	function getMode() {
		return $this->mode;
	}

}

class SearchHelper {

	function getTables($table) {
		if ($table == 'post') {
			$settingKey = 'posttable_info';
			$tableName = 'forum_post';
		} elseif ($table == 'thread') {
			$settingKey = 'threadtable_info';
			$tableName = 'forum_thread';
		} else {
			return false;
		}

		global $_G;

		$infos = unserialize($_G['setting'][$settingKey]);
		if ($infos) {
			$tables = array();
			foreach($infos as $id => $row) {
				$suffix = $id ? "_$id" : '';
				$tables[] = $tableName . $suffix;
			}
		} else {
			$tables = array($tableName);
		}
		return $tables;
	}

	function _convertForum($row) {
		$result = array();
		$map = array(
					'fid'	=> 'fId',
					'fup'	=> 'pId',
					'name'	=> 'fName',
					'type'	=> 'type',
					'displayorder'	=> 'displayOrder',
					);
		foreach($row as $k => $v) {
			if (array_key_exists($k, $map)) {
				$result[$map[$k]] = $v;
				continue;
			}

			if ($k == 'status') {
				$isGroup = false;
				switch ($v) {
					case '0' :
						$displayStatus = 'hidden';
						break;
					case '1' :
						$displayStatus = 'normal';
						break;
					case '2' :
						$displayStatus = 'some';
						break;
					case '3' :
						$displayStatus = 'normal';
						$isGroup = true;
						break;
					default :
						$displayStatus = 'unknown';
				}
				$result['displayStatus'] = $displayStatus;
				$result['isGroup'] = $isGroup;
			}
		}
		$result['sign'] = md5(serialize($result));
		return $result;
	}

	function getForums($fIds = array()) {

		if ($fIds) {
			$where = ' AND fid IN (' . implode(',', $fIds) . ')';
		}

		$result = array();
		$sql = sprintf("SELECT COUNT(*) FROM %s
				WHERE 1 %s", DB::table('forum_forum'), $where);

		$result['totalNum'] = DB::result_first($sql);

		$sql = sprintf("SELECT * FROM %s
				WHERE 1 %s
				ORDER BY fid",
				DB::table('forum_forum'), $where);
		$query = DB::query($sql);
		while($forum = DB::fetch($query)) {
			$result['data'][$forum['fid']] = SearchHelper::_convertForum($forum);
		}

		if (!$fIds) {
			$result['sign'] = md5(serialize($result['data']));
		}
		return $result;
	}

	function getUserGroupPermissions($userGroupIds) {
		$fields = array(
						'groupid' => 'userGroupId',
						'grouptitle' => 'userGroupName',
						'readaccess'	=> 'readPermission',
						'allowvisit'	=> 'allowVisit',
						'allowsearch'	=> 'searchLevel',
						);
		$userGroups = array();
		$sql = sprintf("SELECT ug.groupid, ug.grouptitle, ug.allowvisit, ugf.readaccess, ugf.allowsearch
					   FROM %s ug
					  LEFT JOIN %s ugf ON ug.groupid = ugf.groupid
					  WHERE ug.groupid IN (%s)",
					  DB::table('common_usergroup'), DB::table('common_usergroup_field'), implode(',', $userGroupIds));
		$query = DB::query($sql);
		while($row = DB::fetch($query)) {
			foreach($row as $k => $v) {
				if (array_key_exists($k, $fields)) {
					if ($k == 'allowsearch') {
						$userGroups[$row['groupid']]['allowSearchAlbum'] = ($v & 8) ? true : false;
						$userGroups[$row['groupid']]['allowSearchBlog'] = ($v & 4) ? true : false;
						$userGroups[$row['groupid']]['allowSearchForum'] = ($v & 2) ? true : false;
						$userGroups[$row['groupid']]['allowSearchPortal'] = ($v & 1) ? true : false;
						$userGroups[$row['groupid']]['allowFulltextSearch'] = ($v & 32) ? true : false;
					} else {
						$userGroups[$row['groupid']][$fields[$k]] = $v;
					}
				}
				$userGroups[$row['groupid']]['forbidForumIds'] = array();
				$userGroups[$row['groupid']]['allowForumIds'] = array();
				$userGroups[$row['groupid']]['specifyAllowForumIds'] = array();
			}
		}

		$query = DB::query(sprintf('SELECT fid FROM %s where status IN (1, 2)', DB::table('forum_forum')));
		$fIds = array();
		while($row = DB::fetch($query)) {
			$fIds[$row['fid']] = $row['fid'];
		}

		$fieldForums = array();
		$query = DB::query(sprintf('SELECT * FROM %s where fid IN (%s)', DB::table('forum_forumfield'), implode($fIds, ', ')));
		while($row = DB::fetch($query)) {
			$fieldForums[$row['fid']] = $row;
		}

		foreach($fIds as $fId) {
			$row = $fieldForums[$fId];
			$allowViewGroupIds = array();
			if ($row['viewperm']) {
				$allowViewGroupIds = explode("\t", $row['viewperm']);
			}
			foreach($userGroups as $gid => $_v) {
				if ($row['password']) {
					$userGroups[$gid]['forbidForumIds'][] = $fId;
					continue;
				}
				$perm = unserialize($row['formulaperm']);
				if(is_array($perm)) {
					$spviewperm = explode("\t", $row['spviewperm']);
					if (in_array($gid, $spviewperm)) {
						$userGroups[$gid]['allowForumIds'][] = $fId;
						$userGroups[$gid]['specifyAllowForumIds'][] = $fId;
						continue;
					}
					if ($perm[0] || $perm[1] || $perm['users']) {
						$userGroups[$gid]['forbidForumIds'][] = $fId;
						continue;
					}
				}
				if (!$allowViewGroupIds) {
					$userGroups[$gid]['allowForumIds'][] = $fId;
				} elseif (!in_array($gid, $allowViewGroupIds)) {
					$userGroups[$gid]['forbidForumIds'][] = $fId;
				} elseif (in_array($gid, $allowViewGroupIds)) {
					$userGroups[$gid]['allowForumIds'][] = $fId;
					$userGroups[$gid]['specifyAllowForumIds'][] = $fId;
				}
			}
		}

		foreach($userGroups as $k => $v) {
			ksort($v);
			$userGroups[$k]['sign'] = md5(serialize($v));
		}
		return $userGroups;
	}

	function getGuestPerm($gfIds = array()) {
		$perm = SearchHelper::getUserGroupPermissions(array(7));
		$guestPerm = $perm[7];
		if ($gfIds) {
			$sql = 'SELECT fid, gviewperm FROM ' . DB::table('forum_forumfield') . ' WHERE fid IN (' . implode(',', $gfIds) . ')';
			$query = DB::query($sql);
			while ($row = DB::fetch($query)) {
				if ($row['gviewperm'] == 1) {
					$guestPerm['allowForumIds'][] = $row['fid'];
				} else {
					$guestPerm['forbidForumIds'][] = $row['fid'];
				}
			}

		}
		return $guestPerm;
	}

	function convertThread($row) {
		$result = array();
		$map = array(
					'tid'	=> 'tId',
					'fid'	=> 'fId',
					'authorid'	=> 'authorId',
					'author'	=> 'authorName',
					'special'	=> 'specialType',
					'price'	=> 'price',
					'subject'	=> 'subject',
					'readperm'	=> 'readPermission',
					'lastposter'	=> 'lastPoster',
					'views'	=> 'viewNum',
					'replies'	=> 'replyNum',
					'displayorder'	=> 'stickLevel',
					'highlight'	=> 'isHighlight',
					'digest'	=> 'digestLevel',
					'rate'	=> 'rate',
					'attachment'	=> 'isAttached',
					'moderated'	=> 'isModerated',
					'closed'	=> 'isClosed',
					'supe_pushstatus'	=> 'supeSitePushStatus',
					'recommends'	=> 'recommendTimes',
					'recommend_add'	=> 'recommendSupportTimes',
					'recommend_sub'	=> 'recommendOpposeTimes',
					'heats'		=> 'heats',
					'pid'		=> 'pId',
					'isgroup' => 'isGroup',
					'posttableid' => 'postTableId',
					'favtimes'	=> 'favoriteTimes',
					'sharetimes'=> 'shareTimes',
					'icon'	=> 'icon',
					);
		$map2 = array(
					'dateline'	=> 'createdTime',
					'lastpost'	=> 'lastPostedTime',
					);
		foreach($row as $k => $v) {
			if (array_key_exists($k, $map)) {
				if ($k == 'special') {
					switch($v) {
						case 1:
							$v = 'poll';
							break;
						case 2:
							$v = 'trade';
							break;
						case 3:
							$v = 'reward';
							break;
						case 4:
							$v = 'activity';
							break;
						case 5:
							$v = 'debate';
							break;
						case 127:
							$v = 'plugin';
							break;
						default:
							$v = 'normal';
					}
				}

				if ($k == 'displayorder') {
					if ($v >= 0) {
						$result['displayStatus'] = 'normal';
					} elseif ($v = -1) {
						$result['displayStatus'] = 'recycled';
					} elseif ($v = -2) {
						$result['displayStatus'] = 'unapproved';
					} elseif ($v = -3) {
						$result['displayStatus'] = 'ignored';
					} elseif ($v = -4) {
						$result['displayStatus'] = 'draft';
					} else {
						$result['displayStatus'] = 'unknown';
					}

					switch($v) {
						case 1:
							$v = 'board';
							break;
						case 2:
							$v = 'group';
							break;
						case 3:
							$v = 'global';
							break;
						case 0:
						default:
							$v = 'none';
					}
				}

				if (in_array($k, array('highlight', 'moderated', 'closed', 'isgroup'))) {
					$v = $v ? true : false;
				}
				$result[$map[$k]] = $v;
			} elseif (array_key_exists($k, $map2)) {
				$result[$map2[$k]] = dgmdate($v, 'Y-m-d H:i:s', 8);
			}
		}
		return $result;
	}

	function preGetThreads($table, $tIds) {
		$tIds = implode($tIds, ', ');
		$result = array();
		if($tIds) {
			$sql = sprintf("SELECT * FROM %s WHERE tid IN (%s)", $table, $tIds);

			$query = DB::query($sql);
			while($thread = DB::fetch($query)) {
				$thread['pid'] = $threadPosts[$thread['tid']]['pId'];
				$result[$thread['tid']] = SearchHelper::convertThread($thread);
			}
		}
		return $result;
	}

	function getThreadPosts($tIds) {
		$result = array();
		foreach($tIds as $postTableId => $_tIds) {
			$suffix = $postTableId ? "_$postTableId" : '';
			$sql = sprintf("SELECT * FROM %s
						   WHERE tid IN (%s) AND first = 1", DB::table('forum_post' . $suffix), implode($_tIds, ', ')
						  );
			$query = DB::query($sql);
			while($post = DB::fetch($query)) {
				$result[$post['tid']] = SearchHelper::convertPost($post);
			}
		}
		return $result;
	}

	function getThreads($tIds, $isReturnPostId = true) {
		global $_G;
		$tables = array();
		$infos = unserialize($_G['setting']['threadtable_info']);
		if ($infos) {
			foreach($infos as $id => $row) {
				$suffix = $id ? "_$id" : '';
				$tables[] = 'forum_thread' . $suffix;
			}
		} else {
			$tables = array('forum_thread');
		}

		$tableNum = count($tables);
		$res = $data = $_tableInfo = array();
		for($i = 0; $i < $tableNum; $i++) {
			$_threads = SearchHelper::preGetThreads(DB::table($tables[$i]), $tIds);
			if ($_threads) {
				if (!$data) {
					$data = $_threads;
				} else {
					$data = $data +  $_threads;
				}
				if (count($data) == count($tIds)) {
					break;
				}
			}
		}

		if ($isReturnPostId) {
			$threadIds = array();
			foreach($data as $tId => $thread) {
				$postTableId = $thread['postTableId'];
				$threadIds[$postTableId][] = $tId;
			}

			$threadPosts = SearchHelper::getThreadPosts($threadIds);
			foreach($data as $tId => $thread) {
				$data[$tId]['pId'] = $threadPosts[$tId]['pId'];
			}
		}
		return $data;
	}

	function convertPost($row) {
		$result = array();
		$map = array('pid' => 'pId',
						'tid'	=> 'tId',
						'fid'	=> 'fId',
						'authorid'	=> 'authorId',
						'author'	=> 'authorName',
						'useip'	=> 'authorIp',
						'anonymous'	=> 'isAnonymous',
						'subject'	=> 'subject',
						'message'	=> 'content',
						'invisible'	=> 'displayStatus',
						'htmlon'	=> 'isHtml',
						'attachment'	=> 'isAttached',
						'rate'	=> 'rate',
						'ratetimes'	=> 'rateTimes',
						'dateline'	=> 'createdTime',
						'first'		=> 'isThread',
					   );
		$map2 = array(
					  'bbcodeoff'	=> 'isBbcode',
					  'smileyoff'	=> 'isSmiley',
					  'parseurloff'	=> 'isParseUrl',
					 );
		foreach($row as $k => $v) {
			if (array_key_exists($k, $map)) {
				if ($k == 'invisible') {
					switch($v) {
						case 0:
							$v = 'normal';
							break;
						case -1:
							$v = 'recycled';
							break;
						case -2:
							$v = 'unapproved';
							break;
						case -3:
							$v = 'ignored';
							break;
						case -4:
							$v = 'draft';
							break;
						default:
							$v = 'unkonwn';
					}
				}
				if ($k == 'dateline') {
					$result[$map[$k]] = dgmdate($v, 'Y-m-d H:i:s', 8);
					continue;
				}

				if (in_array($k, array('htmlon', 'attachment', 'first', 'anonymous'))) {
					$v = $v ? true : false;
				}

				$result[$map[$k]] = $v;
			} elseif (array_key_exists($k, $map2)) {
				$result[$map2[$k]] = $v ? false : true;
			}
		}
		$result['isWarned'] = $result['isBanned'] = false;
		if ($row['status'] & 1) {
			$result['isBanned'] = true;
		}
		if ($row['status'] & 2) {
			$result['isWarned'] = true;
		}
		return $result;
	}

	function convertNav($row) {
		$map = array(	'id' => 'id',
						'name' => 'name',
						'title' => 'title',
						'url' => 'url',
						'type' => 'provider',
						'navtype' => 'navType',
						'available' => 'available',
						'displayorder' => 'displayOrder',
						'target' => 'linkTarget',
						'highlight' => 'highlight',
						'level' => 'userGroupLevel',
						'subtype' => 'subLayout',
						'subcols' => 'subColNum',
						'subname' => 'subName',
						'suburl' => 'subUrl',
					   );

		foreach($row as $k => $v) {
			if (array_key_exists($k, $map)) {
				if (in_array($k, array('available'))) {
					$v = $v ? true : false;
				}
				if ($k == 'subtype') {
					if ($v == 1) {
						$v = 'parallel';
					} else {
						$v = 'menu';
					}
				}
				if ($k == 'type') {
					switch($v) {
						case '1':
							$v = 'user';
							break;
						case '0':
						default:
							$v = 'system';
							break;
					}
				}
				if ($k == 'navtype') {
					switch($v) {
						case 1:
							$v = 'footer';
							break;
						case 2:
							$v = 'space';
							break;
						case 3:
							$v = 'my';
							break;
						case 0:
							$v = 'header';
							break;
					}
				}
				$result[$map[$k]] = $v;
			}
		}
		return $result;
	}

	function convertPoll($row) {
		$map = array('polloptionid' => 'id',
				'tid' => null,
				'votes' => 'votes',
				'displayorder' => 'displayOrder',
				'polloption' => 'label',
				'voterids' => 'voterIds',
				);
		$result = array();
		foreach($row as $k => $v) {
			$field = $map[$k];
			if ($field !== null) {
				$result[$field] = $v;
			}
		}
		return $result;
	}

	function getPollInfo($tIds) {
		if (!is_array($tIds) || count($tIds) <= 0) {
			return array();
		}

		$sql = 'SELECT * FROM ' . DB::table('forum_polloption') . ' WHERE tid IN (' . implode(',', $tIds) . ')';
		$result = array();
		$query = DB::query($sql);
		while($row = DB::fetch($query)) {
			$result[$row['tid']][$row['polloptionid']] = SearchHelper::convertPoll($row);
		}
		return $result;

	}

	function allowSearchForum() {
		$query = DB::query("UPDATE " . DB::table('common_usergroup_field') .  " SET allowsearch = allowsearch | 2 WHERE groupid < 20 AND groupid NOT IN (5, 6)");
		require_once libfile('function/cache');
		updatecache('usergroups');
	}

}

class ManyouHelper {

	function getMethodCode($module, $method) {
		$methods = array(
				'Search.getUserGroupPermissions' => 10,
				'Search.getUpdatedPosts' => 11,
				'Search.removePostLogs' => 12,
				'Search.getPosts' => 13,
				'Search.getNewPosts' => 14,
				'Search.getAllPosts' => 15,
				'Search.removePosts' => 16,
				'Search.getUpdatedThreads' => 17,
				'Search.removeThreadLogs' => 18,
				'Search.getThreads' => '1a',
				'Search.getNewThreads' => '1b',
				'Search.getAllThreads' => '1c',
				'Search.getForums' => '1d',
				'Search.recycleThreads' => '1e',
				'Search.recycleThreads' => '1f',
				'Search.setConfig' => '20',
				'Search.getConfig' => '21',
				'Search.setHotWords' => '22',

				'Cloud.getApps' => '30',
				'Cloud.setApp' => '31',
				'Cloud.openCloud' => '32',
				'Cloud.getStatus' => '33',
				'Connect.setConfig' => '34',
				'Union.addAdvs' => '35',

				'Common.setConfig' => '40',
				'Common.getNav' => '41',
				'Site.getUpdatedUsers' => '42',
				'Site.getUpdatedFriends' => '43',
				'Site.getAllUsers' => '44',
				'Site.getStat' => '45',

				'Users.getInfo' => '50',
				'Users.getFriendInfo' => '51',
				'Users.getExtraInfo' => '52',
				'Friends.get' => '53',
				'Friends.areFriends' => '54',
				'Application.update' => '55',
				'Application.remove' => '56',
				'Application.setFlag' => '57',
				'UserApplication.add' => '58',
				'UserApplication.remove' => '5a',
				'UserApplication.update' => '5b',
				'UserApplication.getInstalled' => '5c',
				'UserApplication.get' => '5d',
				'Feed.publishTemplatizedAction' => '5e',
				'Notifications.send' => '5f',
				'Notifications.get' => '60',
				'Profile.setMYML' => '61',
				'Profile.setActionLink' => '62',
				'Request.send' => '63',
				'NewsFeed.get' => '64',
				'VideoAuth.setAuthStatus' => '65',
				'VideoAuth.auth' => '66',
				'Users.getFormHash' => '67',

				'Credit.get' => '70',
				'Credit.update' => '71',
				'MiniBlog.post' => '72',
				'MiniBlog.get' => '73',
				'Photo.createAlbum' => '74',
				'Photo.updateAlbum' => '75',
				'Photo.removeAlbum' => '76',
				'Photo.getAlbums' => '77',
				'Photo.upload' => '78',
				'Photo.get' => '7a',
				'Photo.update' => '7b',
				'Photo.remove' => '7c',
				'ImbotMsn.setBindStatus' => '7d',
				);
		return $methods[$module . '.' . $method];
	}
}

class Cloud_Client {

	var $cloudApiIp = '';

	var $sId = 0;

	var $sKey = '';

	var $url = '';

	var $format = '';

	var $ts = 0;

	var $debug = false;

	var $errno = 0;

	var $errmsg = '';

	function Cloud_Client($sId = 0, $sKey = '') {

			$this->sId = intval($sId);
			$this->sKey = $sKey;
			$this->url = 'http://api.discuz.qq.com/site.php';
			$this->format = 'php';
			$this->ts = time();
	}

	function _callMethod($method, $args) {
		$this->errno = 0;
		$this->errmsg = '';
		$avgDomain = explode('.', $method);
		switch ($avgDomain[0]) {
			case 'site':
				$url = 'http://api.discuz.qq.com/site_cloud.php';
				break;
			case 'qqgroup':
				$url = 'http://api.discuz.qq.com/site_qqgroup.php';
				break;
			case 'connect':
				$url = 'http://api.discuz.qq.com/site_connect.php';
				break;
			case 'security':
				$url = 'http://api.discuz.qq.com/site_security.php';
				break;
			default:
				$url = $this->url;
		}


		$params = array();
		$params['sId'] = $this->sId;
		$params['method'] = $method;
		$params['format'] = strtoupper($this->format);

		$params['sig'] = $this->_generateSig($params, $method, $args);
			$params['ts'] = $this->ts;

		$data = $this->_createPostString($params, $args, true);
		list($errno, $result) = $this->_postRequest($url, $data);
		if ($this->debug) {
			$this->_message('receive data ' . htmlspecialchars($result) . "\n\n");
		}

		if (!$errno && $result) {
			$result = @unserialize($result);
			if(is_array($result) && array_key_exists('result', $result)) {
				if ($result['errCode']) {
					$this->errno = $result['errCode'];
					$this->errmsg = $result['errMessage'];
					return false;
				} else {
					return $result['result'];
				}
			} else {
				return $this->_unknowErrorMessage();
			}
		} else {
			return $this->_unknowErrorMessage();
		}
	}

	function _unknowErrorMessage() {
		$this->errno = 1;
		$this->errmsg = 'An unknown error occurred. May be DNS Error. ';
		return false;
	}

	function _generateSig(&$params, $method, $args) {
		$str = $this->_createPostString($params, $args, true);
		if ($this->debug) {
			$this->_message('sig string: ' . $str . '|' . $this->sKey . '|' . $this->ts . "\n\n");
		}

		return md5(sprintf('%s|%s|%s', $str, $this->sKey, $this->ts));
	}

	function _createPostString($params, $args, $isEncode = false) {
		ksort($params);
		$str = '';
		foreach ($params as $k=>$v) {
			$str .= $k . '=' . $v . '&';
		}

		ksort($args);
		$str .= $this->_buildArrayQuery($args, 'args', $isEncode);
		return $str;
	}

	function _postRequest($url, $data, $ip = '') {
		if ($this->debug) {
			$this->_message('post params: ' . $data. "\n\n");
		}

		$ip = $this->cloudApiIp;

		$result = $this->_fsockopen($url, 4096, $data, '', false, $ip, 5);
		return array(0, $result);
	}

	function _fsockopen($url, $limit = 0, $post = '', $cookie = '', $bysocket = FALSE, $ip = '', $timeout = 15, $block = TRUE) {
		return dfsockopen($url, $limit, $post, $cookie, $bysocket, $ip, $timeout, $block);
	}

	function _message($msg) {
		echo $msg;
	}

	function _buildArrayQuery($data, $key = '', $isEncode = false) {
		require_once libfile('function/cloud');
		return buildArrayQuery($data, $key, $isEncode);
	}

	function register($sName, $sSiteKey, $sUrl, $sCharset,
					  $sTimeZone, $sUCenterUrl, $sLanguage,
					  $sProductType, $sProductVersion,
					  $sTimestamp, $sApiVersion, $sSiteUid, $sProductRelease) {

		return $this->_callMethod('site.register', array('sName' => $sName,
														 'sSiteKey' => $sSiteKey,
														 'sUrl' => $sUrl,
														 'sCharset' => $sCharset,
														 'sTimeZone' => $sTimeZone,
														 'sUCenterUrl' => $sUCenterUrl,
														 'sLanguage' => $sLanguage,
														 'sProductType' => $sProductType,
														 'sProductVersion' => $sProductVersion,
														 'sTimestamp' => $sTimestamp,
														 'sApiVersion' => $sApiVersion,
														 'sSiteUid' => $sSiteUid,
														 'sProductRelease' => $sProductRelease
												   )
								  );
	}

	function sync($sName, $sSiteKey, $sUrl, $sCharset,
				  $sTimeZone, $sUCenterUrl, $sLanguage,
				  $sProductType, $sProductVersion,
				  $sTimestamp, $sApiVersion, $sSiteUid, $sProductRelease) {

		return $this->_callMethod('site.sync', array('sId' => $this->sId,
													 'sName' => $sName,
													 'sSiteKey' => $sSiteKey,
													 'sUrl' => $sUrl,
													 'sCharset' => $sCharset,
													 'sTimeZone' => $sTimeZone,
													 'sUCenterUrl' => $sUCenterUrl,
													 'sLanguage' => $sLanguage,
													 'sProductType' => $sProductType,
													 'sProductVersion' => $sProductVersion,
													 'sTimestamp' => $sTimestamp,
													 'sApiVersion' => $sApiVersion,
													 'sSiteUid' => $sSiteUid,
													 'sProductRelease' => $sProductRelease
													 )
								  );
	}

	function resetKey() {

		return $this->_callMethod('site.resetKey', array('sId' => $this->sId));
	}

	function resume($sUrl, $sCharset, $sProductType, $sProductVersion) {

		return $this->_callMethod('site.resume', array(
																			   'sUrl' => $sUrl,
																			   'sCharset' => $sCharset,
																			   'sProductType' => $sProductType,
																			   'sProductVersion' => $sProductVersion
																			   )
												 );
	}

	function QQGroupMiniportal($topic, $normal, $gIds = array()) {

		return $this->_callMethod('qqgroup.miniportal', array('topic' => $topic, 'normal' => $normal, 'gIds' => $gIds));
	}

	function connectSync($qzoneLikeQQ, $mblogQQ) {

		return $this->_callMethod('connect.sync', array('qzoneLikeQQ' => $qzoneLikeQQ, 'mblogFollowQQ' => $mblogQQ));
	}


	function securityReportUserRegister($batchData) {
		global $_G;

		$data = array(
					  'sId' => $this->sId,
					  );

		$data['data'] = $batchData;
		return $this->_callMethod('security.user.register', $data);
	}

	function securityReportUserLogin($batchData) {

		global $_G;

		$data = array(
					  'sId' => $this->sId,
					  );

		$data['data'] = $batchData;
		return $this->_callMethod('security.user.login', $data);
	}

	function securityReportPost($batchData) {

		global $_G;

		$data = array(
					  'sId' => $this->sId,
					  );

		$data['data'] = $batchData;

		return $this->_callMethod('security.post.handlePost', $data);
	}

	function securityReportDelPost($batchData) {
		global $_G;

		$data = array(
			'sId' => $this->sId,
		);
		$data['data'] = $batchData;

		return $this->_callMethod('security.post.del', $data);
	}

	function securityReportBanUser($batchData) {
		global $_G;

		$data = array(
			'sId' => $this->sId,
		);
		$data['data'] = $batchData;

		return $this->_callMethod('security.user.ban', $data);
	}

	function securityReportOperation($sSiteUid, $operateType, $results, $operateTime, $extra = array()) {

		$data = array(
					  'sId' => $this->sId,
					  'sSiteUid' => $sSiteUid,
					  'operateType' => $operateType,
					  'operateTime' => $operateTime,
					  'results' => $results,
					  'extra' => $extra
					  );
		return $this->_callMethod('security.sitemaster.handleOperation', $data);
	}

}

class Discuz_Cloud_Client {

	var $debug = false;

	var $errno = 0;

	var $errmsg = '';

	var $Client = null;

	var $my_status = false;
	var $cloud_status = false;

	var $siteId = '';
	var $siteKey = '';
	var $siteName = '';
	var $uniqueId = '';
	var $siteUrl = '';
	var $charset = '';
	var $timeZone = 0;
	var $UCenterUrl = '';
	var $language = '';
	var $productType = '';
	var $productVersion = '';
	var $productRelease = '';
	var $timestamp = 0;
	var $apiVersion = '';
	var $siteUid = 0;

	function Discuz_Cloud_Client($debug = false) {

		if(!defined('IN_DISCUZ')) {
			exit('Access Denied');
		}

		global $_G;

		require_once DISCUZ_ROOT.'./source/discuz_version.php';

		$this->my_status = !empty($_G['setting']['my_app_status']) ? $_G['setting']['my_app_status'] : '';
		$this->cloud_status = !empty($_G['setting']['cloud_status']) ? $_G['setting']['cloud_status'] : '';

		$this->siteId = !empty($_G['setting']['my_siteid']) ? $_G['setting']['my_siteid'] : '';
		$this->siteKey = !empty($_G['setting']['my_sitekey']) ? $_G['setting']['my_sitekey'] : '';
		$this->siteName = !empty($_G['setting']['bbname']) ? $_G['setting']['bbname'] : '';
		$this->uniqueId = $siteuniqueid = DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='siteuniqueid'");
		$this->siteUrl = $_G['siteurl'];
		$this->charset = CHARSET;
		$this->timeZone = !empty($_G['setting']['timeoffset']) ? $_G['setting']['timeoffset'] : '';
		$this->UCenterUrl = !empty($_G['setting']['ucenterurl']) ? $_G['setting']['ucenterurl'] : '';
		$this->language = $_G['config']['output']['language'] ? $_G['config']['output']['language'] : 'zh_CN';
		$this->productType = 'DISCUZX';
		$this->productVersion = defined('DISCUZ_VERSION') ? DISCUZ_VERSION : '';
		$this->productRelease = defined('DISCUZ_RELEASE') ? DISCUZ_RELEASE : '';
		$this->timestamp = TIMESTAMP;

		require_once libfile('function/cloud');
		$this->apiVersion = cloud_get_api_version();

		$this->siteUid = $_G['uid'];

		$this->Client = new Cloud_Client($this->siteId, $this->siteKey);

		if ($debug) {
			$this->Client->debug = true;
			$this->debug = true;
		}

		if ($_G['setting']['cloud_api_ip']) {
			$this->setCloudIp($_G['setting']['cloud_api_ip']);
		}

	}

	function register() {

		$data = $this->Client->register($this->siteName, $this->uniqueId, $this->siteUrl, $this->charset,
										$this->timeZone, $this->UCenterUrl, $this->language,
										$this->productType, $this->productVersion,
										$this->timestamp, $this->apiVersion, $this->siteUid, $this->productRelease);

		$this->errno = $this->Client->errno;
		$this->errmsg = $this->Client->errmsg;

		return $data;
	}

	function sync() {

		$data = $this->Client->sync($this->siteName, $this->uniqueId, $this->siteUrl, $this->charset,
									$this->timeZone, $this->UCenterUrl, $this->language,
									$this->productType, $this->productVersion,
									$this->timestamp, $this->apiVersion, $this->siteUid, $this->productRelease);

		$this->errno = $this->Client->errno;
		$this->errmsg = $this->Client->errmsg;

		return $data;
	}

	function resetKey() {

		$data = $this->Client->resetKey();

		$this->errno = $this->Client->errno;
		$this->errmsg = $this->Client->errmsg;

		return $data;
	}

	function resume() {

		$data = $this->Client->resume($this->siteUrl, 'UTF-8', $this->productType, $this->productVersion);

		$this->errno = $this->Client->errno;
		$this->errmsg = $this->Client->errmsg;

		return $data;
	}

	function setCloudIp($ip) {
		$this->Client->cloudApiIp = $ip;
	}

	function QQGroupMiniportal($topic, $normal, $gIds = array()) {

		$data = $this->Client->QQGroupMiniportal($topic, $normal, $gIds);

		$this->errno = $this->Client->errno;
		$this->errmsg = $this->Client->errmsg;

		return $data;
	}

	function connectSync($qzoneQQ, $mblogQQ) {
		$data = $this->Client->connectSync($qzoneQQ, $mblogQQ);

		$this->errno = $this->Client->errno;
		$this->errmsg = $this->Client->errmsg;

		return $data;
	}

}

class Security_Cloud_Client {

	function Security_Cloud_Client($debug = false) {

		if(!defined('IN_DISCUZ')) {
			exit('Access Denied');
		}

		global $_G;

		require_once DISCUZ_ROOT.'./source/discuz_version.php';

		$this->siteId = !empty($_G['setting']['my_siteid']) ? $_G['setting']['my_siteid'] : '';
		$this->siteKey = !empty($_G['setting']['my_sitekey']) ? $_G['setting']['my_sitekey'] : '';
		$this->siteUrl = $_G['siteurl'];

		require_once libfile('function/cloud');
		$this->apiVersion = cloud_get_api_version();

		$this->siteUid = $_G['member']['uid'];

		$this->Client = new Cloud_Client($this->siteId, $this->siteKey);

		if ($debug) {
			$this->Client->debug = true;
			$this->debug = true;
		}

		if ($_G['setting']['cloud_api_ip']) {
			$this->Client->cloudApiIp = $_G['setting']['cloud_api_ip'];
		}

	}


	function securityReportUserRegister($batchData) {

		$data = $this->Client->securityReportUserRegister($batchData);

		$this->errno = $this->Client->errno;
		$this->errmsg = $this->Client->errmsg;

		return $data;
	}

	function securityReportUserLogin($batchData) {

		$data = $this->Client->securityReportUserLogin($batchData);

		$this->errno = $this->Client->errno;
		$this->errmsg = $this->Client->errmsg;

		return $data;
	}


	function securityReportPost($batchData) {

		$data = $this->Client->securityReportPost($batchData);

		$this->errno = $this->Client->errno;
		$this->errmsg = $this->Client->errmsg;

		return $data;
	}

	function securityReportDelPost($batchData) {
		$data = $this->Client->securityReportDelPost($batchData);

		$this->errno = $this->Client->errno;
		$this->errmsg = $this->Client->errmsg;
		return $data;
	}

	function securityReportBanUser($batchData) {
		$data = $this->Client->securityReportBanUser($batchData);

		$this->errno = $this->Client->errno;
		$this->errmsg = $this->Client->errmsg;
		return $data;
	}

	function securityReportOperation($operateType, $results, $operateTime, $extra = array()) {

		$data = $this->Client->securityReportOperation($this->siteUid, $operateType, $results, $operateTime, $extra);

		$this->errno = $this->Client->errno;
		$this->errmsg = $this->Client->errmsg;

		return $data;
	}
}

?>