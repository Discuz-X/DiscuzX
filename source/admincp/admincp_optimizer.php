<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_optimizer.php 31344 2012-08-15 04:01:32Z zhangjie $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

$optimizer_option = array(
	'optimizer_plugin',
	'optimizer_upgrade',
	'optimizer_patch',
	'optimizer_thread',
	'optimizer_setting',
	'optimizer_post',
	'optimizer_member',
	'optimizer_filecheck',
	'optimizer_dbbackup',
	'optimizer_dbbackup_clean',
	'optimizer_seo'
);

if($operation) {
	$type = $_GET['type'];
	if(!in_array($type, $optimizer_option)) {
		cpmsg('parameters_error', '', 'error');
	}
	$_GET['anchor'] = $operation;

	include_once 'source/discuz_version.php';
	$optimizer = new optimizer($type);
}

$_GET['anchor'] = in_array($_GET['anchor'], array('base', 'setting_optimizer', 'log_optimizer')) ? $_GET['anchor'] : 'base';
$current = array($_GET['anchor'] => 1);
showsubmenu('nav_founder_optimizer', array(
	array('founder_optimizer_index', 'optimizer&anchor=base', $current['base']),
	array('founder_optimizer_setting', 'optimizer&operation=setting_optimizer&type=optimizer_setting&anchor=setting_optimizer', $current['setting_optimizer']),
));

if($operation == 'optimize_unit') {

	$optimizer->optimizer();

} elseif($operation == 'check_unit') {

	$checkstatus = $optimizer->check();

	C::t('common_optimizer')->update($type.'_checkrecord', ($checkstatus['status'] == 1 ? 1 : 0));
	C::t('common_optimizer')->update('check_record_time', $_G['timestamp']);

	include template('common/header_ajax');
	echo '<script type="text/javascript">updatecheckstatus(\''.$type.'\', \''.$checkstatus['lang'].'\', \''.$checkstatus['status'].'\', \''.$checkstatus['type'].'\');</script>';
	include template('common/footer_ajax');
	exit;

} elseif($operation == 'setting_optimizer') {

	if(submitcheck('setting_optimizer', 1)) {
		$setting_options = $_GET['options'];
		if($optimizer->option_optimizer($setting_options)) {
			cpmsg('founder_optimizer_setting_succeed', 'action=optimizer&operation=setting_optimizer&type=optimizer_setting', 'succeed');
		} else {
			cpmsg('founder_optimizer_setting_error', '', 'error');
		}
	} else {

		showformheader('optimizer&operation=setting_optimizer&type=optimizer_setting');
		showtableheader();

		$option = $optimizer->get_option();

		echo '<tr class="header">';
		echo '<th></th>';
		echo '<th class="td24">'.$lang['founder_optimizer_setting_option'].'</th>';
		echo '<th>'.$lang['founder_optimizer_setting_option_description'].'</th>';
		echo '<th class="td24">'.$lang['founder_optimizer_setting_description'].'</th>';
		echo '</tr>';
		foreach($option as $setting) {
			$color = ' style="'.($setting[4] ? 'color:red;' : 'color:green').'"';
			echo '<tr>';
			echo '<td><input type="checkbox" name="options[]" value="'.$setting[0].'" '.($setting[4] ? 'checked' : 'disabled').' /></td>';
			echo '<td'.$color.'>'.$setting[1].'</td>';
			echo '<td'.$color.'>'.$setting[2].'</td>';
			echo '<td'.$color.'>'.$setting[3].'</td>';
			echo '</tr>';
		}
		showsubmit('setting_optimizer');

		showtablefooter();
		showformfooter();
	}


} else {

	$checkrecordtime = C::t('common_optimizer')->fetch('check_record_time');

	showtableheader();

	echo '<div class="optblock cl">';
	echo $_GET['checking'] ? '<a href="javascript:;" id="checking" class="btn_big">'.$lang['founder_optimizer_checking'].'</a>' :
		'<a href="'.ADMINSCRIPT.'?action=optimizer&checking=1" id="checking" class="btn_big">'.$lang['founder_optimizer_start_check'].'</a>';
	if($_GET['checking']) {
		echo '<div class="pbg" id="processid">';
		echo '<div class="pbr" style="width: 0;" id="percentprocess"></div>';
		echo '<div class="xs0" id="percent">0%</div>';
		echo '</div>';
	}
	echo '<div id="checkstatus">';
	if(!$checkrecordtime) {
		echo $lang['founder_optimizer_first_use'];
	} else {
		$num = 0;
		$checkrecordkey = array();
		foreach($optimizer_option as $option) {
			$checkrecordkey[] = $option.'_checkrecord';
		}
		foreach(C::t('common_optimizer')->fetch_all($checkrecordkey) as $checkrecordvalue) {
			if($checkrecordvalue['v'] == 1) {
				$num++;
			}
		}
		if(!$_GET['checking']) {
			echo $lang['founder_optimizer_lastcheck'].dgmdate($checkrecordtime).$lang['founder_optimizer_findnum'].$num.$lang['founder_optimizer_neednum'];
		}
	}
	echo '</div>';
	echo '</div>';
	if($_GET['checking']) {
		$inc_unit = 100/count($optimizer_option);
		$adminscipt = ADMINSCRIPT;
		print <<<END
			<script type="text/javascript">
				var checkpercent = 0;
				var checknum = 0;
				var optimize_num = 0;
				function updatecheckpercent() {
					checkpercent += {$inc_unit};
					checknum++;
					$('percent').innerHTML = parseInt(checkpercent) + '%';
					$('percentprocess').style.width = parseInt(checkpercent) * 2 + 'px';
				}
				function updatecheckstatus(id, msg, status, type) {
					var optiontype = id;
					id = 'progress_' + id;
					if(status == 1) {
						$(id).style.color = 'red';
						optimize_num++;
					} else {
						$(id).style.color = 'green';
					}
					if(status == 1) {
						if(type == 'header') {
							$(id + '_status').innerHTML = '<a class="btn" href="$adminscipt?action=optimizer&operation=optimize_unit&type='+ optiontype +'" target="_blank">{$lang[founder_optimizer_optimizer]}</a>';
						} else if(type == 'view') {
							$(id + '_status').innerHTML = '<a class="btn" href="$adminscipt?action=optimizer&operation=optimize_unit&type='+ optiontype +'" target="_blank">{$lang[founder_optimizer_view]}</a>';
						} else if(type == 'scan') {
							$(id + '_status').innerHTML = '<a class="btn" href="$adminscipt?action=optimizer&operation=optimize_unit&type='+ optiontype +'" target="_blank">{$lang[founder_optimizer_scan]}</a>';
						}
					}
					if(msg) {
						$(id).innerHTML = msg;
					}
					if(parseInt(checkpercent) >= 100) {
						$('checking').innerHTML = '{$lang[founder_optimizer_recheck_js]}';
						$('checking').href = '{$adminscipt}?action=optimizer&checking=1';
						$('processid').style.display = 'none';
						$('checkstatus').innerHTML = '{$lang[founder_optimizer_check_complete_js]}' + checknum + '{$lang[founder_optimizer_findnum]}' +  optimize_num + '{$lang[founder_optimizer_neednum]}';
					}
				}
			</script>
END;
		echo '<table class="tb tb2 tb3" style="margin-top:0;">';
		echo '<tr class="header">';
		echo '<th width="200">'.$lang['founder_optimizer_check_option'].'</th>';
		echo '<th width="350">'.$lang['description'].'</th>';
		echo '<th>'.$lang['founder_optimizer_status'].'</th>';
		echo '</tr>';
		foreach($optimizer_option as $option) {
			echo '<tr class="hover">';
			echo '<td>'.$lang['optimizer_check_unit_'.$option].'</td>';
			echo '<td><div id="progress_'.$option.'">'.$lang['founder_optimizer_checking'].'...</div></td><script type="text/javascript">ajaxget(\''.ADMINSCRIPT.'?action=optimizer&operation=check_unit&type='.$option.'\', \'progress_'.$option.'\', \'\', \'\', \'\', updatecheckpercent)</script>';
			echo '<td><div id="progress_'.$option.'_status"></div></td>';
			echo '</tr>';
		}
		echo '</table>';
	}

	showtablefooter();
}

?>