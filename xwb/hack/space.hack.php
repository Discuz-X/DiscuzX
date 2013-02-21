<?php
/*
 * @version $Id: space.hack.php 375 2010-12-08 06:06:11Z yaoying $
 */
if( !defined('IS_IN_XWB_PLUGIN') ){
	exit('Access Denied!');
}

$sina_uid = XWB_plugin::F('sinaUidFilter', array($uid));
$appkey = XWB_APP_KEY;

//签名解析
$GLOBALS['space']['sightml'] = isset($GLOBALS['space']['sightml']) ? XWB_plugin::F('xwb_format_signature', $GLOBALS['space']['sightml']) : '';