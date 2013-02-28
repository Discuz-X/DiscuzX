<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: language.php 30640 2012-06-07 09:12:45Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$dir = DISCUZ_ROOT.'/data/plugindata/';
$filename = $plugin['identifier'].'.lang.php';
if(file_exists($dir.$filename)) {
	require_once $dir.$filename;
}
if(!submitcheck('pluginsubmit')) {
	if(empty($templatelang[$plugin['identifier']])) {
		$templatelang[$plugin['identifier']] = array('' => '');
	}
	if(empty($scriptlang[$plugin['identifier']])) {
		$scriptlang[$plugin['identifier']] = array('' => '');
	}
	if(empty($installlang[$plugin['identifier']])) {
		$installlang[$plugin['identifier']] = array('' => '');
	}
	$tpllang = '{lang 唯一标识符:语言包变量名称}';
} else {
	foreach($_GET['scriptlangnew_key'] as $key => $val) {
		if($_GET['scriptlangnew_val'][$key]) {
			$scriptlangnew[$val] = $_GET['scriptlangnew_val'][$key];
		}
	}
	foreach($_GET['languagenew_key'] as $key => $val) {
		if($_GET['languagenew_val'][$key]) {
			$language[$val] = $_GET['languagenew_val'][$key];
		}
	}
	foreach($_GET['installlangnew_key'] as $key => $val) {
		if($_GET['installlangnew_val'][$key]) {
			$installlangnew[$val] = $_GET['installlangnew_val'][$key];
		}
	}
	require_once libfile('function/cache');
	if(!is_dir($dir)) {
		dmkdir($dir, 0777);
	}
	$cachedata = "\$scriptlang['$plugin[identifier]'] = ".arrayeval($scriptlangnew).";\n\n";
	$cachedata .= "\$templatelang['$plugin[identifier]'] = ".arrayeval($language).";\n\n";
	$cachedata .= "\$installlang['$plugin[identifier]'] = ".arrayeval($installlangnew).";\n";
	if($fp = @fopen($dir.$filename, 'wb')) {
		fwrite($fp, "<?php\n//Discuz! cache file, DO NOT modify me!\n\n$cachedata?>");
		fclose($fp);
	} else {
		exit('Can not write to cache files, please check directory ./data/ and ./data/sysdata/ .');
	}
	if($action == 'edit') {
		devmessage('提交成功', "develop.php?mod=plugin&action=$action&operation=language&pluginid=$pluginid", 'succeed');
	} else {
		dheader("location:develop.php?mod=plugins&action=$action&operation=style&pluginid=$pluginid");
		//devmessage('提交成功', "develop.php?mod=plugin&action=$action&operation=style&pluginid=$pluginid", 'succeed');
	}
}
include template('header', 0, 'develop/template/common');
include template('language', 0, 'develop/template');
include template('footer', 0, 'develop/template/common');
exit();
?>