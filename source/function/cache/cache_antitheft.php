<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_antitheft.php 30814 2012-06-21 06:37:56Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_antitheft() {
	$antitheft = array();
	$antitheftsetting = C::t('common_setting')->fetch('antitheftsetting', true);
	foreach($antitheftsetting as $key => $_ips) {
		$antitheft[$key] = array();
		$_ips = explode("\n", $_ips);
		foreach($_ips as $_ip) {
			$_ip = trim($_ip);
			$_ipdata = explode('.', $_ip);
			if($_ipdata) {
				$_ipcount = count($_ipdata);
				if($_ipcount < 4) {
					$_max = null;
					switch ($_ipcount) {
						case 1:
							$_ipdata[1] = '0';
							$_max = '.255.255.255';
						case 2:
							$_ipdata[2] = '0';
							if(!$_ipdata[1]) $_ipdata[1] = '0';
							if(!isset($_max)) $_max = '.255.255';
						case 3:
							$_ipdata[3] = '0';
							if(!$_ipdata[2]) $_ipdata[2] = '0';
							if(!isset($_max)) $_max = '.255';
					}
					$_ipmin = implode('.', $_ipdata);
					$_ipmax = trim($_ip, '.').$_max;
					$antitheft[$key]['range'][] = array('min'=>ip2long($_ipmin), 'max'=>ip2long($_ipmax));
				} else {
					$antitheft[$key]['single'][] = ip2long($_ip);
				}
			}
		}
	}
	savecache('antitheft', $antitheft);
}

?>