<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_flinks.php 31327 2012-08-13 07:01:41Z liulanbo $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

if(!$operation){

	if(!submitcheck('flinkssubmit')&&!submitcheck('delflinkssubmit')) {
		shownav('sitemanager', 'menu_sitemanager_links');
		showsubmenu('menu_sitemanager_links', array(
			array('list', 'flinks', 1)
		));
		print_r('<script type="text/javascript" src="dps_asset/scripts/lib/sea.js"></script>');
		$navGroup = array();
		foreach(C::t('custom_usefullinks')->fetch_groups() as $nav) {
			$navGroup[$nav['uid']] = $nav;
		}
		$linksHTML = '';
		foreach($navGroup as $nav){
			$linksHTML .= "<dl class=\"lineheight\">"
				."<dd class=\"partition\">"
				."<input class=\"checkbox\" type=\"checkbox\" name=\"deleteGroup[]\" value=\"$nav[groupid]\">"
				.$nav['title']
				."<a href=\"?action=flinks&operation=add&groupid=$nav[groupid]\" style='float:right;height:18px;background-position: 0 -595px;' class=\"addchildboard\"></a>"
				."</dd>";
			foreach(C::t('custom_usefullinks')->fetch_links_by_groups($nav['groupid']) as $link) {
				$linksHTML .=  "<dt><input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$link[uid]\"><a href='$link[url]' target='_blank'>$link[title]</a>"
					."<a href='?action=flinks&operation=edit&uid=$link[uid]' class='files'></a></dt>";
			}
			$linksHTML .= "</dl>";
		}
		showformheader('flinks', '', 'usefullinksforum');
		showtableheader();
		showtablerow('', array(''), array(
			$linksHTML
		));
		echo '<tbody><tr><td colspan="6"><div><a class="addtr" href="'.ADMINSCRIPT.'?action=flinks&operation=add">'.'添加分类'.'</a></div></td><td colspan="3">&nbsp;</td></tr></tbody>';
		showsubmit('delflinkssubmit', 'submit', '');
		showtablefooter();
		echo <<<EOF
<style type="text/css">
dl { border:1px solid #CCCCCC; width:220px; margin:10px; float:left; padding-bottom:6px; }
dd { margin-bottom:6px; }
dt { margin-left:5px; }
.files { padding: 0;width: 14px;height: 16px;display: inline-block;float: right;margin: 2px 8px; background-position:-286px -150px; }
</style>
EOF;
	} else {
		if(is_array($_GET['flinksnew'])) {
			$data = array(
				'groupid' => $_GET['groupid'],
				'title' => $_GET['flinksnew']['name'],
				'url' => $_GET['flinksnew']['url'],
				'linktype' => 0
			);
			C::t('custom_usefullinks')->insert($data);
			cpmsg('链接成功添加', 'action=flinks', 'succeed');
		} elseif(is_array($_GET['delete'])||is_array($_GET['deleteGroup'])) {
			foreach($_GET['deleteGroup'] as $deleteGroup){
				C::t('custom_usefullinks')->delete_by_groupid($deleteGroup);
			}
			foreach($_GET['delete'] as $delete){
				C::t('custom_usefullinks')->delete_by_uid($delete);
			}
			cpmsg('group_update_succeed', 'action=flinks', 'succeed');
		} elseif (is_array($_GET['flinksgroupnew'])){
			$data = array(
				'groupid' => $_GET['groupid'],
				'title' => $_GET['flinksgroupnew']['name'],
				'id' => $_GET['flinksgroupnew']['id'],
				'linktype' => 1
			);
			C::t('custom_usefullinks')->insert($data);
			cpmsg('group_update_succeed', 'action=flinks', 'succeed');

		} elseif(is_array($_GET['flinks'])){
			$data = array(
				'groupid' => $_GET['flinks']['groupid'],
				'title' => $_GET['flinks']['name'],
				'url' => $_GET['flinks']['url'],
				'linktype' => 0
			);
			C::t('custom_usefullinks')->update_by_uid($_GET['uid'], $data);
			cpmsg('group_update_succeed', 'action=flinks', 'succeed');
		}
	}

} elseif ($operation=='add') {

	if(!empty($_GET['groupid'])){
		$groupid = $_GET['groupid'];
		showsubmenu('menu_sitemanager_links',  array(
			array('list', 'flinks', 0),
			array('add', "flinks&operation=add&groupid=$groupid", 1),
		));

		showformheader('flinks', '', 'usefullinksforum');
		showhiddenfields(array('groupid'=>$groupid));
		showtableheader();
		showsetting('flinks_name', 'flinksnew[name]', '', 'text');
		showsetting('flinks_url', 'flinksnew[url]', '', 'text');
		showsubmit('flinkssubmit', 'submit', '');
		showtablefooter();

	} else {
		//print_r('sdg');
		$groupid = (!empty($_GET['pickedgroupid']))?$_GET['pickedgroupid']:(C::t('custom_usefullinks')->pickup_a_groupid());
		//print_r($groupid);
		showsubmenu('menu_sitemanager_links',  array(
			array('list', 'flinks', 0),
			array('add', "flinks&operation=add&pickedgroupid=$groupid", 1),
		));
		showformheader('flinks', '', 'usefullinksforum');
		showhiddenfields(array('groupid'=>$groupid));
		showtableheader();
		showsetting('flinks_group_name', 'flinksgroupnew[name]', '', 'text');
		showsetting('flinks_group_id', 'flinksgroupnew[id]', '', 'text');
		showsubmit('flinkssubmit', 'submit', '');
		showtablefooter();
	}

} elseif($operation=='edit'){
	$uid = $_GET['uid'];
	showsubmenu('menu_sitemanager_links',  array(
		array('list', 'flinks', 0),
		array('edit', "flinks&operation=edit&uid=$uid", 1),
	));
	$flink = C::t('custom_usefullinks')->fetch_link_by_uid($uid);
	showformheader('flinks', '', 'usefullinksforum');
	showhiddenfields(array('uid'=>$uid));
	showtableheader();
	$groupidItem = '';
	foreach(C::t('custom_usefullinks')->fetch_groups() as $item){
		$groupidItem .= '<option value="'.$item['groupid'].'"'.($flink['groupid']===$item['groupid']?' selected':'').'>'.$item['title'].'</option>';
	}
	$groupidSelect = '<select name="flinks[groupid]">'.$groupidItem.'</select>';
	showsetting('flinks_groupid', '', '', $groupidSelect);
	showsetting('flinks_name', 'flinks[name]', $flink['title'], 'text');
	showsetting('flinks_url', 'flinks[url]', $flink['url'], 'text');
	showsubmit('flinkssubmit', 'submit', '');
	showtablefooter();
}


?>