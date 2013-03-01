<?php

/**
 *		[Discuz!] (C)2001-2099 Comsenz Inc.
 *		This is NOT a freeware, use is subject to license terms
 *
 *		$Id: function_sec.php 28975 2012-03-21 05:19:29Z songlixin $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
function updateEvilTableOperate($idtype, $moderation) {
	if (notOpenService()) {
		return false;
	}
	$allowidtype = array('tid', 'pid', 'uid', 'replies', 'threads');
	$result['validate'] = 1;
	$result['delete'] = 2;

	if ($moderation['invalidate']) {
		$moderation['delete'] = array_merge($moderation['delete'], $moderation['invalidate']);
	}
	$moderations = array('delete' => $moderation['delete'], 'validate' => $moderation['validate']);
	if (!in_array($idtype, $allowidtype) || !$moderations) {
		return true;
	}

	if ($idtype == 'tid' || $idtype == 'threads') {
		foreach($moderations as $key => $value) {
			if (is_array($value) && count($value) > 0) {
				DB::query("UPDATE ".DB::table('security_evilpost')." SET operateresult = '".$result[$key]."' WHERE pid IN (".dimplode($value).") AND type = 1");
			}
		}
	} elseif($idtype == 'pid' || $idtype == 'replies') {
		foreach($moderations as $key => $value) {
			if (is_array($value) && count($value) > 0) {
				DB::query("UPDATE ".DB::table('security_evilpost')." SET operateresult = '".$result[$key]."' WHERE pid IN (".dimplode($value).")");
			}
		}
	} elseif($idtype == 'uid') {
		foreach($moderations as $key => $value) {
			if (is_array($value) && count($value) > 0) {
				DB::query("UPDATE ".DB::table('security_eviluser')." SET operateresult = '".$result[$key]."' WHERE uid IN (".dimplode($value).")");
			}
		}
	}
	return true;
}

function _checkEvilExist($idtype, $ids) {
	$allowidtype = array('tid', 'pid', 'uid');
	$returnid = array();
	return $returnid;
}


function getTable($tid) {
	global $_G;

	$result = $ret = array();
	if(!is_numeric($tid)) {
		return $ret;
	}
	loadcache('threadtableids');
	$threadtableids = array(0);
	if(!empty($_G['cache']['threadtableids'])) {
		$threadtableids = array_merge($threadtableids, $_G['cache']['threadtableids']);
	}

	foreach($threadtableids as $tableid) {
		$table = $tableid > 0 ? "forum_thread_{$tableid}" : 'forum_thread';
		$ret = DB::fetch_first("SELECT * FROM ".DB::table($table)." WHERE tid='$tid' LIMIT 1");

		if($ret) {
			$result['threadtable'] = $table;
			$result['posttable'] = 'forum_post'.($ret['posttableid'] ? '_'.$ret['posttableid'] : '');
			break;
		}
	}
	return $result;
}


function handleEvilPost($tid, $pid, $evilType, $evilLevel = 1) {
	global $_G;
	if (notOpenService()) {
		return false;
	}
	include_once DISCUZ_ROOT.'./source/language/lang_admincp_cloud.php';
	loadSecLog($pid, 'pid');
	$evilPost = DB::fetch_first("SELECT * FROM ".DB::table('security_evilpost')." WHERE pid='$pid'");

	if (is_array($evilPost)) {
		$data = $evilPost;
		$data['evilcount'] = $evilPost['evilcount'] + 1;
	} else {
		require_once libfile('function/delete');
		require_once libfile('function/forum');
		require_once libfile('function/post');

		$data = array('pid' => $pid, 'tid' => $tid, 'evilcount' => 1, 'eviltype' => $evilType, 'createtime' => TIMESTAMP);
		$post = get_post_by_pid($pid);

		if (is_array($post) && count($post) > 0) {
			if ($tid != $post['tid']) {
				return false;
			}

			if ($post['first']) {
				$data['type'] = 1;
				if (checkThreadIgnore($tid)) {
					return false;
				}
				DB::insert('security_evilpost', $data, 0, 1);
				updateEvilCount('thread');
				DB::query("UPDATE ".DB::table('forum_thread')." SET displayorder='-1', digest='0', moderated='1' WHERE tid = '".$tid."'");
				deletethread(array($tid), true, true, true);
				updatepost(array('invisible' => '-1'), "tid = '".$tid."'");
				updatemodlog($tid, 'DEL', 0, 1, $extend_lang['security_modreason']);
			} else {
				$data['type'] = 0;
				if (checkPostIgnore($pid, $post)) {
					return false;
				}
				DB::insert('security_evilpost', $data, 0, 1);
				updateEvilCount('post');

				deletepost(array($pid), 'pid', true, false, true);
			}

		} else {
			$data['operateresult'] = 2;
			DB::insert('security_evilpost', $data, 0, 1);
		}
	}

	return true;
}


function handleEvilUser($uid, $evilType, $evilLevel = 1) {
	global $_G;
	if (notOpenService()) {
		return false;
	}
	include_once DISCUZ_ROOT.'./source/language/lang_admincp_cloud.php';
	loadSecLog($uid, 'uid');
	$evilUser = DB::fetch_first("SELECT * FROM ".DB::table('security_eviluser')." WHERE uid='$uid'");

	if (is_array($evilUser)) {
		$data = $evilUser;
		$data['evilcount'] = $evilUser['evilcount'] + 1;
	} else {

		if (checkUserIgnore($uid)) {
			return true;
		}
		$data = array('uid' => $uid, 'evilcount' => 1, 'eviltype' => $evilType, 'createtime' => TIMESTAMP);
		$user = DB::fetch_first("SELECT * FROM " . DB::table('common_member') . " WHERE uid = '$uid'");

		DB::insert('security_eviluser', $data, 0, 1);
		updateEvilCount('member');

		if (is_array($user)) {
			require_once libfile('function/misc');
			$update = DB::update('common_member', array('groupid' => 4), "uid = '".$uid."'");
			if ($update) {
				$_G['member']['username'] = 'SYSTEM';
				savebanlog($user['username'], $user['groupid'], 3, 0, $extend_lang['security_modreason']);
			}
		} else {
			$data['operateresult'] = 2;
			DB::insert('security_eviluser', $data, 0, 1);
		}
	}

	return true;
}

function logDeleteThread($tids, $reason = 'Admin Delete') {
	global $_G;
	if (notOpenService()) {
		return false;
	}
	if (!is_array($tids)) {
		$tids = array($tids);
	}
	$postids = array();
	$logData = array();
	loadcache(array('threadtableids', 'posttableids'));
	$threadtableids = !empty($_G['cache']['threadtableids']) ? $_G['cache']['threadtableids'] : array();
	$posttableids = !empty($_G['cache']['posttableids']) ? $_G['cache']['posttableids'] : array();
	$threadtableids = array_unique(array_merge(array('0'), $threadtableids));
	$posttableids = array_unique(array_merge(array('0'), $threadtableids));


	foreach($threadtableids as $tableid) {
		$threadtable = !$tableid ? "forum_thread" : "forum_thread_$tableid";
		$idStrs = dimplode($tids);
		$query = DB::query("SELECT tid, posttableid, displayorder FROM ".DB::table($threadtable)." WHERE tid IN ($idStrs)");
		while($row = DB::fetch($query)) {
			if ($row['displayorder'] != '-1') {
				$row['posttableid'] = !empty($row['posttableid']) && in_array($row['posttableid'], $posttableids) ? $row['posttableid'] : '0';
				$postids[$row['posttableid']][$row['tid']] = $row['tid'];
			}
		}
	}


	foreach ($posttableids as $postTableId) {
		if (count($postids[$postTableId])) {
			$postTable = ($postTableId > 0) ? "forum_post_{$postTableId}" : "forum_post";
			$postTableTids = dimplode($postids[$postTableId]);
			$query = DB::query("SELECT * FROM " . DB::table($postTable) . " WHERE tid IN ($postTableTids) AND first = '1'");
			while($data = DB::fetch($query)) {
				$logData[] = array(
					'tid' => $data['tid'],
					'pid' => $data['pid'],
					'fid' => $data['fid'],
					'uid' => $data['authorid'],
					'clientIp' => $data['useip'],
					'openId' => getOpenId($data['authorid']),
				);
			}
		}
	}
	file_put_contents('delete.log', var_export($logData, TRUE), FILE_APPEND);

	if (count($logData)) {
		require_once libfile('class/sec');
		$sec = Sec::getInstance();
		foreach ($logData as $data) {
			$sec->logFailed('delThread', $data, $reason);
		}
	}
	return true;
}

function logDeletePost($pids, $reason = 'Admin Delete') {
	if (notOpenService()) {
		return false;
	}
	if (!is_array($pids)) {
		$pids = array($pids);
	}
	$logData = array();
	require_once libfile('function/forum');

	foreach ($pids as $pid) {
		$postInfo = get_post_by_pid($pid);
		if ($postInfo['invisible'] != '-5') {
			$logData[] = array(
				'tid' => $postInfo['tid'],
				'pid' => $postInfo['pid'],
				'fid' => $postInfo['fid'],
				'uid' => $postInfo['authorid'],
				'clientIp' => $postInfo['useip'],
				'openId' => getOpenId($postInfo['authorid']),
			);
		}
	}
	file_put_contents('delete.log', var_export($logData, TRUE), FILE_APPEND);
	if (count($logData)) {
		require_once libfile('class/sec');
		$sec = Sec::getInstance();
		foreach ($logData as $data) {
			$sec->logFailed('delPost', $data, $reason);
		}
	}
}

function logBannedMember($username, $reason = 'Admin Banned') {
	if (notOpenService()) {
		return false;
	}
	if (!$username) {
		return false;
	}
	$username = daddslashes($username);
	$uid = DB::result_first("SELECT uid FROM " . DB::table('common_member') . " WHERE username = '$username'");
	if ($uid) {
		require_once libfile('class/sec');
		$sec = Sec::getInstance();
		$data = array(
			'uid' => $uid,
			'openId' => getOpenId($uid),
			'clientIp' => getMemberIp($uid),
		);
		$sec->logFailed('banUser', $data, $reason);
	}
}


function loadSecLog($id, $type) {
	global $_G;
	$debug = 0;
	if (!$debug) {
		return false;
	}
	$date = date("Y-m-d", $_G['timestamp']);
	$logfile = DISCUZ_ROOT."/data/LoadLog" . $type . '-' . $date . ".log";
	$data = date("Y-m-d H:i:s", $_G['timestamp']) . "\t" . $id . "\r\n";
	@file_put_contents($logfile, $data, FILE_APPEND);
}

function checkThreadIgnore($tid) {
	if (!intval($tid)) {
		return true;
	}
	require_once libfile('function/forum');
	$checkFiled = array('highlight', 'displayorder', 'digest');
	$thread = get_thread_by_tid($tid);
	$checkResult = false;
	$checkResult = checkBoardIgnore($thread['fid']);
	$checkResult = $checkResult ? true : checkUserIgnore($thread['authorid']);
	foreach ($checkFiled as $field) {
		if ($thread[$field] > 0) {
			$checkResult = true;
		};
	}
	return $checkResult;
}

function checkUserIgnore($uid) {
	global $_G;
	if (!intval($uid)) {
		return true;
	}
	$whiteList = unserialize($_G['setting']['security_usergroups_white_list']);
	$whiteList = is_array($whiteList) ? $whiteList : array();
	$memberInfo = DB::fetch_first("SELECT * FROM " . DB::table('common_member') . " WHERE uid = '$uid'");
	$checkResult = false;
	if ($memberInfo['adminid'] > 0 || in_array($memberInfo['groupid'], $whiteList)) {
		$checkResult = true;
	}
	return $checkResult;
}

function checkPostIgnore($pid, $post) {
	if (!intval($pid)) {
		return true;
	}
	$checkResult = false;
	$checkResult = checkBoardIgnore($post['fid']);
	$checkResult = $checkResult ? true : checkUserIgnore($post['authorid']);

	$postStick = DB::result_first("SELECT count(*) FROM " . DB::table('forum_poststick') . " WHERE pid = '$pid'");
	if ($checkResult || $postStick) {
		$checkResult = true;
	}

	return $checkResult;
}

function checkBoardIgnore($fid) {
	global $_G;
	$checkResult = false;
	if (!intval($fid)) {
		return false;
	}
	$whiteList = unserialize($_G['setting']['security_forums_white_list']);
	$whiteList = is_array($whiteList) ? $whiteList : array();
	if (in_array($fid, $whiteList)) {
		$checkResult = true;
	}
	return $checkResult;
}

function updateEvilCount($type) {
	if (empty($type)) {
		return false;
	}
	$settingKey = 'cloud_security_stats_' . $type;
	$count = DB::result_first("SELECT svalue FROM " . DB::table('common_setting') . " WHERE skey = '$settingKey'");
	if ($count) {
		DB::query("UPDATE " . DB::table('common_setting') . " SET `svalue` = $count + 1 WHERE `skey` = '$settingKey'");
	} else {
		DB::insert("common_setting", array('skey' => $settingKey, 'svalue' => 1));
	}
}

function getMemberIp($uid) {
	if (empty($uid)) {
		return false;
	}
	$member = DB::fetch_first("SELECT * FROM " . DB::table('common_member_status') . " WHERE uid = '$uid'");
	if ($member['lastip']) {
		return $member['lastip'];
	} else {
		return false;
	}
}

function getOpenId($uid) {
	$member = DB::fetch_first("SELECT * FROM " . DB::table('common_member') . " WHERE uid = '$uid'");
	if ($member['conisbind']) {
		$openId = DB::result_first("SELECT conopenid FROM " . DB::table('common_member_connect') . " WHERE uid = '$uid'");
	} else {
		$openId = '0';
	}
	return $openId;
}

function markasreported($operateType, $operateData) {
	if (notOpenService()) {
		return false;
	}

	foreach ($operateData as $data) {
		$operateId[] = $data['operateId'];
	}
	if (count($operateId) > 0) {
		$operateId = dimplode($operateId);
	}
	if ($operateType == 'member') {
		DB::query("UPDATE ".DB::table('security_eviluser')." SET isreported = '1' WHERE uid IN ({$operateId})");
	} elseif(in_array($operateType, array('thread', 'post'))) {
		DB::query("UPDATE ".DB::table('security_evilpost')." SET isreported = '1' WHERE pid IN ({$operateId})");
	}
	return true;
}

function getOperateData($type, $limit = 20) {
	if (notOpenService()) {
		return false;
	}
	$allowType = array('post', 'user', 'member');
	$operateData = array();
	$operateResultData = array();
	if ($type == 'member') {
		$type = 'user';
	}
	if (!in_array($type, $allowType)) {
		return false;
	}
	$tableName = DB::table('security_evil' . $type);
	$query = "SELECT * FROM " . $tableName . " WHERE isreported = 0 AND operateresult > 0 LIMIT $limit";
	$query = DB::query($query);

	while ($tempData = DB::fetch($query)) {
		$operateData[] = $tempData;
	}

	foreach($operateData as $tempData) {
		$operateResult = $tempData['operateresult'] == 1 ? 'recover' : 'delete';
		if ($type == 'post') {
			require_once libfile('function/forum');
			$detailData = get_post_by_pid($tempData['pid']);
			$id = $tempData['pid'];
		} elseif ($type == 'user') {
			$detailData = DB::fetch_first("SELECT * FROM " . DB::table('common_member') . " WHERE uid = '$tempData[uid]'");
			$id = $tempData['uid'];
		}
		if ($type == 'post') {
			$operateType = $detailData['first'] ? 'thread' : 'post';
		} elseif ($type == 'user') {
			$operateType = 'member';
		}
		$data = array(
						'tid' => $detailData['tid'] ? $detailData['tid'] : 0,
						'pid' => $detailData['pid'] ? $detailData['pid'] : 0,
						'fid' => $detailData['fid'] ? $detailData['fid'] : 0,
						'operateType' => $operateType,
						'operate' => $operateResult,
						'operateId' => $id,
						'uid' => $detailData['authorid'] ? $detailData['authorid'] : $detailData['uid'],
					);
		$data['openId'] = getOpenId($data['uid']);
		$data['clientIp'] = $detailData['useip'] ? $detailData['useip'] : getMemberIp($data['uid']);
		$operateResultData[] = $data;
	}
	return $operateResultData;
}

function updatePostOperate($ids, $result) {
	if (notOpenService()) {
		return false;
	}
	$ids = dimplode($ids);
	if ($ids) {
		DB::update('security_evilpost', array('operateresult' => $result), "pid IN (" . $ids . ")");
	}
}

function updateThreadOperate($ids, $result) {
	if (notOpenService()) {
		return false;
	}
	$ids = dimplode($ids);
	if ($ids) {
		DB::update('security_evilpost', array('operateresult' => $result), "tid IN (" . $ids . ")");
	}
}

function updateMemberOperate($ids, $result) {
	if (notOpenService()) {
		return false;
	}
	$ids = dimplode($ids);
	if ($ids) {
		DB::update('security_eviluser', array('operateresult' => $result), "uid IN (" . $ids . ")");
	}
}

function updateMemberRecover($username) {
	if (notOpenService()) {
		return false;
	}
	$username = daddslashes($username);
	$uid = DB::result_first("SELECT uid FROM " . DB::table('common_member') . " WHERE username = '$username'");
	updateMemberOperate(array($uid), 1);
}

function notOpenService() {
	require_once libfile('function/cloud');
	$secStatus = getcloudappstatus('security', 0);

	if (!$secStatus) {
		return true;
	} else {
		return false;
	}
}
?>