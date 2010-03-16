<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: function_image.php 7350 2008-05-12 09:36:04Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}
//红包卡

//上个红包的剩余积分
$leftcredit = 0;
$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('magicuselog')." WHERE uid='$_SGLOBAL[supe_uid]' AND mid='$mid'");
$value = $_SGLOBAL['db']->fetch_array($query);
if($value && $value['data']) {
	$data = unserialize($value['data']);
	$leftcredit = intval($data['left']);
}

//每份最大数
$magic['custom']['maxchunk'] = $magic['custom']['maxchunk'] ? intval($magic['custom']['maxchunk']) : 20;

if(submitcheck("usesubmit")) {
	$_POST['credit'] = intval($_POST['credit']);
	$_POST['chunk'] = intval($_POST['chunk']);

	if($_POST['chunk'] < 1 || $_POST['chunk'] > $_POST['credit'] || $_POST['chunk'] > $magic['custom']['maxchunk']) {
		showmessage('magicuse_bad_chunk_given');
	}
	if($_POST['credit'] < 1 || $_POST['credit'] > $space['credit']) {
		showmessage("magicuse_bad_credit_given");
	}

	$_SGLOBAL['db']->query('UPDATE '.tname('space')." SET credit = credit - $_POST[credit] + $leftcredit WHERE uid = '$_SGLOBAL[supe_uid]'");
	$data = array('credit'=>$_POST['credit'], 'chunk'=>$_POST['chunk'], 'left'=>$_POST['credit']);

	magic_use($mid, array('data'=>serialize($data)), true);
	showmessage("magicuse_success", $_POST['refer']);
}

?>