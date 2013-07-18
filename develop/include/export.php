<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: export.php 30694 2012-06-12 09:26:01Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
if(!submitcheck('pluginsubmit')) {

	$data = createPluginPackage($plugin);
	if($_GET['down'] == 'xml') {
		$plugin_export = file_get_contents($data['xml']);
		ob_end_clean();
		dheader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		dheader('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
		dheader('Cache-Control: no-cache, must-revalidate');
		dheader('Pragma: no-cache');
		dheader('Content-Encoding: none');
		dheader('Content-Length: '.strlen($plugin_export));
		dheader('Content-Disposition: attachment; filename='.$data['xmlname']);
		dheader('Content-Type: text/xml');
		echo $plugin_export;
		define('FOOTERDISABLED' , 1);
	} elseif($_GET['down'] == 'zip') {
		dheader('Content-Encoding: none');
		dheader('Content-Type: application/zip');
		dheader('Pragma: no-cache');
		dheader('Expires: 0');
		dheader('Content-Disposition: attachment; filename='.$plugin['identifier'].'.zip');
		readfile($data['zip']);
		@unlink($data['zip']);
		
		exit;
	}
}

function createPluginPackage($plugin) {
	//将文件写入data/develop目录下并打包成ZIP包
	$basedir = DISCUZ_ROOT.'data/develop/'.$plugin['identifier'];
	if(!is_dir($basedir)) {
		dmkdir($basedir);
	}
	//创建模板目录
	$tpldri = $basedir.'/template';
	dmkdir($tpldri);
	$scripttype = array('magic', 'cron', 'adv', 'task', 'secqaa', 'seccode', 'navigation');
	$basetype = array('general', 'special', 'mobile');
	//创建相应的文件
	$baseClass = $baseMethod = $expandMethod = $specialClass = array();
	require_once DISCUZ_ROOT.'develop/include/hooklist.php';
	require_once DISCUZ_ROOT.'develop/include/phptpl.php';
	foreach($plugin['modules'] as $key => $scripts) {
		if(in_array($key, $scripttype) && $key != 'navigation') {
			foreach($scripts as $name => $scrinfo) {
				$path = $basedir.'/'.$key;
				if(!is_dir($path)) {
					dmkdir($path, 0777, false);
				}
				$filePath = $path.'/'.$key.'_'.$name.'.php';
				if($key == 'cron') {
					$code = str_replace('{modulename}', $key.'_'.$name, $phptpl[$key]);
					$code = str_replace('{weekday}', $scrinfo['weekday'], $code);
					$code = str_replace('{day}', $scrinfo['day'], $code);
					$code = str_replace('{hour}', $scrinfo['hour'], $code);
					$code = str_replace('{minute}', $scrinfo['minute'], $code);
				} else {
					$code = str_replace('{modulename}', $key.'_'.$name, $phptpl['emptyfile']);
					$code = str_replace('//==={code}===', $phptpl[$key], $code);
				}
				$code = str_replace('{name}', $name, $code);
				$code = str_replace('{desc}', $scrinfo['description'], $code);
				file_put_contents($filePath, $code);
			}
		} elseif($key == 'navigation') {
			foreach($scripts as $name => $scrinfo) {
				$filePath = $basedir.'/'.$name.'.inc.php';
				$code = str_replace('//==={code}===', '//TODO - Insert your code here', $phptpl['emptyfile']);
				file_put_contents($filePath, $code);
			}
		} elseif(in_array($key, $basetype)) {
			$hooklist = $key == 'mobile' ? $mobilehook : $generalhook;
			
			if($key == 'special') {
				$specialClass[$scripts['name']] = $phptpl['specialclass'];
			}
			$baseClass[$scripts['name']] = $scripts['name'];
			foreach($scripts['hooks'] as $type => $pages) {
				foreach($pages as $pagename => $hooks) {
					foreach($hooks as $hook) {
						$key = $hook;
						if(stripos($hook, '_output') && substr($hook, stripos($hook, '_output')) == '_output') {
							$key = substr($key, 0, stripos($hook, '_output'));
						}
						$hookinfo = $hooklist[$type][$pagename][$key];
						$code = str_replace('{methodName}', $hook, $phptpl['methodtpl']);
						$code = str_replace('{returncomment}', $hookinfo['return'], $code);
						$code = str_replace('{return}', $hookinfo['return'] == 'string' ? '\'TODO:'.$hook.'\'' : 'array()', $code);
							
						if($type == 'common') {
							$baseMethod[$scripts['name']][$hook] = $code;
						} else {
							$expandMethod[$scripts['name']][$type][$hook] = $code;
						}
					}
				}
			}
		} elseif($key == 'extra') {
			//写入样式表信息
			if($scripts['extrastyle']) {
				$styleCode = "/** plugin::$plugin[identifier] **/\n".$scripts['extrastyle']."\n/** end **/\n";
				$filePath = $tpldri.'/extend_module.css';
				file_put_contents($filePath, $styleCode);
			}
			//写入安装脚本
			if($scripts['install']) {
				$filePath = $basedir.'/install.php';
				$code = str_replace('{modulename}', 'install', $phptpl['emptyfile']);
				$code = str_replace('//==={code}===', str_replace('{sql}', $scripts['install'], $phptpl['sqlcode']), $code);
				file_put_contents($filePath, $code);
			}
			if($scripts['uninstall']) {
				$filePath = $basedir.'/uninstall.php';
				$code = str_replace('{modulename}', 'uninstall', $phptpl['emptyfile']);
				$code = str_replace('//==={code}===', str_replace('{sql}', $scripts['uninstall'], $phptpl['sqlcode']), $code);
				file_put_contents($filePath, $code);
			}
			if($scripts['upgrade']) {
				$filePath = $basedir.'/upgrade.php';
				$code = str_replace('{modulename}', 'upgrade', $phptpl['emptyfile']);
				$code = str_replace('//==={code}===', $scripts['upgrade'], $code);
				file_put_contents($filePath, $code);
			}
		}
	}

	//写入嵌入点脚本
	foreach($baseClass as $name) {
		$filePath = $basedir.'/'.$name.'.class.php';
			
		$code = str_replace('{modulename}', $name, $phptpl['baseclass']);
		$code = str_replace('//==={code}===', implode('', $baseMethod[$name]), $code);
		//添加扩展类
		foreach($expandMethod[$name] as $extend => $methods) {
			$extcode = str_replace('{modulename}', $name, $phptpl['extendclass']);
			$extcode = str_replace('{curscript}', $extend, $extcode);
			$code .= str_replace('//==={code}===', implode('', $methods), $extcode);
		}
		if(!empty($specialClass[$name])) {
			$code .= $specialClass[$name];
			$specialClass[$name] = '';
		}
		$code = str_replace('//==={code}===', $code, $phptpl['emptyfile']);
		file_put_contents($filePath, $code);
	}
	//生成XML文档
	$pluginarray = array();
	$pluginarray['plugin'] = $plugin;
	unset($pluginarray['plugin']['pluginid']);
	//踢除扫描目录的文件
	foreach($pluginarray['plugin']['modules'] as $key => $scripts) {
		if(in_array($key, $scripttype)) {
			if($key == 'navigation') {
				foreach($scripts as $name => $scrinfo) {
					$name = 'nav_'.$name;
					$pluginarray['plugin']['modules'][$name] = $scrinfo;
				}
			}
			unset($pluginarray['plugin']['modules'][$key]);
		} elseif(in_array($key, $basetype)) {
			unset($pluginarray['plugin']['modules'][$key]['hooks']);
		}
	}
	$pluginarray['version'] = strip_tags($plugin['modules']['extra']['version'] ? $plugin['modules']['extra']['version'] : $_G['setting']['version']);
	foreach(C::t('common_pluginvar')->fetch_all_by_pluginid($pluginid) as $var) {
		unset($var['pluginvarid'], $var['pluginid']);
		$pluginarray['var'][] = $var;
	}
	$modules = $pluginarray['plugin']['modules'];
	if(file_exists($file = DISCUZ_ROOT.'./data/plugindata/'.$pluginarray['plugin']['identifier'].'.lang.php')) {
		include $file;
		if(!empty($scriptlang[$pluginarray['plugin']['identifier']])) {
			$pluginarray['language']['scriptlang'] = $scriptlang[$pluginarray['plugin']['identifier']];
		}
		if(!empty($templatelang[$pluginarray['plugin']['identifier']])) {
			$pluginarray['language']['templatelang'] = $templatelang[$pluginarray['plugin']['identifier']];
		}
		if(!empty($installlang[$pluginarray['plugin']['identifier']])) {
			$pluginarray['language']['installlang'] = $installlang[$pluginarray['plugin']['identifier']];
		}
	}
	unset($modules['extra']);
	$pluginarray['plugin']['modules'] = serialize($modules);
	if(file_exists($basedir.'/install.php')) {
		$pluginarray['installfile'] = 'install.php';
	}
	if(file_exists($basedir.'/uninstall.php')) {
		$pluginarray['uninstallfile'] = 'uninstall.php';
	}
	if(file_exists($basedir.'/upgrade.php')) {
		$pluginarray['upgradefile'] = 'upgrade.php';
	}
	if(file_exists($basedir.'/check.php')) {
		$pluginarray['checkfile'] = 'check.php';
	}
	
	$name = 'Discuz! Plugin';
	require_once libfile('class/xml');
	require_once libfile('function/admincp');
	$root = array(
			'Title' => $name,
			'Version' => $_G['setting']['version'],
			'Time' => dgmdate(TIMESTAMP, 'Y-m-d H:i'),
			'From' => $_G['setting']['bbname'].' ('.$_G['siteurl'].')',
			'Data' => exportarray($pluginarray, 1)
	);
	$filename = strtolower(str_replace(array('!', ' '), array('', '_'), $name)).'_'.$plugin['identifier'].'.xml';
	$plugin_export = array2xml($root, 1);
	$filePath = $basedir.'/'.$filename;
	file_put_contents($filePath, array2xml($root, 1));
	//打成zip包下载
	require_once DISCUZ_ROOT.'develop/include/pclzip.lib.php';
	$zipFileName = DISCUZ_ROOT.'data/develop/'.$plugin['identifier'].'.zip';
	$zip = new PclZip($zipFileName);
	$zip->create($basedir, PCLZIP_OPT_REMOVE_PATH, $basedir);
	return array('zip' => $zipFileName, 'xml' => $filePath, 'xmlname' => $filename);
}
?>