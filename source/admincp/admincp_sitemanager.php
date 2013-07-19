<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_sitemanager.php 31327 2012-08-13 07:01:41Z liulanbo $
 */

if (!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

if (!$operation || $operation == 'todo') {
	shownav('sitemanager', 'menu_sitemanager_todo');
} elseif ($operation == 'phpmyadmin') {
	if (empty($admincp) || !is_object($admincp) || !$admincp->isfounder) {
		exit('Access Denied');
	}
	shownav('sitemanager', 'menu_sitemanager_phpmyadmin');
	print_r('<iframe id="frame_content" src="/phpmyadmin" scrolling="yes" frameborder="0" style="position: absolute; left:0px; top:0px; width:100%; border:0px; height: 100%;"></iframe>');
} elseif ($operation == 'filemanager') {
	if (empty($admincp) || !is_object($admincp) || !$admincp->isfounder) {
		exit('Access Denied');
	}
	shownav('sitemanager', 'menu_sitemanager_filemanager');
	if (!submitcheck('filemanagersubmit')) {
		require_once libfile('class/file');
		$data['path']['current'] = './';
		$sdir = $sfile = $data['dirs'] = $data['files'] = array();
		File::show_dir($data['path']['current'], $sdir, $sfile, 0);
//	foreach($sdir as $val){
//		$dir_arr_temp = array();
//		//$dir = $data['path']['current'].$val.'/';
//		//if(in_array($dir, C('LIST_CONF.DISPLAY_NOTALLOW'))){continue;}
//		$dir_arr_temp = stat($dir);
//		//$dir_arr_temp['name']  = LANG_GBK?g2u($val):$val;
//		$dir_arr_temp['chmod'] = substr(sprintf('%o', fileperms($dir)), -4);
//		$dir_arr_temp['atime'] = date('Y-m-d H:i:s', $dir_arr_temp['atime']);
//		$dir_arr_temp['mtime'] = date('Y-m-d H:i:s', $dir_arr_temp['mtime']);
//		$dir_arr_temp['ctime'] = date('Y-m-d H:i:s', $dir_arr_temp['ctime']);
//		$dir_arr_temp['size']  = 'no size';
//		$data['dirs'][]        = $dir_arr_temp;
//	}
//
//	foreach($sfile as $val){
//		$file_arr_temp = array();
//		$file = $data['path']['current'].$val;
//		//if(in_array($file, C('LIST_CONF.DISPLAY_NOTALLOW'))){continue;}
//		$file_arr_temp = stat($file);
//		//$file_arr_temp['name']  = LANG_GBK?g2u($val):$val;
//		$file_arr_temp['chmod'] = substr(sprintf('%o', fileperms($file)), -4);
//		$file_arr_temp['atime'] = date('Y-m-d H:i:s', $file_arr_temp['atime']);
//		$file_arr_temp['mtime'] = date('Y-m-d H:i:s', $file_arr_temp['mtime']);
//		$file_arr_temp['ctime'] = date('Y-m-d H:i:s', $file_arr_temp['ctime']);
//		//$file_arr_temp['ext']   = get_ext($file);
//		$file_arr_temp['_size'] = $file_arr_temp['size'];
//		//$file_arr_temp['size']  = dealsize($file_arr_temp['size']);
//		$data['files'][]        = $file_arr_temp;
//	}
		showformheader('sitemanager&operation=filemanager');
		showhiddenfields(array('site' => 'TRYANDERROR'));
		showtableheader();
		echo '<tr><td>';
		foreach ($sdir as $item) {
			echo '<div class="exploreritem">' . $item . '<input type="checkbox" name="operationtargets[]" value="'.$item.'" /></div>';
		}
		foreach ($sfile as $item) {
			echo '<div class="exploreritem">' . $item . '<input type="checkbox" name="operationtargets[]" value="'.$item.'" /></div>';
		}
		echo '</td></tr>';
		showtablefooter();
		showsubmit('filemanagersubmit');
		showformfooter();
		echo <<<STY
<style>
.exploreritem{
width:128px;
height:128px;
float:left;
border:2px solid #777;
margin:6px;
white-space:nowrap;
text-align:center;
}
</style>
STY;
	} else {
		//echo 'afuahfao';
		//echo serialize($_GET['operationtargets']);
		$fp = fopen('./package.zip', 'w');
		$data = dfsockopen('http://localhost/manage/sync.php', 0, 'operation=fetch&includefiles='.rawurlencode($_GET['operationtargets']));
		fwrite($fp, $data);
		fclose($fp);
		echo strlen($data);

	}
//function dfsockopen($url, $limit = 0, $post = '', $cookie = '', $bysocket = FALSE, $ip = '', $timeout = 15, $block = TRUE, $encodetype  = 'URLENCODE', $allowcurl = TRUE, $position = 0) {
	//print_r(serialize($data));
}
?>