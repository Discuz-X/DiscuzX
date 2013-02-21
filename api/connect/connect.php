<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: connect.php 22929 2011-06-02 03:03:41Z congyushuai $
 */

define('IN_API', true);
define('CURSCRIPT', 'api');
define('X_LANGUAGE', 'zh_cn');

require_once '../../source/class/class_core.php';

$cachelist = array();
$discuz = & discuz_core::instance();

$discuz->cachelist = $cachelist;
$discuz->init_cron = false;
$discuz->init_setting = true;
$discuz->init_user = false;
$discuz->init_session = false;

$discuz->init();

require_once DISCUZ_ROOT . './api/connect/server.php';

define('X_BOARDURL', $_G['setting']['discuzurl']);

class connect extends server {

	function onUserGet($uins) {
		global $_G;

		if(!$uins) {
			return array();
		}

		$users = array();
		$query = DB::query("SELECT m.uid, m.email, m.username, m.conisqzoneavatar, m.conisregister, m.conuin, mp.gender, mp.birthyear, mp.birthmonth, mp.birthday
			FROM ".DB::table('common_member')." m
			LEFT JOIN ".DB::table('common_member_profile')." mp USING(uid) WHERE m.conuin IN (".dimplode($uins).")");
		while($user = DB::fetch($query)) {
			$users[$user['conuin']] = $this->_convertUser($user);
		}

		return $users;
	}

	function onThreadGet($tIds, $returnThreadPost = false, $returnForum = false) {
		global $_G;

		if(!$tIds || !is_array($tIds)) {
			return false;
		}

		$result = $this->_getThreads($tIds, $returnThreadPost, true);
		return $result;
	}

	function onForumGet($fIds) {
		if (!$fIds || !is_array($fIds)) {
		    return array();
		}

		return $this->_getForum($fIds);
	}

	function _getThreads($tIds, $returnThreadPost = false, $returnForum = false) {
		global $_G;

		$threadPosts = $this->_getThreadPosts($tIds);

		$query = DB::query("SELECT * FROM ".DB::table('forum_thread')." WHERE tid IN (".dimplode($tIds).") AND displayorder >= 0");
		$result = array();
		$fIds = array();
		while($thread = DB::fetch($query)) {
			$thread['pid'] = $threadPosts[$thread['tid']]['pId'];
			$result[$thread['tid']] = $this->_convertThread($thread);
			if($returnThreadPost) {
				$result[$thread['tid']]['postInfo'] = $threadPosts[$thread['tid']];
			}
			$fIds[] = $thread['fid'];
		}

		if($returnForum) {
			$forums = $this->_getForum($fIds);
			foreach($result as $tId => $thread) {
				foreach($forums as $fId => $forum) {
					$result[$tId]['forumInfo'] = $forum;
				}
			}
		}

		return $result;
	}

	function _getForum($fIds) {
		global $_G;

		if(!$fIds || !is_array($fIds)) {
			return array();
		}

		$forums = array();
		$query = DB::query("SELECT * FROM ".DB::table('forum_forum')." f LEFT JOIN ".DB::table('forum_forumfield')." ff USING (fid) WHERE f.fid IN (".dimplode($fIds).") AND f.status='1'");
		while($forum = DB::fetch($query)) {
			$forums[$forum['fid']] = $this->_convertForum($forum);
		}
		return $forums;
	}

	function _getThreadPosts($tIds) {
		global $_G;

		$result = array();
		$posttable = getposttablebytid($tIds);

		foreach($posttable AS $posttableid => $tid) {
			$query = DB::query("SELECT * FROM ".DB::table($posttableid)." WHERE tid IN (".dimplode($tid).") AND first='1' AND invisible='0'");
			while($post = DB::fetch($query)) {
				$result[$post['tid']] = $this->_convertPost($post);
			}
		}
		return $result;
	}

	function _convertThread($row) {
		$result = array();
		$map = array(
			'tid' => 'tId',
			'fid' => 'fId',
			'authorid' => 'authorId',
			'author' => 'authorName',
			'special' => 'specialType',
			'price'	=> 'price',
			'subject' => 'subject',
			'readperm' => 'readPermission',
			'lastposter' => 'lastPoster',
			'views'	=> 'viewNum',
			'displayorder' => 'stickLevel',
			'highlight' => 'isHighlight',
			'digest' => 'digestLevel',
			'rate' => 'isRated',
			'attachment' => 'isAttached',
			'moderated' => 'isModerated',
			'closed' => 'isClosed',
			'supe_pushstatus' => 'supeSitePushStatus',
			'recommends' => 'recommendTimes',
			'recommend_add' => 'recommendSupportTimes',
			'recommend_sub' => 'recommendOpposeTimes',
			'heats' => 'heats',
			'pid' => 'pId',
		);
		$map2 = array(
			'dateline' => 'createdTime',
			'lastpost' => 'lastPostedTime',
		);
		foreach($row as $k => $v) {
			if(array_key_exists($k, $map)) {
				if($k == 'special') {
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

				if($k == 'displayorder') {
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

				if(in_array($k, array('highlight', 'rate', 'attachment', 'moderated', 'closed'))) {
					$v = $v ? true : false;
				}
				$result[$map[$k]] = $v;
			} elseif(array_key_exists($k, $map2)) {
				$result[$map2[$k]] = date('Y-m-d H:i:s', $v);
			}
		}
		$result['visitorVisible'] = true;
		return $result;
	}

	function _convertPost($row) {
		$result = array();
		$map = array(
			'pid' => 'pId',
			'tid' => 'tId',
			'fid' => 'fId',
			'authorid' => 'authorId',
			'author' => 'authorName',
			'useip'	=> 'authorIp',
			'anonymous' => 'isAnonymous',
			'subject' => 'subject',
			'message' => 'bbcodeContent',
			'htmlon' => 'isHtml',
			'attachment' => 'isAttached',
			'rate' => 'rate',
			'ratetimes' => 'rateTimes',
			'status' => 'status',
			'dateline' => 'createdTime',
			'first' => 'isThread',
		);
		$map2 = array(
			'bbcodeoff' => 'isBbcode',
			'smileyoff' => 'isSmiley',
			'parseurloff' => 'isParseUrl',
		);
		foreach($row as $k => $v) {
			if(array_key_exists($k, $map)) {
				if($k == 'dateline') {
					$result[$map[$k]] = date('Y-m-d H:i:s', $v);
					continue;
				}
				if($k == 'status') {
					if($v & 1) {
						$v = 'banned';
					} elseif(($v &2) >> 1) {
						$v = 'warned';
					} else {
						$v = 'normal';
					}
				}

				if (in_array($k, array('htmlon', 'attachment', 'first', 'anonymous'))) {
				    $v = $v ? true : false;
				}

				$result[$map[$k]] = $v;
			} elseif (array_key_exists($k, $map2)) {
				$result[$map2[$k]] = $v ? false : true;
			}
		}

		$result['htmlContent'] = $this->_parseBbcode($result['bbcodeContent'], $row['fid'], $row['pid'], $attachImages);
		$result['attachImages'] = $attachImages;

		return $result;
	}

	function _convertUser($row) {
		$result = array();
		$map = array(
			'uid' => 'uId',
			'email'	=> 'email',
			'username' => 'username',
			'conisqzoneavatar' => 'isQzoneAvatar',
			'showemail' => 'showEmail',
			'gender' => 'sex',
			'birthyear' => 'birthyear',
			'birthmonth' => 'birthmonth',
			'birthday' => 'birthday',
			'conisregister' => 'registerType',
			'conuin' => 'uin'
		);
		foreach($row as $k => $v) {
			if(array_key_exists($k, $map)) {
				if($k == 'gender') {
					switch ($v) {
						case 1:
							$v = 'male';
							break;
						case 2:
							$v = 'female';
							break;
						default:
							$v = 'unknown';
					}
				} elseif($k == 'conisregister') {
					$v = $v ? 'register' : 'bind';
				} elseif(in_array($k, array('conisqzoneavatar', 'showemail'))) {
					$v = $v ? true : false;
				}

				$result[$map[$k]] = $v;
			}
		}
		$result['birthday'] = sprintf('%04d', $result['birthyear']).'-'.sprintf('%02d', $result['birthmonth']).'-'.sprintf('%02d', $result['birthday']);
		unset($result['birthyear'], $result['birthmonth']);
		return $result;
	}

	function _convertForum($row) {
		$result = array();
		$map = array(
			'fid' => 'fId',
			'fup' => 'pId',
			'type' => 'type',
			'name' => 'fName',
			'status' => 'status',
			'displayorder' => 'displayOrder',
			'allowsmilies' => 'allowSmilies',
			'allowhtml' => 'allowHtml',
			'allowbbcode' => 'allowBbcode',
			'allowimgcode' => 'allowImageCode',
			'allowmediacode' => 'allowMediaCode',
			'password' => 'password'
		);
		$map2 = array('formulaperm');
		foreach($row as $k => $v) {
			if(array_key_exists($k, $map)) {
				if($k == 'status') {
					switch($v) {
						case 0:
							$v = 'hide';
							break;
						case 1:
							$v = 'display';
							break;
						case 2:
							$v = 'displayPart';
							break;
					}
				}
				if(in_array($k, array('allowsmilies', 'allowhtml', 'allowbbcode', 'allowimgcode', 'allowmediacode'))) {
					$v = $v ? true : false;
				}
				$result[$map[$k]] = $v;
			} elseif(in_array($k, $map2)) {
				if($k == 'formulaperm') {
					if($v && $perm = unserialize($v)) {
						$onlyViewUsers = str_replace(array("\r\n", "\r"), array("\n", "\n"), $perm['users']);
						$onlyViewUsers = explode("\n", trim($onlyViewUsers));
						$onlyViewUsers = array_filter($onlyViewUsers);
						$result['onlyViewUsers'] = $onlyViewUsers;
						$result['onlyViewMedals'] = $perm['medal'];
						$result['forumExp'] = $perm['0'];
					}
				}
			}
		}
		return $result;
	}

	function _parseBbcode($bbcode, $fId, $pId, $isHtml, &$attachImages = array()) {
		include_once libfile('function/discuzcode');

		$result = preg_replace('/\[hide(=\d+)?\].*?\[\/hide\](\r\n|\n|\r)/i', '', $bbcode);
		$result = preg_replace('/\[payto(=\d+)?\].+?\[\/payto\](\r\n|\n|\r)/i', '', $result);
		$result = discuzcode($result, 0, 0, $isHtml, 1, 2, 1, 0, 0, 0, 0, 1, 0);
		$result = preg_replace('/<img src="images\//i', "<img src=\"".X_BOARDURL."/images/", $result);

		$result = $this->_parseAttach($result, $fId, $pId, $attachImages);
		return $result;
	}

	function _parseAttach($content, $fId, $pId, &$attachImages = array()) {
		global $_G;

		$permissions = $this->_getUserGroupPermissions(array(7));
		$visitorPermission = $permissions[7];

		$attachNames = array();
		$query = DB::query("SELECT aid, filename, isimage, readperm, price FROM ".DB::table(getattachtablebypid($pId))." WHERE pid='$pId'");
		while($attach = DB::fetch($query)) {
			if($attach['price'] > 0
				|| $attach['readperm'] > $visitorPermission['readPermission']
				|| in_array($fId, $visitorPermission['forbidViewAttachForumIds'])) {
				continue;
			}

			$attachNames[$attach['aid']] = $attach['filename'];
			if($attach['isimage']) {
				$imageURL = X_BOARDURL . '/attachment.php?aid=' . aidencode($attach['aid']);
				$attachImages[] = $imageURL;
			}
		}
		$content = preg_replace('/\[attach\](\d+)\[\/attach\]/ie', '$this->_parseAttachTag(\\1, $attachNames)', $content);
		return $content;
	}

	function _parseAttachTag($attachId, $attachNames) {
		include_once libfile('function/discuzcode');
		if(array_key_exists($attachId, $attachNames)) {
			return '<span class="attach"><a href="'.X_BOARDURL.'/attachment.php?aid='.aidencode($attachId).'">'.$attachNames[$attachId].'</a></span>';
		}
		return '';
	}

	function _markThreadAsPublished($tIds) {
		global $_G;
		if($tIds && is_array($tIds)) {
			$existsThreadIds = array();
			$query = DB::query("SELECT tid FROM ".DB::table('connect_feedlog')." WHERE tid IN (".dimplode($tIds).")");
			while($row = DB::fetch($query)) {
				$existsThreadIds[] = $row['tid'];
			}
			$deletedThreadIds = array_diff($tIds, $existsThreadIds);
			if($existsThreadIds) {
				DB::query("UPDATE ".DB::table('connect_feedlog')." SET status = 3 WHERE tid IN (".dimplode($existsThreadIds).") AND status != 4");
			}
			if($deletedThreadIds) {
				DB::query("UPDATE ".DB::table('connect_feedlog')." SET status = 4 WHERE tid IN (".dimplode($deletedThreadIds).")");
			}
		}
		return true;
	}

	function _getUserGroupPermissions($userGroupIds) {
		global $_G;

		$fields = array(
			'groupid' => 'userGroupId',
                        'grouptitle' => 'userGroupName',
                        'readaccess' => 'readPermission',
                        'allowvisit' => 'allowVisit',
		);
		$userGroups = array();
		$query = DB::query("SELECT f.*,ff.* FROM ".DB::table('common_usergroup')." f
			LEFT JOIN ".DB::table('common_usergroup_field')." ff USING(groupid)
			WHERE f.groupid IN (".dimplode($userGroupIds).")");
		while($row = DB::fetch($query)) {
			foreach($row as $k => $v) {
				if(array_key_exists($k, $fields)) {
					$userGroups[$row['groupid']][$fields[$k]] = $v;
				}
				$userGroups[$row['groupid']]['forbidForumIds'] = array();
				$userGroups[$row['groupid']]['allowForumIds'] = array();
				$userGroups[$row['groupid']]['specifyAllowForumIds'] = array();
				$userGroups[$row['groupid']]['allowViewAttachForumIds'] = array();
				$userGroups[$row['groupid']]['forbidViewAttachForumIds'] = array();
			}
		}

		$query = DB::query("SELECT ff.* FROM ".DB::table('forum_forum')." f
			INNER JOIN ".DB::table('forum_forumfield')." ff USING(fid) WHERE f.status='1'");
		while($row = DB::fetch($query)) {
			$allowViewGroupIds = array();
			if($row['viewperm']) {
				$allowViewGroupIds = explode("\t", $row['viewperm']);
			}
			$allowViewAttachGroupIds = array();
			if($row['getattachperm']) {
				$allowViewAttachGroupIds = explode("\t", $row['getattachperm']);
			}
			foreach($userGroups as $gid => $_v) {
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
				} elseif(!in_array($gid, $allowViewGroupIds)) {
					$userGroups[$gid]['forbidForumIds'][] = $row['fid'];
				} elseif(in_array($gid, $allowViewGroupIds)) {
					$userGroups[$gid]['allowForumIds'][] = $row['fid'];
					$userGroups[$gid]['specifyAllowForumIds'][] = $row['fid'];
				}
				if(!$allowViewAttachGroupIds) {
					$userGroups[$gid]['allowViewAttachForumIds'][] = $row['fid'];
				} elseif(!in_array($gid, $allowViewAttachGroupIds)) {
					$userGroups[$gid]['forbidViewAttachForumIds'][] = $row['fid'];
				} elseif(in_array($gid, $allowViewGroupIds)) {
					$userGroups[$gid]['allowViewAttachForumIds'][] = $row['fid'];
				}
			}
		}
		return $userGroups;
	}
}

$connect = new connect($_G['setting']['connectsiteid'], $_G['setting']['connectsitekey'], $_G['setting']['timeoffset'], $_G['setting']['version'], $_G['config']['db'][1]['dbcharset'], X_LANGUAGE);
$connect->run();