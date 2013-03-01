<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_custom_usefullinks.php 31559 2012-09-10 03:23:40Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_custom_usefullinks extends discuz_table
{
	public function __construct() {

		$this->_table = 'custom_usefullinks';
		$this->_pk    = 'uid';

		parent::__construct();
	}

	public function fetch_groups(){
		return DB::fetch_all("SELECT * FROM %t WHERE linktype=%d", array($this->_table, 1));
	}

	public function fetch_links_by_groups($groupid){
		return DB::fetch_all("SELECT * FROM %t WHERE linktype=%d AND groupid=%d ORDER BY displayorder ASC", array($this->_table, 0, $groupid));
	}
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
}






?>