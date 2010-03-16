<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp.php 12352 2009-06-11 06:59:06Z liguode $
*/

//通用文件
include_once('./common.php');
include_once(S_ROOT.'./source/function_cp.php');
include_once(S_ROOT.'./source/function_magic.php');

$mid = empty($_GET['mid'])?'':trim($_GET['mid']);
$op = empty($_GET['op'])?'use':$_GET['op'];
$id = empty($_GET['id'])?0:intval($_GET['id']);
$idtype = empty($_GET['idtype'])?'':trim($_GET['idtype']);

//权限判断
if(empty($_SGLOBAL['supe_uid'])) {
	showmessage('to_login', 'do.php?ac='.$_SCONFIG['login_action']);
}

//站点关闭
checkclose();

//MID检查
if(empty($mid)) {
	showmessage('unknown_magic');
}

//获取空间信息
$space = getspace($_SGLOBAL['supe_uid']);
if(empty($space)) {
	showmessage('space_does_not_exist');
}

//获得道具
$magic = magic_get($mid);

//是否拥有该道具
$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname("usermagic")." WHERE uid='$_SGLOBAL[supe_uid]' AND mid='$mid'");
$usermagic = $_SGLOBAL['db']->fetch_array($query);
if(empty($usermagic['count'])) {
	$op = 'buy';
}

//提交购买
$frombuy = false;
if(submitcheck('buysubmit')) {
	//获得道具信息
	$results = magic_buy_get($magic);
	extract($results);

	//购买道具
	magic_buy_post($magic, $magicstore, $coupon);

	$op = 'use';
	$frombuy = true;//标记是购买后立即使用
	$usermagic['count'] += $_POST['buynum'];
}

//购买道具
if($op == 'buy') {

	//获得道具信息
	$results = magic_buy_get($magic);
	extract($results);

	//某些道具需要传递的附加信息
	$extra = '';
	if($mid == 'doodle') {
		$extra = "&showid=$_GET[showid]&target=$_GET[target]&from=$_GET[from]";
	}

	include_once template('cp_magic');
	exit();

}

//检查在使用周期内的使用次数
if($magic['useperoid'] > 0) {
	$time = $_SGLOBAL['timestamp'] - $magic['useperoid'];
	$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('magicuselog')." WHERE uid='$_SGLOBAL[supe_uid]' AND mid='$mid' AND dateline > '$time'"), 0);
	if($count >= $magic['usecount']) {
		//取周期内最早使用的一条记录，计算下次使用时间
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('magicuselog')." WHERE uid='$_SGLOBAL[supe_uid]' AND mid='$mid' AND dateline > '$time' ORDER BY dateline LIMIT 1");
		$value = $_SGLOBAL['db']->fetch_array($query);
		$nexttime = sgmdate('m-d H:i:s', $value['dateline'] + $magic['useperoid']);
		showmessage('magic_usecount_limit', '', '', array($nexttime));
	}
}

include_once(S_ROOT.'./source/magic_'.$mid.'.php');
include_once template('magic_'.$mid);

?>