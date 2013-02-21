<?php

/**
 * 新浪微博签名替换函数
 * @version $Id: xwb_format_signature.function.php 689 2011-05-04 06:44:17Z yaoying $
 * @param string $s
 */
function xwb_format_signature($s) {
	static $xweibourl = null;
	if(null == $xweibourl){
		$xweibourl = rtrim(strval(XWB_plugin::pCfg('baseurl_to_xweibo')), '/');
	}
	if(XWB_plugin::pCfg('switch_to_xweibo') && !empty($xweibourl)){
		$xweibourl_ta = $xweibourl. '/index.php?m=ta&id=';
	}else{
		$xweibourl_ta = 'http://weibo.com/';
	}	
	
	$p = "#&lt;-sina_sign,(\d+),([a-z0-9]+),(\d+)-&gt;#sim";
	$rp = '<a href="'. $xweibourl_ta. '\1" target="_blank"><img border="0" src="http://service.t.sina.com.cn/widget/qmd/\1/\2/\3.png"/></a>';
	//$p = XWB_plugin::convertEncoding($p,'UTF8', XWB_S_CHARSET);
	//$rp= XWB_plugin::convertEncoding($rp,'UTF8', XWB_S_CHARSET);
	if (! empty ( $s ) && preg_match ( $p, $s, $m )) {
		return preg_replace ( $p, $rp, $s );
	}
	return $s;
}