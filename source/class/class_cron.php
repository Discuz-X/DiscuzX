<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id:$
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class discuz_cron
{

	function run($cronid = 0) {

		global $_G;
		$timestamp = TIMESTAMP;
		$cron = DB::fetch_first("SELECT * FROM ".DB::table('common_cron')."
				WHERE ".($cronid ? "cronid='$cronid'" : "available>'0' AND nextrun<='$timestamp'")."
				ORDER BY nextrun LIMIT 1");

		$processname ='DZ_CRON_'.(empty($cron) ? 'CHECKER' : $cron['cronid']);

		if($cronid && !empty($cron)) {
			discuz_process::unlock($processname);
		}

		if(discuz_process::islocked($processname, 600)) {
			return false;
		}

		if($cron) {

			$cron['filename'] = str_replace(array('..', '/', '\\'), '', $cron['filename']);
			$cronfile = DISCUZ_ROOT.'./source/include/cron/'.$cron['filename'];

			$cron['minute'] = explode("\t", $cron['minute']);
			discuz_cron::setnextime($cron);

			@set_time_limit(1000);
			@ignore_user_abort(TRUE);

			if(!@include $cronfile) {
				return false;
			}
		}

		discuz_cron::nextcron();
		discuz_process::unlock($processname);
		return true;
	}

	function nextcron() {
		$nextrun = DB::result_first("SELECT nextrun FROM ".DB::table('common_cron')." WHERE available>'0' ORDER BY nextrun LIMIT 1");
		if($nextrun !== FALSE) {
			save_syscache('cronnextrun', $nextrun);
		} else {
			save_syscache('cronnextrun', TIMESTAMP + 86400 * 365);
		}
		return true;
	}

	function setnextime($cron) {

		global $_G;

		if(empty($cron)) return FALSE;

		list($yearnow, $monthnow, $daynow, $weekdaynow, $hournow, $minutenow) = explode('-', gmdate('Y-m-d-w-H-i', TIMESTAMP + $_G['setting']['timeoffset'] * 3600));

		if($cron['weekday'] == -1) {
			if($cron['day'] == -1) {
				$firstday = $daynow;
				$secondday = $daynow + 1;
			} else {
				$firstday = $cron['day'];
				$secondday = $cron['day'] + gmdate('t', TIMESTAMP + $_G['setting']['timeoffset'] * 3600);
			}
		} else {
			$firstday = $daynow + ($cron['weekday'] - $weekdaynow);
			$secondday = $firstday + 7;
		}

		if($firstday < $daynow) {
			$firstday = $secondday;
		}

		if($firstday == $daynow) {
			$todaytime = discuz_cron::todaynextrun($cron);
			if($todaytime['hour'] == -1 && $todaytime['minute'] == -1) {
				$cron['day'] = $secondday;
				$nexttime = discuz_cron::todaynextrun($cron, 0, -1);
				$cron['hour'] = $nexttime['hour'];
				$cron['minute'] = $nexttime['minute'];
			} else {
				$cron['day'] = $firstday;
				$cron['hour'] = $todaytime['hour'];
				$cron['minute'] = $todaytime['minute'];
			}
		} else {
			$cron['day'] = $firstday;
			$nexttime = discuz_cron::todaynextrun($cron, 0, -1);
			$cron['hour'] = $nexttime['hour'];
			$cron['minute'] = $nexttime['minute'];
		}

		$nextrun = @gmmktime($cron['hour'], $cron['minute'] > 0 ? $cron['minute'] : 0, 0, $monthnow, $cron['day'], $yearnow) - $_G['setting']['timeoffset'] * 3600;

		$availableadd = $nextrun > TIMESTAMP ? '' : ', available=\'0\'';
		DB::query("UPDATE ".DB::table('common_cron')." SET lastrun='$_G[timestamp]', nextrun='$nextrun' $availableadd WHERE cronid='$cron[cronid]'");

		return true;
	}

	function todaynextrun($cron, $hour = -2, $minute = -2) {
		global $_G;

		$hour = $hour == -2 ? gmdate('H', TIMESTAMP + $_G['setting']['timeoffset'] * 3600) : $hour;
		$minute = $minute == -2 ? gmdate('i', TIMESTAMP + $_G['setting']['timeoffset'] * 3600) : $minute;

		$nexttime = array();
		if($cron['hour'] == -1 && !$cron['minute']) {
			$nexttime['hour'] = $hour;
			$nexttime['minute'] = $minute + 1;
		} elseif($cron['hour'] == -1 && $cron['minute'] != '') {
			$nexttime['hour'] = $hour;
			if(($nextminute = discuz_cron::nextminute($cron['minute'], $minute)) === false) {
				++$nexttime['hour'];
				$nextminute = $cron['minute'][0];
			}
			$nexttime['minute'] = $nextminute;
		} elseif($cron['hour'] != -1 && $cron['minute'] == '') {
			if($cron['hour'] < $hour) {
				$nexttime['hour'] = $nexttime['minute'] = -1;
			} elseif($cron['hour'] == $hour) {
				$nexttime['hour'] = $cron['hour'];
				$nexttime['minute'] = $minute + 1;
			} else {
				$nexttime['hour'] = $cron['hour'];
				$nexttime['minute'] = 0;
			}
		} elseif($cron['hour'] != -1 && $cron['minute'] != '') {
			$nextminute = discuz_cron::nextminute($cron['minute'], $minute);
			if($cron['hour'] < $hour || ($cron['hour'] == $hour && $nextminute === false)) {
				$nexttime['hour'] = -1;
				$nexttime['minute'] = -1;
			} else {
				$nexttime['hour'] = $cron['hour'];
				$nexttime['minute'] = $nextminute;
			}
		}

		return $nexttime;
	}

	function nextminute($nextminutes, $minutenow) {
		foreach($nextminutes as $nextminute) {
			if($nextminute > $minutenow) {
				return $nextminute;
			}
		}
		return false;
	}
}

?>