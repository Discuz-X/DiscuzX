<?php

/*
	[Discuz!] (C)2001-2009 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: test.php 13226 2010-07-23 00:24:28Z monkey $
*/

//定义应用 ID
define('APPTYPEID', 127);

require './source/class/class_core.php';

$discuz = & discuz_core::instance();

$discuz->cachelist = $cachelist;
$discuz->init();

$editorid = 'e';
$_G['setting']['editoroptions'] = str_pad(decbin($_G['setting']['editoroptions']), 2, 0, STR_PAD_LEFT);
$editormode = $_G['setting']['editoroptions']{0};
$allowswitcheditor = $_G['setting']['editoroptions']{1};
$editor = array(
	'editormode' => $editormode,
	'allowswitcheditor' => $allowswitcheditor,
	'allowhtml' => 1,
	'allowhtml' => 1,
	'allowsmilies' => 1,
	'allowbbcode' => 1,
	'allowimgcode' => 1,
	'allowcustombbcode' => 0,
	'allowresize' => 1,
	'textarea' => 'message',
	'simplemode' => !isset($_G['cookie']['editormode_'.$editorid]) ? 1 : $_G['cookie']['editormode_'.$editorid],
);

loadcache('bbcodes_display');

include template('forum/1');

?>