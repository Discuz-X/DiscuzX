<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: search_trade.php 18409 2010-11-23 03:58:39Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$orderby = in_array($orderby, array('dateline', 'price', 'expiration')) ? $orderby : 'dateline';
$ascdesc = isset($ascdesc) && $ascdesc == 'asc' ? 'asc' : 'desc';

if(!empty($searchid)) {

	$page = max(1, intval($_G['gp_page']));
	$start_limit = ($page - 1) * $_G['tpp'];

	$index = DB::fetch_first("SELECT searchstring, keywords, threads, tids FROM ".DB::table('common_searchindex')." WHERE searchid='$searchid'");
	if(!$index) {
		showmessage('search_id_invalid');
	}
	$index['keywords'] = rawurlencode($index['keywords']);
	$index['searchtype'] = preg_replace("/^([a-z]+)\|.*/", "\\1", $index['searchstring']);

	$threadlist = $tradelist = array();

	$query = DB::query("SELECT * FROM ".DB::table('forum_trade')." WHERE pid IN ($index[tids]) ORDER BY $orderby $ascdesc LIMIT $start_limit, $_G[tpp]");
	while($tradethread = DB::fetch($query)) {
		$tradethread['lastupdate'] = dgmdate($tradethread['lastupdate'], 'u');
		$tradethread['lastbuyer'] = rawurlencode($tradethread['lastbuyer']);
		if($tradethread['expiration']) {
			$tradethread['expiration'] = ($tradethread['expiration'] - TIMESTAMP) / 86400;
			if($tradethread['expiration'] > 0) {
				$tradethread['expirationhour'] = floor(($tradethread['expiration'] - floor($tradethread['expiration'])) * 24);
				$tradethread['expiration'] = floor($tradethread['expiration']);
			} else {
				$tradethread['expiration'] = -1;
			}
		}
		$tradelist[] = $tradethread;
	}

	$multipage = multi($index['threads'], $_G['tpp'], $page, "forum.php?mod=search&searchid=$searchid".($orderby ? "&orderby=$orderby" : '')."&srchtype=trade&searchsubmit=yes");

	$url_forward = 'forum.php?mod=search&'.$_SERVER['QUERY_STRING'];

	include template('forum/search_trade');

} else {

	!($_G['group']['exempt'] & 2) && checklowerlimit('search');

	$srchtxt = isset($srchtxt) ? trim($srchtxt) : '';
	$srchuname = isset($srchuname) ? trim($srchuname) : '';

	$forumsarray = array();
	if(!empty($srchfid)) {
		foreach((is_array($srchfid) ? $srchfid : explode('_', $srchfid)) as $forum) {
			if($forum = intval(trim($forum))) {
				$forumsarray[] = $forum;
			}
		}
	}

	$fids = $comma = '';
	foreach($_G['cache']['forums'] as $fid => $forum) {
		if($forum['type'] != 'group' && (!$forum['viewperm'] && $_G['group']['readaccess']) || ($forum['viewperm'] && forumperm($forum['viewperm']))) {
			if(!$forumsarray || in_array($fid, $forumsarray)) {
				$fids .= "$comma'$fid'";
				$comma = ',';
			}
		}
	}

	$srchfilter = in_array($srchfilter, array('all', 'digest', 'top')) ? $srchfilter : 'all';

	$searchstring = 'trade|'.addslashes($srchtxt).'|'.intval($srchtypeid).'|'.intval($srchuid).'|'.$srchuname.'|'.addslashes($fids).'|'.intval($srchfrom).'|'.intval($before).'|'.$srchfilter;
	$searchindex = array('id' => 0, 'dateline' => '0');

	$query = DB::query("SELECT searchid, dateline,
		('".$_G['setting']['searchctrl']."'<>'0' AND ".(empty($_G['uid']) ? "useip='$_G[clientip]'" : "uid='$_G[uid]'")." AND $_G[timestamp]-dateline<".$_G['setting']['searchctrl'].") AS flood,
		(searchstring='$searchstring' AND expiration>'$_G[timestamp]') AS indexvalid
		FROM ".DB::table('common_searchindex')."
		WHERE ('".$_G['setting']['searchctrl']."'<>'0' AND ".(empty($_G['uid']) ? "useip='$_G[clientip]'" : "uid='$_G[uid]'")." AND $_G[timestamp]-dateline<".$_G['setting']['searchctrl'].") OR (searchstring='$searchstring' AND expiration>'$_G[timestamp]')
		ORDER BY flood");

	while($index = DB::fetch($query)) {
		if($index['indexvalid'] && $index['dateline'] > $searchindex['dateline']) {
			$searchindex = array('id' => $index['searchid'], 'dateline' => $index['dateline']);
			break;
		} elseif($index['flood']) {
			showmessage('search_ctrl', 'forum.php?mod=search', array('searchctrl' => $_G['setting']['searchctrl']));
		}
	}

	if($searchindex['id']) {

		$searchid = $searchindex['id'];

	} else {

		if(!$srchtxt && !$srchtypeid && !$srchuid && !$srchuname && !$srchfrom && !in_array($srchfilter, array('digest', 'top'))) {
			showmessage('search_invalid', 'forum.php?mod=search');
		} elseif(isset($srchfid) && $srchfid != 'all' && !(is_array($srchfid) && in_array('all', $srchfid)) && empty($forumsarray)) {
			showmessage('search_forum_invalid', 'forum.php?mod=search');
		} elseif(!$fids) {
			showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
		}

		if($_G['setting']['maxspm']) {
			if(DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_searchindex')." WHERE dateline>'$_G[timestamp]'-60") >= $_G['setting']['maxspm']) {
				showmessage('search_toomany', 'forum.php?mod=search', array('maxspm' => $_G['setting']['maxspm']));
			}
		}

		$digestltd = $srchfilter == 'digest' ? "t.digest>'0' AND" : '';
		$topltd = $srchfilter == 'top' ? "AND t.displayorder>'0'" : "AND t.displayorder>='0'";

		if(!empty($srchfrom) && empty($srchtxt) && empty($srchtypeid) && empty($srchuid) && empty($srchuname)) {

			$searchfrom = $before ? '<=' : '>=';
			$searchfrom .= TIMESTAMP - $srchfrom;
			$sqlsrch = "FROM ".DB::table('forum_trade')." tr INNER JOIN ".DB::table('forum_thread')." t ON tr.tid=t.tid AND $digestltd t.fid IN ($fids) $topltd WHERE tr.dateline$searchfrom";
			$expiration = TIMESTAMP + $cachelife_time;
			$keywords = '';

		} else {

			$sqlsrch = "FROM ".DB::table('forum_trade')." tr INNER JOIN ".DB::table('forum_thread')." t ON tr.tid=t.tid AND $digestltd t.fid IN ($fids) $topltd WHERE 1";

			if($srchuname) {
				$srchuid = $comma = '';
				$srchuname = str_replace('*', '%', addcslashes($srchuname, '%_'));
				$query = DB::query("SELECT uid FROM ".DB::table('common_member')." WHERE username LIKE '".str_replace('_', '\_', $srchuname)."' LIMIT 50");
				while($member = DB::fetch($query)) {
					$srchuid .= "$comma'$member[uid]'";
					$comma = ', ';
				}
				if(!$srchuid) {
					$sqlsrch .= ' AND 0';
				}
			} elseif($srchuid) {
				$srchuid = "'$srchuid'";
			}

			if($srchtypeid) {
				$srchtypeid = intval($srchtypeid);
				$sqlsrch .= " AND tr.typeid='$srchtypeid'";
			}

			if($srchtxt) {
				require_once libfile('function/search');
				$srcharr = searchkey($srchtxt, "tr.subject LIKE '%{text}%'", true);
				$srchtxt = $srcharr[0];
				$sqlsrch .= $srcharr[1];
			}

			if($srchuid) {
				$sqlsrch .= " AND tr.sellerid IN ($srchuid)";
			}

			if(!empty($srchfrom)) {
				$searchfrom = ($before ? '<=' : '>=').(TIMESTAMP - $srchfrom);
				$sqlsrch .= " AND tr.dateline$searchfrom";
			}


			$keywords = str_replace('%', '+', $srchtxt).(trim($srchuname) ? '+'.str_replace('%', '+', $srchuname) : '');
			$expiration = TIMESTAMP + $cachelife_text;

		}

		$threads = $tids = 0;
		$query = DB::query("SELECT tr.tid, tr.pid, t.closed $sqlsrch ORDER BY tr.pid DESC LIMIT ".$_G['setting']['maxsearchresults']);
		while($post = DB::fetch($query)) {
			if($thread['closed'] <= 1) {
				$tids .= ','.$post['pid'];
				$threads++;
			}
		}
		DB::free_result($query);

		DB::query("INSERT INTO ".DB::table('common_searchindex')." (keywords, searchstring, useip, uid, dateline, expiration, threads, tids)
				VALUES ('$keywords', '$searchstring', '$_G[clientip]', '$_G[uid]', '$_G[timestamp]', '$expiration', '$threads', '$tids')");
		$searchid = DB::insert_id();

		!($_G['group']['exempt'] & 2) && updatecreditbyaction('search');

	}

	showmessage('search_redirect', "forum.php?mod=search&searchid=$searchid&srchtype=trade&orderby=$orderby&ascdesc=$ascdesc&searchsubmit=yes");

}

?>