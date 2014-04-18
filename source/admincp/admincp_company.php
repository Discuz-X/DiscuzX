<?PHP (defined('IN_DISCUZ') && defined('IN_ADMINCP')) || die('Access Denied');

cpheader();
loadcache('plugin');
global $_G;

$anchors = array(
    'info',
    'board',
);

if(!submitcheck('companysubmit')) {
    $_CA = C::t('common_setting')->fetch_all(null);
    $company = (array)dunserialize($_CA['company']);
    $companyboard = (array)dunserialize($_CA['companyboard']);

    in_array($_GET['anchor'], $anchors) || ($_GET['anchor'] = 'info');
    $menulist = array();
    foreach($anchors as $anchor) {
        $menulist[] = array('company_message_' . $anchor, 'company&anchor=' . $anchor, $_GET['anchor'] == $anchor);
    }
    showsubmenu('setting_functions', $menulist);

    if(!$operation || $operation == 'mytest') {
        shownav('global', 'message_company');
        showsubmenu('message_company', array());

        showformheader('company');
        showhiddenfields(array('operation' => $operation));

        showtableheader('', 'nobottom', 'id="info"' . ($_GET['anchor'] != 'info' ? ' style="display: none"' : ''));
        showsetting('message_company_phone', 'companynew[phone]', $company['phone'], 'text');
        showsetting('message_company_fax', 'companynew[fax]', $company['fax'], 'text');
        showsetting('message_company_master', 'companynew[master]', $company['master'], 'text');
        showsetting('message_company_handphone', 'companynew[handphone]', $company['handphone'], 'text');
        showsetting('message_company_address', 'companynew[address]', $company['address'], 'text');
        showsetting('message_company_adminemail', 'companynew[adminemail]', $company['adminemail'], 'text');
        showsetting('message_company_zipcode', 'companynew[zipcode]', $company['zipcode'], 'text');
        //showsetting('message_company_site_qq', 'companynew[site_qq]', $company['site_qq'], 'text', $disabled = '', $hidden = 0, $comment = '', $extra = 'id="settingnew[site_qq]"');
        //showsetting('message_company_icp', 'companynew[icp]', $company['icp'], 'text');
        //showsetting('message_company_boardlicensed', 'companynew[boardlicensed]', $company['boardlicensed'], 'radio');
        //showsetting('message_company_stat', 'companynew[statcode]', $company['statcode'], 'textarea');
        showtablefooter();

        showtableheader('', 'nobottom', 'id="board"' . ($_GET['anchor'] != 'board' ? ' style="display: none"' : ''));
        showsubtitle(array('display_order', 'name', 'company_message_leadingname', 'available', ''));
        foreach($companyboard as $name => $value) {
            showtablerow('', array('class="td25"', '', '', '', '', ''), array(
                "<input type=\"text\" class=\"txt\" size=\"2\" name=\"displayordernew[$name]\" value=\"" . $companyboard[$name]['displayorder'] . "\">",
                cplang('message_company_' . $name),
                "<input type=\"text\" class=\"txt\" size=\"10\" name=\"leadingnamenew[$name]\" value=\"" . $companyboard[$name]['leadingname'] . "\">",
                "<input class=\"checkbox\" type=\"checkbox\" name=\"availablenew[$name]\" value=\"1\" " . ($companyboard[$name]['available'] > 0 ? 'checked' : '') . ">",
                '',
            ));
        }
        showtablefooter();

        showtableheader('', 'notop');
        showsubmit('companysubmit', 'submit');
        showtablefooter();
        showformfooter();

        //var_export($company);

    } elseif($operation == 'filemanager') {
        if(empty($admincp) || !is_object($admincp) || !$admincp->isfounder) {
            exit('Access Denied');
        }
        shownav('sitemanager', 'menu_sitemanager_filemanager');
        print_r('解压功能（临时）');
    }

} else {
    $settingnew = $_GET['settingnew'];
    $companynew = $_GET['companynew'];

    $valueSlot = array('companyname');
    foreach($companynew as $name => $value) {
        $valueSlot[] = $name;
    }
    foreach($valueSlot as $value) {
        $companyboardnew[$value] = array(
            'displayorder' => intval($_GET['displayordernew'][$value]),
            'leadingname' => trim($_GET['leadingnamenew'][$value]),
            'available' => intval($_GET['availablenew'][$value]),
        );
    }
    $arraySort = array();
    foreach($companyboardnew as $name => $array) {
        $arraySort[$name] = $array['displayorder'];
    }
    asort($arraySort);
    foreach($arraySort as $name => $order) {
        $arraySort[$name] = $companyboardnew[$name];
    }
    $companyboardnew = $arraySort;
    //$settingnew['company']['address'] = $companynew['address'];
    //	if (is_array($_GET['hooker'])) {
    //		foreach ($_GET['hooker'] as $templatehookerid => $val) {
    //			//$templatehookerid = intval($templatehookerid);
    //			//print_r($templatehookerid );
    //			//print_r($val);
    //			//echo intval($templatehookerid == '');
    //			$updatearr = array('templatehookerid' => /*dhtmlspecialchars($_GET['hooker'][$templatehookerid])*/
    //			htmlentities($_GET['hooker'][$templatehookerid], ENT_QUOTES, 'UTF-8'), 'hooker' => /*dhtmlspecialchars($_GET['hooker'][$templatehookerid])*/
    //			htmlentities($_GET['hooker'][$templatehookerid], ENT_QUOTES, 'UTF-8'), 'file' => $_GET['file'][$templatehookerid], 'pattern' => /*dhtmlspecialchars($_GET['pattern'][$templatehookerid])*/
    //			htmlentities($_GET['pattern'][$templatehookerid], ENT_QUOTES, 'UTF-8'), 'replacement' => /*dhtmlspecialchars($_GET['replacement'][$templatehookerid])*/
    //			htmlentities($_GET['replacement'][$templatehookerid], ENT_QUOTES, 'UTF-8'),);
    //			//C::t('home_click')->update($id, $updatearr);
    //			$settingnew['templatehooker'][htmlentities($templatehookerid, ENT_QUOTES, 'UTF-8')] = $updatearr;
    //		}
    //	}
    //	if (is_array($_GET['delete'])) {
    //		foreach ($_GET['delete'] as $id => $val) {
    //			//$ids[] = $id;
    //			//echo $_GET['delete'][$id];
    //			//echo '=';
    //			echo $_GET['delete'][$id];
    //			//echo ';';
    //			//$templatehooker[($id)] = array();
    //			//$templatehooker = array_splice($templatehooker, intval($id), 1);
    //			unset($settingnew['templatehooker'][$_GET['delete'][$id]]);
    //		}
    //		if ($ids) {
    //			//C::t('home_click')->delete($ids, true);
    //		}
    //	}
    //	//print_r($_GET['newhooker']);
    //	if (is_array($_GET['newhooker'])) {
    //		foreach ($_GET['newhooker'] as $key => $value) {
    //			//echo $key;
    //			//echo "=";
    //			//echo $value;
    //			if ($value != '' && $_GET['newhooker'][$key] != '') {
    //				$data = array('templatehookerid' => dhtmlspecialchars($_GET['newhooker'][$key]), 'hooker' => dhtmlspecialchars($_GET['newhooker'][$key]), 'file' => dhtmlspecialchars($_GET['newfile'][$key]), 'pattern' => dhtmlspecialchars($_GET['newpattern'][$key]), 'replacement' => dhtmlspecialchars($_GET['newreplacement'][$key]));
    //				//C::t('home_click')->insert($data);
    //				//print_r( $data);
    //				//array_push($templatehooker, $data);
    //				$settingnew['templatehooker'][dhtmlspecialchars($_GET['newhooker'][$key])] = $data;
    //			}
    //		}
    //	}
    function trim_value(&$value) {
        $value = trim($value);
    }

    array_walk($companynew, 'trim_value');
    $settingnew['company'] = serialize($companynew);
    $settingnew['companyboard'] = serialize($companyboardnew);
    //var_export($settingnew['companyboard']);
    C::t('common_setting')->update_batch($settingnew);
    updatecache('setting');
    cpmsg('setting_update_succeed', 'action=company' . (!empty($_GET['anchor']) ? '&anchor=' . $_GET['anchor'] : ''), 'succeed');
}