<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: admincp_config.php 12998 2009-08-05 03:29:54Z liguode $
*/

if(!defined('IN_UCHOME') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('manageconfig')) {
	cpmessage('no_authority_management_operation');
}

if(submitcheck('thevaluesubmit')) {

	$setarr = array();
	
	//视频认证
	if($_POST['config']['videophoto'] && empty($_SCONFIG['my_status'])) $_POST['config']['videophoto'] = 0;

	//UCenter路径
	$_POST['config']['uc_dir'] = trim($_POST['config']['uc_dir']);
	
	if($_POST['config']['uc_dir']) {
		@define('IN_UC', TRUE);
		if(strexists($_POST['config']['uc_dir'], ':/') || strexists($_POST['config']['uc_dir'], ':\\') || !@include($_POST['config']['uc_dir'].'./model/base.php')) {
			cpmessage('config_uc_dir_error');
		}
	}

	$_POST['config']['newspaceavatar'] = intval($_POST['config']['newspaceavatar']);
	$_POST['config']['newspacerealname'] = intval($_POST['config']['newspacerealname']);
	$_POST['config']['newspacevideophoto'] = intval($_POST['config']['newspacevideophoto']);
	
	foreach ($_POST['config'] as $var => $value) {
		$value = trim($value);
		if($var == 'timeoffset') {
			$value = intval($value);
		}
		$setarr[] = "('$var', '$value')";
	}
	if($setarr) {
		$_SGLOBAL['db']->query("REPLACE INTO ".tname('config')." (var, datavalue) VALUES ".implode(',', $setarr));
	}
	
	//date_set
	$setarr = array();
	foreach ($_POST['dataset'] as $var => $value) {
		$value = trim($value);
		$setarr[] = "('$var', '$value')";
	}
	if($setarr) {
		$_SGLOBAL['db']->query("REPLACE INTO ".tname('data')." (var, datavalue) VALUES ".implode(',', $setarr));
	}
	
	//data设置
	$datas = array();
	foreach ($_POST['data'] as $var => $value) {
		$datas[$var] = trim(stripslashes($value));
	}
	data_set('setting', $datas);
	
	//发送邮件设置
	$mails = array();
	foreach ($_POST['mail'] as $var => $value) {
		$mails[$var] = trim(stripslashes($value));
	}
	data_set('mail', $mails);

	//更新缓存
	include_once(S_ROOT.'./source/function_cache.php');
	config_cache();

	cpmessage('do_success', 'admincp.php?ac=config');
}

$configs = array();
$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('config'));
while ($value = $_SGLOBAL['db']->fetch_array($query)) {
	$configs[$value['var']] = shtmlspecialchars($value['datavalue']);
}

if(empty($configs['feedfilternum']) || $configs['feedfilternum']<1) $configs['feedfilternum'] = 1;

$datasets = $datas = $mails = array();
$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('data'));
while ($value = $_SGLOBAL['db']->fetch_array($query)) {
	if($value['var'] == 'setting' || $value['var'] == 'mail') {
		$datasets[$value['var']] = empty($value['datavalue'])?array():unserialize($value['datavalue']);
	} else {
		$datasets[$value['var']] = shtmlspecialchars($value['datavalue']);
	}
}

$datas = $datasets['setting'];
$mails = $datasets['mail'];

//模板目录
$templatearr = array('default' => 'default');
$tpl_dir = sreaddir(S_ROOT.'./template');
foreach ($tpl_dir as $dir) {
	if(file_exists(S_ROOT.'./template/'.$dir.'/style.css')) {
		$templatearr[$dir] = $dir;
	}
}

$templateselect = array($configs['template'] => ' selected');
$toselect = array($configs['timeoffset'] => ' selected'); 

$onlineip = getonlineip();

?>