<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: class_eaccelerator.php 7158 2010-03-30 03:40:23Z cnteacher $
 */

class discuz_eaccelerator
{

	function discuz_eaccelerator() {

	}

	function init($config) {

	}

	function get($key) {
		return eaccelerator_get($key);
	}

	function set($key, $value, $ttl = 0) {
		return eaccelerator_put($key, $value, $ttl);
	}

	function rm($key) {
		return eaccelerator_rm($key);
	}

}

?>