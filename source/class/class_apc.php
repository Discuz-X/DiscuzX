<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: class_apc.php 22162 2011-04-22 09:45:15Z zhangguosheng $
 */

class discuz_apc
{

	function discuz_apc() {

	}

	function init($config) {

	}

	function get($key) {
		return apc_fetch($key);
	}

	function set($key, $value, $ttl = 0) {
		return apc_store($key, $value, $ttl);
	}

	function rm($key) {
		return apc_delete($key);
	}

	function clear() {
		return apc_clear_cache('user');
	}

}