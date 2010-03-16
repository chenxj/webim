<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: admincp_hotuser.php 12778 2009-07-20 08:03:03Z zhengqingpeng $
*/

if(!defined('IN_UCHOME') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

//权限
$managehotuser = checkperm('managehotuser');
$managedefaultuser = checkperm('managedefaultuser');
$vars = array();
if($ac == 'hotuser') {
	if(!$managehotuser) {
		cpmessage('no_authority_management_operation');
	}
	$vars[] = 'spacebarusername';
} else {
	if(!$managedefaultuser) {
		cpmessage('no_authority_management_operation');
	}
	$vars[] = 'defaultfusername';
	$vars[] = 'defaultpoke';
}

if(submitcheck('thevaluesubmit')) {

	$setarr = array();
	
	if($ac != 'hotuser') {
		//默认好友
		$fs = array();
		$_POST['config']['defaultfusername'] = trim(preg_replace("/(\s*(\r\n|\n\r|\n|\r)\s*)/", "\r\n", $_POST['config']['defaultfusername']));
		if($_POST['config']['defaultfusername']) {
			$query = $_SGLOBAL['db']->query("SELECT uid,username FROM ".tname('space')." WHERE username IN (".simplode(explode("\r\n", $_POST['config']['defaultfusername'])).")");
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				$fs[$value['uid']] = saddslashes($value['username']);
			}
		}
		$_POST['config']['defaultfusername'] = empty($fs)?'':implode(',', $fs);
	} else {
		//优秀用户
		$fs = array();
		$_POST['config']['spacebarusername'] = trim(preg_replace("/(\s*(\r\n|\n\r|\n|\r)\s*)/", "\r\n", $_POST['config']['spacebarusername']));
		if($_POST['config']['spacebarusername']) {
			$query = $_SGLOBAL['db']->query("SELECT uid,username FROM ".tname('space')." WHERE username IN (".simplode(explode("\r\n", $_POST['config']['spacebarusername'])).")");
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				$fs[$value['uid']] = saddslashes($value['username']);
			}
		}
		$_POST['config']['spacebarusername'] = empty($fs)?'':implode(',', $fs);
	}
	
	foreach ($_POST['config'] as $var => $value) {
		$value = trim($value);
		if(!isset($_SCONFIG[$var]) || $_SCONFIG[$var] != $value) {
			$setarr[] = "('$var', '$value')";
		}
	}

	if($setarr) {
		$_SGLOBAL['db']->query("REPLACE INTO ".tname('config')." (var, datavalue) VALUES ".implode(',', $setarr));
	}

	//更新缓存
	include_once(S_ROOT.'./source/function_cache.php');
	config_cache();

	cpmessage('do_success', 'admincp.php?ac='.$ac);
}

$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('config')." WHERE var IN (".simplode($vars).")");
while ($value = $_SGLOBAL['db']->fetch_array($query)) {
	$value['datavalue'] = shtmlspecialchars($value['datavalue']);
	if(in_array($value['var'], array('defaultfusername', 'spacebarusername'))) {
		$value['datavalue'] = implode("\r\n", explode(',', $value['datavalue']));
	}
	$configs[$value['var']] = $value['datavalue'];
}

?>