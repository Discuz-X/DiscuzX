<?php

/**
 * 在附属站点注册新用户（根据代码member_register进行重写和类封装）
 * For Discuz!X 1.5
 * @author yaoying<yaoying@staff.sina.com.cn>
 * @copyright Xweibo (C)1996-2099 SINA Inc.
 * $Id: xwbSiteUserRegister.class.php 836 2011-06-15 01:48:00Z yaoying $
 *
 */
class xwbSiteUserRegister{
	
	var $uid = -999;
	var $username = '';
	var $password = '';
	var $email = '';
	var $question = '';
	var $answer = '';
	var $groupid = -999;
	var $ip;
	var $timestamp = 0;
	
	var $db;
	
	/**
	 * 24 小时注册尝试次数限制（regfloodctrl）专用判断属性，系统在运行时自动调整
	 * -1：系统没有使用regfloodctrl
	 * 0：系统使用了regfloodctrl，但本ip暂没有记录，需要准备记录
	 * 大于0：系统使用了regfloodctrl，数值为本ip的记录次数
	 *
	 * @var integer
	 */
	var $regfloodcount = -1;
	
	
	/**
	 * 资源初始化
	 * @access public
	 * @return xwbSiteUserRegister
	 */
	function xwbSiteUserRegister(){
		global $_G;
		loaducenter();
		$this->db = XWB_plugin::getDB();
		$this->ip = (string)$_G ['clientip'];
		$this->timestamp = TIMESTAMP;
	}
	
	
	/**
	 * 注册一个新帐户
	 * @access public
	 * @param string $name 和论坛编码相符合的用户名
	 * @param string $email 和论坛编码相符合的Email
	 * @param mixed $pwd
	 * @return integer 
	 */
	function reg( $name, $email, $pwd= false ){
		$this->username = mysql_escape_string(trim($name));
		$this->email = mysql_escape_string(trim($email));
		$this->password = $pwd ? mysql_escape_string($pwd) : rand(100000,999999);
		
		$checkRegCTRL = $this->_checkRegCTR();
		if( $checkRegCTRL < 0 ){
			return $checkRegCTRL;
		}
		
		$this->_initGroupid();
		$this->_regToUCDZX();
		if( $this->uid > 0 ){
			$this->_updateRegCTR();
			$this->_updateSiteUserCount();
			$this->_updateUserRegVerify();
		}
		
		return $this->uid;
		
	}
	
	/**
	 * 在UC和DZX进行用户初始化注册
	 * @access protected
	 * @return boolen
	 */
	function _regToUCDZX(){
		global $_G;
		$this->uid = (int)uc_user_register($this->username, $this->password, $this->email, $this->questionid, $this->answer);
		if ($this->uid>0){
			//在有UC的情况下，附属论坛的members表password列并不存储真实密码，只是用于cookies登陆状态校样。
			$init_arr = explode ( ',', $_G ['setting'] ['initcredits'] );
			$userdata = array (
				'uid' => $this->uid, 
				'username' => $this->username, 
				'password' => md5(rand(100000,999999)), 
				'email' => $this->email, 
				'adminid' => 0, 
				'groupid' => $this->groupid, 
				'regdate' => $this->timestamp, 
				'credits' => $init_arr [0], 
				'timeoffset' => 9999 
			);
			DB::insert ( 'common_member', $userdata );
			$status_data = array (
				'uid' => $this->uid, 
				'regip' => $this->ip, 
				'lastip' => $this->ip, 
				'lastvisit' => $this->timestamp, 
				'lastactivity' => $this->timestamp, 
				'lastpost' => 0, 
				'lastsendmail' => 0 
			);
			DB::insert ( 'common_member_status', $status_data );
			$profile ['uid'] = $this->uid;
			DB::insert ( 'common_member_profile', $profile );
			DB::insert ( 'common_member_field_forum', array ('uid' => $this->uid ) );
			DB::insert ( 'common_member_field_home', array ('uid' => $this->uid ) );
			//初始化积分
			$count_data = array (
				'uid' => $this->uid, 
				'extcredits1' => $init_arr [1], 
				'extcredits2' => $init_arr [2], 
				'extcredits3' => $init_arr [3], 
				'extcredits4' => $init_arr [4], 
				'extcredits5' => $init_arr [5], 
				'extcredits6' => $init_arr [6], 
				'extcredits7' => $init_arr [7], 
				'extcredits8' => $init_arr [8] 
			);
			DB::insert ( 'common_member_count', $count_data );
			DB::insert ( 'common_setting', array ('skey' => 'lastmember', 'svalue' => $this->username ), false, true );
			manyoulog ( 'user', $this->uid, 'add' );
			
			return true;
		}else{
			return false;
		}
	}
	
	
	/**
	 * 更新用户数
	 * @access protected
	 * @return bool
	 */
	function _updateSiteUserCount(){
		global $_G;
		if( $this->uid < 0 ){
			return false;
		}
		
		//更新最新注册
		$totalmembers = $this->db->result_first("SELECT COUNT(*) FROM ". DB::table('common_member'));
		$userstats = array('totalmembers' => $totalmembers, 'newsetuser' => $this->username);
		save_syscache('userstats', $userstats);
		
        //更新最新注册（用户名）
        loadcache('setting', true);
		$_G['setting']['lastmember'] = $this->username;
		save_syscache('setting', $_G['setting']);
		
		return true;
	}
	
	
	/**
	 * 根据附属站点注册控制设置进行对应更新操作
	 * @access protected
	 * @return bool
	 */
	function _updateRegCTR(){
		global $_G;
		if( $this->uid < 0 ){
			return false;
		}
		
		//ip控制更新
		if( $this->regfloodcount == 0 ){
			$this->db->query("INSERT INTO ". DB::table('common_regip'). " (ip, count, dateline)
				VALUES ('{$this->ip}', '1', '{$this->timestamp}')");
		}elseif( $this->regfloodcount > 0 ){
			$this->db->query("UPDATE ". DB::table('common_regip'). " SET count=count+1 WHERE ip='{$this->ip}' AND count>'0'");
		}
		if ($_G ['setting'] ['regctrl'] || $_G ['setting'] ['regfloodctrl']) {
			$this->db->query( "DELETE FROM ". DB::table('common_regip'). " WHERE dateline<='{$this->timestamp}'-" . ($_G ['setting'] ['regctrl'] > 72 ? $_G ['setting'] ['regctrl'] : 72) . "*3600", 'UNBUFFERED' );
			if ($_G ['setting'] ['regctrl']) {
				$this->db->query( "INSERT INTO ". DB::table('common_regip'). " (ip, count, dateline)
				    VALUES ('{$this->ip}', '-1', '{$this->timestamp}')" );
			}
		}

		
	}
	
	
	/**
	 * 更新用户的注册审核信息（包括发邮件）
	 * @access protected
	 * @return bool
	 */
	function _updateUserRegVerify(){
		global $_G;
		if( $this->uid < 0 ){
			return false;
		}
		
		//人工审核
		if( $_G['setting']['regverify'] == 2  || $_G['setting']['regverify'] == 3 ) {
			$regmessage = 'SINA_WEIBO_API_REGISTER';
			$this->db->query("REPLACE INTO ". DB::table('common_member_validate'). " (uid, submitdate, moddate, admin, submittimes, status, message, remark)
				VALUES ('{$this->uid}', '{$this->timestamp}', '0', '', '1', '0', '$regmessage', '')");
		
		//EMAIL验证
		}elseif( $_G['setting']['regverify'] == 1 ){
			
			if(!function_exists('sendmail')) {
				include libfile('function/mail');
			}
			
			$idstring = random(6);
			$authstr = $_G['setting']['regverify'] == 1 ? "{$this->timestamp}\t2\t{$idstring}" : '';
			$this->db->query("UPDATE ". DB::table('common_member_field_forum'). " SET authstr='$authstr' WHERE uid='{$this->uid}'");
			$verifyurl = "{$_G['siteurl']}member.php?mod=activate&amp;uid={$this->uid}&amp;id={$idstring}";
			$email_verify_message = lang('email', 'email_verify_message', array(
				'username' => $this->username,
				'bbname' => $_G['setting']['bbname'],
				'siteurl' => $_G['siteurl'],
				'url' => $verifyurl
			));
			sendmail("$this->username <$this->email>", lang('email', 'email_verify_subject'), $email_verify_message);
		}
		
		return true;
	}
	
	/**
	 * 初始化注册用户组
	 * @access protected
	 */
	function _initGroupid(){
		global $_G;
		if($_G['setting']['regverify']) {
			$this->groupid = 8;
		} else {
			$this->groupid = $_G['setting']['newusergroupid'];
		}
	}
	
	
	/**
	 * 根据附属站点注册控制设置进行控制性检查
	 * @access protected
	 * @return integer
	 */
	function _checkRegCTR(){
		$checkRegIP = $this->_checkRegIP();
		if (  $checkRegIP < 0 ){
			return $checkRegIP;
		}
		$checkUsername = $this->_checkUsername();
		if (  $checkUsername < 0 ){
			return $checkUsername;
		}
		return 0;
	}
	
	
	/**
	 * 检查用户名是否正确
	 * @access protected
	 * @return integer 0：正常；-3：与论坛设置不相符
	 */
	function _checkUsername(){
		global $_G;
		$censorexp = '/^('.str_replace(array('\\*', "\r\n", ' '), array('.*', '|', ''), preg_quote(($_G['setting']['censoruser'] = trim($_G['setting']['censoruser'])), '/')).')$/i';

		if($_G['setting']['censoruser'] && @preg_match($censorexp, $this->username)) {
			return -3;
		}else{
			return 0;
		}
	}
	
	
	/**
	 * 检查ip是否允许注册
	 * @access protected
	 * @return integer 检查结果
	 * 0：正常
	 * -1001：regfloodctrl 24 小时注册尝试次数限制
	 * -1002：regctrl IP 注册间隔限制(小时)
	 * -1003：ipregctrl 特殊 IP 注册限制（每 72 小时将至多只允许注册一个帐号）
	 */
	function _checkRegIP(){
		global $_G;
		if ($_G ['setting'] ['regverify']) {
			if ($_G ['setting'] ['areaverifywhite']) {
				$location = $whitearea = '';
				require_once libfile ( 'function/misc' );
				$location = trim ( convertip ( $this->ip, "./" ) );
				if ($location) {
					$whitearea = preg_quote ( trim ( $_G ['setting'] ['areaverifywhite'] ), '/' );
					$whitearea = str_replace ( array ("\\*" ), array ('.*' ), $whitearea );
					$whitearea = '.*' . $whitearea . '.*';
					$whitearea = '/^(' . str_replace ( array ("\r\n", ' ' ), array ('.*|.*', '' ), $whitearea ) . ')$/i';
					if (@preg_match ( $whitearea, $location )) {
						$_G ['setting'] ['regverify'] = 0;
					}
				}
			}
			
			if ($_G ['cache'] ['ipctrl'] ['ipverifywhite']) {
				foreach ( explode ( "\n", $_G ['cache'] ['ipctrl'] ['ipverifywhite'] ) as $ctrlip ) {
					if (preg_match ( "/^(" . preg_quote ( ($ctrlip = trim ( $ctrlip )), '/' ) . ")/", $this->ip )) {
						$_G ['setting'] ['regverify'] = 0;
						break;
					}
				}
			}
		}
		
		if ($_G ['cache'] ['ipctrl'] ['ipregctrl']) {
			foreach ( explode ( "\n", $_G ['cache'] ['ipctrl'] ['ipregctrl'] ) as $ctrlip ) {
				if (preg_match ( "/^(" . preg_quote ( ($ctrlip = trim ( $ctrlip )), '/' ) . ")/", $this->ip )) {
					$ctrlip = $ctrlip . '%';
					$_G ['setting'] ['regctrl'] = 72;
					break;
				} else {
					$ctrlip = $this->ip;
				}
			}
		} else {
			$ctrlip = $this->ip;
		}
		if ($_G ['setting'] ['regctrl']) {
			$result = $this->db->result_first( "SELECT ip FROM " . DB::table('common_regip'). " WHERE ip LIKE '$ctrlip' AND count='-1' AND dateline>{$this->timestamp}-'" . $_G ['setting'] ['regctrl'] . "'*3600 LIMIT 1" );
			if (!empty($result)) {
				return -1002;
			}
		}
		
		if ($_G ['setting'] ['regfloodctrl']) {
			if ($regattempts = $this->db->result_first( "SELECT count FROM " . DB::table('common_regip'). " WHERE ip='{$this->ip}' AND count>'0' AND dateline>'{{$this->timestamp}}'-86400" )) {
				if ($regattempts >= $_G ['setting'] ['regfloodctrl']) {
					return -1001;
				} else {
					$this->regfloodcount = (int)$regattempts;
				}
			} else {
				$this->regfloodcount = 0;
			}
		}
		return 0;
	}
	
}