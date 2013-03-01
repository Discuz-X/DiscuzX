<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: class_tree.php 6757 2010-03-25 09:01:29Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class tree {
	var $data = array();
	var $child = array(-1 => array());
	var $layer = array(-1 => -1);
	var $parent = array();
	var $countid = 0;
	function tree() {}

	function setNode($id, $parent, $value) {

		$parent = $parent?$parent:0;

		$this->data[$id] = $value;
		$this->child[$parent][]  = $id;
		$this->parent[$id] = $parent;

		if(!isset($this->layer[$parent])) {
			$this->layer[$id] = 0;
		} else {
			$this->layer[$id] = $this->layer[$parent] + 1;
		}
	}

	function getList(&$tree, $root= 0) {
		foreach($this->child[$root] as $key => $id) {
			$tree[] = $id;
			if($this->child[$id]) $this->getList($tree, $id);
		}
	}

	function getValue($id) {
		return $this->data[$id];
	}

	function reSetLayer($id) {
		if($this->parent[$id]) {
			$this->layer[$this->countid] = $this->layer[$this->countid] + 1;
			$this->reSetLayer($this->parent[$id]);
		}
	}

	function getLayer($id, $space = false) {
		$this->layer[$id] = 0;
		$this->countid = $id;
		$this->reSetLayer($id);
		return $space?str_repeat($space, $this->layer[$id]):$this->layer[$id];
	}

	function getParent($id) {
		return $this->parent[$id];
	}

	function getParents($id) {
		while($this->parent[$id] != -1) {
			$id = $parent[$this->layer[$id]] = $this->parent[$id];
		}

		ksort($parent);
		reset($parent);

		return $parent;
	}

	function getChild($id) {
		return $this->child[$id];
	}

	function getChilds($id = 0) {
		$child = array();
		$this->getList($child, $id);

		return $child;
	}
}

?>