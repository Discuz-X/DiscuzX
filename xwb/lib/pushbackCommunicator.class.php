<?php
/**
 * 评论回推服务通讯器。
 * 
 * @author yaoying
 * @since 2010-12-22
 * @version $Id: pushbackCommunicator.class.php 807 2011-05-30 07:59:53Z yaoying $
 *
 */
class pushbackCommunicator{
	
	var $http;
	
	//评论回推服务器的地址
	var $serverUrl = '';
	
	var $appkey = '';
	var $appsecret = '';
	var $pushbackAuthKey = '';
	
	//以下是与评论回推服务器的通讯结果
	var $reqUrl = '';
	var $method = '';
	var $httpcode = 0;
	var $result = array();

	
	/**
	 * 构造函数
	 */
	function pushbackCommunicator(){
		$this->http = XWB_plugin::getHttp();
		$this->appkey = XWB_APP_KEY;
		$this->appsecret = XWB_APP_SECRET_KEY;
		$this->serverUrl = XWB_PUSHBACK_URL;
		$this->pushbackAuthKey = (string)XWB_Plugin::pCfg('pushback_authkey');
	}
	
	/**
	 * 获取评论回推查询授权码
	 * @return array
	 */
	function getAuthKey(){
		return $this->communicate( 
			$this->serverUrl. 'auth', 
			'POST', 
			array('ver' => XWB_P_VERSION, 'project' => XWB_P_PROJECT), 
			false
		);
	}
	
	/**
	 * 定制回推项
	 * @param array|string $type 定制选项
	 * @return array
	 */
	function setPushback( $type = array() ){
		if( !empty($type) ){
			$type = is_array($type) ? implode(',', $type) : (string)$type;
		}else{
			$type = '';
		}
		return $this->communicate( 
			$this->serverUrl. 'pushback', 
			'POST', 
			array('type' => $type)
		);
	}
	
	/**
	 * 取消所有定制回推项
	 * @param array|string $type 要取消的定制选项，传入空值则表示取消所有定制回推
	 * @return array
	 */
	function cancelPushback( $type = array() ){
		if( !empty($type) ){
			$type = is_array($type) ? implode(',', $type) : (string)$type;
		}else{
			$type = '';
		}
		return $this->communicate( 
			$this->serverUrl. 'unpushback', 
			'POST', 
			array('type' => $type)
		);
	}
	
	/**
	 * 获取可以获取的评论数量
	 * @param integer $fromid 查询起始游标位。为0表示查询全部内容
	 * @return array
	 */
	function getCommentCount( $fromid = 0 ){
		return $this->communicate( 
			$this->serverUrl. 'comment/count', 
			'GET', 
			array('fromid' => $fromid)
		);
	}
	
	/**
	 * 获取评论
	 * @param integer $fromid 查询起始游标位。为0表示查询全部内容
	 * @param integer $count 获取数量。缺省值20，最大值200
	 * @return array
	 */
	function getComments( $fromid = 0, $count = 20 ){
		return $this->communicate( 
			$this->serverUrl. 'comment', 
			'GET', 
			array('fromid' => $fromid, 'count'=> $count)
		);
	}
	
	/**
	 * 与评论回推服务器通讯
	 * @param string $url URL
	 * @param string $method 通讯方法，目前可选GET/POST
	 * @param array $param 查询参数。如果$method为GET，则这些$param将附加到$url上
	 * @return array 请查看方法{@link pushbackCommunicator->_decodeResult()}
	 */
	function communicate( $url, $method, $param = array(), $useHash = true ){
		$param = $this->createParam($param, $useHash);
		$this->requrl = $url;
		$this->method = strtoupper($method);
		
		switch ($this->method) {
			case 'GET' :
				if( !empty($param) ){
					$delimiter = (false === strpos($url, '?')) ? '?' : '&';
					$this->requrl .= ($delimiter. http_build_query($param));
				}
				break;
			case 'POST' :
				$this->http->setData ( $param );
				break;
			default:
				trigger_error('WRONG REQUEST METHOD', E_USER_ERROR);
				break;
		}
		
		$this->http->setUrl($this->requrl);
		//$this->http->max_retries = 2;
		$this->result = $this->http->request(strtolower($this->method));
		$this->httpcode = $this->http->getState();
		$this->_logRespond( array('param' => $param, 'triggered_error' => $this->http->get_triggered_error()) );
		return $this->_decodeResult();
	}
	
	
	/**
	 * 快速生成与评论回推服务器通讯的正确查询参数
	 * @param array $param 查询参数
	 * @param bool $useHash 是否使用评论回推服务器通讯必须的hash？否则使用APP KEY SECRET
	 * @return array
	 */
	function createParam( $param = array(), $useHash = true ){
		$param['appkey'] = $this->appkey;
		if( false === $useHash ){
			$param['secret'] = $this->appsecret;
		}else{
			$param['appcode'] = $this->createAuthHash();
		}
		return $param;
	}
	
	/**
	 * 生成与评论回推服务器通讯必须的hash
	 * @return string
	 */
	function createAuthHash(){
		return md5($this->appkey. $this->appsecret. $this->pushbackAuthKey);
	}
	
	/**
	 * 临时更换评论回推服务器的通讯验证key
	 * @param string $key
	 */
	function changePushbackAuthKey($key){
		$this->pushbackAuthKey = $key;
	}
	
	/**
	 * 对评论回推服务器返回的信息进行解码，并返回
	 * @access protected
	 * @return array array('httpcode'=> (int)服务器返回的状态码 , 'data'=> (mixed)服务器返回的body主数据 )
	 */
	function _decodeResult(){
		if( $this->httpcode == 200 ){
			$this->result = json_decode(preg_replace('#(?<=[,\{\[])\s*("\w+"):(\d{6,})(?=\s*[,\]\}])#si', '${1}:"${2}"', $this->result), true);
		}
		if( !is_array($this->result) ){
			$this->result = array();
		}
		return array('httpcode'=>$this->httpcode, 'data'=>$this->result);
	}
	
	/**
	 * 记录与评论回推服务器进行的通讯
	 * @access protected
	 * @param array $extraMsg
	 */
	function _logRespond($extraMsg = array()){
		if( !defined('XWB_DEV_LOG_ALL_RESPOND') || XWB_DEV_LOG_ALL_RESPOND != true ){
			return 0;
		}
		
		//调用这个类的当前页面的url
		$callURL = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '__UNKNOWN__';
		
		if( $this->httpcode == 0 ){
			//timeout
			$respondResult = '__CONNECTION MAYBE TIME OUT ?__';
		}elseif ( $this->httpcode == -1 ){
			$respondResult = '__CAN NOT CONNECT TO PUSH BACK SERVER; OR CREATE A WRONG PUSH BACK REQUEST URL. PLEASE INSPECT THE LOG__';
		}else{
			$respondResult = $this->result;
		}
		
		if( empty($extraMsg['triggered_error']) ){
			unset($extraMsg['triggered_error']);
		}
		
		$msg = $this->method. "\t".
				$this->httpcode. "\t".
				$this->requrl. "\t".
				"\r\n". str_repeat('-', 5). '[EXTRA MESSAGE START]'. str_repeat('-', 5)."\r\n".
				'[CALL URL]'. $callURL. "\r\n".
				'[RESPOND RESULT]'. "\r\n". print_r($respondResult, 1). "\r\n\r\n".
				'[EXTRA LOG MESSAGE]'. "\r\n". print_r($extraMsg, 1). "\r\n".
				str_repeat('-', 5). '[EXTRA MESSAGE END]'. str_repeat('-', 5)."\r\n\r\n\r\n"
				;
		
		$logFile = XWB_P_DATA.'/pushback_respond_'. date("Y-m-d_H"). '.txt.php';
		XWB_plugin::LOG($msg, $logFile);
		
		return 1;
		
	}
	
	

}