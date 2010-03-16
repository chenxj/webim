<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: admincp_ip.php 12776 2009-07-20 07:57:21Z zhengqingpeng $
*/

if(!defined('IN_UCHOME') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('manageip')) {
	cpmessage('no_authority_management_operation');
}

if(submitcheck('thevaluesubmit')) {

	$setarr = array();
	
	//ip允许
	$_POST['config']['ipaccess'] = trim(preg_replace("/(\s*(\r\n|\n\r|\n|\r)\s*)/", "\r\n", $_POST['config']['ipaccess']));
	if(!ipaccess($_POST['config']['ipaccess'])) {
		cpmessage('ip_is_not_allowed_to_visit_the_area', '', 1, array($_SGLOBAL[onlineip]));
	}
	
	//ip禁止
	$_POST['config']['ipbanned'] = saddslashes(trim(preg_replace("/(\s*(\r\n|\n\r|\n|\r)\s*)/", "\r\n", $_POST['config']['ipbanned'])));
	if(ipbanned($_POST['config']['ipbanned'])) {
		cpmessage('the_prohibition_of_the_visit_within_the_framework_of_ip', '', 1, array($_SGLOBAL[onlineip]));
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
	config_cache();;

	cpmessage('do_success', 'admincp.php?ac=ip');
}
$ipbanned = '';
$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('config')." WHERE var IN ('ipbanned', 'ipaccess')");
while ($value = $_SGLOBAL['db']->fetch_array($query)) {
	$value['datavalue'] = shtmlspecialchars($value['datavalue']);
	$configs[$value['var']] = $value['datavalue'];
}

$onlineip = getonlineip();

?>