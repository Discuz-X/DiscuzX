<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_cpanel.php 13454 2010-07-27 06:56:19Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class AdminSession {

	var $uid = 0;
	var $panel = 0;
	var $inadmincp = false;
	var $isfounder = false;
	var $cpaccess = 0;
	var $checkip = 1;
	var $logfile = 'cplog';
	var $timelimit;
	var $errorcount = 0;
	var $storage = array();
	var $db = null;
	var $tablepre = '';

	function adminsession($uid, $groupid, $adminid, $ip) {
		global $_G;

		$this->panel = defined('IN_ADMINCP') ? 1 : (defined('IN_MODCP') ? 2 : -1);

		$this->inadmincp = defined('IN_ADMINCP');
		$this->uid = $uid;
		$this->timelimit = time() - 1800;
		$this->db = &$db;
		$this->tablepre = &$tablepre;
		if($uid < 1 || $adminid < 1 || ($this->inadmincp && $adminid != 1)) {
			$cpaccess = 0;
		}elseif($this->inadmincp && $_G['setting']['adminipaccess'] && !ipaccess($ip, $_G['setting']['adminipaccess'])) {
			$cpaccess = 2;
		} else {
			$session = $this->_loadsession($uid, $ip, $_G['config']['admincp']['checkip']);
			$this->errorcount = $session['errorcount'];
			$this->storage = $session['storage'];
			if(empty($session)) {
				$this->creatsession($uid, $adminid, $ip);
				$cpaccess = 1;
			} elseif($session['errorcount'] == -1) {
				$this->update();
				$cpaccess = 3;
			} elseif($session['errorcount'] <= 3) {
				$cpaccess = 1;
			} else {
				$cpaccess = -1;
			}
		}

		if($cpaccess == 0) {
			showmessage('admin_cpanel_noaccess', 'member.php?mod=logging&action=login');
		} elseif($cpaccess == 2) {
			showmessage('admin_cpanel_noaccess_ip', NULL);
		} elseif($cpaccess == -1) {
			showmessage('admin_cpanel_locked', NULL);
		}

		$this->cpaccess = $cpaccess;

	}

	function _loadsession($uid, $ip, $checkip = 1) {
		global $_G;
		$session = array();

		return DB::fetch_first("SELECT uid, adminid, panel, ip, dateline, errorcount, storage FROM ".DB::table('common_adminsession')."
			WHERE uid='$uid' ".($checkip ? "AND ip='$ip'" : '')." AND panel='{$this->panel}' AND dateline>'{$this->timelimit}'", 'SILENT');
	}

	function creatsession($uid, $adminid, $ip) {
		$url_forward = !empty($_SERVER['QUERY_STRING']) ? addslashes($_SERVER['QUERY_STRING']) : '';
		$this->destroy($uid);
		$data = array(
			'uid' => $uid,
			'adminid' => $adminid,
			'panel' => $this->panel,
			'ip' => $ip,
			'dateline' => time(),
			'errorcount' => 0,
		);
		DB::insert('common_adminsession', $data);
		$this->set('url_forward', $url_forward, true);
	}

	function destroy($uid = 0) {
		empty($uid) && $uid = $this->uid;
		DB::query("DELETE FROM ".DB::table('common_adminsession')." WHERE (uid='$uid' AND panel='$this->panel') OR dateline<'$this->timelimit'");
	}

	function _loadstorage() {
		$storage = DB::result_first("SELECT storage FROM ".DB::table('common_adminsession')." WHERE uid='{$this->uid}' AND panel='$this->panel'");
		if(!empty($storage)) {
			$this->storage = unserialize(base64_decode($storage));
		} else {
			$this->storage = array();
		}
	}

	function isfounder($user = '') {
		global $_G;
		$user = empty($user) ? array('uid' => $_G['uid'], 'adminid' => $_G['adminid'], 'username' => $_G['member']['username']) : $user;
		$founders = str_replace(' ', '', $GLOBALS['forumfounders']);
		if($user['adminid'] <> 1) {
			return FALSE;
		} elseif(empty($founders)) {
			return TRUE;
		} elseif(strexists(",$founders,", ",$user[uid],")) {
			return TRUE;
		} elseif(!is_numeric($user['username']) && strexists(",$founders,", ",$user[username],")) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	function set($varname, $value, $updatedb = false) {
		$this->storage[$varname] = $value;
		$updatedb && $this->update();
	}

	function get($varname, $fromdb = false) {
		$return = null;
		$fromdb && $this->_loadstorage();
		if(isset($this->storage[$varname])) {
			$return = $this->storage[$varname];
		}
		return $return;
	}

	function clear($updatedb = false) {
		$this->storage = array();
		$updatedb && $this->update();
	}

	function update() {
		if($this->uid) {
			$timestamp = time();
			$storage = !empty($this->storage) ? base64_encode((serialize($this->storage))) : '';
			DB::query("UPDATE ".DB::table('common_adminsession')." SET dateline='$timestamp', errorcount='{$this->errorcount}', storage='{$storage}'
				WHERE uid='{$this->uid}' AND panel='$this->panel'", 'UNBUFFERED');
		}
	}
}
?>