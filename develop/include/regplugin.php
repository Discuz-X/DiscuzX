<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: regplugin.php 30638 2012-06-07 09:06:10Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
if(!submitcheck('pluginsubmit')) {

} else {
	$namenew	= dhtmlspecialchars(trim($_GET['namenew']));
	$versionnew	= strip_tags(trim($_GET['versionnew']));
	$directorynew	= dhtmlspecialchars($_GET['directorynew']);
	$identifiernew	= trim($_GET['identifiernew']);
	$descriptionnew	= dhtmlspecialchars($_GET['descriptionnew']);
	$copyrightnew	= $plugin['copyright'] ? addslashes($plugin['copyright']) : dhtmlspecialchars($_GET['copyrightnew']);
	//$adminidnew	= ($_GET['adminidnew'] > 0 && $_GET['adminidnew'] <= 3) ? $_GET['adminidnew'] : 1;
	if(!$identifiernew) {
		devmessage($devlang['plugins_edit_identifier_invalid'], '', 'error');
		
	} elseif(!$namenew) {
		devmessage($devlang['plugins_edit_name_invalid'], '', 'error');
	//} elseif(!isplugindir($directorynew)) {
		//cpmsg('plugins_edit_directory_invalid', '', 'error');
	} elseif($identifiernew != $plugin['identifier']) {
//				$query = DB::query("SELECT pluginid FROM ".DB::table('common_plugin')." WHERE identifier='$identifiernew' LIMIT 1");
		$plugin = C::t('common_plugin')->fetch_by_identifier($identifiernew);
		require_once libfile('function/admincp');
		if($plugin || !ispluginkey($identifiernew)) {
			devmessage($devlang['plugins_edit_identifier_invalid'], '', 'error');
		}
	}
	/*
	if($_GET['langexists'] && !file_exists($langfile = DISCUZ_ROOT.'./data/plugindata/'.$identifiernew.'.lang.php')) {
		cpmsg('plugins_edit_language_invalid', '', 'error', array('langfile' => $langfile));
	}
	 * 
	 */
	$plugin['modules']['extra']['langexists'] = $_GET['langexists'];
//			DB::query("UPDATE ".DB::table('common_plugin')." SET adminid='$adminidnew', version='$versionnew', name='$namenew', modules='".addslashes(serialize($plugin['modules']))."', identifier='$identifiernew', description='$descriptionnew', directory='$directorynew', copyright='$copyrightnew' WHERE pluginid='$pluginid'");
	$dzversion = $_GET['dzversionnew'] ? dhtmlspecialchars(preg_replace("/[^A-Za-z0-9_,\.]/", '', trim($_GET['dzversionnew']))) : '';
	if($action == 'edit' || $pluginid) {
		$plugin['modules']['extra']['version'] = $dzversion;
		C::t('common_plugin')->update($pluginid, array(
			'adminid' => $adminidnew,
			'version' => $versionnew,
			'name' => $namenew,
			'identifier' => $identifiernew,
			'description' => $descriptionnew,
			'copyright' => $copyrightnew,
			'modules' => serialize($plugin['modules'])
		));
		devmessage('提交成功', 'develop.php?mod=plugin&action=edit&operation=regplugin&pluginid='.$pluginid, 'succeed');
	} elseif($action == 'create') {
		$data = array(
			'name' => $namenew,
			'version' => $versionnew,
			'identifier' => $identifiernew,
			'directory' => $identifiernew.'/',
			'description' => $descriptionnew,
			'available' => 0,
			'copyright' => $copyrightnew,
		);
		$plugin['modules']['extra']['version'] = $dzversion;
		$data['modules'] = serialize($plugin['modules']);
		$pluginid = C::t('common_plugin')->insert($data, true);
		dmkdir(DISCUZ_ROOT.'source/plugin/'.$identifiernew);
		$filename = DISCUZ_ROOT.'/data/develop_data.php';
		if(file_exists($filename)) {
			require_once $filename;
		} else {
			$develop_list = array();
		}
		require_once libfile('function/cache');
		$develop_list[] = $identifiernew;
		$cachedata = "\$develop_list = ".arrayeval($develop_list).";\n\n";
		if($fp = @fopen($filename, 'wb')) {
			fwrite($fp, "<?php\n//Discuz! cache file, DO NOT modify me!\n\n$cachedata?>");
			fclose($fp);
		} else {
			exit('Can not write to cache files, please check directory ./data/ and ./data/sysdata/ .');
		}
		//updatecache(array('plugin', 'setting', 'styles'));
		//cleartemplatecache();
		dheader("location:develop.php?mod=plugins&action=create&operation=script&pluginid=$pluginid");
		//devmessage($devlang['plugins_add_succeed'], "develop.php?mod=plugins&action=create&operation=script&pluginid=$pluginid", 'succeed');
	}
}
?>