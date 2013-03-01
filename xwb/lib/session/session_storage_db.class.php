<?php

/**
 * session_storage_db类，参考cmstop、phpcms、CI部分写法。
 * 建表语句（pre_和常量XWB_S_TBPRE等同；xwb_session和本类属性$_sessionTable等同）：
 * <pre>
 * CREATE TABLE `pre_xwb_session` (
 *   `sessionid` char(32) NOT NULL default '',
 *   `lasttime` int(10) unsigned NOT NULL default '0',
 *   `data` text NOT NULL,
 *   UNIQUE KEY `sessionid` (`sessionid`),
 *   KEY `lasttime` (`lasttime`)
 * ) ENGINE=MyISAM;
 * </pre>
 * @author yaoying<yaoying@staff.sina.com.cn>
 * @since 2010-12-02
 * @version $Id: session_storage_db.class.php 836 2011-06-15 01:48:00Z yaoying $
 */
class session_storage_db{

	var $_gcRunned = false;
	
	var $_db = null;
	
	var $_sessionTable = 'xwb_session';
	
	/**
	 * 构造函数
	 */
	function session_storage_db(){
		$this->_db = XWB_plugin::getDB();
	}
	
	
	/**
	 * 打开一个session
	 * @param string $save_path
	 * @param string $session_name
	 */
	function open($save_path, $session_name){
		return true;
	}
	
	/**
	 * 关闭一个session
	 * php mannual的matt at openflows dot org (20-Sep-2006 08:02)提到
	 * Debian和Ubuntu发行版不会调用gc，需要手工触发。
	 * BUT IS THAT TRUE?
	 * 
	 */
	function close(){
		//兼容性导致不能用mt_rand，但rand的不安全性，只能增大一个“随机”干扰，虽然天知道有没有作用。
		$extentBase = rand(1,100);
		$hitNum = rand(1,100);
		if( $hitNum >= $extentBase - 1 && $hitNum <= $extentBase + 1 ){
			$this->gc();
		}
		
	}
	
	
	/**
	 * 读取一个session
	 * @param string $id session id
	 * @return string  session数据。按原样传出字符串
	 */
	function read($id){
		$id = mysql_real_escape_string($id);
		
		$result = $this->_db->result_first( 'SELECT `data` FROM '. DB::table($this->_sessionTable). 
												" WHERE `sessionid` = '{$id}' " 
											);
		return (string)$result;
	}
	
	
	/**
	 * 写入一个session
	 * @param string $id session id
	 * @param string $sess_data session数据。为兼容php自身行为，请保证传入的是字符串
	 */
	function write($id, $sess_data){
		$id = mysql_real_escape_string($id);
		$sess_data = mysql_real_escape_string($sess_data);
		$lasttime = time();
		
		$this->_db->query('REPLACE INTO '. DB::table($this->_sessionTable). 
										' (`sessionid`, `lasttime`, `data`) '.
										"VALUES ( '{$id}', '{$lasttime}', '{$sess_data}' )"
							);
	}
	
	
	/**
	 * 销毁一个session
	 * @param string $id session id
	 */
	function destroy($id){
		$id = mysql_real_escape_string($id);
		$this->_db->query('DELETE FROM '. DB::table($this->_sessionTable). 
							" WHERE `sessionid` = '{$id}' "
							);
	}
	
	
	/**
	 * session回收
	 * @param integer $maxlifetime session存活时间，默认为1400秒
	 */
	function gc($maxlifetime = 1400){
		//防止多次运行gc引发效率下降
		if( true == $this->_gcRunned ){
			return false;
		}
		$expiretime = intval(time() - $maxlifetime);
		$this->_db->query('DELETE FROM '. DB::table($this->_sessionTable). 
							" WHERE `lasttime` < '{$expiretime}' "
							);
		$this->_gcRunned = true;
		return true;
	}
	

}