<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_faqs.php 16696 2010-09-13 05:02:24Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_faqs() {
	$data = array();
	$query = DB::query("SELECT fpid, id, identifier, keyword FROM ".DB::table('forum_faq')." WHERE identifier!='' AND keyword!=''");

	while($faqs = DB::fetch($query)) {
		$data[$faqs['identifier']]['fpid'] = $faqs['fpid'];
		$data[$faqs['identifier']]['id'] = $faqs['id'];
		$data[$faqs['identifier']]['keyword'] = $faqs['keyword'];
	}

	save_syscache('faqs', $data);
}

?>