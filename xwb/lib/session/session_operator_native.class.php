<?php

/**
 * session原生操作器。
 * 本类将所有内容存储在$_SESSION[XWB_CLIENT_SESSION]下
 * 支持自行使用$_SESSION数组完成对session的操作。
 * 
 * @author yaoying<yaoying@staff.sina.com.cn>
 * @uses XWB_CLIENT_SESSION
 * @since 2010-12-02
 * @version $Id: session_operator_native.class.php 724 2011-05-10 05:28:00Z yaoying $
 *
 */
class session_operator_native{
	
	
	function session_operator_native(){
	}
	
	
	/**
	 * 注册一个session存储器实例。
	 * 对于本原生操作器来讲，这个为可选方法，并且要在本类的session_start之前启动。
	 * @param object &$handler session存储器实例
	 */
	function setStorageHandler(&$handler){
		session_set_save_handler(
			array(&$handler, "open"), 
			array(&$handler, "close"), 
			array(&$handler, "read"), 
			array(&$handler, "write"),
			array(&$handler, "destroy"),
			array(&$handler, "gc")
		);
	}
	
	/**
	 * 清除session
	 */
	function clear(){
		$_SESSION[XWB_CLIENT_SESSION] = array();
	}
	
	/**
	 * 设置session
	 * @param mixed $k
	 * @param mixed $v
	 */
	function set($k,$v=false){
		if( is_array($k) ){
			$_SESSION[XWB_CLIENT_SESSION] = array_merge($_SESSION[XWB_CLIENT_SESSION],$k);
		}else{
			$_SESSION[XWB_CLIENT_SESSION][$k] = $v;
		}
	}
	
	/**
	 * 获取session
	 * @param string|null $key
	 * @return mixed
	 */
	function get($key = null){
		if( null !==  $key ){
			return isset($_SESSION[XWB_CLIENT_SESSION][$key]) ? $_SESSION[XWB_CLIENT_SESSION][$key] : null;
		}else{
			return $_SESSION[XWB_CLIENT_SESSION];
		}
	}
	
	/**
	 * 删除某个session
	 * @param string $k
	 * @return bool
	 */
	function del($k){
		if ( empty($_SESSION[XWB_CLIENT_SESSION]) ){
			return true;
		}
		if(!is_array($k)) {$k = array($k);}
		foreach($k as $kv ){
			if (isset($_SESSION[XWB_CLIENT_SESSION][$kv])) unset($_SESSION[XWB_CLIENT_SESSION][$kv]);
		}
		return true;
	}
	
	
	/**
	 * 模拟php函数session_id
	 * @param string $id 不为空则表示设置session_id为传入的值
	 * @return string
	 */
	function session_id( $id = null ){
		return session_id($id);
	}
	
	/**
	 * 模拟php函数session_regenerate_id，重新生成并设置一个session_id
	 * @param bool $delete_old_session 是否删除以前的session
	 */
	function session_regenerate_id( $delete_old_session = false ){
		return session_regenerate_id($delete_old_session);
	}
	
	/**
	 * 模拟php函数session_start，启动session机制。
	 * @uses dzx1.5的$_G, XWB_CLIENT_SESSION, dsetcookie 
	 * @return string
	 */
	function session_start(){
		session_start();
		if( !is_array($_SESSION[XWB_CLIENT_SESSION]) ){
			$this->clear();
		}
	}
	
}