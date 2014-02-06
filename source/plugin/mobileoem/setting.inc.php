<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: setting.inc.php 34241 2013-11-21 08:34:48Z nemohou $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$mobileoemtpldir = '';
$pqroot = preg_quote(DISCUZ_ROOT, '/');
$tplnames = $oemtpls = $setarray = array();

foreach(C::t('common_style')->fetch_all_data(true) as $_style) {
	$tplnames[realpath(DISCUZ_ROOT.$_style['directory'])] = $_style['tplname'];
}
foreach(C::t('common_plugin')->fetch_all_data(true) as $_plugin) {
	$_realpath = realpath(DISCUZ_ROOT.'./source/plugin/'.$_plugin['directory'].'/template');
	if($_realpath) {
		if($_plugin['identifier'] != 'mobileoem') {
			$tplnames[$_realpath] = $_plugin['name'];
		} else {
			$tplnames = array($_realpath => $_plugin['name']) + $tplnames;
		}
	}
}

foreach($tplnames as $tpldir => $name) {
	$_oemdir = $tpldir.'/mobileoem';
	if(is_dir($_oemdir)) {
		$_key = substr(md5($tpldir),10, 9);
		$_cleardir = preg_replace('/^'.$pqroot.'/', '', $tpldir);
		$oemtpls[$_key] = $_cleardir;
		$setarray[] = array($_key, $name);
		if($_cleardir == $_G['setting']['mobileoemtpldir']) {
			$mobileoemtpldir = $_key;
		}
	}
}

if(!submitcheck('settingsubmit')) {
	showformheader('plugins&operation=config&do='.$pluginid.'&identifier=mobileoem&pmod=setting', 'settingsubmit');
	showtableheader();
	showsetting(lang('plugin/mobileoem', 'default_tpl'), array('defaulttpl', $setarray), $mobileoemtpldir, 'select', 0, 0, lang('plugin/mobileoem', 'default_tpl_comment', array('ADMINSCRIPT' => ADMINSCRIPT)));
	showsubmit('settingsubmit');
	showtablefooter();
	showformfooter();
} else {
	if(!isset($oemtpls[$_GET['defaulttpl']])) {
		cpmsg('mobileoem:tpl_nofound', '', 'error');
	}
	$settings = array('mobileoemtpldir' => $oemtpls[$_GET['defaulttpl']]);
	C::t('common_setting')->update_batch($settings);
	updatecache('setting');
	cpmsg('mobileoem:tpl_updated', 'action=plugins&operation=config&do='.$pluginid.'&identifier=mobileoem&pmod=setting', 'succeed');
}

?>