<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: develop.php 30238 2012-05-17 07:30:20Z liulanbo $
 */
// 定义应用 ID
define('APPTYPEID', 128);
define('CURSCRIPT', 'plugindevelop');
require_once './source/class/class_core.php';

$discuz = C::app();

//核心类
$cachelist = array('plugin', 'diytemplatename');
$discuz->cachelist = $cachelist;
$discuz->init();

//脚本引导
if(!in_array($_GET['mod'], array('plugin'))) {
	$_GET['mod'] = 'plugin';
}
require_once DISCUZ_ROOT.'develop/'.$_GET['mod'].'.php';

function devmessage($message, $url = '', $type = '', $values = array(), $extra = '', $extrajs = '', $confirmedname = 'confirmed') {
	global $_G;
	if(!empty($_G['gp_inajax'])) {
		echo $message;
		return;
	}
	switch($type) {
		case 'download':
		case 'form':
		case 'succeed': $classname = 'infotitle2';break;
		case 'window':
		case 'error': $classname = 'infotitle3';break;
		case 'loadingform': case 'loading': $classname = 'infotitle1';break;
		default: $classname = 'marginbot normal';break;
	}
	$message = "<h4 class=\"$classname\">$message</h4>";
	$url .= $url && !empty($_G['gp_scrolltop']) ? '&scrolltop='.intval($_G['gp_scrolltop']) : '';

	if($type == 'form') {
		$message = "<form method=\"post\" action=\"$url\"><input type=\"hidden\" name=\"formhash\" value=\"".FORMHASH."\">".
			"<br />$message$extra".
			"<p class=\"margintop\"><input type=\"submit\" class=\"btn\" name=\"$confirmedname\" value=\"确定\"".($extrajs ? ' '.$extrajs : '')."> &nbsp; \n".
			"<script type=\"text/javascript\">".
			"if(history.length > (BROWSER.ie ? 0 : 1)) document.write('<input type=\"button\" class=\"btn\" value=\"取消\" onClick=\"history.go(-1);\">');".
			"</script>".
			"</p></form><br />";
	} elseif($type == 'loadingform') {
		$message = "<form method=\"post\" action=\"$url\" id=\"loadingform\"><input type=\"hidden\" name=\"formhash\" value=\"".FORMHASH."\"><br />$message$extra<img src=\"static/image/admincp/ajax_loader.gif\" class=\"marginbot\" /><br />".
			'<p class="marginbot"><a href="###" onclick="$(\'loadingform\').submit();" class="lightlink">如果您的浏览器没有自动跳转，请点击这里</a></p></form><br /><script type="text/JavaScript">setTimeout("$(\'loadingform\').submit();", 2000);</script>';
	} else {
		$message .= $extra.($type == 'loading' ? '<img src="static/image/admincp/ajax_loader.gif" class="marginbot" />' : '');
		if($url) {
			if($type == 'button') {
				$message = "<br />$message<br /><p class=\"margintop\"><input type=\"submit\" class=\"btn\" name=\"submit\" value=\"".cplang('start')."\" onclick=\"location.href='$url'\" />";
			} else {
				$message .= '<p class="marginbot"><a href="'.$url.'" class="lightlink">如果您的浏览器没有自动跳转，请点击这里</a></p>';
				//$url = transsid($url);
				$timeout = $type != 'loading' ? 3000 : 0;
				$message .= "<script type=\"text/JavaScript\">setTimeout(\"redirect('$url');\", $timeout);</script>";
			}
		} elseif($type != 'succeed' && $type != 'window') {
			$message .= '<p class="marginbot">'.
			"<script type=\"text/javascript\">".
			"if(history.length > (BROWSER.ie ? 0 : 1)) document.write('<a href=\"javascript:history.go(-1);\" class=\"lightlink\">返回</a>');".
			"</script>".
			'</p>';
		}
	}
	include template('header', 0, 'develop/template/common');
	include template('showmessage', 0, 'develop/template/common');
	include template('footer', 0, 'develop/template/common');
	exit;
}
?>