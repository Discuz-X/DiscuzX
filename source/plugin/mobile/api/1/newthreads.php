<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forumnav.php 31700 2012-09-24 03:46:59Z zhangjie $
 */

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

include_once 'forum.php';

class mobile_api {

	function common() {
		global $_G;
		$start = !empty($_GET['start']) ? $_GET['start'] : 0;
		$limit = !empty($_GET['limit']) ? $_GET['limit'] : 20;
		$variable['data'] = C::t('forum_newthread')->fetch_all_by_fids(dintval(explode(',', $_GET['fids']), true), $start, $limit);
		foreach(C::t('forum_thread')->fetch_all_by_tid(array_keys($variable['data']), 0, $limit) as $thread) {
			$variable['data'][$thread['tid']] = $thread;
		}
		mobile_core::result(mobile_core::variable($variable));
	}

	function output() {}

}

?>