<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: admincp_cron.php 11954 2009-04-17 09:29:53Z liguode $
*/

if(!defined('IN_UCHOME') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

include_once(S_ROOT.'./source/function_cron.php');

//权限
if(!checkperm('managecron')) {
	cpmessage('no_authority_management_operation');
}

$list = $thevalue = array();
$cronid = empty($_GET['cronid'])?0:intval($_GET['cronid']);

if(submitcheck('cronsubmit')) {

	$_POST['name'] = shtmlspecialchars($_POST['name']);

	$_POST['filename'] = str_replace(array('..', '/', '\\'), array('', '', ''), $_POST['filename']);
	if(!is_readable(S_ROOT.'./source/cron/'.$_POST['filename'])) {
		cpmessage('designated_script_file_incorrect');
	}

	if($_POST['weekday'] != '-1') {
		$_POST['day'] = '-1';
	}

	if(is_array($_POST['minute']) && $_POST['minute']) {
		foreach($_POST['minute'] as $key => $var) {
			if($var < 0 || $var > 59) {
				unset($_POST['minute'][$key]);
			}
		}
		sort($_POST['minute']);
		$_POST['minute'] = array_unique($_POST['minute']);
	}
	$postminute = implode("\t", $_POST['minute']);
	
	if($_POST['weekday'] == -1 && $_POST['day'] == -1 && $_POST['hour'] == -1 && $postminute == '') {
		cpmessage('implementation_cycle_incorrect_script');
	}
	
	$setarr = array(
		'name' => $_POST['name'],
		'filename' => $_POST['filename'],
		'available' => $_POST['available'],
		'weekday' => $_POST['weekday'],
		'day' => $_POST['day'],
		'hour' => $_POST['hour'],
		'minute' => $postminute
	);
	
	if(empty($cronid)) {
		//ADD
		$setarr['type'] = 'user';
		$setarr['nextrun'] = $_SGLOBAL['timestamp'];
		$setarr['cronid'] = inserttable('cron', $setarr, 1);//返回cronid
	} else {
		//UPDATE
		updatetable('cron', $setarr, array('cronid'=>$cronid));
		$setarr['cronid'] = $cronid;
	}
	
	//重新计算下次执行时间
	cronnextrun($setarr);
	
	//更新config
	cron_config();
		
	cpmessage('do_success', 'admincp.php?ac=cron');
}

if($_GET['op'] == 'edit') {
	
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('cron')." WHERE cronid='$cronid'");
	$thevalue = $_SGLOBAL['db']->fetch_array($query);

} elseif ($_GET['op'] == 'add') {
	
	$thevalue = array('week'=>'-1', 'hour'=>'-1', 'day'=>'-1', 'minute'=>'0', 'available'=>1);
	
} elseif ($_GET['op'] == 'delete') {

	$_SGLOBAL['db']->query("DELETE FROM ".tname('cron')." WHERE cronid='$cronid' AND type='user'");
	
	//更新缓存
	cron_config();
	
	cpmessage('do_success', 'admincp.php?ac=cron');

} elseif ($_GET['op'] == 'run') {
	
	runcron($cronid);
	
	cpmessage('do_success', 'admincp.php?ac=cron');
	
} else {
	//列表
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('cron')." ORDER BY type DESC");
	while($cron = $_SGLOBAL['db']->fetch_array($query)) {
		foreach(array('weekday', 'day', 'hour', 'minute') as $key) {
			if(in_array($cron[$key], array(-1, ''))) {
				$cron[$key] = '*';
			} elseif($key == 'weekday') {
				$cron[$key] = 1+$cron[$key];
			} elseif($key == 'minute') {
				foreach($cron[$key] = explode("\t", $cron[$key]) as $k => $v) {
					$cron[$key][$k] = sprintf('%02d', $v);
				}
				$cron[$key] = implode(',', $cron[$key]);
			}
		}
		$cron['lastrun'] = $cron['lastrun'] ? sgmdate("Y-m-d H:i:s", $cron['lastrun']) : 'N/A';
		$cron['nextrun'] = $cron['available'] && $cron['nextrun'] ? sgmdate("Y-m-d H:i:s", $cron['nextrun']) : 'N/A';
		$list[] = $cron;
	}
	
	$actives = array('view' => ' class="active"');
}

$weekdays = array($thevalue['weekday'] => ' selected');

$daystr = '';
for($i=1; $i<32; $i++) {
	$selstr = $thevalue['day'] == $i?' selected':'';
	$daystr .= "<option value=\"$i\"$selstr>$i</option>";
}

$hourstr = '';
for($i=0; $i<24; $i++) {
	$selstr = $thevalue['hour'] == $i?' selected':'';
	$hourstr .= "<option value=\"$i\"$selstr>$i</option>";
}

$minuteselect = '';
$cronminutearr = explode("\t", trim($thevalue['minute']));
for($i = 0; $i < 12; $i++) {
	$minuteselect .= '<select name="minute[]"><option value="-1">*</option>';
	for($j = 0; $j <= 59; $j++) {
		$selected = '';
		if(isset($cronminutearr[$i]) && $cronminutearr[$i] == $j) {
			$selected = ' selected';
		}
		$minuteselect .= '<option value="'.$j.'"'.$selected.'>'.sprintf("%02d", $j).'</option>';
	}
	$minuteselect .= '</select>'.($i == 5 ? '<br>' : ' ');
}

$availables = array($thevalue['available'] => ' checked');

?>