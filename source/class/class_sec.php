<?php
require_once DISCUZ_ROOT . './api/manyou/Manyou.php';

class Sec {
	var $_secClient;
	var $_secStatus;
	var $postAction = array('newThread', 'newPost', 'editPost', 'editThread');
	var $userAction = array('register', 'login');
	var $delPostAction = array('delThread', 'delPost');
	var $delUserAction = array('banUser');
	var $retryLimit = 5;
	var $specialType = array('1' => 'poll', '2' => 'trade', '3' => 'reward', '4' => 'activity', '5' => 'debate');

	function getInstance() {
		$_instance = '';
		if (!$_instance) {
			$_instance = new self;
			$_instance->setClient();
		}

		return $_instance;
	}

	function __construct() {
	}

	function setClient() {
		global $_G;

		require_once libfile('function/cloud');
		$this->_secStatus = getcloudappstatus('security', 0);



		if (!$this->_secStatus) {
			return false;
		}

		$this->_secClient = new Security_Cloud_Client;
	}


	function reportRegister($uid, $extra = null) {
		global $_G;

		if (!$this->_secStatus) {
			return false;
		}
        $startTime = microtime(true);
		$uid = intval($uid);
		$member = DB::fetch_first("SELECT * FROM " . DB::table('common_member') . " WHERE uid = '$uid'");
		if (!is_array($member)) {
			return true;
		}

		if ($member['conisbind']) {
			$openId = DB::result_first('SELECT conopenid FROM ' . DB::table('common_member_connect') . " WHERE uid = '$uid'");
		} else {
			$openId = 0;
		}
		$email = $member['email'];
		$secReportCodeStatus = ($_G['setting']['seccodestatus'] & 1) ? 1 : 2;

		$batchData = array();
		$batchData[] = array(
			'siteUid' => $uid,
			'username' => $member['username'],
			'email' => $email,
			'openId' => $openId,
			'registerTime' => $_G['timestamp'],
			'clientIp' => $_G['clientip'],
			'remoteIp' => $_SERVER['REMOTE_ADDR'],
			'hasVerifyCode' => $secReportCodeStatus,
			'regResult' => 1,
			'extra' => $extra
		);

		$result = $this->_secClient->securityReportUserRegister($batchData);
		if (!$result) {
			$ids = array($uid);
			$this->logFailed('register', $ids);
		}
        $this->benchMarkLog($startTime, $uid, $batchData, 'register');

        return $result;
	}

	function reportLogin($uid) {
		global $_G;

		if (!$this->_secStatus) {
			return false;
        }
        $startTime = microtime(true);
		$uid = intval($uid);
		$member = DB::fetch_first("SELECT * FROM " . DB::table('common_member') . " WHERE uid='$uid'");
		if (!is_array($member)) {
			return true;
		}

		$memberField = DB::fetch_first("SELECT * FROM " . DB::table('common_member_field_forum') . " WHERE uid='$uid'");
		$memberStatus = DB::fetch_first("SELECT * FROM " . DB::table('common_member_status') . " WHERE uid='$uid'");
		$memberCount = DB::fetch_first("SELECT * FROM " . DB::table('common_member_count') . " WHERE uid='$uid'");
		$memberVerify = DB::fetch_first("SELECT * FROM " . DB::table('common_member_verify') . " WHERE uid = '$uid'");


		if ($member['conisbind']) {
			$memberConnect = DB::fetch_first('SELECT * FROM ' . DB::table('common_member_connect') . ' WHERE uid=\'' . $uid . '\'');
			$openId = $memberConnect['conopenid'];
		} else {
			$openId = 0;
		}
		$userBitMap['isAdmin'] = $member['adminid'] ? 1 : 2;
		$userBitMap['hasMedal'] = $memberField['medals'] ? 1 : 2;
		$userBitMap['hasAvatar'] = $member['avatarstatus'] ? 1 : 2;
		$userBitMap['hasVerify'] = (is_array($memberVerify)) ? 1 : 2;

		$username = $member['username'];
		$email = $member['email'];
		$emailStatus = $member['emailstatus'];
		$sUrl = $_G['siteurl'];
		$credits = $member['credits'];
		$regIp = $memberStatus['regip'];
		$regDate = $member['regdate'];
		$friends = $memberCount['friends'];
		$onlineTime = $memberCount['oltime']*3600;
		$threads = $memberCount['threads'];
		$posts = $memberCount['posts'];
		$signature = $memberField['sightml'];

		if (!$regIp) {
			$regIp = 'N/A';
		}

		$batchData = array();
		$batchData[] = array(
			'siteUid' => $uid,
			'openId' => $openId,
			'loginTime' => TIMESTAMP,
			'clientIp' => $_G['clientip'],
			'remoteIp' => $_SERVER['REMOTE_ADDR'],
			'username' => $username,
			'email' => $email,
			'emailStatus' => $emailStatus,
			'sUrl' => $sUrl,
			'credits' => $credits,
			'registerIp' => $regIp,
			'registerTime' => $regDate,
			'friends' => $friends,
			'onlineTime' => $onlineTime,
			'threads' => $threads,
			'posts' => $posts,
			'signature' => $signature,
			'userBitMap' => $userBitMap,
			'extra' => $extra
		);
		$result = $this->_secClient->securityReportUserLogin($batchData);
		if (!$result) {
			$ids = array($uid);
			$this->logFailed('login', $ids);
		}
        $this->benchMarkLog($startTime, $uid, $batchData, 'login');

        return $result;
	}


	function reportNewThread($tid, $pid, $extra = null) {
		global $_G;

		if (!$this->_secStatus) {
			return false;
		}
		$url = $_G['siteurl'] . "forum.php?mod=viewthread&tid=$tid";
		return $this->_reportPostData('new', $tid, $pid, $url, $extra);
	}

	function reportNewPost($tid, $pid, $extra = null) {
		global $_G;

		if (!$this->_secStatus) {
			return false;
		}
		$url = $_G['siteurl'] . "forum.php?mod=redirect&goto=findpost&ptid=$tid&pid=$pid";
		return $this->_reportPostData('new', $tid, $pid, $url, $extra);
	}


	function reportEditPost($tid, $pid, $extra = null) {
		global $_G;

		if (!$this->_secStatus) {
			return false;
		}
		$url = $_G['siteurl'] . "forum.php?mod=redirect&goto=findpost&ptid=$tid&pid=$pid";
		return $this->_reportPostData('edit', $tid, $pid, $url, $extra);
	}

	function reportDeletePost($tid, $pid, $extra = null) {
		global $_G;

		if (!$this->_secStatus) {
			return false;
		}
		$url = $_G['siteurl'] . "forum.php?mod=redirect&goto=findpost&ptid=$tid&pid=$pid";
		return $this->_reportPostData('delete', $tid, $pid, $url, $extra);
	}


	function reportOperate($operateType, $operateData, $extra = null) {
		global $_G;

		if (!$this->_secStatus) {
			return false;
		}
		return $this->_secClient->securityReportOperation($operateType, $operateData, time(), $extra);
	}

	function _reportPostData($type, $tid, $pid, $threadUrl, $extra = null) {
		global $_G;

		if (!$this->_secStatus) {
			return false;
        }
        $startTime = microtime(true);

		$tid = intval($tid);
		$pid = intval($pid);

		$thread = DB::fetch(DB::query("SELECT * FROM ".DB::table('forum_thread')." WHERE tid='$tid'"));
		if (!is_array($thread)) {
			return true;
		}
		$post = DB::fetch(DB::query("SELECT * FROM ".DB::table(getposttable($thread['posttableid']))." WHERE pid='$pid'"));
		$member = DB::fetch_first("SELECT * FROM ".DB::table('common_member')." WHERE uid = '$post[authorid]'");
		$memberField = DB::fetch_first("SELECT * FROM " . DB::table('common_member_field_forum') . " WHERE uid='$post[authorid]'");
		$memberStatus = DB::fetch_first("SELECT * FROM " . DB::table('common_member_status') . " WHERE uid='$post[authorid]'");
		$memberVerify = DB::fetch_first("SELECT * FROM " . DB::table('common_member_verify') . " WHERE uid = '$post[authorid]'");
		if ($post['first'] == 1) {
			$type = $type . 'Thread';
		} else {
			$type = $type . 'Post';
		}


		$query = DB::query('SELECT filename,filesize FROM ' . DB::table(getattachtablebytid($tid)) . " WHERE pid='$pid'");
		while ($res = DB::fetch($query)) {
			$postAttachs[] = array('filename' => $res['filename'], 'filesize' => $res['filesize']);
		}

		if (!$post['first']) {
			$firstPost = DB::fetch(DB::query("SELECT * FROM ".DB::table(getposttable($thread['posttableid']))." WHERE tid='$tid' and first=1"));

			$query = DB::query('SELECT filename,filesize FROM ' . DB::table(getattachtablebytid($tid)) . " WHERE pid='$pid'");
			while ($res = DB::fetch($query)) {
				$threadAttachs[] = array('filename' => $res['filename'], 'filesize' => $res['filesize']);
			}
		} else {
			$firstPost = $post;
			$firstPostAttachs = $postAttachs;
		}

		$views = intval($thread['views']);
		$replies = intval($thread['replies']);
		$favourites = intval($thread['favtimes']);
		$supports = intval($thread['recommend_add']);
		$opposes = intval($thread['recommend_sub']);
		$shares = intval($thread['sharetimes']);

		if ($member['conisbind']) {
			$memberConnect = DB::fetch_first('SELECT * FROM ' . DB::table('common_member_connect') . ' WHERE uid=\'' . $_G['uid'] . '\'');
			$openId = $memberConnect['conopenid'];
		} else {
			$openId = 0;
		}

		if (!$thread || !$post) {
			return true;
		}

		$fid = $thread["fid"];

		if ($post['first']) {
			$threadShield = ($post['status'] & 1) ? 1 : 2;
			$threadWarning = ($post['status'] & 2) ? 1 : 2;
		} else {
			$threadShield = ($firstPost['status'] & 1) ? 1 : 2;
			$threadWarning = ($firstPost['status'] & 2) ? 1 : 2;
		}

		$threadSort = 2;
		if ($thread['sortid']) {
			$threadSort = 1;
			if ($post['first']) {
				$sortMessage = $this->convertSortInfo($thread['sortid'], $thread['tid']);
				$post['message'] = $sortMessage . '<br/>' . $post['message'];
			}
		}

		$contentBitMap = array(
			'onTop' => $thread['displayorder'] ? 1:2,
			'hide' => (strpos($post['message'], '[hide')) ? 1:2,
			'digest' => $thread['digest'] ? 1 : 2,
			'highLight' => $thread['highlight'] ? 1:2,
			'special' => $thread['special'] ? 1:2,
			'threadAttach' => 2,
			'threadAttachFlash' => 2,
			'threadAttachPic' => 2,
			'threadAttachVideo' => 2,
			'threadAttachAudio' => 2,
			'threadShield' => $threadShield,
			'threadWarning' => $threadWarning,
			'postAttach' => 2,
			'postAttachFlash' => 2,
			'postAttachPic' => 2,
			'postAttachVideo' => 2,
			'postAttachAudio' => 2,
			'postShield' => ($post['status'] & 1) ? 1 : 2,
			'postWarning' => ($post['status'] & 2) ? 1 : 2,
			'isAdmin' => $member['adminid'] ? 1 : 2,
			'threadSort' => $threadSort,
            'isRush' => getstatus($thread['status'], 3) ? 1 : 2,
            'hasReadPerm' => $thread['readperm'] ? 1 : 2,
            'hasStamp' => ($thread['stamp'] >= 0) ? 1 : 2,
            'hasIcon' => ($thread['icon'] >= 0) ? 1 : 2,
            'isPushed' => $thread['pushedaid'] ? 1 : 2,
            'hasCover' => $thread['cover'] ? 1 : 2,
            'hasReward' => $thread['replycredit'] ? 1 : 2,
            'threadStatus' => $thread['status'],
            'postStatus' => $post['status'],
        );

        if ($post['first']) {
            $contentBitMap['isMobile'] = $this->isMobile($thread['status']) ? 1 : 2;
            if ($contentBitMap['isMobile'] == 1) {
                $contentBitMap['isMobileSound'] = $this->mobileHasSound($thread['status']) ? 1 : 2;
                $contentBitMap['isMobilePhoto'] = $this->mobileHasPhoto($thread['status']) ? 1 : 2;
                $contentBitMap['isMobileGPS'] = $this->mobileHasGPS($thread['status']) ? 1 : 2;
            }
        } else {
            $contentBitMap['isMobile'] = getstatus($post['status'], 4) ? 1 : 2;
        }

		$userBitMap = array(
			'isAdmin' => $member['adminid'] ? 1 : 2,
			'hasMedal' => $memberField['medals'] ? 1 : 2,
			'hasAvatar' => $member['avatarstatus'] ? 1 : 2,
			'hasVerify' => (is_array($memberVerify)) ? 1 : 2,
		);

		$videoExt = array('.rm', '.flv', '.mkv', '.rmvb', '.avi', '.wmv', '.mp4', '.mpeg', '.mpg');
		$audioExt = array('.wav', '.mid', '.mp3', '.m3u', '.wma', '.asf', '.asx');
		if ($firstPostAttachs) {
			foreach($firstPostAttachs as $attach) {
				$fileExt = substr($attach['filename'], strrpos($attach['filename'], '.'));
				if ($fileExt == '.bmp' || $fileExt == '.jpg' || $fileExt == '.jpeg' || $fileExt == '.gif' || $fileExt == '.png') {
					$contentBitMap['threadAttachPic'] = 1;
				}
				if ($fileExt == '.swf' || $fileExt == '.fla') {
					$contentBitMap['threadAttachFlash'] = 1;
				}
				if (in_array($fileExt, $videoExt)) {
					$contentBitMap['threadAttachVideo'] = 1;
				}
				if (in_array($fileExt, $audioExt)) {
					$contentBitMap['threadAttachAudio'] = 1;
				}
			}

			$contentBitMap['threadAttach'] = 1;
		}
		if ($postAttachs) {
			foreach($postAttachs as $attach) {

				$fileExt = substr($attach['filename'], strrpos($attach['filename'], '.'));
				if ($fileExt == '.bmp' || $fileExt == '.jpg' || $fileExt == '.jpeg' || $fileExt == '.gif' || $fileExt == '.png') {
					$contentBitMap['postAttachPic'] = 1;
				}
				if ($fileExt == '.swf' || $fileExt == '.fla') {
					$contentBitMap['postAttachFlash'] = 1;
				}
				if (in_array($fileExt, $videoExt)) {
					$contentBitMap['postAttachVideo'] = 1;
				}
				if (in_array($fileExt, $audioExt)) {
					$contentBitMap['postAttachAudio'] = 1;
				}
			}

			$contentBitMap['postAttach'] = 1;
		}

		if ($thread['authorid'] == $_G['uid']) {
			$threadEmail = $_G['member']['email'];
		} else {
			$threadEmail = DB::result_first('SELECT email FROM '.DB::table('common_member').' WHERE uid='.$thread['authorid']);
		}

		if ($post['authorid'] == $_G['uid']) {
			$postEmail = $_G['member']['email'];
		} else {
			$postEmail = DB::result_first('SELECT email FROM '.DB::table('common_member').' WHERE uid='.$post['authorid']);
		}

		if ($thread['special']) {
			if (array_key_exists($thread['special'], $this->specialType)) {
				$threadSpecial = $this->specialType[$thread['special']];
			} else {
				$threadSpecial = 'other';
			}
		}


		$batchData[] = array(
						'tId' => $tid,
						'pId' => $pid,
						'threadUid' => intval($thread['authorid']),
						'threadUsername' => $thread['author'],
						'threadEmail' => $threadEmail,
						'postUid' => intval($post['authorid']),
						'postUsername' => $post['author'],
						'postEmail' => $postEmail,
						'openId' => $openId,
						'fId' => intval($fid),
						'threadUrl' => $threadUrl,
						'operateTime' => $_G['timestamp'],
						'clientIp' => $_G['clientip'],
						'remoteIp' => $_SERVER['REMOTE_ADDR'],
						'views' => $views,
						'replies' => $replies,
						'favourites' => $favourites,
						'supports' => $supports,
						'opposes' => $opposes,
						'shares' => $shares,
						'title' => $post['subject'],
						'content' => $post['message'],
						'attachList' => $postAttachs,
						'reportType' => $type,
						'contentBitMap' => $contentBitMap,
						'userBitMap' => $userBitMap,
						'extra' => $extra,
						'specialType' => $threadSpecial,
						'signature' => $memberField['sightml'],
		);

		$result = $this->_secClient->securityReportPost($batchData);
        $this->benchMarkLog($startTime, $pid, $batchData, $type);

        if (!$result) {
			$ids = array($tid, $pid);
			$this->logFailed($type, $ids);
			return false;
		} else {
			return true;
		}
	}

	function convertSortInfo($sortId, $tid) {
		global $_G;
		$returnStr = array();
		require_once libfile('function/threadsort');
		$sortInfo = threadsortshow($sortId, $tid);
		foreach ($sortInfo['optionlist'] as $value) {
			if ($value['type'] != 'select') {
				$returnStr[] = $value['title'] . ':' . $value['value'];
			}
		}
		if (count($returnStr)) {
			return implode('<br/>', $returnStr);
		}
		return false;
	}


	function reportDelPost($logId) {
		if (!$this->_secStatus) {
			return false;
		}
		if (!$logId) {
			return true;
		}
		$log = DB::fetch_first("SELECT * FROM " . DB::table('security_failedlog') . " WHERE id = '$logId' LIMIT 1");
		if ($log['pid'] == 0) {
			return true;
		}
		$extra2 = unserialize($log['extra2']);
		$batchData[] = array(
			'tid' => $log['tid'],
			'pid' => $log['pid'],
			'uid' => $log['uid'],
			'delType' => $log['reporttype'],
			'findEvilTime' => $log['createtime'],
			'postTime' => $log['posttime'],
			'reason' => $log['delreason'],
			'fid' => $extra2['fid'],
			'clientIp' => $extra2['clientIp'],
			'openId' => $extra2['openId'],
		);

		$result = $this->_secClient->securityReportDelPost($batchData);
		if (!$result) {
			$ids = array($log['tid'], $log['pid']);
			$this->logFailed($log['reporttype'], $ids);
			return false;
		} else {
			return true;
		}
	}


	function reportBanUser($logId) {
		if (!$this->_secStatus) {
			return false;
		}
		if (!$logId) {
			return true;
		}

		$log = DB::fetch_first("SELECT * FROM " . DB::table('security_failedlog') . " WHERE id = '$logId' LIMIT 1");
		if ($log['uid'] == 0) {
			return true;
		}

		$extra2 = unserialize($log['extra2']);
		$batchData[] = array(
			'uid' => $log['uid'],
			'findEvilTime' => $log['createtime'],
			'postTime' => $log['posttime'],
			'reason' => $log['delreason'],
			'clientIp' => $extra2['clientIp'],
			'openId' => $extra2['openId'],
		);

		$result = $this->_secClient->securityReportBanUser($batchData);
		if (!$result) {
			$ids = array($log['uid']);
			$this->logFailed('banUser', $ids);
			return false;
		} else {
			return true;
		}
	}

	function logFailed($reportType, $ids, $reason = '') {
		global $_G;
		if (!$this->_secStatus) {
			return false;
		}
		$this->_checkAndClearFailNum();

		if (!is_array($ids)) {
			$ids = array($ids);
		}
		$postTime = 0;
		if (in_array($reportType, $this->postAction) || in_array($reportType, $this->delPostAction)) {
			$tid = intval($ids[0]) ? intval($ids[0]) : intval($ids['tid']);
			$pid = intval($ids[1]) ? intval($ids[1]) : intval($ids['pid']);
			$uid = intval($ids['uid']);
			if ($pid == 0) {
				return false;
			}
			if (in_array($reportType, $this->delPostAction)) {
				require_once libfile('function/forum');
				$postInfo = get_post_by_pid($pid);
				$postTime = $postInfo['dateline'];
			}
			$oldDataSql = "SELECT * FROM " . DB::table('security_failedlog') . " WHERE pid = '$pid' LIMIT 1";
		} elseif (in_array($reportType, $this->userAction) || in_array($reportType, $this->delUserAction)) {
			$tid = 0;
			$pid = 0;
			$uid = intval($ids[0]) ? intval($ids[0]) : intval($ids['uid']);
			if ($uid == 0) {
				return false;
			}
			if (in_array($reportType, $this->delUserAction)) {
				$postTime = DB::result_first("SELECT lastpost FROM " . DB::table('common_member_status') . " WHERE uid = '$uid'");
			}
			$oldDataSql = "SELECT * FROM " . DB::table('security_failedlog') . " WHERE uid = '$uid' LIMIT 1";
		} else {
			return false;
		}
		$oldData = DB::fetch_first($oldDataSql);

		if (is_array($oldData)) {
			$data = $oldData;
			$data['reporttype'] = $reportType;
			$data['lastfailtime'] = $_G['timestamp'];
			$data['scheduletime'] = $_G['timestamp'] + 300;
			$data['failcount']++;
		} else {
			$data = array(
						'reporttype' => $reportType,
						'tid' => $tid,
						'pid' => $pid,
						'uid' => $uid,
						'failcount' => 1,
						'createtime' => $_G['timestamp'],
						'posttime' => $postTime,
						'delreason' => daddslashes($reason),
						'scheduletime' => $_G['timestamp'] + 60,
						'lastfailtime' => $_G['timestamp'],
					);
			$data['extra2'] = serialize(array('fid' => $ids['fid'], 'clientIp' => $ids['clientIp'], 'openId' => $ids['openId']));
		}
		if (!$data['uid'] && !$data['tid'] && !$data['pid']) {
			return false;
		}
		DB::insert('security_failedlog', $data, 0, 1);
	}

	function _checkAndClearFailNum($maxNum = '50000') {
		$num = DB::result_first("SELECT count(*) FROM " . DB::table('security_failedlog'));
		if ($num >= $maxNum) {
			return DB::query("TRUNCATE TABLE " . DB::table('security_failedlog'));
		}
	}

	function retryReportData() {
		global $_G;
		if (!$this->_secStatus) {
			return false;
		}
		DB::delete('security_failedlog', 'failcount >= ' . $this->retryLimit);
		DB::delete('security_failedlog', 'lastfailtime = 0');

		$result = 0;
		$clearIds = array();
		$data = DB::fetch_first("SELECT * FROM " . DB::table('security_failedlog') . " ORDER BY id LIMIT 1");
		if (!$data['uid'] && !$data['tid'] && !$data['pid']) {
			return false;
		}
		if ($data['scheduletime'] > $_G['timestamp']) {
			return false;
		}

		$reportType = $data['reporttype'];
		$logId = $data['id'];
		$uid = $data['uid'];
		$tid = $data['tid'];
		$pid = $data['pid'];
		$failcount = $data['failcount'];

		if ($failcount >= $this->retryLimit) {
			if ($this->isDebuging()) {
				writelog('security_failedlog', json_encode($data));
			}
			$clearIds[] = $data['id'];
		} else {
			switch ($reportType) {
				case 'newThread':
					$result = $this->reportNewThread($tid, $pid);
					break;
				case 'newPost':
					$result = $this->reportNewPost($tid, $pid);
					break;
				case 'editPost':
				case 'editThread':
					$result = $this->reportEditPost($tid, $pid);
					break;
				case 'register':
					$result = $this->reportRegister($uid);
					break;
				case 'login':
					$result = $this->reportLogin($uid);
					break;
				case 'delThread':
				case 'delPost':
					$result = $this->reportDelPost($logId);
					break;
				case 'banUser':
					$result = $this->reportBanUser($logId);
					break;
				default:break;
			}
			if ($result) {
				$clearIds[] = $data['id'];
			}
		}
		$this->clearFailed($clearIds);
	}

	function clearFailed($ids = array()) {
		if (!$this->_secStatus) {
			return false;
		}

		if (!is_array($ids)) {
			$ids = array($ids);
		}
		if (count($ids) < 1) {
			return false;
		}

		$ids = dimplode($ids);
		DB::delete('security_failedlog', "id IN ($ids)");
	}

	function isDebuging() {
		$securityPluginClass = DISCUZ_ROOT.'/source/plugin/security/security.class.php';
		if (file_exists($securityPluginClass)) {
			require_once $securityPluginClass;
			$securityPluginClass = new plugin_security();
			return $securityPluginClass->debug;
		} else {
			return false;
		}
    }

    function isMobile($status) {
        if (getstatus($status, 11) || getstatus($status, 12) || getstatus($status, 13)) {
            return true;
        }
        return false;
    }

    function mobileHasSound() {
        if (getstatus($status, 13)) {
            return true;
        }
        return false;
    }

    function mobileHasPhoto() {
        if (getstatus($status, 12) && getstatus($status, 11)) {
            return true;
        }
        return false;
    }

    function mobileHasGPS() {
        if (getstatus($status, 12)) {
            return true;
        }
        return false;
    }

    function benchMarkLog($startTime, $id, $data, $type) {
        return true;
        $endTime = microtime(true);
        include_once libfile('function/cloud');
        $dataSize = strlen(cloud_http_build_query($data));
        $content = array(
            date('Y-m-d H:i:s', $startTime),
            $endTime - $startTime,
            $type,
            $id,
            $dataSize,
        );
        $content = join(',', $content) . "\n";
    }
}

?>