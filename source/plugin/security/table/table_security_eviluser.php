<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_security_eviluser.php 33076 2013-04-18 06:22:07Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_security_eviluser extends discuz_table {

	public function __construct() {
		$this->_table = 'security_eviluser';
		$this->_pk = 'uid';

		parent::__construct();
	}

	public function fetch_all_report($limit = 20) {
		return DB::fetch_all("SELECT * FROM %t WHERE isreported = 0 AND operateresult > 0 LIMIT %d", array($this->_table, $limit));
	}

	public function range_by_operateresult($operateresult, $start, $limit) {

		return DB::fetch_all('SELECT * FROM %t WHERE ' . DB::field('operateresult', $operateresult) . ' ' . DB::limit($start, $limit), array($this->_table), $this->_pk);
	}

	public function fetch_range($start, $perPage = '20', $orderBy = 'createtime') {

		$orderSql = " ORDER BY $orderBy DESC ";
		$limitSql = DB::limit($start, $perPage);

		return DB::fetch_all("SELECT * FROM %t $orderSql $limitSql", array($this->_table));
	}

}