<?php
/**
 * 基于curl的http Client请求类。
 * 本文件可单独使用。原文件注释：
 * @package	xweibo_plugin_core
 * @subpackage adapter_http
 * @author heli <heli1@staff.sina.com.cn>
 * @author yaoying <yaoying@staff.sina.com.cn>
 * @copyright (C)1996-2099 SINA Inc.
 * @version $Id: curlHttp.class.php 724 2011-05-10 05:28:00Z yaoying $
 */
class curlHttp
{
	var $_curlInit;
	var $_serverUrl;
	var $_param = array();
	//默认设置
	var $_option = array();
	//保存返回服务器的内容
	var $_server_content;
	var $_codeInfo;
	var $_outputInteraction = false;
	var $triggered_error = array();
	var $mimes = array(
						'gif' => 'image/gif',
						'png' => 'image/png',
						'bmp' => 'image/bmp',
						'jpeg' => 'image/jpeg',
						'pjpg' => 'image/pjpg',
						'jpg' => 'image/jpeg',
						'tif' => 'image/tiff',
						'htm' => 'text/html',
						'css' => 'text/css',
						'html' => 'text/html',
						'txt' => 'text/plain',
						'gz' => 'application/x-gzip',
						'tgz' => 'application/x-gzip',
						'tar' => 'application/x-tar',
						'zip' => 'application/zip',
						'hqx' => 'application/mac-binhex40',
						'doc' => 'application/msword',
						'pdf' => 'application/pdf',
						'ps' => 'application/postcript',
						'rtf' => 'application/rtf',
						'dvi' => 'application/x-dvi',
						'latex' => 'application/x-latex',
						'swf' => 'application/x-shockwave-flash',
						'tex' => 'application/x-tex',
						'mid' => 'audio/midi',
						'au' => 'audio/basic',
						'mp3' => 'audio/mpeg',
						'ram' => 'audio/x-pn-realaudio',
						'ra' => 'audio/x-realaudio',
						'rm' => 'audio/x-pn-realaudio',
						'wav' => 'audio/x-wav',
						'wma' => 'audio/x-ms-media',
						'wmv' => 'video/x-ms-media',
						'mpg' => 'video/mpeg',
						'mpga' => 'video/mpeg',
						'wrl' => 'model/vrml',
						'mov' => 'video/quicktime',
						'avi' => 'video/x-msvideo',
						'xml' => 'text/xml',
						'bin' => 'application/octet-stream',
						'js' => 'application/x-javascript',
					);
	
	function curlHttp($_outputInteraction = false){
		$this->__construct($_outputInteraction);
	}
	
	function __construct($_outputInteraction = false){
		$this->_outputInteraction = $_outputInteraction;
		$this->_curlInit = curl_init();
		$this->_reset_option();
	}
	
	/**
	 * 重置初始项
	 */
	function _reset_option(){
		$this->_option = array(CURLOPT_RETURNTRANSFER => true,
							CURLOPT_HEADER => false,
							CURLOPT_TIMEOUT => 10,
							CURLOPT_USERAGENT => (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'curl with no useragent'),
							);
	}	
	
	/**
	 * 设置访问的url
	 *
	 * @param string $url
	 * @return object
	 */
	function setUrl($url)
	{
		$this->_serverUrl = $url;
		$this->_option[CURLOPT_URL] = $this->_serverUrl;
		return $this;
	}


	/**
	 * 设置请求的方式 'get'|'post'|'put'|'file'
	 *
	 * @param string $method
	 * @return object
	 */
	function setMethod($method = 'GET')
	{
		$method = empty($method) ? 'get' : $method;
		$method = strtolower($method);
		switch ($method) {
			case 'post':
			case 'file':
				$this->_option[CURLOPT_POST] = true;
				$this->_option[CURLOPT_CUSTOMREQUEST] = 'POST';
				break;
			case 'get':
				$this->_option[CURLOPT_POST] = false;
				$this->_option[CURLOPT_CUSTOMREQUEST] = 'GET';
				break;
			case 'put':
				$this->_option[CURLOPT_POST] = false;
				$this->_option[CURLOPT_CUSTOMREQUEST] = 'PUT';
				break;
			case 'delete':
				$this->_option[CURLOPT_POST] = false;
				$this->_option[CURLOPT_CUSTOMREQUEST] = 'DELETE';
				break;
			default:
					$this->_option[CURLOPT_POST] = false;
					$this->_option[CURLOPT_CUSTOMREQUEST] = 'GET';
		}
		return $this;
	}
	
	/**
	 * 使用rawurlencode编码的参数
	 *
	 * @param unknown_type $array
	 * @return unknown
	 */
	function http_build_query_rawurl($array)
	{
		if (!empty($array)) {
			if (is_array($array)) {
				$params = array();
				foreach ($array as $key => $value) {
					$params[] = $key .'='.rawurlencode($value);
				}
				$params_string = implode('&', $params);
			} else {
				$params_string = $array;
			}
			return $params_string;
		}
		return false;
	}
	
	/**
	 * 设置请求方式是get的参数
	 *
	 * @param array|string $data
	 * @param bool $raw 是否用rawurlencode编码
	 * @return object
	 */
	function _setParameterGet($data, $raw = false)
	{
		if (!empty($data)) {
			if (is_array($data)) {
				if ($raw == true) {
					$params = $this->http_build_query_rawurl($data);
				} else {
					$params = http_build_query($data);
				}
			} else {
				$params = $data;
			}
			if (strpos($this->_serverUrl, '?')) {
				$this->_serverUrl = $this->_serverUrl.'&'.$params;
			} else {
				$this->_serverUrl = $this->_serverUrl.'?'.$params;
			}

			$this->_option[CURLOPT_URL] = $this->_serverUrl;
		}
		return $this;
	}
	
	/**
	 * 设置请求方式是post的参数
	 *
	 * @param array|string $data
	 * @param bool $isFile 是否上传文件
	 * @return object
	 */
	function _setParameterPost($data, $isFile = false)
	{
		if (!empty($data)) {
			if ($isFile){
				if (!is_array($data)) {
					$params['fileName'] = '@'.$data;
				} else {
					foreach ($data as $key => $value) {
						if ($key == 'fileName') {
							$params[$key] = '@'.$value;
						}
					}
				}
			} else {
				if (is_array($data)) {
					$temp = array();
					foreach ($data as $key => $value) {
						if (substr($key, -2) == '[]' && is_array($value)) {
							foreach ($value as $part) {
								$temp[] = $key . '=' . urlencode($part);
							}
						} else {
							$temp[] = $key .'='. urlencode($value);
						}
					}
					$params = implode('&', $temp);
				} else {
					$params = $data;
				}
			}

			$this->_option[CURLOPT_POSTFIELDS] = $params;
		}
		return $this;
	}
	
	/**
	 * 设置get|post的数据
	 *
	 * @param array|string $data
	 * @return object
	 */
	function setData($data)
	{
		$this->_param = $data;
		return $this;
	}
	
	/**
	 * 设置发送头内容格式(文件)
	 *
	 * @param string $data
	 * @param string $content_type
	 */
	function setRawData($data = null, $content_type)
	{
		$this->_param = $data;
		if(isset($this->mimes[$content_type])){
			$this->setHeader('Content-Type', $this->mimes[$content_type]);
		}else{
			$this->setHeader('Content-Type', $content_type);
		}
		return $this;
	}
	
	/**
	 * 设置发送头信息
	 * @param string $k
	 * @param string $v
	 */
	function setHeader($k, $v){
		if(!isset($this->_option[CURLOPT_HTTPHEADER]) || !is_array($this->_option[CURLOPT_HTTPHEADER])){
			$this->_option[CURLOPT_HTTPHEADER] = array();
		}
		$this->_option[CURLOPT_HTTPHEADER][] = $k.": ".$v;
		return $this;
	}
	
	/**
	 * 设置curl的配置参数值（替代函数curl_setopt设置）
	 *
	 * @param array|string $config
	 * @param mixed $value
	 */
	function setopt($config, $value = null)
	{
		if (is_array($config)) {
			foreach ($config as $key => $opt) {
			    $this->_option[$key] = $opt;
			}
		}else{
		    $this->_option[$config] = $value;
		}

		return $this;
	}

	/**
	 * 兼容fsockopenHttp的方法(设置发送头信息)
	 * @param array $config
	 * @return curlHttp
	 */
	function setConfig($config){
		foreach ($config as $var) {
			foreach($var as $k) {
				$headers = explode(':', $k);
				$headers[1] = trim($headers[1]);
				if (empty($headers[1])){
					continue;
				}
				$this->setHeader($headers[0], $headers[1]);
			}
		}
	}
	
	/**
	 * 设置curl选项,代替curl_setopt_array
	 *
	 * @param curl handle $ch
	 * @param array $data
	 */
	 function _setCurlOption($ch, $options)
	{
		foreach ($options as $key => $value) {
			curl_setopt($ch, $key, $value);
		}
	}
	
	/**
	 * 兼容fsockopenHttp，调用GET方法
	 * @param string $url
	 * @return string
	 */
    function Get($url){
        $this->setUrl($url);
        return $this->request('get', 0 == strpos($url, 'https') ? true : false);
    }
    
	/**
	 * 兼容fsockopenHttp，调用POST方法
	 * @param string $url
	 * @return string
	 */
    function Post($url){
    	$this->setUrl($url);
    	return $this->request('post', 0 == strpos($url, 'https') ? true : false);
    }
    
	/**
	 * 发送请求,获取的内容
	 *
	 * @param string $method
	 * @return array
	 */
	function request($method = null, $https = false)
	{
		//支持https
		if ($https) {
			$this->_option[CURLOPT_SSL_VERIFYPEER] = false;
		}
		$method = empty($method) ? "get" : $method;
		if (strtolower($method) == 'post' || strtolower($method) == 'put') {
			$this->setMethod($method);
			$this->_setParameterPost($this->_param);
		} elseif (strtolower($method) == 'file') {
			$this->setMethod('file');
			$this->_setParameterPost($this->_param, true);
		} elseif (strtolower($method) == 'reg') {
			$this->setMethod();
			$this->_setParameterGet($this->_param, true);
		} elseif (strtolower($method) == 'delete') {
			$this->setMethod('delete');
			$this->_setParameterGet($this->_param);
		} else {
			$this->setMethod();
			$this->_setParameterGet($this->_param);
		}
		
		//交互输出debug
		if($this->_outputInteraction){
		    if(defined('CURLINFO_HEADER_OUT')){
		        $this->_option[CURLINFO_HEADER_OUT] = true;
		    }
		    $this->_option[CURLOPT_VERBOSE] = true;
		    
			echo "=========CURL OPTION=========\r\n";
			$this->_outputCliMsg($this->_option);
		}
		
		$this->_curl_exec_once();
		
		//再重试访问一次
		if ($this->getState() == 0) {
			$this->_curl_exec_once();
		}
		
		//重置curl的配置选项和清除数据
		$this->_param = array();
		$this->_reset_option();
		
		return $this->_server_content;
	}
	
	function _curl_exec_once(){
		if(!function_exists('curl_setopt_array')) {
			$this->_setCurlOption($this->_curlInit, $this->_option);
		} else {
			curl_setopt_array($this->_curlInit, $this->_option);
		}
		
		//返回结果
		$this->_server_content = curl_exec($this->_curlInit);
		
		//获取curl请求的信息
		$this->_codeInfo = curl_getinfo($this->_curlInit);
		
		$curl_errno = curl_errno($this->_curlInit);
		if($curl_errno){
			$curl_error = 'curl error:'. curl_error($this->_curlInit). '[ErrCode '. $curl_errno. ']';
			$this->_trigger_error($curl_error, E_USER_WARNING);
		}
		
		//交互输出debug
		if($this->_outputInteraction){
			if($curl_errno){
				echo $curl_error. "\n\r";
			}
			echo "=========BEGIN OF curl_getinfo AFTER REQUEST=========\r\n";
			$this->_outputCliMsg($this->_codeInfo);
			echo "=========END OF curl_getinfo AFTER REQUEST=========\r\n";
			echo "=========RESPOND CONTENT=========\r\n";
			echo $this->_server_content. "\r\n";
		}
	}
	
	/**
	 * 获取返回的http状态
	 *
	 * @return int
	 */
	function getState()
	{
		return isset($this->_codeInfo['http_code']) ? $this->_codeInfo['http_code'] : -1;
	}
	
	/**
	 * 获取调用的url
	 *
	 * @return string
	 */
	function getUrl()
	{
		return isset($this->_codeInfo['url']) ? $this->_codeInfo['url'] : '__UNKNOWN_URL__';
	}
	
	/**
	 * 获取发送curl请求的返回有关curl的信息
	 *
	 * @return array
	 */
	function getHttpInfo()
	{
		return $this->_codeInfo;
	}

	/**
	 * 关闭curl
	 *
	 */
	function closeHttp()
	{
		curl_close($this->_curlInit);
	}
	
	/**
	 * 析函数,关闭curl
	 *
	 */
	function __destruct()
	{
		if ($this->_curlInit) {
			$this->closeHttp();
		}
	}
	
    /**
     * 添加一个错误触发器，主要为了方便外部debug
     * 
     * @since 2010-08-24 15:39
     * @param $errmsg
     * @param $errno
     */
    function _trigger_error( $errmsg, $errno ){
    	$this->triggered_error[] = array('errmsg' => $errmsg, 'errno' => $errno );
    	trigger_error($errmsg, $errno);
    }
    
    /**
     * 获取已经触发的错误信息
     * @since 2010-08-24 15:39
     */
    function get_triggered_error(){
    	return $this->triggered_error;
    }
    
    function _outputCliMsg($msg){
    	if(is_array($msg)){
    		foreach($msg as $k => $v){
    			if(is_array($v)){
    				echo ">>>>>>DATA OF Array ". $k. "<<<<<<\r\n";
    				$this->_outputCliMsg($v);
    				echo ">>>>>>END OF Array ". $k. "<<<<<<\r\n";
    			}else{
    				echo $k. ': '. $v. "\r\n";
    			}
    		}
    	}else{
    		echo $msg. "\r\n";
    	}
    }
	
}
