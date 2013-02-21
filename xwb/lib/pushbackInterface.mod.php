<?php
/**
 * 评论回推设置和运行模块。
 * 
 * @author yaoying
 * @since 2010-12-22
 * @version $Id: pushbackInterface.mod.php 836 2011-06-15 01:48:00Z yaoying $
 *
 */
class pushbackInterface{

	/**
	 * 评论回推设置页面
	 */
	function cfgPage(){
		if (!defined('XWB_S_IS_ADMIN') || !XWB_S_IS_ADMIN){
			XWB_plugin::deny('');
		}
		
		$pushKey = strval(XWB_Plugin::pCfg('pushback_authkey'));
		if( empty($pushKey) ){
			$isOpen = 0;
		}else{
			$isOpen = 1;
		}
		
		$db = XWB_plugin::getDB();
		$query = $db->query('SELECT `cachekey`,`cachevalue` FROM '. DB::table('common_cache'). " WHERE `cachekey` IN ('xwb_pushback_nexttime', 'xwb_pushback_lasttime')");
		$res = array();
		while( $row = $db->fetch_array($query) ){
			$res[$row['cachekey']] = $row['cachevalue'];
		}
		
		$lastUpdateTime = isset($res['xwb_pushback_lasttime']) ? (int)$res['xwb_pushback_lasttime'] : 0;
		$nextUpdateTime = isset($res['xwb_pushback_nexttime']) ? (int)$res['xwb_pushback_nexttime'] : 0;
		$fromid = (float)XWB_Plugin::pCfg('pushback_fromid');
		include XWB_P_ROOT.'/tpl/plugin_cfg_pushback.tpl.php';
	}
	
	
	/**
	 * 评论回推设置：设置评论回推通讯密钥
	 */
	function doCfg4setAuthKey(){
		if (!defined('XWB_S_IS_ADMIN') || !XWB_S_IS_ADMIN || !XWB_plugin::isRequestBy('POST')){
			XWB_plugin::deny('');
		}
		$pushInstance = XWB_Plugin::O('pushbackCommunicator');
		$res = $pushInstance->getAuthKey();
		if( $res['httpcode'] != 200 || !isset($res['data']['code'])  ){
			$ret = array(0,'向评论回推服务器请求通讯密钥失败，请重试或者向Xweibo求助。');
			echo json_encode($ret);
		}else{
			//根据以前的评论回推设置，重置评论回推总开关is_pushback_open
			$is_pushback_open = $this->_checkPushbackOpenConfig();
			//注册虚拟账户
			$username = '微博评论';
			$username_site = XWB_plugin::convertEncoding('微博评论', 'UTF-8', XWB_S_CHARSET);
			$uid = $this->_setPushbackSiteAccount($username_site);
			if( $uid > 0 ){
				XWB_Plugin::setPCfg( array( 'is_pushback_open'=>$is_pushback_open, 'pushback_authkey' => strval($res['data']['code']), 'pushback_username' => $username, 'pushback_uid' => $uid,) );
			}else{
				XWB_Plugin::setPCfg( array( 'is_pushback_open'=>$is_pushback_open, 'pushback_authkey' => strval($res['data']['code']) ) );
			}
			
			//根据is_pushback_open，进行评论回推服务器定制通知
			$pushInstance->changePushbackAuthKey($res['data']['code']);
			if( 1 == $is_pushback_open ){
				$pushInstance->setPushback('comment');
			}else{
				$pushInstance->cancelPushback();
			}
			
			$ret = array(1,'开启成功！');
			echo json_encode($ret);
			
		}
	}
	
	/**
	 * 评论回推设置：设置和开启评论回推选项
	 */
	function doCfg4pushback(){
		if (!defined('XWB_S_IS_ADMIN') || !XWB_S_IS_ADMIN || !XWB_plugin::isRequestBy('POST')){
			XWB_plugin::deny('');
		}
		
		$is_pushback_open = 1;
		$pushback_to_thread = intval(XWB_plugin::V('p:pushback_to_thread'));
        $pushback_to_blog = intval(XWB_plugin::V('p:pushback_to_blog'));
        $pushback_to_doing = intval(XWB_plugin::V('p:pushback_to_doing'));
        $pushback_to_share = intval(XWB_plugin::V('p:pushback_to_share'));
		if( !$pushback_to_thread && !$pushback_to_blog && !$pushback_to_doing && !$pushback_to_share){
			$is_pushback_open = 0;
		}
		
		$res = XWB_Plugin::setPCfg(array(
            'is_pushback_open'=>$is_pushback_open,
            'pushback_to_thread'=>$pushback_to_thread,
            'pushback_to_blog'=>$pushback_to_blog,
            'pushback_to_doing'=>$pushback_to_doing,
            'pushback_to_share'=>$pushback_to_share,
        ));
		
		if( true == $res ){
			$ret = array(1,'设置保存成功。');
		}else{
			$ret = array(0,'设置保存失败，请检查配置文件app.cfg.php是否具有可写权限？');
		}
		$this->_oScript('xwbSetTips',$ret);
		
		$pushInstance = XWB_Plugin::O('pushbackCommunicator');
		if( 1 == $is_pushback_open ){
			$pushInstance->setPushback('comment');
		}else{
			$pushInstance->cancelPushback();
		}
	}
	
	/**
	 * 运行评论回推
	 */
	function forcePushback(){
		//exit('NOT ALLOW YET.');
		//error_reporting(E_ALL);
		$pushInstance = XWB_Plugin::O('pushbackDispatcher');
		if ( true == $pushInstance->prepare(false) ){
			$pushInstance->processMain();
			echo 'var xwb_pushback_success = 1;';
		}else{
			echo 'var xwb_pushback_success = 0;';
		}
		
	}
	
	
	function _oScript($func,$ret){
		echo '<script>';
		echo "parent.".$func."(".json_encode($ret).");";
		echo '</script>';
	}
	
	
	/**
	 * 设置评论回推用户
	 * @param string $username 用户名，请传参前自行转码到论坛用户的编码
	 * @return int 用户uid
	 */
	function _setPushbackSiteAccount($username){
		loaducenter();
		$userInfo = uc_get_user($username);
		if( is_array($userInfo) && $userInfo[0] > 0 ){
			$this->_importUserFromUC($userInfo);
			return $userInfo[0];
		}
		
		$email = 'xweibo_user'. rand(1,99999). '@sina.com';
		$siteRegister = XWB_plugin::O('xwbSiteUserRegister');
		$uid = $siteRegister->reg($username, $email);
		return $uid > 0 ? $uid : 0;
	}
	
	/**
	 * 检查is_pushback_open设置的真实性
	 * @return integer
	 */
	function _checkPushbackOpenConfig(){
		$is_pushback_open = 1;
		$config = XWB_Plugin::pCfg();
		if( !$config['pushback_to_thread'] && !$config['pushback_to_blog'] && !$config['pushback_to_doing'] && !$config['pushback_to_share']){
			$is_pushback_open = 0;
		}
		return $is_pushback_open;
	}
	
	
	/**
	 * 将用户帐号导入（主要应对用了UC的多论坛）
	 * 本函数主要供_setPushbackSiteAccount方法使用
	 * @param array $userInfo uc_get_user返回的数据
	 */
	function _importUserFromUC($userInfo){
		$uid = (int)$userInfo[0];
		
		$db = XWB_plugin::getDB();
		$exist_uid = intval($db->result_first("SELECT uid FROM ". DB::table('common_member'). " WHERE uid='{$userInfo[0]}' LIMIT 0,1 "));
		if($exist_uid > 0){
			return true;
		}
		
		$username = mysql_real_escape_string($userInfo[1]);
		$email = mysql_real_escape_string($userInfo[2]);
		$password = md5( rand(1,10000) );
		
		$db->query("INSERT IGNORE INTO ". DB::table('common_member'). " (uid, username, password, adminid, groupid, email)
			VALUES ('{$uid}', '{$username}', '{$password}', '0', '10', '{$email}')");
		$db->query("INSERT IGNORE INTO ". DB::table('common_member_status'). " (uid)
			VALUES ('$uid')");
		$db->query("INSERT IGNORE INTO ". DB::table('common_member_profile'). " (uid)
			VALUES ('$uid')");
		$db->query("INSERT IGNORE INTO ". DB::table('common_member_field_forum'). " (uid)
			VALUES ('$uid')");
		$db->query("INSERT IGNORE INTO ". DB::table('common_member_field_home'). " (uid)
			VALUES ('$uid')");
		$db->query("INSERT IGNORE INTO ". DB::table('common_member_count'). " (uid)
			VALUES ('$uid')");
		manyoulog ( 'user', $this->uid, 'add' );
		return true;
	}
	
}