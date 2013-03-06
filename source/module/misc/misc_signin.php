<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_signin.php 32570 2013-02-21 08:05:57Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if($_GET['formhash'] != FORMHASH) {
	showmessage('undefined_action', dreferer(), array(), array('showdialog'=>1, 'closetime' => true, 'msgtype' => 2));
}

$lang = 'to_login';
if($_G['uid']) {
	$result = updatecreditbyaction('daylogin', $_G['uid']);
	if($result['updatecredit']) {
		showmessage('login_reward_succeed', dreferer(), array(), array('showdialog'=>1, 'closetime' => true, 'msgtype' => 2, 'extrajs' => '<script type="text/javascript" reload="1">$(\'ntcmsg\').style.display=\'none\';showCreditPrompt()</script>'));
	} else {
		showmessage('login_reward_error', dreferer(), array(), array('showdialog'=>1, 'closetime' => true, 'msgtype' => 2));
	}
}

?>