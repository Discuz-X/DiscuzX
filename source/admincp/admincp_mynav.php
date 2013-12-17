<?PHP (defined('IN_DISCUZ') && defined('IN_ADMINCP')) || die('Access Denied');

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_mynav.php 31327 2012-08-13 07:01:41Z liulanbo $
 */


cpheader();
global $_G;

if(!$operation || $operation == 'mytest') {
	shownav('sitemanager', 'menu_sitemanager_todo');
	$links = file_get_contents(DISCUZ_ROOT . '../message.txt');
	$links = ltrim($links);
	$name = substr($links, 0, strpos($links, "\n"));
	$links = str_replace($name, '', $links);
	$links = ltrim($links);
	$url = substr($links, 0, strpos($links, "\n"));;
	$links = str_replace($url, '', $links);
	$links = ltrim($links);
	print_r($links);
	file_put_contents(DISCUZ_ROOT . '../message.txt', $links);
	showformheader('flinks', '', 'usefullinksforum');
	showhiddenfields(array('groupid' => 8));
	showtableheader();
	showsetting('flinks_name', 'flinksnew[name]', $name, 'text');
	showsetting('flinks_url', 'flinksnew[url]', $url, 'text');
	showsubmit('flinkssubmit', 'submit', '');
	showtablefooter();
} elseif($operation == 'templateflag') {
	if(!submitcheck('templateflagsubmit')) {
		$_C = C::t('common_setting')->fetch_all(null);
		//$company = (array)dunserialize($_C['company']);
		$templateflags = $_C['templateflags'];

		shownav('global', 'flags_template');
		showsubmenu('flags_template', array());

		showformheader('mynav');
		showhiddenfields(array('operation' => $operation));

		showtableheader('');
		showsetting('flags_flags_template', 'templateflagsnew', $templateflags, 'text');
		//showsetting('message_company_fax', 'companynew[fax]', $company['fax'], 'text');
		//showsetting('message_company_master', 'companynew[master]', $company['master'], 'text');
		//showsetting('message_company_handphone', 'companynew[handphone]', $company['handphone'], 'text');
		//showsetting('message_company_address', 'companynew[address]', $company['address'], 'text');
		//showsetting('message_company_adminemail', 'companynew[adminemail]', $company['adminemail'], 'text');
		//showsetting('message_company_site_qq', 'companynew[site_qq]', $company['site_qq'], 'text', $disabled = '', $hidden = 0, $comment = '', $extra = 'id="settingnew[site_qq]"');
		//showsetting('message_company_icp', 'companynew[icp]', $company['icp'], 'text');
		//showsetting('message_company_boardlicensed', 'companynew[boardlicensed]', $company['boardlicensed'], 'radio');
		//showsetting('message_company_stat', 'companynew[statcode]', $company['statcode'], 'textarea');
		showsubmit('templateflagsubmit', 'submit');
		showtablefooter();
		showformfooter();
	} else {
		$settingnew = $_GET['settingnew'];
		$templateflagsnew = $_GET['templateflagsnew'];
		$settingnew['templateflags'] = $templateflagsnew;
		C::t('common_setting')->update_batch($settingnew);
		updatecache('setting');
		cpmsg('setting_update_succeed', 'action=mynav&operation=templateflag', 'succeed');
	}
}
