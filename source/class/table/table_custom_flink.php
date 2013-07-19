<?php defined('IN_DISCUZ') || die('Access Denied');

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_custom_usefullinks.php 31559 2012-09-10 03:23:40Z liulanbo $
 */


class table_custom_flink extends discuz_table {

	public function __construct() {
		$this->_table = 'vizto_flink';
		$this->_pk    = 'uid';
		parent::__construct();
	}

	public function fetch_groups() {
		return DB::fetch_all("SELECT pid,title FROM %t", array($this->_table));
	}

	public function fetch_links_by_group($groupid) {
		$result = DB::fetch_all("SELECT * FROM %t WHERE pid=%d", array($this->_table, $groupid));
		return dunserialize($result[0]['data']);
	}

	public function safe_links_by_group($groupid, array $links){

	}

	public function fetch_link_from_group_by_index($groupid, $index){
		$grouplinks = self::fetch_links_by_group($groupid);
		return $grouplinks[$index];
	}

	public function refresh_link_from_group_by_index($groupid, $index, $new){
		return;
	}
	/*
	public function fetch_link_by_uid($uid){
		return DB::fetch_first('SELECT * FROM %t WHERE uid=%d', array($this->_table, $uid));
	}
	public function update_by_uid($uid, $data){
		DB::update($this->_table, $data, DB::field('uid', $uid));
	}
	public function pickup_a_groupid(){
		$tmp = array();
		$tmp = $this->fetch_link_by_uid(6);
		if( !strpos($tmp['url'],',') ){
			$gid = $tmp['url'];
			$tmp['url'] = $gid+1;
		}else{
			$pos = strpos($tmp['url'],',');
			$gid = substr($tmp['url'], 0, $pos);
			$tmp['url'] = substr($tmp['url'], $pos+1);
		}
		DB::update($this->_table, $tmp, DB::field('uid', 6));
		return $gid;
	}
	public function release_a_groupid($groupid){
		$tmp = array();
		$tmp = DB::fetch_first('SELECT * FROM %t WHERE uid=%d', array($this->_table, 6));
		$tmp['url'] = $groupid.','.$tmp['url'];
		DB::update($this->_table, $tmp, DB::field('uid', 6));

	}
	public function delete_by_uid($uid) {
		return DB::delete($this->_table, DB::field('uid', $uid));
	}
	public function delete_by_groupid($groupid){
		return DB::delete($this->_table, DB::field('groupid', $groupid));
	}
	*/
}


?>