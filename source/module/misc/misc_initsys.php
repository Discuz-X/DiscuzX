<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_initsys.php 32883 2013-03-20 02:49:01Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!($_G['adminid'] == 1 && $_GET['formhash'] == formhash()) && $_G['setting']) {
	exit('Access Denied');
}

require_once libfile('function/cache');
updatecache();

require_once libfile('function/block');
blockclass_cache();

if($_G['config']['output']['tplrefresh']) {
	cleartemplatecache();
}

$plugins = array('qqconnect', 'cloudstat', 'soso_smilies', 'cloudsearch', 'qqgroup', 'security', 'xf_storage', 'mobile', 'pcmgr_url_safeguard');
$opens = array('mobile', 'pcmgr_url_safeguard');
$allowcloses = array('pcmgr_url_safeguard');

$cloudapps = array('qqconnect' => 'connect', 'cloudstat' => 'stats', 'soso_smilies' => 'smilies', 'cloudsearch' => 'search', 'qqgroup' => 'qqgroup', 'security' => 'security');

$apps = C::t('common_setting')->fetch('cloud_apps', true);
if (!$apps) {
	$apps = array();
}

if (!is_array($apps)) {
	$apps = dunserialize($apps);
}

unset($apps[0]);

if($apps) {
	foreach($cloudapps as $key => $appname) {
		if($apps[$appname]['status'] == 'normal') {
			$opens[] = $key;
		}
	}
}

require_once libfile('function/plugin');
require_once libfile('function/admincp');

foreach($plugins as $pluginid) {
	$importfile = DISCUZ_ROOT.'./source/plugin/'.$pluginid.'/discuz_plugin_'.$pluginid.'.xml';
	if(!file_exists($importfile)) {
		continue;
	}
	$systemvalue = in_array($pluginid, $allowcloses) ? 1 : 2;
	$importtxt = @implode('', file($importfile));
	$pluginarray = getimportdata('Discuz! Plugin', $importtxt);
	$plugin = C::t('common_plugin')->fetch_by_identifier($pluginid);
	if($plugin) {
		$modules = unserialize($plugin['modules']);
		if($modules['system'] > 0) {
			if($pluginarray['plugin']['version'] != $plugin['version']) {
				pluginupgrade($pluginarray, '');
				if($pluginarray['upgradefile']) {
					$plugindir = DISCUZ_ROOT.'./source/plugin/'.$pluginarray['plugin']['directory'];
					if(file_exists($plugindir.'/'.$pluginarray['upgradefile'])) {
						@include_once $plugindir.'/'.$pluginarray['upgradefile'];
					}
				}
			}
			if($modules['system'] != $systemvalue) {
				$modules['system'] = $systemvalue;
				$modules = serialize($modules);
				C::t('common_plugin')->update($plugin['pluginid'], array('modules' => $modules));
			}
			continue;
		}
		C::t('common_plugin')->delete_by_identifier($pluginid);
	}

	if($plugin['available']) {
		$opens[] = $pluginid;
	}

	$pluginarray['plugin']['modules'] = unserialize(dstripslashes($pluginarray['plugin']['modules']));
	$pluginarray['plugin']['modules']['system'] = $systemvalue;
	$pluginarray['plugin']['modules'] = serialize($pluginarray['plugin']['modules']);
	plugininstall($pluginarray, '', in_array($pluginid, $opens));

	if($pluginarray['installfile']) {
		$plugindir = DISCUZ_ROOT.'./source/plugin/'.$pluginarray['plugin']['directory'];
		if(file_exists($plugindir.'/'.$pluginarray['installfile'])) {
			@include_once $plugindir.'/'.$pluginarray['installfile'];
		}
	}
}

?>