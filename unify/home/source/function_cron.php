<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: function_cron.php 8281 2008-07-31 02:54:10Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//执行计划任务
function runcron($cronid = 0) {
	global $_SGLOBAL, $_SCONFIG, $_SBLOCK, $_TPL, $_SCOOKIE, $_SN, $space;
	
	$where = $cronid ? "cronid='$cronid'" : "available>'0' AND nextrun<='$_SGLOBAL[timestamp]'";
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('cron')." WHERE $where ORDER BY nextrun LIMIT 1");//只运行一个
	if($cron = $_SGLOBAL['db']->fetch_array($query)) {

		$lockfile = S_ROOT.'./data/runcron_'.$cron['cronid'].'.lock';
		$cronfile = S_ROOT.'./source/cron/'.$cron['filename'];

		if(is_writable($lockfile)) {
			$locktime =  filemtime($lockfile);
			if($locktime > $_SGLOBAL['timestamp'] - 600) {//10分钟
				return NULL;
			}
		} else {
			@touch($lockfile);
		}

		@set_time_limit(1000);
		@ignore_user_abort(TRUE);

		cronnextrun($cron);

		if(!@include $cronfile) {
			runlog('CRON', $cron['name'].' : Cron script('.$cron['filename'].') not found or syntax error', 0);
		}

		@unlink($lockfile);
	}
	
	//更新config
	cron_config();
}

//最先执行
function cron_config() {
	global $_SGLOBAL;
	
	//下次执行cron时间
	$query = $_SGLOBAL['db']->query("SELECT nextrun FROM ".tname('cron')." WHERE available>'0' ORDER BY nextrun LIMIT 1");
	$nextrun = $_SGLOBAL['db']->result($query, 0);
	if(empty($nextrun)) $nextrun = 0;

	//更新config
	inserttable('config', array('var'=>'cronnextrun', 'datavalue'=>$nextrun), 0, true);
	include_once S_ROOT.'./source/function_cache.php';
	config_cache(false);
}

//下次执行的时间
function cronnextrun($cron) {
	global $_SGLOBAL, $_SCONFIG;

	if(empty($cron)) return FALSE;

	list($yearnow, $monthnow, $daynow, $weekdaynow, $hournow, $minutenow) = explode('-', sgmdate('Y-m-d-w-H-i', $_SGLOBAL['timestamp']));
	
	$cron['minute'] = explode("\t", $cron['minute']);
	
	if($cron['weekday'] == -1) {
		if($cron['day'] == -1) {
			$firstday = $daynow;
			$secondday = $daynow + 1;
		} else {
			$firstday = $cron['day'];
			$secondday = $cron['day'] + sgmdate('t', $_SGLOBAL['timestamp']);
		}
	} else {
		$firstday = $daynow + ($cron['weekday'] - $weekdaynow);
		$secondday = $firstday + 7;
	}

	if($firstday < $daynow) {
		$firstday = $secondday;
	}

	if($firstday == $daynow) {
		$todaytime = crontodaynextrun($cron);
		if($todaytime['hour'] == -1 && $todaytime['minute'] == -1) {
			$cron['day'] = $secondday;
			$nexttime = crontodaynextrun($cron, 0, -1);
			$cron['hour'] = $nexttime['hour'];
			$cron['minute'] = $nexttime['minute'];
		} else {
			$cron['day'] = $firstday;
			$cron['hour'] = $todaytime['hour'];
			$cron['minute'] = $todaytime['minute'];
		}
	} else {
		$cron['day'] = $firstday;
		$nexttime = crontodaynextrun($cron, 0, -1);
		$cron['hour'] = $nexttime['hour'];
		$cron['minute'] = $nexttime['minute'];
	}

	//更新下次运行时间
	$nextrun = @gmmktime($cron['hour'], $cron['minute'], 0, $monthnow, $cron['day'], $yearnow) - $_SCONFIG['timeoffset'] * 3600;
	$setarr = array(
		'lastrun' => $_SGLOBAL['timestamp'],
		'nextrun' => $nextrun
	);
	if($nextrun <= $_SGLOBAL['timestamp']) {
		$setarr['available'] = 0;
	}
	updatetable('cron', $setarr, array('cronid'=>$cron['cronid']));
	return TRUE;
}

function crontodaynextrun($cron, $hour = -2, $minute = -2) {
	global $_SGLOBAL;

	$hour = $hour == -2 ? sgmdate('H', $_SGLOBAL['timestamp']) : $hour;
	$minute = $minute == -2 ? sgmdate('i', $_SGLOBAL['timestamp']) : $minute;

	$nexttime = array();
	if($cron['hour'] == -1 && !$cron['minute']) {
		$nexttime['hour'] = $hour;
		$nexttime['minute'] = $minute + 1;
	} elseif($cron['hour'] == -1 && $cron['minute'] != '') {
		$nexttime['hour'] = $hour;
		if(($nextminute = cronnextminute($cron['minute'], $minute)) === false) {
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
		$nextminute = cronnextminute($cron['minute'], $minute);
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

function cronnextminute($nextminutes, $minutenow) {
	foreach($nextminutes as $nextminute) {
		if($nextminute > $minutenow) {
			return $nextminute;
		}
	}
	return false;
}

?>