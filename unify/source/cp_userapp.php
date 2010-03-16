<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_userapp.php 12754 2009-07-17 08:57:12Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

if(submitcheck('ordersubmit')) {
	if(empty($_POST['order'])) $_POST['order'] = array();
	$displayorder = count($_POST['order']);
	
	foreach ($_POST['order'] as $key => $appid) {
		$appid = intval($appid);
		if($_SGLOBAL['my_userapp'][$appid]['menuorder'] != $displayorder) {
			updatetable('userapp', array('menuorder'=>$displayorder), array('uid'=>$space['uid'], 'appid'=>$appid));
		}
		$displayorder--;
	}
	$_POST['menunum'] = abs(intval($_POST['menunum']));
	if($_POST['menunum'] != $space['menunum']) {
		updatetable('spacefield', array('menunum'=>$_POST['menunum']), array('uid'=>$space['uid']));
	}
	showmessage('do_success', 'cp.php?ac=userapp');
}

//实名认证
ckrealname('userapp');

//视频认证
ckvideophoto('userapp');

//uchome地址
$uchUrl = getsiteurl().'cp.php?ac=userapp';
	
//manyou
$my_prefix = 'http://uchome.manyou.com';
if(empty($_GET['my_suffix'])) {
	$appId = intval($_GET['appid']);
	if ($appId) {
		$mode = $_GET['mode'];
		if ($mode == 'about') {
			$my_suffix = '/userapp/about?appId='.$appId;
		} else {
			$my_suffix = '/userapp/privacy?appId='.$appId;
		}
	} else {
		$my_suffix = '/userapp/list';
	}
} else {
	$my_suffix = $_GET['my_suffix'];
}
$my_extra = isset($_GET['my_extra']) ? $_GET['my_extra'] : '';

$delimiter = strrpos($my_suffix, '?') ? '&' : '?';
$myUrl = $my_prefix.urldecode($my_suffix.$delimiter.'my_extra='.$my_extra);
	
//本地列表
$my_userapp = $my_default_userapp = array();
if($my_suffix == '/userapp/list') {
	$_GET['op'] = 'menu';//模板
	$max_order = 0;
	foreach($_SGLOBAL['userapp'] as $value) {
		if(isset($_SGLOBAL['my_userapp'][$value['appid']])) {
			$my_default_userapp[$value['appid']] = $value;
			unset($_SGLOBAL['my_userapp'][$value['appid']]);
		}
	}
	foreach ($_SGLOBAL['my_userapp'] as $value) {
		$my_userapp[$value['appid']] = $value;
		if($value['displayorder']>$max_order) $max_order = $value['displayorder'];
	}
}
	
$timestamp = $_SGLOBAL['timestamp'];
$hash = $_SCONFIG['my_siteid'].'|'.$_SGLOBAL['supe_uid'].'|'.$_SCONFIG['my_sitekey'].'|'.$timestamp;
$hash = md5($hash);
$delimiter = strrpos($myUrl, '?') ? '&' : '?';
	
$url = $myUrl.$delimiter.'s_id='.$_SCONFIG['my_siteid'].'&uch_id='.$_SGLOBAL['supe_uid'].'&uch_url='.urlencode($uchUrl).'&my_suffix='.urlencode($my_suffix).'&timestamp='.$timestamp.'&my_sign='.$hash;

$actives = array('view'=> ' class="active"');
$menunum[$space['menunum']] = ' selected ';

include_once template("cp_userapp");

?>
