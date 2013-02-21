<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: server.php 21489 2011-03-28 08:34:46Z monkey $
 */

class server {

	var $siteId;
	var $siteKey;
	var $apiVersion = '0.0';
	var $timezone;
	var $version;
	var $charset;
	var $language;
	var $errno = 0;
	var $errmsg = '';

	function server($siteId, $siteKey, $timezone, $version, $charset, $language) {
		if(!$siteKey) {
			exit;
		}
		$this->siteId = $siteId;
		$this->siteKey = $siteKey;
		$this->timezone = $timezone;
		$this->version = $version;
		$this->charset = $charset;
		$this->language = $language;
	}

	function run() {
		$response = $this->_processServerRequest();
		echo serialize($this->_formatLocalResponse($response));
	}

	function _call($method, $params) {
		list($module, $method) = explode('.', $method);
		$response = $this->_callServerMethod($module, $method, $params);
		return $this->_formatServerResponse($response);
	}

	function _processServerRequest() {
		$request = $_POST;
		$module = $request['module'];
		$method = $request['method'];
		$params = $request['params'];

		if(!$module || !$method) {
			return new ErrorResponse('1', 'Invalid Method: ' . $method);
		}

		$params = stripslashes($params);
		$sig = $this->_generateSign($module, $method, $params, $this->siteKey);

		if($sig != $request['sig']) {
			return new ErrorResponse('10', 'Error Sig');
		}

		$params = unserialize($params);

		$params = $this->_myAddslashes($params);

		return $this->_callLocalMethod($module, $method, $params);
	}

	function _formatLocalResponse($data) {
		$res = array(
		    'my_version' => $this->apiVersion,
		    'timezone' => $this->timezone,
		    'version' => $this->version,
		    'charset' => $this->charset,
		    'language' => $this->language
		);
		if(strtolower(get_class($data)) == 'response') {
			if(is_array($data->result) && $data->getMode() == 'Batch') {
				foreach($data->result as $result) {
					if(strtolower(get_class($result)) == 'response') {
						$res['result'][] = $result->getResult();
					} else {
						$res['result'][] = array('errno' => $result->getErrno(),
						    'errmsg' => $result->getErrmsg()
						);
					}
				}
			} else {
				$res['result'] = $data->getResult();
			}
		} else {
			$res['errCode'] = $data->getErrno();
			$res['errMessage'] = $data->getErrmsg();
		}
		return $res;
	}

	function _callLocalMethod($module, $method, $params) {
		if($module == 'Batch' && $method == 'run') {
			$response = array();
			foreach($params as $param) {
				$response[] = $this->_callLocalMethod($param['module'], $param['method'], $param['params']);
			}
			return new Response($response, 'Batch');
		}

		$methodName = $this->_getMethodName($module, $method);
		if(method_exists($this, $methodName)) {
			$result = @call_user_func_array(array($this, $methodName), $params);
			if (is_object($result) && is_a($result, 'ErrorResponse')) {
				return $result;
			}
			return new Response($result);
		} else {
			return new ErrorResponse('2', 'Method not implemented: ' . $methodName);
		}
	}

	function _getMethodName($module, $method) {
		return 'on' . ucfirst($module) . ucfirst($method);
	}

	function _generateSign($module, $method, $params, $siteKey) {
		$args = array('module' => $module,
		    'method' => $method,
		    'params' => $params
		);

		ksort($args);
		$str = '';
		foreach ($args as $k => $v) {
			if ($v) {
				$str .= $k . '=' . $v . '&';
			}
		}
		return md5($str . $siteKey);
	}

	function _myAddslashes($string) {
		if(is_array($string)) {
			foreach ($string as $key => $val) {
				$string[$key] = $this->_myAddslashes($val);
			}
		} else {
			$string = ($string === null) ? null : addslashes($string);
		}
		return $string;
	}

}

class ErrorResponse {

	var $errno = 0;
	var $errmsg = '';

	function ErrorResponse($errno, $errmsg) {
		$this->errno = $errno;
		$this->errmsg = $errmsg;
	}

	function getErrno() {
		return $this->errno;
	}

	function getErrmsg() {
		return $this->errmsg;
	}

	function getResult() {
		return null;
	}

}

class Response {

	var $result;
	var $mode;

	function Response($res, $mode = null) {
		$this->result = $res;
		$this->mode = $mode;
	}

	function getResult() {
		return $this->result;
	}

	function getMode() {
		return $this->mode;
	}

}