<?php
/**
 * 模块：认证
 * @author xionghui<xionghui1@staff.sina.com.cn>
 * @since 2010-06-08
 * @copyright Xweibo (C)1996-2099 SINA Inc.
 * @version $Id: xwbAuth.mod.php 809 2011-05-31 02:38:26Z yaoying $
 *
 */
class xwbAuth {
	function xwbAuth (){
	}
	
	function default_action(){$this->login();}
	
	
	function login(){
		if( !XWB_plugin::pCfg('is_account_binding') ){
			XWB_plugin::showError('网站管理员关闭了插件功能“新浪微博绑定”。请稍后再试。');
		}
		
		$aurl = $this->_getOAuthUrl();//echo $aurl;exit;
		$sess = XWB_plugin::getUser();
		//$sess->clearToken();
		$sess->setInfo('waiting_site_bind',	1);
		$sess->logReferer();
		
		XWB_plugin::redirect($aurl, 3);
	}
	
	/// 测试方法
	function show(){
		exit('DEBUG IS DISABLED');
	}
	
	// 从OAUTH登录时的回调模块
	function authCallBack(){
		if( !XWB_plugin::pCfg('is_account_binding') ){
			XWB_plugin::showError('网站管理员关闭了插件功能“新浪微博绑定”。请稍后再试。');
		}
		
		//--------------------------------------------------------------------
        global $_G;
		$sess = XWB_plugin::getUser();
		$waiting_site_bind = $sess->getInfo('waiting_site_bind');
		if (empty($waiting_site_bind)){
			//XWB_plugin::deny();
			$siteUrl = XWB_plugin::siteUrl(0);
			XWB_plugin::redirect($siteUrl, 3);
		}
		
		$sess->setOAuthKey(array(),true);
		//--------------------------------------------------------------------
		$wbApi		= XWB_plugin::getWB();
		$db			= XWB_plugin::getDB();
		$last_key	= $wbApi->getAccessToken(XWB_plugin::V('r:oauth_verifier')) ;
		
		//print_r($last_key);
		if( !isset($last_key['oauth_token']) || !isset($last_key['oauth_token_secret']) ){
			$api_error_origin = isset($last_key['error']) ? $last_key['error'] : 'UNKNOWN ERROR. MAYBE SERVER CAN NOT CONNECT TO SINA API SERVER';
			$api_error = ( isset($last_key['error_CN']) && !empty($last_key['error_CN']) && 'null' != $last_key['error_CN'] ) ? $last_key['error_CN'] : '';
			
			XWB_plugin::LOG("[WEIBO CLASS]\t[ERROR]\t#{$wbApi->req_error_count}\t{$api_error}\t{$wbApi->last_req_url}\tERROR ARRAY:\r\n".print_r($last_key, 1));
			XWB_plugin::showError("服务器获取Access Token失败；请稍候再试。<br />错误原因：{$api_error}[{$api_error_origin}]");
		}
		
		$sess->setOAuthKey($last_key, true);
		$wbApi->setConfig();
		$uInfo = $wbApi->verifyCredentials();
		$sess->setInfo('sina_uid', $uInfo['id']);
		$sess->setInfo('sina_name', $uInfo['screen_name']);
		//print_r($uInfo);
		//--------------------------------------------------------------------
		/// 此帐号是否已经在当前站点中绑定
		$sinaHasBinded = false;
		$stat_is_bind_type = 0; 
		if(defined('XWB_S_UID') &&  XWB_S_UID > 0){
			$bInfo = XWB_plugin::getBUById(XWB_S_UID, $uInfo['id']);
		}else{
			$bInfo = XWB_plugin::getBindUser($uInfo['id'], 'sina_uid'); //远程API
		}
        if( !is_array($bInfo) && (defined('XWB_S_UID') &&  XWB_S_UID > 0)){
        	$bInfo = XWB_plugin::getBindUser(XWB_S_UID, 'site_uid'); //登录状态下再查一次API，确保没有绑定
        }
		if ( !empty($bInfo) && is_array($bInfo) ){
			$sinaHasBinded = true;
			dsetcookie($this->_getBindCookiesName($bInfo['uid']) , (string)$bInfo['sina_uid'], 604800);
			//核查存储的access token是否有更新，有更新则进行自动更新
			if( $bInfo['sina_uid'] == $uInfo['id'] && ($bInfo['token'] != $last_key['oauth_token'] || $bInfo['tsecret'] != $last_key['oauth_token_secret']) ){
                XWB_plugin::updateBindUser($bInfo['uid'], $bInfo['sina_uid'], (string)$last_key['oauth_token'], (string)$last_key['oauth_token_secret'], $uInfo['screen_name']); //远程API
            }
		}
		
		//--------------------------------------------------------------------
		/// 决定在首页中显示什么浮层
		$tipsType = '';
		//xwb_tips_type
		//已在论坛登录
		
		if (defined('XWB_S_UID') &&  XWB_S_UID ){
			if ($sinaHasBinded){
				//$sinaHasBinded为true时，$bInfo必定存在
				if(XWB_S_UID != $bInfo['uid'] || $bInfo['sina_uid'] != $uInfo['id']){
					$tipsType = 'hasBinded';
					$sess->clearToken();
				}else{
					$tipsType = 'autoLogin';
				}
				
			}else{
                 //远程API
				$rst = XWB_plugin::addBindUser(XWB_S_UID, $uInfo['id'], (string)$last_key['oauth_token'], (string)$last_key['oauth_token_secret'], $uInfo['screen_name']);
				
				if(!$rst){echo "DB ERROR";exit;return false;}
				$tipsType = 'bind';
				dsetcookie($this->_getBindCookiesName(XWB_S_UID) , (string)$uInfo['id'], 604800);
				
				//正向绑定统计上报
				$sess->appendStat('bind', array( 'uid' => $uInfo['id'], 'type' => 1 ));
				
			}
			
		}else{
			//从 wb 登录后 检查用户是否绑定，如果绑定了 则在附属站点自
			if ($sinaHasBinded){
				require_once XWB_P_ROOT. '/lib/xwbSite.inc.php';
				$result = xwb_setSiteUserLogin((int)$bInfo['uid']);
				if( false == $result ){
					dsetcookie($this->_getBindCookiesName($bInfo['uid']) , -1, 604800);
                    XWB_plugin::delBindUser($bInfo['uid']); //远程API
					$tipsType = 'siteuserNotExist';
				}else{
					$stat_is_bind_type = 1;
					$tipsType = 'autoLogin';
				}
				
			}else{
				//已登录WB，没有附属站点的帐号 引导注册
				$sess->setInfo('waiting_site_reg', '1');
				$tipsType = 'reg';
			}
		}
		//--------------------------------------------------------------------
		
		//bind的页面需要跳转，故需要使用cookies记录
		if( $tipsType == 'bind' ){
			dsetcookie('xwb_tips_type', $tipsType, 0);
		}
		//$sess->setInfo('xwb_tips_type', $tipsType);
		$sess->setInfo('waiting_site_bind',	0);
		
		//使用sina微博帐号登录成功（不管是否绑定）统计上报
		$sess->appendStat('login', array( 'uid' => $uInfo['id'], 'is_bind' => $stat_is_bind_type ));
		
		//所有跟站点相关的对接，必须放到_showBinging
		$this->_showBinging( $tipsType );
		
	}
	
	
	/**
	 * 根据在首页中显示的浮层显示对应的操作（内部函数，被authCallback最后调用）
	 * 所有跟站点相关的对接，必须放到_showBinging
	 * @param string $tipsType 类型
	 * @uses showmessage（dz函数）
	 */
	function _showBinging( $tipsType ){
		
		global $_G;
		$sess = XWB_plugin::getUser();
		$referer = $sess->getInfo('referer');
		if( empty($referer) ){
			$referer = 'index.php';
		}
		
		//用于启动浮层
		$GLOBALS['xwb_tips_type'] = $tipsType;
		
		//不完美解决方案
		if( 0 != $_G['config']['output']['forceheader'] && 'UTF8' != XWB_S_CHARSET ){
			@header("Content-type: text/html; charset=".$_G['config']['output']['charset'] );
		}
		
		if( 'autoLogin' == $tipsType ){
			$_G['cookie'][$this->_getBindCookiesName((int)$_G['uid'])] = 99999;  //仅为了不显示绑定按钮
			$_G['username'] = empty($_G['username']) ? 'SinaAPIUser': $_G['username'];
		    if ($_G['setting']['allowsynlogin'] && 0 < $_G['uid'])
            {
                loaducenter();
                $ucsynlogin = $_G['setting']['allowsynlogin'] ? uc_user_synlogin($_G['uid']) : '';
                $param = array('username' => $_G['username'], 'uid' => $_G['uid'], 'usergroup' => '');
                showmessage('login_succeed', $referer, $param, array('showdialog' => 1, 'locationtime' => true, 'extrajs' => $ucsynlogin));
            }
            else
            {
                showmessage('login_succeed', $referer, array('username' => $_G['username'], 'uid' => 0, 'usergroup' => ''));
            }
            
		}elseif( 'siteuserNotExist' == $tipsType ){
			showmessage( XWB_plugin::L('xwb_site_user_not_exist'),'', array(), array(), 1 );
			
		}elseif( 'reg' == $tipsType ){
			showmessage(XWB_plugin::L('xwb_process_binding', 'openReg4dx' ), null, array(), array(), 1 );
			
		}elseif( 'hasBinded' == $tipsType ){
			showmessage(XWB_plugin::L('xwb_process_binding', 'hasBind' ), null, array(), array(), 1 );
		
		//直接跳转到bind页面
		}else{
			if(version_compare(XWB_S_VERSION, '2', '>=')){
				$pluginid = 'sina_xweibo_x2';
			}else{
				$pluginid = 'sina_xweibo';
			}
			XWB_plugin::redirect(XWB_plugin::siteUrl(0).'home.php?mod=spacecp&ac=plugin&id='. $pluginid. ':home_binding', 3);
		}
	}
	
	
	/// 获取 OAUTH 认证URL
	function _getOAuthUrl(){
		static $aurl = null;
		if (!empty($aurl)) {return $aurl; }
		
		$sess = XWB_plugin::getUser();
		$sess->clearToken();
		
		$wbApi = XWB_plugin::getWB();
		$keys = $wbApi->getRequestToken();
		
		if( !isset($keys['oauth_token']) || !isset($keys['oauth_token_secret']) ){
			$api_error_origin = isset($keys['error']) ? $keys['error'] : 'UNKNOWN ERROR. MAYBE SERVER CAN NOT CONNECT TO SINA API SERVER';
			$api_error = ( isset($keys['error_CN']) && !empty($keys['error_CN']) && 'null' != $keys['error_CN'] ) ? $keys['error_CN'] : '';
			
			XWB_plugin::LOG("[WEIBO CLASS]\t[ERROR]\t#{$wbApi->req_error_count}\t{$api_error}\t{$wbApi->last_req_url}\tERROR ARRAY:\r\n".print_r($keys, 1));
			XWB_plugin::showError("服务器获取Request Token失败；请稍候再试。<br />错误原因：{$api_error}[{$api_error_origin}]");
		}
		
		//print_r($keys);
		$aurl = $wbApi->getAuthorizeURL($keys['oauth_token'] ,false , XWB_plugin::getEntryURL('xwbAuth.authCallBack'));
		$sess->setOAuthKey($keys, false);
		return rtrim($aurl, '&');
	}
	
	/**
	 * 获取Bind cookies名称
	 * @param integer $uid
	 * @return string
	 */
	function _getBindCookiesName($uid){
		return 'sina_bind_'. $uid;
	}
	
}
?>
