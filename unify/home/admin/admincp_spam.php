<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: admincp_spam.php 10621 2008-12-11 02:24:51Z liguode $
*/

if(!defined('IN_UCHOME') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('manageconfig')) {
	cpmessage('no_authority_management_operation');
}

if(submitcheck('spamsubmit')) {

	if(empty($_POST['config']['seccode_login'])) $_POST['config']['seccode_login'] = 0;
	if(empty($_POST['config']['seccode_register'])) $_POST['config']['seccode_register'] = 0;

	//去除空的
	$datas = array();
	foreach ($_POST['data']['question'] as $key => $value) {
		$value = trim($value);
		$a_value = trim($_POST['data']['answer'][$key]);
		if($value && $a_value) {
			$datas['question'][] = $value;
			$datas['answer'][] = $a_value;
		}
	}
	if(empty($datas['question']) && $_POST['config']['questionmode']) {
		$_POST['config']['questionmode'] = 0;
	}
	data_set('spam', $datas);
	
	$setarr = array();
	foreach ($_POST['config'] as $var => $value) {
		$value = trim($value);
		$setarr[] = "('$var', '$value')";
	}
	if($setarr) {
		$_SGLOBAL['db']->query("REPLACE INTO ".tname('config')." (var, datavalue) VALUES ".implode(',', $setarr));
	}

	//更新缓存
	include_once(S_ROOT.'./source/function_cache.php');
	config_cache();
	
	cpmessage('do_success', 'admincp.php?ac=spam');
}

$configs = array();
$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('config'));
while ($value = $_SGLOBAL['db']->fetch_array($query)) {
	$configs[$value['var']] = shtmlspecialchars($value['datavalue']);
}

$datas = data_get('spam');
if($datas) $datas = unserialize($datas);
$onlineip = getonlineip();

?>