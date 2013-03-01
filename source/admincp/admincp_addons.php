<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_addons.php 22609 2011-05-16 01:55:33Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
		exit('Access Denied');
}

define('ADDONS_SERVER', 'http://addons.discuz.com');

cpheader();

if(!$operation) {

	shownav('addons');
	showsubmenu('addons', array(
		array('addons_list', 'addons', 1),
		array('addons_plugin', 'http://addons.discuz.com', 0, 1, 1),
	));
	showtips('addons_tips');

	$addons = array();
	$query = DB::query("SELECT * FROM ".DB::table('common_addon')." ORDER BY `key` ASC");
	while($addon = DB::fetch($query)) {
		$addons[] = $addon['key'];
		showproviderinfo($addon, 0);
	}

	$extra = !empty($_G['gp_category']) ? '&category='.rawurlencode($_G['gp_category']) : '';
	$data = dfsockopen(ADDONS_SERVER.'/list.xml');
	require_once libfile('class/xml');
	if(strtoupper(CHARSET) != 'UTF-8') {
		require_once libfile('class/chinese');
		$c = new Chinese('utf8', CHARSET, TRUE);
		$data = $c->Convert($data);
	}
	$data = xml2array($data);

	showtableheader('addons_recommend');
	if(is_array($data) && $data) {
		$data = dstrip_tags($data);
		echo '<tr><td>';
		foreach($data as $row) {
			if(in_array($row['key'], $addons)) {
				continue;
			}
			echo '<div class="hover" style="float:left;width:20%;height:80px;padding:5px 0"><div style="text-align:center"><a href="'.ADMINSCRIPT.'?action=addons&operation=add&providerkey='.$row['key'].'">'.
			($row['logo'] ? '<img width="100" height="50" src="'.$row['logo'].'" />' : '<img width="100" height="50" src="static/image/common/none.gif" />').
			'</a><br /><a href="'.ADMINSCRIPT.'?action=addons&operation=add&providerkey='.$row['key'].'">'.$row['sitename'].'</a></div></div>';
		}
		echo '</td></tr>';

	} else {
		echo '<tr><td>'.$lang['addons_provider_listinvalid'].'</td></tr>';
	}
	showtablefooter();

	showformheader('addons&operation=add');
	showtableheader('addons_add_input');
	showsetting('addons_provider_key', 'providerkey', '', 'text');
	showsubmit('newsubmit');
	showtablefooter();
	showformfooter();

} elseif($operation == 'list') {

	require_once DISCUZ_ROOT.'./source/discuz_version.php';
	$plugins = array();
	foreach($_G['setting']['plugins']['available'] as $pluginid) {
		$plugins[] = $pluginid.'/'.$_G['setting']['plugins']['version'][$pluginid];
	}
	$plugins = implode("\t", $plugins);
	$baseparm = 'version='.rawurlencode(DISCUZ_VERSION).'&release='.rawurlencode(DISCUZ_RELEASE).'&charset='.rawurlencode(CHARSET).'&boardurl='.rawurlencode($_G['siteurl']).'&plugins='.rawurlencode($plugins);
	$addon = dstrip_tags(DB::fetch_first("SELECT * FROM ".DB::table('common_addon')." WHERE `key`='{$_G['gp_provider']}'"));
	if(!$addon) {
		cpmsg('addons_provider_nonexistence', '', 'error');
	}
	$providerapi = trim(dfsockopen(ADDONS_SERVER, 0, $baseparm.'&key='.rawurlencode($_G['gp_provider'])));
	if(!$providerapi) {
		cpmsg('addons_provider_disabled', '', 'error');
	}

	$extra = !empty($_G['gp_category']) ? '&category='.rawurlencode($_G['gp_category']) : '';
	$data = dfsockopen($providerapi, 0, $baseparm.$extra);
	require_once libfile('class/xml');
	if(strtoupper(CHARSET) != 'UTF-8') {
		require_once libfile('class/chinese');
		$c = new Chinese('utf8', CHARSET, TRUE);
		$data = $c->Convert($data);
	}
	$data = xml2array($data);
	if(!is_array($data) || !$data || $data['Key'] != $_G['gp_provider']) {
		cpmsg('addons_provider_apiinvalid', 'action=addons', 'error');
	}
	checkinfoupdate($data, $addon);

	$data = dstrip_tags($data);
	shownav('addons', $data['Title']);
	showsubmenu($data['Title']);

	showproviderinfo($addon, 1);

	showtableheader('', 'noborder');
	echo '<tr><td valign="top" width="150" style="padding-top:0"><ul class="menu">';
	foreach($data['Category'] as $categoryid => $Category) {
		echo '<li class="a"><a'.($_G['gp_category'] == $categoryid ? ' class="tabon"' : '').' href="'.ADMINSCRIPT.'?action=addons&operation=list&provider='.$_G['gp_provider'].'&category='.$categoryid.'">'.$Category.'</a></li>';
	}
	echo '</ul></td><td valign="top" style="padding-top:0">';
	if($data['Searchlink'] != '') {
		echo '<form method="post" autocomplete="off" action="'.$data['Searchlink'].'" target="_blank">'.
			'<input type="hidden" name="version" value="'.DISCUZ_VERSION.'" />'.
			'<input type="hidden" name="release" value="'.DISCUZ_RELEASE.'" />'.
			'<input type="hidden" name="charset" value="'.CHARSET.'" />'.
			'<input type="hidden" name="boardurl" value="'.htmlspecialchars($_G['siteurl']).'" />'.
			'<input type="hidden" name="plugins" value="'.htmlspecialchars($plugins).'" />'.
			'<input name="keyword" /><input name="submit" class="btn" style="margin: -4px 0 0 2px" type="submit" value="'.$lang['addons_search'].'" />'.
			'</form>';
	}
	$count = 0;
	showtableheader('', 'fixpadding', 'style="margin-top:0"');
	if(is_array($data['Data'])) foreach($data['Data'] as $row) {
		$count++;
		$Charset = explode(',', $row['Charset']);
		foreach($Charset as $k => $v) {
			if(preg_match('/^SC\_GBK$/i', $v)) {
				$Charset[$k] = '&#31616;&#20307;&#20013;&#25991;';
				if(strtoupper(CHARSET) == 'GBK') {
					$Charset[$k] = '<b>'.$Charset[$k].'</b>';
				}
			} elseif(preg_match('/^SC\_UTF8$/i', $v)) {
				$Charset[$k] = '&#31616;&#20307;&#20013;&#25991;&#85;&#84;&#70;&#56;';
				if(strtoupper(CHARSET) == 'UTF-8') {
					$Charset[$k] = '<b>'.$Charset[$k].'</b>';
				}
			} elseif(preg_match('/^TC\_BIG5$/i', $v)) {
				$Charset[$k] = '&#32321;&#39636;&#20013;&#25991;';
				if(strtoupper(CHARSET) == 'BIG5') {
					$Charset[$k] = '<b>'.$Charset[$k].'</b>';
				}
			} elseif(preg_match('/^TC\_UTF8$/i', $v)) {
				$Charset[$k] = '&#32321;&#39636;&#20013;&#25991;&#85;&#84;&#70;&#56;';
				if(strtoupper(CHARSET) == 'UTF-8') {
					$Charset[$k] = '<b>'.$Charset[$k].'</b>';
				}
			}
		}

		$data['ThumbWidth'] = !isset($data['ThumbWidth']) ? 100 : $data['ThumbWidth'];
		echo '<tr><th colspan="3" class="partition">'.($row['Time'] != '' ? '<div class="right">'.$row['Time'].'</div>' : '').'<a href="'.$row['Url'].'" target="_blank">'.($row['Greenplugin'] ? '<img class="vmiddle" title="'.$lang['addons_greenplugin'].'" src="static/image/admincp/greenplugin.gif" /> ' : '').$row['Name'].($row['Version'] != '' ? ' '.$row['Version'] : '').'</a></th></tr>'.
			'<tr><td valign="top" width="'.($data['ThumbWidth'] + 10).'">'.($row['Thumb'] != '' ? '<a href="'.$row['Url'].'" target="_blank"><img onerror="this.src=\'static/image/common/none.gif\'" src="'.$row['Thumb'].'" width="'.$data['ThumbWidth'].'" /></a>' : '').'</td>'.
			'<td class="lineheight" valign="top">'.($row['Charset'] != '' ? $lang['addons_charset'].implode(', ', $Charset).'<br /><br />' : '').($row['Description'] != '' ? nl2br($row['Description']) : '').'</td></tr>';
		if($count == 20) {
			break;
		}
	}
	showtablefooter();
	if($data['Morelink'] != '') {
		showtableheader('', 'fixpadding');
		echo '<tr><td class="partition"><a href="'.$data['Morelink'].'" target="_blank">'.$lang['addons_more'].'</a></td></tr>';
		showtablefooter();
	}
	echo '</td></tr>';
	showtablefooter();

} elseif($operation == 'remove') {

	$addon = DB::fetch_first("SELECT * FROM ".DB::table('common_addon')." WHERE `key`='{$_G['gp_provider']}'");
	if(!$addon) {
		cpmsg('addons_provider_nonexistence', '', 'error');
	}
	DB::query("DELETE FROM ".DB::table('common_addon')." WHERE `key`='{$_G['gp_provider']}' AND system='0'");
	cpmsg('addons_provider_removesucceed', 'action=addons', 'succeed');

} elseif($operation == 'add') {

	$_G['gp_providerkey'] = trim($_G['gp_providerkey']);
	if(!$_G['gp_providerkey']) {
		cpmsg('addons_provider_nonexistence', '', 'error');
	}
	$addon = DB::fetch_first("SELECT * FROM ".DB::table('common_addon')." WHERE `key`='{$_G['gp_providerkey']}'");
	if($addon) {
		dheader('location:'.$BASESCRIPT.'?action=addons&operation=list&provider='.rawurlencode($_G['gp_providerkey']));
	}
	require_once DISCUZ_ROOT.'./source/discuz_version.php';
	$baseparm = 'version='.rawurlencode(DISCUZ_VERSION).'&release='.rawurlencode(DISCUZ_RELEASE).'&charset='.rawurlencode(CHARSET);
	$providerapi = trim(dfsockopen(ADDONS_SERVER, 0, $baseparm.'&key='.rawurlencode($_G['gp_providerkey'])));
	if(!$providerapi) {
		cpmsg('addons_provider_disabled', '', 'error');
	}
	DB::insert('common_addon', array('key' => $_G['gp_providerkey']));
	dheader('location:'.$BASESCRIPT.'?action=addons&operation=list&provider='.rawurlencode($_G['gp_providerkey']));

}

function showproviderinfo($addon, $simple) {
	$contact = $addon['contact'];
	$contact = preg_replace("/(((https?){1}:\/\/|www\.).+?)(\s|$)/ies", "parsetaga('\\1', '\\4', 0)", $contact);
	$contact = preg_replace("/(([a-z0-9\-_.+]+)@([a-z0-9\-_]+[.][a-z0-9\-_.]+))(\s|$)/ies", "parsetaga('\\1', '\\4', 1)", $contact);
	if($simple) {
		echo '<div class="colorbox">';
	}
	showtableheader('', $simple ? 'noborder' : '');
	echo (!$simple ? '<tr><th colspan="3" class="partition"><a href="'.ADMINSCRIPT.'?action=addons&operation=list&provider='.$addon['key'].'">'.$addon['title'].'</a></th></tr>' : '').
		'<tr><td width="110" valign="top"><a href="'.ADMINSCRIPT.'?action=addons&operation=list&provider='.$addon['key'].'"><img onerror="this.src=\'static/image/common/none.gif\'" src="'.$addon['logo'].'" /></a></td>'.
		'<td valign="top">'.nl2br($addon['description']).'<br /><br />'.
		cplang('addons_provider').'<a href="'.$addon['siteurl'].'" target="_blank">'.$addon['sitename'].'</a>&nbsp;&nbsp;'.
		cplang('addons_contact').$contact.'</td>'.
		(!$simple ? '<td align="right" width="50">'.(!$addon['system'] ? '<a href="'.ADMINSCRIPT.'?action=addons&operation=remove&provider='.$addon['key'].'" onclick="return confirm(\''.cplang('addons_delete_confirm').'\')">'.cplang('delete').'</a>' : '').'&nbsp;</td>' : '').'</tr>';
	showtablefooter();
	if($simple) {
		echo '</div>';
	}
}

function checkinfoupdate($data, &$addon) {
	global $_G;
	$update = array();
	if($data['Title'] != $addon['title']) {
		$update[] = "title='".addslashes($data['Title'])."'";
	}
	if($data['Sitename'] != $addon['sitename']) {
		$update[] = "sitename='".addslashes($data['Sitename'])."'";
	}
	if($data['Siteurl'] != $addon['siteurl']) {
		$update[] = "siteurl='".addslashes($data['Siteurl'])."'";
	}
	if($data['Description'] != $addon['description']) {
		$update[] = "description='".addslashes($data['Description'])."'";
	}
	if($data['Contact'] != $addon['contact']) {
		$update[] = "contact='".addslashes($data['Contact'])."'";
	}
	if($data['Logo'] != $addon['logo']) {
		$update[] = "logo='".addslashes($data['Logo'])."'";
	}
	if($update) {
		DB::query("UPDATE ".DB::table('common_addon')." SET ".implode(',', $update)." WHERE `key`='$_G[gp_provider]'");
	}
	$addon = DB::fetch_first("SELECT * FROM ".DB::table('common_addon')." WHERE `key`='$_G[gp_provider]'");
}

function parsetaga($href, $s, $mailto) {
	return '<a href="'.($mailto ? 'mailto:' : '').$href.'" target="_blank">'.$href.'</a>'.$s;
}

function dstrip_tags($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = dstrip_tags($val);
		}
	} else {
		$string = strip_tags($string);
	}
	return $string;
}

?>