<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portalcp_article.php 7701 2010-04-12 06:01:33Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$op = in_array($_GET['op'], array('verify')) ? $_GET['op'] : 'verify';

$allowdiy = checkperm('allowdiy');
if(!$allowdiy && !$admincp4 && !$admincp6) {
	showmessage('portal_nopermission', dreferer());
}

loadcache('diytemplatename');
$blocks = $bids = $tpls = array();
$diytemplate = array();
if($allowdiy) {
	$tpls = array_keys($_G['cache']['diytemplatename']);
} else {
	$permissions = getallowdiytemplate($_G['uid']);
	foreach($permissions as $value) {
		if($value['allowmanage'] || ($value['allowrecommend'] && empty($value['needverify'])) || ($op=='recommend' && $value['allowrecommend'])) {
			$tpls[] = $value['targettplname'];
		}
	}
}
if(!$allowdiy) {
	$query = DB::query('SELECT bid FROM '.DB::table('common_block_permission')." WHERE uid='$_G[uid]' AND (allowmanage='1' OR (allowrecommend='1' AND needverify='0'))");
	while(($value=DB::fetch($query))) {
		$bids[$value['bid']] = intval($value['bid']);
	}
}

if(!$allowdiy && empty($bids)) {
	showmessage('portal_nopermission', dreferer());
}

if(submitcheck('batchsubmit')) {

	if(!in_array($_POST['optype'], array('pass', 'delete'))) {
		showmessage('select_a_option', dreferer());
	}
	$ids = $updatebids = array();
	if($_POST['ids']) {
		$query = DB::query('SELECT dataid, bid FROM '.DB::table('common_block_item_data')." WHERE dataid IN (".dimplode($_POST['ids']).')');
		while(($value=DB::fetch($query))) {
			if($allowdiy || in_array($value['bid'], $bids)) {
				$ids[$value['dataid']] = intval($value['dataid']);
				$updatebids[$value['bid']] = $value['bid'];
			}
		}
	}
	if(empty($ids)) {
		showmessage('select_a_moderate_data', dreferer());
	}

	if($_POST['optype']=='pass') {
		DB::query('UPDATE '.DB::table('common_block_item_data')." SET isverified='1', verifiedtime='$_G[timestamp]' WHERE dataid IN (".dimplode($ids).")");
		if($updatebids) {
			DB::query('UPDATE '.DB::table('common_block').' SET dateline=dateline-cachetime-1000 WHERE bid IN ('.dimplode($updatebids).') AND cachetime>0');
		}
	} elseif($_POST['optype']=='delete') {
		DB::query('DELETE FROM '.DB::table('common_block_item_data')." WHERE dataid IN (".dimplode($ids).")");
	}
	showmessage('operation_done', dreferer());
}

$theurl = 'portal.php?mod=portalcp&ac=blockdata';
$perpage = 20;
$page = max(1,intval($_GET['page']));
$start = ($page-1)*$perpage;
if($start<0) $start = 0;

if($_GET['searchkey']) {
	$_GET['searchkey'] = trim($_GET['searchkey']);
	if (preg_match('/^[#]?(\d+)$/', $_GET['searchkey'],$match)) {
		$bid = intval($match[1]);
		$bids = $allowdiy || isset($bids[$bid]) ? array($bid) : array(0);
	} else {
		$_GET['searchkey'] = stripsearchkey($_GET['searchkey']);
		if(!empty($bids)) {
			$where =  "bid IN (".dimplode($bids).") AND";
		}
		$query = DB::query('SELECT bid FROM '.DB::table('common_block')." WHERE $where name LIKE '%$_GET[searchkey]%'");
		$searchbids = array();
		while(($value=DB::fetch($query))) {
			if($allowdiy) {
				$searchbids[$value['bid']] = intval($value['bid']);
			} elseif(isset($bids[$value['bid']])) {
				$searchbids[$value['bid']] = $value['bid'];
			}
		}
		$bids = $searchbids;
	}
	$_GET['searchkey'] = dhtmlspecialchars($_GET['searchkey']);
}
$datalist = $ids = array();
$multi = $where = '';
if($bids) {
	$where =  "bid IN (".dimplode($bids).") AND";
}
$count = DB::result_first('SELECT COUNT(*) FROM '.DB::table('common_block_item_data')." WHERE $where isverified='0'");
if($count) {
	$query = DB::query('SELECT * FROM '.DB::table('common_block_item_data')." WHERE $where isverified='0' LIMIT $start, $perpage");
	while(($value=DB::fetch($query))) {
		$datalist[] = $value;
		$ids[$value['bid']] = $value['bid'];
	}
	$multi = multi($count, $perpage, $page, $theurl);
}

if($ids) {
	include_once libfile('function/block');
	$query = DB::query('SELECT b.bid, b.name as blockname, tb.targettplname FROM '.DB::table('common_block')." b LEFT JOIN ".DB::table('common_template_block')." tb ON b.bid=tb.bid WHERE b.bid IN (".dimplode($ids).")");
	while(($value=DB::fetch($query))) {
		$diyurl = block_getdiyurl($value['targettplname']);
		$value['diyurl'] = $diyurl['url'];
		$value['tplname'] = isset($_G['cache']['diytemplatename'][$value['targettplname']]) ? $_G['cache']['diytemplatename'][$value['targettplname']] : $value['targettplname'];
		$value['blockname'] = !empty($value['blockname']) ? $value['blockname'] : '#'.$value['bid'];
		$blocks[$value['bid']] = $value;
	}
}

include_once template("portal/portalcp_blockdata");

?>