<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: script.php 30659 2012-06-11 02:10:10Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$scripttype = array('navigation', 'secqaa');
if(!defined('DISCUZ_VERSION')) {
	require_once DISCUZ_ROOT.'./source/discuz_version.php';
}
$version = floatval(substr(DISCUZ_VERSION, 1));
if($version > 2.5) {
	$scripttype = array_merge($scripttype, array('seccode', 'magic', 'cron', 'adv', 'task'));
} elseif($version > 2) {
	$scripttype = array_merge($scripttype, array('seccode'));
}
$basetype = array('general', 'special', 'mobile');
$type = in_array($_GET['type'], array_merge($scripttype, $basetype, array('system'))) ? $_GET['type'] : '';
$filename = dhtmlspecialchars(preg_replace("/[^\[A-Za-z0-9_\.\]]/", '', $_GET['filename']));
if(!submitcheck('pluginsubmit')) {
	$scripts = array();
	$scriptList = false;
	if($type && $filename && (in_array($type, $basetype) && $plugin['modules'][$type] || $plugin['modules'][$type][$filename])) {
		if(in_array($type, $basetype)) {
			$module = $plugin['modules'][$type];
			require_once DISCUZ_ROOT.'develop/include/hooklist.php';
			$hooklist = $type == 'mobile' ? $mobilehook : $generalhook;
		} else {
			
			$module = $plugin['modules'][$type][$filename];
			if($type == 'cron') {
				$days = array(-1,0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31);
				$week = array('0'=>'星期日', '1'=>'星期一', '2'=>'星期二', '3'=>'星期三', '4'=>'星期四', '5'=>'星期五', '6'=>'星期六');
				$module['weekday'] = isset($module['weekday']) ? $module['weekday'] : '-1';
				$module['day'] = isset($module['day']) ? $module['day'] : '-1';
				$module['hour'] = isset($module['hour']) ? $module['hour'] : '-1';
			} elseif($type == 'navigation') {
				$allowgroup = array('0' => '普通用户', '1' => '管理员', '2' => '超级版主', '3' => '版主');
			}
		}
	} elseif($type == 'system' && in_array($filename, array('install', 'uninstall', 'upgrade'))) {
		$extra = $plugin['modules']['extra'];
	} else {
		$scriptList = true;
		$scripts = $plugin['modules'];
	}
	$navtypes = array(
			'g1' => array(
					'1' => array('h' => '1100100', 'e' => 'inc', 'value' => '1', 'desc' => $devlang['plugins_edit_modules_type_1']),
					'5' => array('h' => '1111', 'e' => 'inc', 'value' => '5', 'desc' => $devlang['plugins_edit_modules_type_5']),
					'27' => array('h' => '1100100', 'e' => 'inc', 'value' => '27', 'desc' => $devlang['plugins_edit_modules_type_27']),
					'23' => array('h' => '1100100', 'e' => 'inc', 'value' => '23', 'desc' => $devlang['plugins_edit_modules_type_23']),
					'25' => array('h' => '1100110', 'e' => 'inc', 'value' => '25', 'desc' => $devlang['plugins_edit_modules_type_25']),
					'24' => array('h' => '1100111', 'e' => 'inc', 'value' => '24', 'desc' => $devlang['plugins_edit_modules_type_24'])
			),
			'g3' => array(
					'7' => array('h' => '1111', 'e' => 'inc', 'value' => '7', 'desc' => $devlang['plugins_edit_modules_type_7']),
					'17' => array('h' => '1111', 'e' => 'inc', 'value' => '17', 'desc' => $devlang['plugins_edit_modules_type_17']),
					'19' => array('h' => '1111', 'e' => 'inc', 'value' => '19', 'desc' => $devlang['plugins_edit_modules_type_19']),
					'14' => array('h' => '1001', 'e' => 'inc', 'value' => '14', 'desc' => $devlang['plugins_edit_modules_type_14']),
					'26' => array('h' => '1111', 'e' => 'inc', 'value' => '26', 'desc' => $devlang['plugins_edit_modules_type_26']),
					'21' => array('h' => '1111', 'e' => 'inc', 'value' => '21', 'desc' => $devlang['plugins_edit_modules_type_21']),
					'15' => array('h' => '1001', 'e' => 'inc', 'value' => '15', 'desc' => $devlang['plugins_edit_modules_type_15']),
					'16' => array('h' => '1001', 'e' => 'inc', 'value' => '16', 'desc' => $devlang['plugins_edit_modules_type_16']),
					'3' => array('h' => '1001', 'e' => 'inc', 'value' => '3', 'desc' => $devlang['plugins_edit_modules_type_3'])
			)
	);
} else {
	require_once libfile('function/admincp');
	require_once libfile('function/plugin');
	$_GET['description'] = dhtmlspecialchars($_GET['description']);
	if(submitcheck('addhook')) {
		
		require_once DISCUZ_ROOT.'develop/include/hooklist.php';
		$sort = dhtmlspecialchars(preg_replace("/[^\[A-Za-z0-9_\]]/", '', $_GET['sort']));
		$page = dhtmlspecialchars(preg_replace("/[^\[A-Za-z0-9_\.\]]/", '', $_GET['page']));
		$hooklist = $type == 'mobile' ? $mobilehook : $generalhook;
		if(in_array($type, $basetype)) {
			//初始化钩子
			$plugin['modules'][$type]['hooks'] = array();
			foreach($_GET['hooks'] as $skey => $value) {
				$sorts = $hooklist[$skey];
				if($sorts) {
					foreach($value as $fkey => $hval) {
						if($sorts[$fkey]) {
							foreach($hval as $hook) {
								$key = $hook;
								if(stripos($hook, '_output') && substr($hook, stripos($hook, '_output')) == '_output') {
									$key = substr($key, 0, stripos($hook, '_output'));
								}
								//判断是否有选择主方法，如果没有抛弃output的钩子
								if(isset($sorts[$fkey][$key]) && isset($_GET['hooks'][$skey][$fkey][$key])) {
									$plugin['modules'][$type]['hooks'][$skey][$fkey][$hook] = $hook;
								}
							}
						}
					}
				}
			}
			//添加钩子
			if(isset($hooklist[$sort])) {
				$sorts = $hooklist[$sort];
				if($sorts && $sorts[$page]) {
					foreach($_GET['newhook'] as $hook) {
						$key = $hook;
						if(stripos($hook, '_output') && substr($hook, stripos($hook, '_output')) == '_output') {
							$key = substr($key, 0, stripos($hook, '_output'));
						}
						//判断是否有选择主方法，如果没有抛弃output的钩子
						if(isset($sorts[$page][$key]) && isset($_GET['newhook'][$key])) {
							$plugin['modules'][$type]['hooks'][$sort][$page][$hook] = $hook;
						}
					}
				}
			}
			//写入插件信息
			C::t('common_plugin')->update($pluginid, array('modules' => serialize($plugin['modules'])));
			
		}
		devmessage('嵌入点添加成功，继续下一步。', "develop.php?mod=plugins&action=$action&operation=$operation&pluginid=$pluginid&filename=$filename&type=$type", 'succeed');
	} elseif(submitcheck('editcron')) {
		//过滤分钟数据
		$minutes = explode(',', $_GET['newminute']);
		foreach($minutes as $minute) {
			$minute = intval($minute);
			if($minute < 0 || 59 < $minute) {
				continue;
			}
			$newminute[$minute] = $minute;
		}
		$plugin['modules'][$type][$filename]['weekday'] = $_GET['newweekday'] < -1 || 6 < $_GET['newweekday'] ? -1 : intval($_GET['newweekday']);
		$plugin['modules'][$type][$filename]['day'] = $_GET['newday'] < -1 || 31 < $_GET['newday'] ? -1 : intval($_GET['newday']);
		$plugin['modules'][$type][$filename]['hour'] = $_GET['newhour'] < -1 || 23 < $_GET['newhour'] ? -1 : intval($_GET['newhour']);
		$plugin['modules'][$type][$filename]['minute'] = $newminute ? implode(',', $newminute) : '';
		$plugin['modules'][$type][$filename]['description'] = $_GET['description'];
		//写入插件信息
		C::t('common_plugin')->update($pluginid, array('modules' => serialize($plugin['modules'])));
		devmessage('计划任务设置完成', "develop.php?mod=plugins&action=$action&operation=$operation&pluginid=$pluginid", 'succeed');
	} elseif(submitcheck('editadv')) {
		$plugin['modules'][$type][$filename]['description'] = $_GET['description'];
		//写入插件信息
		C::t('common_plugin')->update($pluginid, array('modules' => serialize($plugin['modules'])));
		devmessage('脚本编辑完成', "develop.php?mod=plugins&action=$action&operation=$operation&pluginid=$pluginid", 'succeed');
	} elseif(submitcheck('editnav')) {
		
		if(!ispluginkey($_GET['name'])) {
			devmessage($devlang['plugins_edit_modules_name_invalid'], '', 'error');
		} elseif($_GET['name'] != $filename && isset($plugin['modules'][$type][$_GET['name']])) {
			devmessage($devlang['plugins_script_'.$type].$devlang['plugins_script_repeat'], '', 'error');
		}
		
		$plugin['modules'][$type][$filename] = array(
				'name' => $_GET['name'],
				'menu' => trim($_GET['menu']),
				'url' => trim($_GET['url']),
				'type' => intval($_GET['newtype']),
				'adminid' => $_GET['adminid'] >= 0 && $_GET['adminid'] <= 3 ? intval($_GET['adminid']) : 1,
				'displayorder' => intval($_GET['order']),
				'navtitle' => $_GET['navtitle'],
				'navicon' => $_GET['navicon'],
				'navsubname' => $_GET['navsubname'],
				'navsuburl' => $_GET['navsuburl'],
				'description' => trim($_GET['menu'])
			);
		//写入插件信息
		C::t('common_plugin')->update($pluginid, array('modules' => serialize($plugin['modules'])));
		devmessage('脚本编辑完成', "develop.php?mod=plugins&action=$action&operation=$operation&pluginid=$pluginid", 'succeed');
	} elseif(submitcheck('editsystem')) {
		if(in_array($filename, array('install', 'uninstall', 'upgrade'))) {
			$plugin['modules']['extra'][$filename] = $_GET[$filename];
			//添加表时同时增加删除表SQL
			if($filename == 'install' && empty($plugin['modules']['extra']['uninstall'])) {
				preg_match_all("/CREATE\s+TABLE.+?pre\_(.+?)\s*\((.+?)\)\s*(ENGINE|TYPE)\s*\=/is", $_GET[$filename], $matches);
				if($matches[1]) {
					$uninstall = '';
					foreach($matches[1] as $table) {
						$uninstall .= "DROP TABLE IF EXISTS pre_$table;\n";
					}
					$plugin['modules']['extra']['uninstall'] = $uninstall;
				}
			}
			//写入插件信息
			C::t('common_plugin')->update($pluginid, array('modules' => serialize($plugin['modules'])));
		}
		devmessage('脚本编辑完成', "develop.php?mod=plugins&action=$action&operation=$operation&pluginid=$pluginid", 'succeed');
	} else {
		
		$modules = array();
		//整理新的脚本名称
		foreach($_POST['script'] as $key => $scripts) {
			if($key === 'extra' || $key === 'system') {
				continue;
			}
			if(in_array($key, array('general', 'special', 'mobile'))) {
				if(empty($scripts)) {
					unset($plugin['modules'][$key]);
					continue;
				} elseif(!empty($scripts)) {
					if(!ispluginkey($scripts)) {
						devmessage($devlang['plugins_edit_modules_name_invalid'], '', 'error');
					}
					$plugin['modules'][$key]['name'] = $scripts;
					$plugin['modules'][$key]['displayorder'] = 0;

					$plugin['modules'][$key]['menu'] = '';
					$plugin['modules'][$key]['url'] = '';
					$plugin['modules'][$key]['type'] = $key == 'general' ? 11 : ($key == 'special' ? 12 : 28);
					$plugin['modules'][$key]['adminid'] = 1;
					$plugin['modules'][$key]['navtitle'] = '';
					$plugin['modules'][$key]['navicon'] = '';
					$plugin['modules'][$key]['navsubname'] = '';
					$plugin['modules'][$key]['navsuburl'] = '';
				}
			} elseif(in_array($key, $scripttype)) {
				$existscripts = array();
				foreach($scripts as $scrkey => $scriptname) {
					if(isset($_POST['delete'][$key][$scriptname])) {
						unset($plugin['modules'][$key][$scriptname]);
						continue;
					} elseif(!empty($scriptname)) {
						if(!ispluginkey($scriptname)) {
							devmessage($devlang['plugins_edit_modules_name_invalid'], '', 'error');
						} elseif(@in_array($scriptname, $existscripts)) {
							devmessage($devlang['plugins_script_'.$key].$devlang['plugins_script_repeat'], '', 'error');
						}
						$init = isset($plugin['modules'][$key][$scriptname]) ? false : true;
						//更名
						if($scrkey != $scriptname && !is_numeric($scrkey)) {
							$plugin['modules'][$key][$scriptname] = $plugin['modules'][$key][$scrkey];
							unset($plugin['modules'][$key][$scrkey]);
						}
						$plugin['modules'][$key][$scriptname]['name'] = $scriptname;
						$plugin['modules'][$key][$scriptname]['displayorder'] = 0;
						if($key == 'navigation' && $init) {
							$plugin['modules'][$key][$scriptname]['menu'] = '';
							$plugin['modules'][$key][$scriptname]['url'] = '';
							$plugin['modules'][$key][$scriptname]['type'] = 1;
							$plugin['modules'][$key][$scriptname]['adminid'] = 0;
							$plugin['modules'][$key][$scriptname]['navtitle'] = '';
							$plugin['modules'][$key][$scriptname]['navicon'] = '';
							$plugin['modules'][$key][$scriptname]['navsubname'] = '';
							$plugin['modules'][$key][$scriptname]['navsuburl'] = '';
						}
						$existscripts[$scriptname] = $scriptname;
						
					}
				}
			}
		}
		C::t('common_plugin')->update($pluginid, array('modules' => serialize($plugin['modules'])));
		if($action == 'edit') {
			devmessage('脚本添加成功', "develop.php?mod=plugins&action=$action&operation=script&pluginid=$pluginid", 'succeed');
		} else {
			dheader("location:develop.php?mod=plugins&action=$action&operation=setting&pluginid=$pluginid");
			//devmessage('脚本添加成功', "develop.php?mod=plugins&action=$action&operation=setting&pluginid=$pluginid", 'succeed');
		}
	}
}
?>