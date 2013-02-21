<?php
/**
 * 普通用户校验（For DX）
 * 请保证传参所用字符集和论坛字符集一致，否则请先自行转换再传参
 * 返回值有两个array( 0 => UID, 1 => ADMINID )
 * 第一个数组下标（$return[0]）若大于0，则表示验证成功的登录uid。否则为错误信息：
 *  -1:UC用户不存在，或者被删除
 *  -2:密码错
 *  -3:安全提问错
 *  -4:用户没有在dx注册
 * 第二个数组下标（$return[1]）若大于等于0，则表示验证成功的adminid；
 * 否则为-1，表示验证失败
 * @author yaoying
 * @version $Id: siteUserVerifier.class.php 836 2011-06-15 01:48:00Z yaoying $
 */
class siteUserVerifier{

	var $db;

	function siteUserVerifier(){
		$this->db = XWB_plugin::getDB();
	}

	/**
	 * 进行身份验证
	 * 请保证传参所用字符集和论坛字符集一致，否则请先自行转换再传参
	 * @param string $username
	 * @param string $password
	 * @param int $questionid
	 * @param string $answer
	 * @param boolen $isuid 使用UID验证么？
	 * @return array
	 *    第一个数组下标（$return[0]）若大于0，则表示验证成功的登录uid。否则为错误信息：
	 *   	 -1:UC用户不存在，或者被删除
	 *    	 -2:密码错
	 *   	 -3:安全提问错
	 *   	 -4:用户没有在dz注册
	 *    第二个数组下标（$return[1]）若大于等于0，则表示验证成功的adminid；
	 *   	 否则为-1，表示验证失败
	 */
	function verify( $username, $password, $questionid = '', $answer = '',$isuid = 0 ){

		$return = array( 0 => -1, 1 => -1);

        loaducenter();
		$ucresult = uc_user_login ($username, $password, $isuid, 1, $questionid, $answer );
		if( $ucresult[0] < 1 ){
			$return[0] = $ucresult[0];

		}else{
			$uid = (int)$ucresult[0];
			$member =$this->db->fetch_first("SELECT uid, username, adminid
												FROM ". DB::table('common_member'). " 
												WHERE uid='{$uid}'");

			if( !$member ){
				$return[0] = -4;
			}else{
				$return[0] = (int)$member['uid'];
				$return[1] = (int)$member['adminid'];
			}

		}

		return $return;

	}


}