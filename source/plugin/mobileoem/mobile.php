<?php

/**
 *	  [Discuz! X] (C)2001-2099 Comsenz Inc.
 *	  This is NOT a freeware, use is subject to license terms
 *
 *	  $Id: mobile.php 34241 2013-11-21 08:34:48Z nemohou $
 */

define('IN_MOBILE_API', 1);

chdir('../../../');

require './source/class/class_core.php';
$discuz = C::app();
$cachelist = array('plugin');
$discuz->cachelist = $cachelist;
$discuz->init();

if((empty($_G['uid']) || $_GET['formhash'] != FORMHASH)) {
	exit(oemjson(array('status' => -2)));
}

if(empty($_GET['module']) || !preg_match('/^[\w\.]+$/', $_GET['module'])) {
	exit(oemjson(array('status' => -3)));
}

$apifile = 'source/plugin/mobileoem/api/'.$_GET['module'].'.php';

if(file_exists($apifile)) {
	require_once $apifile;
} else {
	exit(oemjson(array('status' => -3)));
}

echo oemjson($result);
exit;

function oemjson($encode) {
	require_once 'source/plugin/mobileoem/json.class.php';
	return CJSON::encode($encode);
}