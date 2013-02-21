<?php

/**
 * session模拟管理操作器（session委托操作器）。
 * 不允许自行使用$_SESSION数组完成对session的操作，必须透过此类提供的方法完成对模拟session存取操作
 * 纯php session id生成和校验参考ecmall思路，在此感谢
 * 部分方法只适应DZX1.5，故移植到地方需要进行修改
 * 
 * @author yaoying<yaoying@staff.sina.com.cn>
 * @since 2010-12-02
 * @version $Id: session_operator_simulator.class.php 724 2011-05-10 05:28:00Z yaoying $
 * 
 */
class session_operator_simulator{
	
	/**
	 * session数据
	 * @var array
	 */
	var $_sess_data = array();
	
	/**
	 * session id
	 * @var string
	 */
	var $_session_id = '';
	
	/**
	 * session存储器实例
	 * @var object
	 */
	var $_storageHandler = null;
	
	/**
	 * 构造方法
	 */
	function session_operator_simulator(){
		//php4的特殊问题导致不能将register_shutdown_function到__destruct那里。
		//原因：详见php手册评论kwazy at php dot net (29-Jan-2003 11:53)
	}
	
	/**
	 * 注册一个session存储器实例。
	 * 对于本模拟器来讲，这个是必须要执行的方法，并且要在本类的session_start之前启动。
	 * @param object &$handler session存储器实例
	 */
	function setStorageHandler(&$handler){
		if( version_compare(PHP_VERSION, '5', '<') ){
			register_shutdown_function(array(&$this, '__destruct'));
		}
		$this->_storageHandler = &$handler;
	}
	
	/**
	 * 清除session
	 */
	function clear(){
		$this->_sess_data = array();
	}
	
	/**
	 * 设置session
	 * @param mixed $k
	 * @param mixed $v
	 */
	function set($k,$v=false){
		//echo '<pre>';print_r($this->_sess_data);
		if( is_array($k) ){
			$this->_sess_data = array_merge($this->_sess_data,$k);
		}else{
			$this->_sess_data[$k] = $v;
		}
		//echo '<pre>';print_r($this->_sess_data);
	}
	
	/**
	 * 获取session
	 * @param mixed $key
	 * @return mixed
	 */
	function get($key = null){
		if( null !==  $key ){
			return isset($this->_sess_data[$key]) ? $this->_sess_data[$key] : null;
		}else{
			return $this->_sess_data;
		}
	}
	
	/**
	 * 删除某个session
	 * @param string $k
	 * @return bool
	 */
	function del($k){
		if ( empty($this->_sess_data) ){
			return true;
		}
		if(!is_array($k)) {$k = array($k);}
		foreach($k as $kv ){
			if (isset($this->_sess_data[$kv])) unset($this->_sess_data[$kv]);
		}
		return true;
	}
	
	/**
	 * 模拟php函数session_id
	 * @param string $id 不为空则表示设置session_id为传入的值
	 * @return string
	 */
	function session_id( $id = null ){
		if( !empty($id) ){
			$this->_session_id = $id;
		}
		return $this->_session_id;
	}
	
	/**
	 * 模拟php函数session_regenerate_id，重新生成并设置一个session_id
	 * @param bool $delete_old_session 是否删除以前的session
	 */
	function session_regenerate_id( $delete_old_session = false ){
		if( true == $delete_old_session ){
			$this->clear();
		}
		
		$this->_session_id = $this->generateSessionid();
		$session_id_cookie = $this->_session_id. $this->generateSessionHash($this->_session_id);
		//dz函数
		if( function_exists('dsetcookie') ){
			dsetcookie(XWB_CLIENT_SESSION, $session_id_cookie, 0);
		}else{
			setcookie(XWB_CLIENT_SESSION, $session_id_cookie, 0);
		}
		return true;
	}
	
	
	/**
	 * 模拟php函数session_start，启动session机制。
	 * @uses dzx1.5的$_G, XWB_CLIENT_SESSION, dsetcookie 
	 * @return string
	 */
	function session_start(){
		global $_G;
		$session_id = '';
		if ( isset($_G['cookie'][XWB_CLIENT_SESSION]) ){
			$session_id = (string)$_G['cookie'][XWB_CLIENT_SESSION];
		}
		
		$session_id = $this->checkSessionHash($session_id);
		
		//session id校验失败时，重新生成并获取新的session id
		if( empty($session_id) ){
			$this->session_regenerate_id();
			$session_id = $this->session_id();
		//成功时，则设置实例为新的session id
		}else{
			$this->session_id($session_id);
		}
		
		$sess_data = @unserialize( $this->_storageHandler->read( $session_id ) );
		if( is_array($sess_data) ){
			$this->set($sess_data);
		}
		
	}
	
	
	/**
	 * 模拟php，生成一个session_id，长度为32位
	 * @return string
	 */
	function generateSessionid(){
		if( function_exists('mt_rand') ){
			$prefix = mt_rand(). XWB_plugin::getIP();
		}else{
			$prefix = rand(). XWB_plugin::getIP();
		}
		
		$sessionid = md5(uniqid( $prefix, true ));
		
		return $sessionid;
	}
	
	
	/**
	 * 生成一个session id校验
	 * @uses XWB_P_ROOT , XWB_plugin
	 * @param string $id
	 */
	function generateSessionHash( $id ){
		$key = '';
		if( !empty($_SERVER['HTTP_USER_AGENT']) ){
			$key .= $_SERVER['HTTP_USER_AGENT'];
		}
		$key = XWB_P_ROOT. XWB_plugin::getIP(). $id;
		return sprintf('%08x', crc32($key));
	}
	
	
	/**
	 * session校验，并返回真实的session id值
	 * 
	 * @param string $hash
	 */
	function checkSessionHash($hash){
		if( empty($hash) ){
			return '';
		}
		$tmp_session_id = substr ( $hash, 0, 32 );
		
		if ($this->generateSessionHash ( $tmp_session_id ) == substr ( $hash, 32 )) {
			return $tmp_session_id;
		} else {
			return '';
		}
	}
	
	
	/**
	 * 析构函数
	 * 执行session存储器实例，将模拟的session内容写入保存
	 */
	function __destruct(){
		
		if( !is_object($this->_storageHandler) ){
			return '';
		}
		
		if( !empty($this->_sess_data) && !empty($this->_session_id) ){
			$sess_data = serialize($this->_sess_data);
			$this->_storageHandler->write($this->_session_id, $sess_data);
		}
		$this->_storageHandler->close();
	}
	
	
}