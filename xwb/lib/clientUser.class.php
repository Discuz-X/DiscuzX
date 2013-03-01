<?php

/**
 * session总启动和管理类
 * 特别提供oauth、绑定状态等相关的session快速操作集合
 * 本例不提供单例化方法，请自行保证单例性，否则将遇到严重问题！
 * 
 * @author xionghui<xionghui1@staff.sina.com.cn>
 * @author yaoying<yaoying@staff.sina.com.cn>
 * @since 2010-06-08
 * @copyright SINA INC.
 * @version $Id: clientUser.class.php 645 2011-03-21 06:26:57Z yaoying $
 */

class clientUser 
{

	var $_operator = null;
	
	/**
	 * 构造方法，将自动启动session机制
	 */
	function clientUser(){
        $this->_session_start();
	}
	
	
	/**
	 * 启动session
	 */
	function _session_start(){
		$operatorType = defined('XWB_P_SESSION_OPERATOR') ? strtolower(XWB_P_SESSION_OPERATOR) : 'native';
		$storageType = defined('XWB_P_SESSION_STORAGE_TYPE') ? strtolower(XWB_P_SESSION_STORAGE_TYPE) : '';
		
		//session操作器初始化
		$this->_operator = XWB_plugin::O('session/session_operator_'. $operatorType);
		
		//session存储器注册到session操作器中
		if( !empty($storageType) ){
			$sessStorage = XWB_plugin::O('session/session_storage_'. $storageType);
			$this->_operator->setStorageHandler($sessStorage);
		//模拟操作器必须要有一个session存储器
		}elseif( 'simulator' == $operatorType ){
			//XWB_plugin::showError('管理员设置错误，导致程序被终止。请联系管理员解决。<br />错误原因：You have defined SIMULATOR session operator but does not define a session STORAGE type! SYSTEM HALTED!');
			trigger_error('You have defined SIMULATOR session operator but does not define a session STORAGE type! SYSTEM HALTED!', 256);
		}
		
		$this->_operator->session_start();
		
	}
	
	/**
	 * 清除session（委托session操作器操作）
	 */
	function clearInfo(){
		$this->_operator->clear();
	}
	
	/**
	 * 设置session（委托session操作器操作）
	 * @param mixed $k
	 * @param mixed $v
	 */
	function setInfo($k,$v=false){
		$this->_operator->set($k,$v);
	}
	
	/**
	 * 获取session（session委托操作器操作）
	 * @param mixed $key
	 * @return mixed
	 */
	function getInfo($key=null){
		return $this->_operator->get($key);
	}
	
	/**
	 * 删除某个session（session委托操作器操作）
	 * @param string $k
	 * @return bool
	 */
	function delInfo($k){
		return $this->_operator->del($k);
	}
	
	/**
	 * SESSION快速操作：设置Oauth token
	 * @param array $keys token数组 
	 * @param bool $is_confirm false表示request token；true表示access token
	 */
	function setOAuthKey($keys,$is_confirm = false){
		$k = $is_confirm ? 'XWB_OAUTH_KEYS2' : 'XWB_OAUTH_KEYS1' ;
		$this->setInfo(array("$k"=>$keys));
	}
	
	/**
	 * SESSION快速操作：获取指定的Oauth token
	 * @param bool $is_confirm false表示存储的是request token；true表示存储的是access token
	 * @return array|null
	 */
	function getOAuthKey($is_confirm = false){
		$k = $is_confirm ? 'XWB_OAUTH_KEYS2' : 'XWB_OAUTH_KEYS1' ;
		return $this->getInfo($k);
	}
	
	
	/**
	 * SESSION快速操作：快速获取Oauth token（access token or request token）。
	 * @return array|null
	 */
	function getToken(){
		$key2 = $this->getOAuthKey(true);
		return empty($key2) ? $this->getOAuthKey(false) : $key2;
	}
	
	/**
	 * SESSION快速操作：清除Oauth token
	 * @param bool $is_confirm false表示存储的是request token；true表示存储的是access token
	 */
	function clearToken(){
		$this->setOAuthKey(array(),true);
		$this->setOAuthKey(array(),false);
	}
	
	/**
	 * 统计上报session数组内容添加
	 * @param string $type
	 * @param array $args
	 * @return bool
	 */
	function appendStat( $type, $args = array() ){
		$originStat = $this->_checkStat();
		$args['xt'] = $type;
		
		$originStat[] = $args;
		$this->setInfo('STAT', $originStat);
		return true;
	}
	
	/**
	 * 统计上报session数组获取
	 * @return array
	 */
	function getStat(){
		return $this->_checkStat();
	}
	
	/**
	 * 统计上报session数组清除
	 * @return array
	 */
	function clearStat(){
		$this->setInfo( 'STAT', array() );
		return array();
	}
	
	/**
	 * 检查统计上报session数组的正确性，并返回统计上报数组session
	 * @return array
	 */
	function _checkStat(){
		$statInfo = $this->getInfo('STAT');
		if( empty( $statInfo ) || !is_array($statInfo) || count($statInfo) > 50 ){
			$statInfo = array();
			$this->setInfo( 'STAT', $statInfo );
		}
		return $statInfo;
		
	}
	
	/**
	 * 用session记录referer
	 */
	function logReferer(){
		if( isset($_SERVER['HTTP_REFERER']) 
				&& false === strpos($_SERVER['HTTP_REFERER'], 'xwb.php') 
				&& strlen($_SERVER['HTTP_REFERER']) < 250
				&& 0 < preg_match("/^(http:|https:)\/\/[a-zA-Z0-9\/\\\\@:%_+.~#*?&=\-]+$/", $_SERVER['HTTP_REFERER'])
				/*&& @parse_url($_SERVER['HTTP_REFERER']) != false*/ ){
			$this->setInfo('referer',	(string)$_SERVER['HTTP_REFERER']);
		}
	}
	
	
}
?>