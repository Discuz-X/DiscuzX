<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: magic_sofa.php 21792 2011-04-12 08:42:07Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class magic_sofa {

	var $version = '1.0';
	var $name = 'sofa_name';
	var $description = 'sofa_desc';
	var $price = '10';
	var $weight = '10';
	var $targetgroupperm = true;
	var $copyright = '<a href="http://www.comsenz.com" target="_blank">Comsenz Inc.</a>';
	var $magic = array();
	var $parameters = array();

	function getsetting(&$magic) {
		global $_G;
		$settings = array(
			'fids' => array(
				'title' => 'sofa_forum',
				'type' => 'mselect',
				'value' => array(),
			),
		);
		loadcache('forums');
		$settings['fids']['value'][] = array(0, '&nbsp;');
		if(empty($_G['cache']['forums'])) $_G['cache']['forums'] = array();
		foreach($_G['cache']['forums'] as $fid => $forum) {
			$settings['fids']['value'][] = array($fid, ($forum['type'] == 'forum' ? str_repeat('&nbsp;', 4) : ($forum['type'] == 'sub' ? str_repeat('&nbsp;', 8) : '')).$forum['name']);
		}
		$magic['fids'] = explode("\t", $magic['forum']);

		return $settings;
	}

	function setsetting(&$magicnew, &$parameters) {
		global $_G;
		$magicnew['forum'] = is_array($parameters['fids']) && !empty($parameters['fids']) ? implode("\t",$parameters['fids']) : '';
	}

	function usesubmit() {
		global $_G;
		if(empty($_G['gp_tid'])) {
			showmessage(lang('magic/sofa', 'sofa_info_nonexistence'));
		}

		$thread = getpostinfo($_G['gp_tid'], 'tid', array('fid', 'authorid', 'dateline', 'subject'));
		$this->_check($thread);

		$firstsofa = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_threadmod')." WHERE magicid='".$this->magic['magicid']."' AND tid='$_G[gp_tid]'");
		if($firstsofa >= 1) {
			showmessage(lang('magic/sofa', 'sofa_info_sofaexistence'), '', array(), array('login' => 1));
		}

		$sofamessage = lang('magic/sofa', 'sofa_text', array('actor' => $_G['member']['username'], 'time' => dgmdate(TIMESTAMP), 'magicname' => $this->magic['name']));
		$dateline = $thread['dateline'] + 1;
		require_once libfile('function/forum');

		insertpost(array(
			'fid' => $thread['fid'],
			'tid' => $_G['gp_tid'],
			'first' => '0',
			'author' => $_G['username'],
			'authorid' => $_G['uid'],
			'dateline' => $dateline,
			'message' => $sofamessage,
			'useip' => $_G['clientip'],
			'usesig' => '1',
		));

		DB::query("UPDATE ".DB::table('forum_thread')." SET replies=replies+1, moderated='1' WHERE tid='$_G[gp_tid]'", 'UNBUFFERED');
		DB::query("UPDATE ".DB::table('forum_forum')." SET posts=posts+1, todayposts=todayposts+1 WHERE fid='$post[fid]'", 'UNBUFFERED');

		usemagic($this->magic['magicid'], $this->magic['num']);
		updatemagiclog($this->magic['magicid'], '2', '1', '0', 0, 'tid', $_G['gp_tid']);
		updatemagicthreadlog($_G['gp_tid'], $this->magic['magicid']);

		if($thread['authorid'] != $_G['uid']) {
			notification_add($thread['authorid'], 'magic', lang('magic/sofa', 'sofa_notification'), array('tid' => $_G['gp_tid'], 'subject' => $thread['subject'], 'magicname' => $this->magic['name']));
		}

		showmessage(lang('magic/sofa', 'sofa_succeed'), dreferer(), array(), array('showdialog' => 1, 'locationtime' => true));
	}

	function show() {
		global $_G;
		$tid = !empty($_G['gp_id']) ? htmlspecialchars($_G['gp_id']) : '';
		if($tid) {
			$thread = getpostinfo($_G['gp_id'], 'tid', array('fid', 'authorid'));
			$this->_check($thread);
		}
		$this->parameters['expiration'] = $this->parameters['expiration'] ? intval($this->parameters['expiration']) : 24;
		magicshowtype('top');
		magicshowsetting(lang('magic/sofa', 'sofa_info', array('expiration' => $this->parameters['expiration'])), 'tid', $tid, 'text');
		magicshowtype('bottom');
	}

	function buy() {
		global $_G;
		if(!empty($_G['gp_id'])) {
			$thread = getpostinfo($_G['gp_id'], 'tid', array('fid', 'authorid'));
			$this->_check($thread);
		}
	}

	function _check($thread) {
		if(!checkmagicperm($this->parameters['forum'], $thread['fid'])) {
			showmessage(lang('magic/sofa', 'sofa_info_noperm'));
		}
		$member = getuserbyuid($thread['authorid']);
		if(!checkmagicperm($this->parameters['targetgroups'], $member['groupid'])) {
			showmessage(lang('magic/sofa', 'sofa_info_user_noperm'));
		}
	}

}

?>