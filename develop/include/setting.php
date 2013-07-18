<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: setting.php 30685 2012-06-12 03:31:31Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$varTypes = array('number', 'text', 'radio', 'textarea', 'select', 'selects', 'color', 'date', 'datetime', 'forum', 'forums', 'group', 'groups', 'extcredit',
			'forum_text', 'forum_textarea', 'forum_radio', 'forum_select', 'group_text', 'group_textarea', 'group_radio', 'group_select');
$editvar = false;

$variables = array();
$pluginvarid = intval($_GET['pluginvarid']);
//取出所有添加的变量值
if($pluginvarid && $plugin['pluginid']) {
	$pluginvar = C::t('common_plugin')->fetch_by_pluginvarid($plugin['pluginid'], $pluginvarid);
	if(!$pluginvar) {
		devmessage('插件变量未找到');
	}
	$editvar = true;
} else if($plugin['pluginid']) {
	foreach(C::t('common_pluginvar')->fetch_all_by_pluginid($plugin['pluginid']) as $var) {
		$var['type'] = $devlang['plugins_edit_vars_type_'. $var['type']];
		$var['title'] .= isset($lang[$var['title']]) ? '<br />'.$lang[$var['title']] : '';
		$variables[$var['pluginvarid']] = $var;
	}
}

if(!submitcheck('pluginsubmit')) {
	
} else {
	
	
	//编辑变量设置
	if($editvar) {
		$titlenew	= cutstr(trim($_GET['titlenew']), 25);
		$descriptionnew	= cutstr(trim($_GET['descriptionnew']), 255);
		$variablenew	= trim($_GET['variablenew']);
		$extranew	= trim($_GET['extranew']);
		
		if(!$titlenew) {
			devmessage('您没有输入配置名称');
		} elseif($variablenew != $pluginvar['variable']) {
			require_once libfile('function/admincp');
			if(!$variablenew || strlen($variablenew) > 40 || !ispluginkey($variablenew) || C::t('common_pluginvar')->check_variable($plugin['pluginid'], $variablenew)) {
				devmessage('plugins_edit_vars_invalid');
			}
		}
		
		C::t('common_pluginvar')->update_by_pluginvarid($plugin['pluginid'], $pluginvarid, array(
			'title' => $titlenew,
			'description' => $descriptionnew,
			'type' => in_array($_GET['vartype'], $varTypes) ? $_GET['vartype'] : 'text',
			'variable' => $variablenew,
			'extra' => $extranew
			));
		
		
	} else {
		if($_GET['delete']) {
			C::t('common_pluginvar')->delete($_GET['delete']);
		}

		if(is_array($_GET['displayordernew'])) {
			foreach($_GET['displayordernew'] as $id => $displayorder) {
				C::t('common_pluginvar')->update($id, array('displayorder' => $displayorder));
			}
		}
		$data = array();
		require_once libfile('function/admincp');
		foreach($_GET['newtitle'] as $key => $newtitle) {
			$newtitle = dhtmlspecialchars(trim($newtitle));
			$newvariable = trim($_GET['newvariable'][$key]);
			if($newtitle && $newvariable) {
				if(strlen($newvariable) > 40 || !ispluginkey($newvariable) || C::t('common_pluginvar')->check_variable($plugin['pluginid'], $newvariable)) {
					devmessage($devlang['plugins_edit_var_invalid'], '', 'error');
				}
				$data = array(
					'pluginid' => $plugin['pluginid'],
					'displayorder' => intval($_GET['newdisplayorder'][$key]),
					'title' => $newtitle,
					'variable' => $newvariable,
					'type' => in_array($_GET['newtype'][$key], $varTypes) ? $_GET['newtype'][$key] : 'text',
				);
				C::t('common_pluginvar')->insert($data);
			}
		}
		
		
	}
	require_once libfile('function/cache');
	updatecache(array('plugin', 'setting', 'styles'));
	cleartemplatecache();
	if($action == 'edit') {
		devmessage($devlang['plugins_edit_vars_succeed'], "develop.php?mod=plugin&action=$action&operation=setting&pluginid=$plugin[pluginid]", 'succeed');
	} else {
		dheader("location:develop.php?mod=plugins&action=$action&operation=language&pluginid=$pluginid");
		//devmessage($devlang['plugins_edit_vars_succeed'], "develop.php?mod=plugin&action=$action&operation=language&pluginid=$plugin[pluginid]", 'succeed');
	}
}

function getVarTypeList($name, $value, $extflag = false) {
	global $devlang, $varTypes;
	
	$extstr = $extflag ? 'onchange="if(this.value.indexOf(\'select\') != -1) $(\'extra\').style.display=\'\'; else $(\'extra\').style.display=\'none\';"' : '';
	$typeselect = '<select name="'.$name.'" '.$extstr.'>';
	foreach($varTypes as $type) {
		$typeselect .= '<option value="'.$type.'" '.($value == $type ? 'selected' : '').'>'.$devlang['plugins_edit_vars_type_'.$type].'</option>';
	}
	$typeselect .= '</select>';
	return $typeselect;
}
?>