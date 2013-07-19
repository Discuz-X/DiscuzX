<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: class_xcache.php 6757 2010-03-25 09:01:29Z cnteacher $
 */

class discuz_xcache
{

	function discuz_xcache() {

	}

	function init($config) {

	}

	function get($key) {
		return xcache_get($key);
	}

	function set($key, $value, $ttl = 0) {
		return xcache_set($key, $value, $ttl);
	}

	function rm($key) {
		return xcache_unset($key);
	}

}