<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_flinks.php 31327 2012-08-13 07:01:41Z liulanbo $
 */

if (!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
if (empty($admincp) || !is_object($admincp) || !$admincp->isfounder) {
	exit('Access Denied');
}

loadcache('plugin');
global $_G;

if (!submitcheck('settingsubmit') && !submitcheck('inserthook')) {

	$_CA = C::t('common_setting')->fetch_all(null);
	cpheader();
	shownav('sitemanager', 'menu_sitemanager_hooker');
	showsubmenu('menu_sitemanager_hooker');
	showformheader('hooker');
	showtableheader('');
	$tableClasses = array('class="td25"', 'class="td29"', 'class="td29"', 'class="td29"', 'class="td29"', 'class="td31"');

	showtablerow('', $tableClasses, array('', cplang('嵌入点($hooker)'), cplang('嵌入点所在文件($file)'), cplang('匹配规则($pattern)'), cplang('替换($replacement)'), '',));
	print "
<script type=\"text/JavaScript\">
	var rowtypedata = [
		[
			[1,'', 'td25'],
			[1,'<input type=\"text\" class=\"txt\" name=\"newhooker[]\" size=\"20\">', 'td29'],
			[1,'<input type=\"text\" class=\"txt\" name=\"newfile[]\" size=\"20\">', 'td29'],
			[1,'<input type=\"text\" class=\"txt\" name=\"newpattern[]\" size=\"20\">', 'td29'],
			[1,'<input type=\"text\" class=\"txt\" name=\"newreplacement[]\" size=\"20\">', 'td29'],
			[1,'', 'td31'],
		]
	];
</script>";


	$_CA['templatehooker'] = (array)dunserialize($_CA['templatehooker']);
	foreach ($_CA['templatehooker'] as $templatehooker) {
		$str = '';
		$file = DISCUZ_ROOT . $_G['style']['tpldir'] . '/' . $templatehooker['file'];
		if (!file_exists($file)) {
			$file = DISCUZ_ROOT . './template/default/' . $templatehooker['file'];
			if (!file_exists($file)) {
				$str = '找不到对应模板文件';
			}
		}
		if ($str == '') {
			$hooker = html_entity_decode($templatehooker['hooker'], ENT_QUOTES, 'UTF-8');
			$pattern = html_entity_decode($templatehooker['pattern'], ENT_QUOTES, 'UTF-8');
			$replacement = html_entity_decode($templatehooker['replacement'], ENT_QUOTES, 'UTF-8');
			$hooker_exist = xm_file_content_exists($file, $hooker);
			$tpd = htmlentities($templatehooker['templatehookerid'], ENT_QUOTES, 'UTF-8');
			if ($hooker_exist) {
				$str = '<span style="color:#999999;">找到嵌入点</span>';
			} else {
				$flag_exist = xm_file_content_exists($file, $pattern);
				if ($flag_exist) {
					$str = "<input type=\"submit\" name=\"inserthook[$tpd]\" value=\"插入嵌入点\" />";
					//$result = xm_file_replace($file, $pattern, $replacement, $hooker);
				} else {
					$str = '找不到参照点';
				}
			}
		}
		if ($templatehooker['hooker'] !== '') {
			showtablerow('', $tableClasses, array("<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$templatehooker[templatehookerid]\">", "<input type=\"text\" class=\"txt\" size=\"20\" name=\"hooker[$templatehooker[templatehookerid]]\" value=\"$templatehooker[hooker]\">", "<input type=\"text\" class=\"txt\" size=\"20\" name=\"file[$templatehooker[templatehookerid]]\" value=\"$templatehooker[file]\" >", "<input type=\"text\" class=\"txt\" size=\"20\" name=\"pattern[$templatehooker[templatehookerid]]\" value=\"$templatehooker[pattern]\">", "<input type=\"text\" class=\"txt\" size=\"20\" name=\"replacement[$templatehooker[templatehookerid]]\" value=\"$templatehooker[replacement]\">", $str));
		}
	}
	echo '<tr><td></td><td colspan="8"><div><a href="###" onclick="addrow(this, 0)" class="addtr">' . '添加嵌入点' . '</a></div></td></tr>';
//	foreach ($_G['cache']['plugin'] as $plugin => $value) {
//		echo '$_G[\'cache\'][\'plugin\']:' . /*serialize*/
//			($plugin) . ';<br />';
//	}
	showsubmit('settingsubmit', 'submit', 'del');
	showtablefooter();
	showformfooter();
} else if (submitcheck('inserthook')) {
	$_CA = C::t('common_setting')->fetch_all(null);
	$_CA['templatehooker'] = (array)dunserialize($_CA['templatehooker']);

	foreach ($_GET['inserthook'] as $inserthook => $value) {
		$templatehooker = $_CA['templatehooker'][$inserthook];
		//print_r($templatehooker);

		$str = '';
		$file = DISCUZ_ROOT . $_G['style']['tpldir'] . '/' . $templatehooker['file'];
		if (!file_exists($file)) {
			$file = DISCUZ_ROOT . './template/default/' . $templatehooker['file'];
			if (!file_exists($file)) {
				$str = '找不到对应模板文件';
			}
		}
		if ($str == '') {
			$hooker = html_entity_decode($templatehooker['hooker'], ENT_QUOTES, 'UTF-8');
			$pattern = html_entity_decode($templatehooker['pattern'], ENT_QUOTES, 'UTF-8');
			$replacement = html_entity_decode($templatehooker['replacement'], ENT_QUOTES, 'UTF-8');
			$hooker_exist = xm_file_content_exists($file, $hooker);
			if ($hooker_exist) {
				$str = '<span style="color:#999999;">找到嵌入点</span>';
			} else {
				$flag_exist = xm_file_content_exists($file, $pattern);
				if ($flag_exist) {
					$str = "插入嵌入点:<input type=\"submit\" name=\"inserthook[$templatehooker[templatehookerid]]\" value=\"$templatehooker[templatehookerid]\" />";
					$result = xm_file_replace($file, $pattern, $replacement, $hooker);
				} else {
					$str = '找不到参照点';
				}
			}
		}
		if ($result) {
			cpmsg('嵌入点插入成功', 'hooker', 'succeed');
		} else {
			cpmsg(('Error ' . $str), 'hooker', 'error');
		}

	}
	;
	/*
	foreach(($_GET['inserthook']) as $inserthook => $value){
		print_r($inserthook);
		//$templatehooker = $_CA['templatehooker'][html_entity_decode($inserthook, ENT_QUOTES, 'UTF-8')];
		//print_r($templatehooker);
	}
	print_r($_CA['templatehooker']['<p><a>Powered by</a> <strong>']);
	*/
} else {

	$settingnew = $_GET['settingnew'];
	if (is_array($_GET['hooker'])) {
		foreach ($_GET['hooker'] as $templatehookerid => $val) {
			//$templatehookerid = intval($templatehookerid);
			//print_r($templatehookerid );
			//print_r($val);
			//echo intval($templatehookerid == '');
			$updatearr = array('templatehookerid' => /*dhtmlspecialchars($_GET['hooker'][$templatehookerid])*/
			htmlentities($_GET['hooker'][$templatehookerid], ENT_QUOTES, 'UTF-8'), 'hooker' => /*dhtmlspecialchars($_GET['hooker'][$templatehookerid])*/
			htmlentities($_GET['hooker'][$templatehookerid], ENT_QUOTES, 'UTF-8'), 'file' => $_GET['file'][$templatehookerid], 'pattern' => /*dhtmlspecialchars($_GET['pattern'][$templatehookerid])*/
			htmlentities($_GET['pattern'][$templatehookerid], ENT_QUOTES, 'UTF-8'), 'replacement' => /*dhtmlspecialchars($_GET['replacement'][$templatehookerid])*/
			htmlentities($_GET['replacement'][$templatehookerid], ENT_QUOTES, 'UTF-8'),);
			//C::t('home_click')->update($id, $updatearr);
			$settingnew['templatehooker'][htmlentities($templatehookerid, ENT_QUOTES, 'UTF-8')] = $updatearr;
		}
	}
	if (is_array($_GET['delete'])) {
		foreach ($_GET['delete'] as $id => $val) {
			//$ids[] = $id;
			//echo $_GET['delete'][$id];
			//echo '=';
			echo $_GET['delete'][$id];
			//echo ';';
			//$templatehooker[($id)] = array();
			//$templatehooker = array_splice($templatehooker, intval($id), 1);
			unset($settingnew['templatehooker'][$_GET['delete'][$id]]);
		}
		if ($ids) {
			//C::t('home_click')->delete($ids, true);
		}
	}
	//print_r($_GET['newhooker']);
	if (is_array($_GET['newhooker'])) {
		foreach ($_GET['newhooker'] as $key => $value) {
			//echo $key;
			//echo "=";
			//echo $value;
			if ($value != '' && $_GET['newhooker'][$key] != '') {
				$data = array('templatehookerid' => dhtmlspecialchars($_GET['newhooker'][$key]), 'hooker' => dhtmlspecialchars($_GET['newhooker'][$key]), 'file' => dhtmlspecialchars($_GET['newfile'][$key]), 'pattern' => dhtmlspecialchars($_GET['newpattern'][$key]), 'replacement' => dhtmlspecialchars($_GET['newreplacement'][$key]));
				//C::t('home_click')->insert($data);
				//print_r( $data);
				//array_push($templatehooker, $data);
				$settingnew['templatehooker'][dhtmlspecialchars($_GET['newhooker'][$key])] = $data;
			}
		}
	}

	$settingnew['templatehooker'] = serialize($settingnew['templatehooker']);
	C::t('common_setting')->update_batch($settingnew);
	updatecache('setting');
	cpmsg('setting_update_succeed', 'hooker', 'succeed');
}


function xm_file_content_exists($file, $message) {
	if (file_exists($file)) {
		$content = file_get_contents($file);
		if (substr($message, 0, 1) !== '/') {
			return stripos($content, $message) !== false;
		} else {
			return preg_match($message, $content);
		}
	}
	return false;
}

function xm_file_replace($file, $pattern, $replace, $hooker, $limit = -1) {
	if (file_exists($file)) {
		$content = file_get_contents($file);
		if (is_callable($replace)) {
			$content = preg_replace_callback($pattern, $replace, $content, $limit);
		} elseif (substr($pattern, 0, 1) !== '/') {
			$content = str_replace($pattern, str_replace('$hooker', $hooker, $replace), $content, $limit);
		} else {
			$content = preg_replace($pattern, str_replace('\r', "\r", str_replace('$hooker', $hooker, str_replace('\t', "\t", str_replace('\n', "\n", $replace)))), $content, $limit);
		}
		if ($content !== false) {
			file_put_contents($file, $content);
			return true;
		}
	}
	return false;
}

?>