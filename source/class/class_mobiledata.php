<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: class_mobiledata.php 22775 2011-05-20 05:43:23Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class mobiledata {

	var $version = '1.0';
	var $params = array();
	var $safevariables = array('/^config/', '/^setting$/', '/^setting\/my_sitekey$/', '/^setting\/connectsitekey$/');

	function validator() {
		global $_G;
		if(empty($_G['gp_mobiledata']) || empty($_G['setting']['my_sitekey'])) {
			return false;
		}
		$mobiledata = $_G['gp_mobiledata'];
		$p = strpos($mobiledata, '|');
		if($p === FALSE) {
			return false;
		}
		$authcode = substr($mobiledata, 0, $p);
		$params = substr($mobiledata, $p + 1);
		if(md5(md5($params).$_G['setting']['my_sitekey']) !== $authcode) {
			return false;
		}
		$this->params = array_merge($this->params, explode('|', $params));
		return true;
	}

	function outputvariables() {
		global $_G;
		$variables = array();
		foreach($this->params as $param) {
			if(substr($param, 0, 1) == '$') {
				if($param == '$_G') {
					continue;
				}
				$var = substr($param, 1);
				if(preg_match("/^[a-zA-Z_][a-zA-Z0-9_]*$/", $var)) {
					$variables[$param] = $GLOBALS[$var];
				}
			} else {
				if(preg_replace($this->safevariables, '', $param) !== $param) {
					continue;
				}
				$variables[$param] = getglobal($param);
			}
		}
		$xml = array(
			'Version' => $this->version,
			'Charset' => strtoupper($_G['charset']),
			'Variables' => $variables,
		);
		if(!empty($_G['messageparam'])) {
			$xml['Message'] = $_G['messageparam'];
		}
		require_once libfile('class/xml');
		echo array2xml($xml);
		exit;
	}
}

?>