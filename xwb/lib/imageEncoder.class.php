<?php

/**
 * UCenter头像数据FLASH编码类。依赖于UCenter
 * @author zhangyang
 * @since 2010-07
 * @copyright Xweibo (C)1996-2099 SINA Inc.
 * @version $Id: imageEncoder.class.php 374 2010-12-08 05:54:41Z yaoying $
 *
 */
class imageEncoder
{
	/**
	 * 对FLASH传递的数据的解码
	 * @param string $s 解密前的字符串
	 * @return string 解密后的字符串
	 */
	function flashdata_decode($s) {
		$r = '';
		$l = strlen($s);
		for($i=0; $i<$l; $i=$i+2) {
			$k1 = ord($s[$i]) - 48;//ascii码转换成数字  0的ascii码为 48
			$k1 -= $k1 > 9 ? 7 : 0;//大写字母A的ascii码为 65 - 48 =17 假设大与 9那么为字母，转换成数字需要减 7
			$k2 = ord($s[$i+1]) - 48;
			$k2 -= $k2 > 9 ? 7 : 0;
			$r .= chr($k1 << 4 | $k2);//k1为高位，所以要向左移动4个位置
		}
		return $r;
	}
	/**
	 * 模拟UCENTER FLASH数据的编码过程
	 * @param string $s 加密前的字符串
	 * @return string 加密后的字符串
	 */
	function flashdata_encode($s){
		if( version_compare($this->_getUCVersion(), '1.0.0', '>') ){
			//UCENTER 1.5.0及以上使用自己的一套flash加密系统完成
			$_loc_2 = "";
			for($i = 0; $i < strlen($s); $i++){
				$_loc_3 = strtoupper($this -> toHexNum(ord($s[$i])));//转换成ascii码，再转换成16进制数据，然后转换成大写
				$_loc_2 .= $_loc_3;//字符串连接
			}
			return $_loc_2;
		}else{
			//UCENTER 1.0.0直接使用base64_encode完成
			return base64_encode( $s );
		}
		
	}
	/**
	 * 转换成ascii码，再转换成16进制数据,假如不足两位0补足。
	 * @param integer $param1 10进制数据
	 * return string
	 */
	function toHexNum($param1)
	{
	     return ($param1 <= 15 ? ("0" . strval(dechex($param1))) :strval(dechex($param1)));
	}
	
	/**
	 * 获取UCenter版本
	 * 由于UCenter 1.0.0的加解密方法是另外一套，所以需要进行版本获取
	 * 
	 * @return string
	 */
	function _getUCVersion()
	{
		loaducenter();
		return defined('UC_CLIENT_VERSION') ? UC_CLIENT_VERSION : UC_VERSION;
	}

}