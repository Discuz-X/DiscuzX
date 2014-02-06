<?php

/**
 *	  [Discuz! X] (C)2001-2099 Comsenz Inc.
 *	  This is NOT a freeware, use is subject to license terms
 *
 *	  $Id: threaddetail.php 34241 2013-11-21 08:34:48Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

loadcache(array('smilies', 'smileytypes', 'forums', 'usergroups',
	'stamps', 'bbcodes', 'smilies',	'custominfo', 'groupicon', 'stamps',
	'threadtableids', 'threadtable_info', 'posttable_info', 'diytemplatenameforum'));

$navtitle = str_replace('{bbname}', $_G['setting']['bbname'], $_G['setting']['seotitle']['forum']);

require DISCUZ_ROOT.'./source/module/forum/forum_viewthread.php';