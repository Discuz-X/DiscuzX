<?php
/**
 * 用户的绑定设置信息
 * @author xionghui<xionghui1@staff.sina.com.cn>
 * @since 2010-06-08
 * @copyright Xweibo (C)1996-2099 SINA Inc.
 * @version $Id: xwbUserProfile.class.php 836 2011-06-15 01:48:00Z yaoying $
 *
 */
class xwbUserProfile{
	var $uid, $db;
	var $info = null;
	var $profile = null;
	
	function xwbUserProfile() {
		$this->uid = XWB_S_UID;
		$this->db = XWB_plugin::getDB();
	}

	function get($index = null, $default = null) {
		if( null === $this->profile ){
			$sql = 'SELECT `profile` FROM ' . DB::table('xwb_bind_info') . ' WHERE uid=' . $this->uid;
			$res = $this->db->fetch_first ( $sql );
			if( !isset($res['profile']) || empty($res['profile']) ){
				$this->profile = array();
			}else{
				$this->profile = @json_decode(preg_replace('#(?<=[,\{\[])\s*("\w+"):(\d{6,})(?=\s*[,\]\}])#si', '${1}:"${2}"', $res['profile']), true);
				if(!is_array($this->profile)){
					$this->profile = array();
				}
			}
		}
		
		if( $index ){
			if( isset($this->profile[$index]) ){
				return $this->profile[$index];
			}else{
				return $default;
			}
		}else{
			return $this->profile;
		}
		
	}

	function set($key, $value = null) {
		if (!is_array($key)) {
			$key = array($key => $value);
		}
		$data = $this->get();
		foreach ($key as $k=>$v) {
			$data[$k] = $v;
		}
		
		$data = json_encode($data);
		$sql = 'UPDATE ' . DB::table('xwb_bind_info') . ' SET `profile`=\'' . addslashes($data). '\' WHERE `uid`='. $this->uid;
		$this->db->query($sql);
	}
	
	function del($key) {
		if (!is_array($key)) {
			$key = array($key);
		}
		$data = $this->get();
		foreach ($key as $value) {
			if (isset($data[$value])) {
				unset($data[$value]);
			}
		}
		$data = json_encode($data);
		$sql = 'UPDATE ' . DB::table('xwb_bind_info') . ' SET `profile`=\'' . $data. '\' WHERE `uid`='. $this->uid;
		$this->db->query($sql);
	}

    function get4Tip($sina_uid, $index = null, $default = null) {
		if( $this->info === null ){
			$sql = 'SELECT `profile` FROM ' . DB::table('xwb_bind_info') . ' WHERE sina_uid=' . $sina_uid;
			$this->info = $this->db->fetch_first ( $sql );
        }

		if (empty($this->info['profile'])) {
			if ($default !== null) {
				return $default;
			}
			return array();
		}
		$object = @json_decode(preg_replace('#(?<=[,\{\[])\s*("\w+"):(\d{6,})(?=\s*[,\]\}])#si', '${1}:"${2}"', $this->info['profile']), true);
		if (!$object ) {
			if ($default !== null) {
				return $default;
			}
			return array();
		}
		if ($index) {
			if (isset($object[$index])) {
				return $object[$index];
			}
			if ($default !== null) {
				return $default;
			}
		}
		return $object;
	}

	function set4Tip($sina_uid, $key, $value = null) {
		if (!is_array($key)) {
			$key = array($key => $value);
		}
		$data = $this->get4Tip($sina_uid);
		foreach ($key as $key=>$value) {
			$data[$key] = $value;
		}
		$data = json_encode($data);
		$sql = 'UPDATE ' . DB::table('xwb_bind_info') . ' SET `profile`=\'' . addslashes($data). '\' WHERE `sina_uid`='. $sina_uid;
		$this->db->query($sql);
	}
}
