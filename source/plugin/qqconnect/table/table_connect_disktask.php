
<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_connect_disktask.php 29265 2012-03-31 06:03:26Z yexinhao $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_connect_disktask extends discuz_table {

	public function __construct() {
		$this->_table = 'connect_disktask';
		$this->_pk = 'taskid';

		parent::__construct();
	}

	public function delete_by_status($status) {
		if (dintval($status)) {
			return DB::query('DELETE FROM %t WHERE status = %d', array($this->_table, $status));
		}
	}
}
