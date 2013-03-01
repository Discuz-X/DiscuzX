<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: class_memcache.php 7158 2010-03-30 03:40:23Z cnteacher $
 */

class discuz_memcache
{
	var $enable;
	var $obj;

	function discuz_memcache() {

	}

	function init($config) {
		if(!empty($config['server'])) {
			$this->obj = new Memcache;
			if($config['pconnect']) {
				$connect = @$this->obj->pconnect($config['server'], $config['port']);
			} else {
				$connect = @$this->obj->connect($config['server'], $config['port']);
			}
			$this->enable = $connect ? true : false;
		}
	}

	function get($key) {
		return $this->obj->get($key);
	}

	function set($key, $value, $ttl = 0) {
		return $this->obj->set($key, $value, MEMCACHE_COMPRESSED, $ttl);
	}

	function rm($key) {
		return $this->obj->delete($key);
	}

}

?>