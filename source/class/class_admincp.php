<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: class_admincp.php 21498 2011-03-29 04:45:05Z monkey $
 */

class discuz_admincp
{
	var $core = null;
	var $script = null;

	var $userlogin = false;
	var $adminsession = array();
	var $adminuser = array();
	var $perms = null;

	var $panel = 1;

	var $isfounder = false;

	var $cpsetting = array();

	var $cpaccess = 0;

	var $sessionlife = 1800;
	var $sessionlimit = 0;

	function &instance() {
		static $object;
		if(empty($object)) {
			$object = new discuz_admincp();
		}
		return $object;
	}

	function discuz_admincp() {

	}

	function init() {

		if(empty($this->core) || !is_object($this->core)) {
			exit('No Discuz core found');
		}

		$this->cpsetting = $this->core->config['admincp'];
		$this->adminuser = & $this->core->var['member'];

		$this->isfounder = $this->checkfounder($this->adminuser);

		$this->sessionlimit = TIMESTAMP - $this->sessionlife;

		$this->check_cpaccess();

		$this->writecplog();
	}

	function writecplog() {
		global $_G;
		$extralog = implodearray(array('GET' => $_GET, 'POST' => $_POST), array('formhash', 'submit', 'addsubmit', 'admin_password', 'sid', 'action'));
		writelog('cplog', implode("\t", clearlogstring(array($_G['timestamp'], $_G['username'], $_G['adminid'], $_G['clientip'], getgpc('action'), $extralog))));
	}

	function check_cpaccess() {

		global $_G;
		$session = array();

		if(!$this->adminuser['uid']) {
			$this->cpaccess = 0;
		} else {

			if(!$this->isfounder) {
				$session = DB::fetch_first("SELECT m.cpgroupid,  m.customperm, s.*
					FROM ".DB::table('common_admincp_member')." m
					LEFT JOIN ".DB::table('common_admincp_session')." s ON(s.uid=m.uid AND s.panel={$this->panel})
					WHERE m.uid='{$this->adminuser['uid']}'");
			} else {
				$session = DB::fetch_first("SELECT * FROM ".DB::table('common_admincp_session')."
					WHERE uid='{$this->adminuser['uid']}' AND panel={$this->panel}");
			}

			if(empty($session)) {
				$this->cpaccess = $this->isfounder ? 1 : -2;

			} elseif($_G['setting']['adminipaccess'] && !ipaccess($_G['clientip'], $_G['setting']['adminipaccess'])) {
				$this->do_user_login();

			} elseif ($session && empty($session['uid'])) {
				$this->cpaccess = 1;

			} elseif ($session['dateline'] < $this->sessionlimit) {
				$this->cpaccess = 1;

			} elseif ($this->cpsetting['checkip'] && ($session['ip'] != $this->core->var['clientip'])) {
				$this->cpaccess = 1;

			} elseif ($session['errorcount'] >= 0 && $session['errorcount'] <= 3) {
				$this->cpaccess = 2;

			} elseif ($session['errorcount'] == -1) {
				$this->cpaccess = 3;

			} else {
				$this->cpaccess = -1;
			}
		}

		if($this->cpaccess == 2 || $this->cpaccess == 3) {
			if(!empty($session['customperm'])) {
				$session['customperm'] = unserialize($session['customperm']);
			}
		}

		$this->adminsession = $session;

		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['admin_password'])) {
			if($this->cpaccess == 2) {
				$this->check_admin_login();
			} elseif($this->cpaccess == 0) {
				$this->check_user_login();
			}
		}

		if($this->cpaccess == 1) {
			DB::delete('common_admincp_session', "(uid='{$this->adminuser['uid']}' AND panel='$this->panel') OR dateline<'$this->sessionlimit'");
			DB::query("INSERT INTO ".DB::table('common_admincp_session')." (uid, adminid, panel, ip, dateline, errorcount)
			VALUES ('{$this->adminuser['uid']}', '{$this->adminuser['adminid']}', '$this->panel', '{$this->core->var['clientip']}', '".TIMESTAMP."', '0')");
		} elseif ($this->cpaccess == 3) {
			$this->load_admin_perms();
			DB::update('common_admincp_session', array('dateline' => TIMESTAMP, 'ip' => $this->core->var['clientip'], 'errorcount' => -1), "uid={$this->adminuser['uid']} AND panel={$this->panel}");
		}

		if($this->cpaccess != 3) {
			$this->do_user_login();
		}

	}

	function check_admin_login() {
		global $_G;
		if((empty($_POST['admin_questionid']) || empty($_POST['admin_answer'])) && $_G['config']['admincp']['forcesecques']) {
			$this->do_user_login();
		}
		loaducenter();
		$ucresult = uc_user_login($this->adminuser['uid'], $_POST['admin_password'], 1, 1, $_POST['admin_questionid'], $_POST['admin_answer']);
		if($ucresult[0] > 0) {
			DB::update('common_admincp_session', array('dateline' => TIMESTAMP, 'ip' => $this->core->var['clientip'], 'errorcount' => -1), "uid={$this->adminuser['uid']} AND panel={$this->panel}");
			dheader('Location: '.ADMINSCRIPT.'?'.cpurl('url', array('sid')));
		} else {
			$errorcount = $this->adminsession['errorcount'] + 1;
			DB::update('common_admincp_session', array('dateline' => TIMESTAMP, 'ip' => $this->core->var['clientip'], 'errorcount' => $errorcount), "uid={$this->adminuser['uid']} AND panel={$this->panel}");
		}
	}

	function check_user_login() {
		global $_G;
		$admin_username = isset($_POST['admin_username']) ? trim($_POST['admin_username']) : '';
		if($admin_username != '') {

			require_once libfile('function/member');
			if(logincheck($_POST['admin_username'])) {
				if((empty($_POST['admin_questionid']) || empty($_POST['admin_answer'])) && $_G['config']['admincp']['forcesecques']) {
					$this->do_user_login();
				}
				$result = userlogin($_POST['admin_username'], $_POST['admin_password'], $_POST['admin_questionid'], $_POST['admin_answer']);
				if($result['status'] == 1) {
					$cpgroupid = DB::result_first("SELECT uid FROM ".DB::table('common_admincp_member')." WHERE uid='{$result['member']['uid']}'");
					if($cpgroupid || $this->checkfounder($result['member'])) {
						DB::insert('common_admincp_session', array(
							'uid' =>$result['member']['uid'],
							'adminid' =>$result['member']['adminid'],
							'panel' =>$this->panel,
							'dateline' => TIMESTAMP,
							'ip' => $this->core->var['clientip'],
							'errorcount' => -1), false, true);

						setloginstatus($result['member'], 0);
						dheader('Location: '.ADMINSCRIPT.'?'.cpurl('url', array('sid')));
					} else {
						$this->cpaccess = -2;
					}
				} else {
					loginfailed($_POST['admin_username']);
				}
			} else {
				$this->cpaccess = -4;
			}
		}
	}

	function allow($action, $operation, $do) {

		if($this->perms === null) {
			$this->load_admin_perms();
		}

		if(isset($this->perms['all'])) {
			return $this->perms['all'];
		}

		if(!empty($_POST) && !array_key_exists('_allowpost', $this->perms) && $action.'_'.$operation != 'misc_custommenu') {
			return false;
		}
		$this->perms['misc_custommenu'] = 1;

		$key = $action;
		if(isset($this->perms[$key])) {
			return $this->perms[$key];
		}
		$key = $action.'_'.$operation;
		if(isset($this->perms[$key])) {
			return $this->perms[$key];
		}
		$key = $action.'_'.$operation.'_'.$do;
		if(isset($this->perms[$key])) {
			return $this->perms[$key];
		}
		return false;
	}

	function load_admin_perms() {

		$this->perms = array();
		if(!$this->isfounder) {
			if($this->adminsession['cpgroupid']) {
				$query = DB::query("SELECT perm FROM ".DB::table('common_admincp_perm')." WHERE cpgroupid='{$this->adminsession['cpgroupid']}'");
				while ($perm = DB::fetch($query)) {
					if(empty($this->adminsession['customperm'])) {
						$this->perms[$perm['perm']] = true;
					} elseif(!in_array($perm['perm'], (array)$this->adminsession['customperm'])) {
						$this->perms[$perm['perm']] = true;
					}
				}
			} else {
				$this->perms['all'] = true;
			}
		} else {
			$this->perms['all'] = true;
		}
	}

	function checkfounder($user) {
		$founders = str_replace(' ', '', $this->cpsetting['founder']);
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

	function do_user_login() {
		require $this->admincpfile('login');
	}

	function do_admin_logout() {
		DB::delete('common_admincp_session', "(uid='{$this->adminuser['uid']}' AND panel='$this->panel') OR dateline<'$this->sessionlimit'");
	}

	function admincpfile($action) {
		return './source/admincp/admincp_'.$action.'.php';
	}

	function show_admincp_main() {
		$this->do_request('main');
	}

	function show_no_access() {
		cpheader();
		cpmsg('action_noaccess', '', 'error');
		cpfooter();
	}

	function do_request($action) {

		global $_G;

		$lang = lang('admincp');
		$title = 'cplog_'.getgpc('action').(getgpc('operation') ? '_'.getgpc('operation') : '');
		$operation = getgpc('operation');
		$do = getgpc('do');
		$sid = $_G['sid'];
		$isfounder = $this->isfounder;
		if($action == 'main' || $this->allow($action, $operation, $do)) {
			require './source/admincp/admincp_'.$action.'.php';
		} else {
			cpheader();
			cpmsg('action_noaccess', '', 'error');
		}
	}
}