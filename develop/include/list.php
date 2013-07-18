<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: list.php 30643 2012-06-08 01:47:48Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
define('ADMINSCRIPT', 'admin.php');
	//取本地开发的插件列表 $develop_list
	$develop_data = DISCUZ_ROOT.'/data/develop_data.php';
	if(file_exists($develop_data)) {
		require_once $develop_data;
	} else {
		$develop_list = array();
	}
	require_once libfile('function/admincp');
	require_once libfile('function/plugin');
	loadcache('plugin');
	$outputsubmit = false;
	$plugins = $addonids = array();
	$plugins = C::t('common_plugin')->fetch_all_data();
	if(empty($_G['cookie']['addoncheck_plugin'])) {
		foreach($plugins as $plugin) {
			$addonids[$plugin['pluginid']] = $plugin['identifier'].'.plugin';
		}
		$checkresult = dunserialize(cloudaddons_upgradecheck($addonids));
		savecache('addoncheck_plugin', $checkresult);
		dsetcookie('addoncheck_plugin', 1, 3600);
	} else {
		loadcache('addoncheck_plugin');
		$checkresult = $_G['cache']['addoncheck_plugin'];
	}
	$splitavailable = $plugin_list = array();
	
	foreach($plugins as $plugin) {
		if(!in_array($plugin['identifier'], $develop_list)) {
			continue;
		}
		$addonid = $plugin['identifier'].'.plugin';
		$updateinfo = '';
		list(, $newver) = explode(':', $checkresult[$addonid]);
		if($newver) {
			$plugin['updateinfo'] = '<a href="'.ADMINSCRIPT.'?action=cloudaddons&id='.$addonid.'" title="'.$devlang['plugins_online_update'].'" target="_blank"><font color="red">'.$devlang['plugins_find_newversion'].' '.$newver.'</font></a>';
		}
		$plugins[] = $plugin['identifier'];
		$hookexists = FALSE;
		$plugin['modules'] = dunserialize($plugin['modules']);
		if((empty($_GET['system']) && $plugin['modules']['system'] && !$updateinfo || !empty($_GET['system']) && !$plugin['modules']['system'])) {
			continue;
		}
		$submenuitem = array();
		if(is_array($plugin['modules'])) {
			foreach($plugin['modules'] as $k => $module) {
				if($module['type'] == 11) {
					$hookorder = $module['displayorder'];
					$hookexists = $k;
				}
				if($module['type'] == 3) {
					$plugin['submenuitem'][] = '<a href="'.ADMINSCRIPT.'?action=plugins&operation=config&do='.$plugin['pluginid'].'&identifier='.$plugin['identifier'].'&pmod='.$module['name'].'" target="_blank">'.$module['menu'].'</a>';
				}
			}
		}
		$outputsubmit = $hookexists !== FALSE && $plugin['available'] || $outputsubmit;
		$hl = !empty($_GET['hl']) && $_GET['hl'] == $plugin['pluginid'];
		$intro = $title = '';
		$order = !$updateinfo ? intval($plugin['modules']['system']) + 1 : 0;
		if($plugin['available']) {
			if(empty($splitavailable[0])) {
				//$title = '<tr><th colspan="15" class="partition">'.$devlang['plugins_list_available'].'</th></tr>';
				$plugin['title'] = $devlang['plugins_list_available'];
				$plugin['splitavailable'][0] = 1;
			}
		} else {
			if(empty($splitavailable[1])) {
				//$title = '<tr><th colspan="15" class="partition">'.$devlang['plugins_list_unavailable'].'</th></tr>';
				$plugin['title'] = $devlang['plugins_list_unavailable'];
				$plugin['splitavailable'][1] = 1;
			}
		}
		$plugin['imgsrc'] = cloudaddons_pluginlogo_url($plugin['identifier']);
		$plugin['name'] = dhtmlspecialchars($plugin['name']);
		$plugin['version'] = dhtmlspecialchars($plugin['version']);
		$plugin['copyright'] = dhtmlspecialchars($plugin['copyright']);
		$plugin['submenuitem'] = implode(' | ', $plugin['submenuitem']);
		$plugin_list[$plugin['identifier']] = $plugin;
	}
		/*
		$pluginlist[$order][$plugin['pluginid']] = $title.showtablerow('', array('valign="top" style="width:45px"', ($plugin['available'] ? 'class="bold"' : 'class="light"').' valign="top" style="width:200px"', 'valign="bottom"', 'align="right" valign="bottom" style="width:160px"'), array(
			'<img src="'.cloudaddons_pluginlogo_url($plugin['identifier']).'" onerror="this.src=\'static/image/admincp/plugin_logo.png\';this.onerror=null" width="40" height="40" align="left" />',
			dhtmlspecialchars($plugin['name']).' '.dhtmlspecialchars($plugin['version']).'<br /><span class="sml">'.$plugin['identifier'].'</span><br />'.$updateinfo,
			($plugin['description'] ? $plugin['description'].'<br /><br />' : '').
				($plugin['copyright'] ? '<span class="light">'.$devlang['author'].': '.dhtmlspecialchars($plugin['copyright']).'</span>' : '').'<div class="psetting">'.
					'<a href="'.ADMINSCRIPT.'?action=cloudaddons&id='.$plugin['identifier'].'.plugin" target="_blank" title="'.$devlang['cloudaddons_linkto'].'">'.$devlang['view'].'</a>'.
					($plugin['modules']['extra']['intro'] ? '<a href="javascript:;" onclick="display(\'intro_'.$plugin['pluginid'].'\')">'.$devlang['plugins_home'].'</a>' : '').
					(isset($_G['cache']['plugin'][$plugin['identifier']]) ? '<a href="'.ADMINSCRIPT.'?action=plugins&operation=config&do='.$plugin['pluginid'].'">'.$devlang['config'].'</a>' : '').
					implode('', $submenuitem).'</div>',
			($hookexists !== FALSE && $plugin['available'] ? $devlang['display_order'].": <input class=\"txt num\" type=\"text\" id=\"displayorder_$plugin[pluginid]\" name=\"displayordernew[$plugin[pluginid]][$hookexists]\" value=\"$hookorder\" /><br /><br />" : '').
			($plugin['modules']['system'] != 2 ? (!$plugin['available'] ? "<a href=\"".ADMINSCRIPT."?action=plugins&operation=enable&pluginid=$plugin[pluginid]\" class=\"bold\">$devlang[enable]</a>&nbsp;&nbsp;" : "<a href=\"".ADMINSCRIPT."?action=plugins&operation=disable&pluginid=$plugin[pluginid]\">$devlang[closed]</a>&nbsp;&nbsp;") : '').
				"<a href=\"".ADMINSCRIPT."?action=plugins&operation=upgrade&pluginid=$plugin[pluginid]\">$devlang[plugins_config_upgrade]</a>&nbsp;&nbsp;".
				(!$plugin['modules']['system'] ? "<a href=\"".ADMINSCRIPT."?action=plugins&operation=delete&pluginid=$plugin[pluginid]\">$devlang[plugins_config_uninstall]</a>&nbsp;&nbsp;" : '').
				(!$plugin['modules']['system'] ? "<a href=\"develop.php?mod=plugin&action=edit&pluginid=$plugin[pluginid]\">$devlang[plugins_editlink]</a>&nbsp;&nbsp;" : ''),
		), true).($plugin['modules']['extra']['intro'] ? showtablerow('class="noborder" id="intro_'.$plugin['pluginid'].'" style="display:none"', array('', 'colspan="3"'), array('', $plugin['modules']['extra']['intro']), true) : '');
	}
	ksort($pluginlist);
	$pluginlist = (array)$pluginlist[0] + (array)$pluginlist[1] + (array)$pluginlist[2] + (array)$pluginlist[3];
	if(!empty($_GET['hl']) && isset($pluginlist[$_GET['hl']])) {
		$hl = $pluginlist[$_GET['hl']];
		unset($pluginlist[$_GET['hl']]);
		array_unshift($pluginlist, $hl);
	}
	$pluginlist = implode('', $pluginlist);
		 * 
		 */
include template('header', 0, 'develop/template/common');
include template('list', 0, 'develop/template');
include template('footer', 0, 'develop/template/common');
?>