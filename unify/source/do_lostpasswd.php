<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: do_lostpasswd.php 12480 2009-06-30 07:56:02Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$op = empty($_GET['op'])?'':$_GET['op'];

if(submitcheck('lostpwsubmit')) {

	$spaceinfo = array();
	$query = $_SGLOBAL['db']->query('SELECT s.uid, s.groupid, s.username, s.flag, sf.email, sf.emailcheck FROM '.tname('space').' s LEFT JOIN '.tname('spacefield')." sf ON sf.uid=s.uid WHERE s.username='$_POST[username]'");
	$spaceinfo = $_SGLOBAL['db']->fetch_array($query);
	if(empty($spaceinfo['email']) || !isemail($spaceinfo['email'])) {
		showmessage('getpasswd_account_notmatch');
	}
	
	//创始人、管理员不允许找回密码
	$founderarr = explode(',', $_SC['founder']);
	if($spaceinfo['flag'] || in_array($spaceinfo['uid'], $founderarr) || checkperm('admin')) {
		showmessage('getpasswd_account_invalid');
	}
	
	$op = 'email';
	$username = $spaceinfo['username'];
	$email = substr($spaceinfo['email'], strpos($spaceinfo['email'], '@'));
	
} elseif(submitcheck('emailsubmit')) {
	
	//获取UCHome本身的邮箱地址
	$spaceinfo = array();
	$query = $_SGLOBAL['db']->query('SELECT s.uid, s.groupid, s.username, s.flag, sf.email, sf.emailcheck FROM '.tname('space').' s LEFT JOIN '.tname('spacefield')." sf ON sf.uid=s.uid WHERE s.username='$_POST[username]'");
	$spaceinfo = $_SGLOBAL['db']->fetch_array($query);
	if(empty($spaceinfo['email']) || $spaceinfo['email'] != $_POST['email']) {
		showmessage('getpasswd_email_notmatch');
	}
	
	//创始人、管理员不允许找回密码
	$founderarr = explode(',', $_SC['founder']);
	if($spaceinfo['flag'] || in_array($spaceinfo['uid'], $founderarr) || checkperm('admin')) {
		showmessage('getpasswd_account_invalid');
	}

	$idstring = random(6);
	$reseturl = getsiteurl().'do.php?ac=lostpasswd&amp;op=reset&amp;uid='.$spaceinfo['uid'].'&amp;id='.$idstring;
	updatetable('spacefield', array('authstr'=>$_SGLOBAL['timestamp']."\t1\t".$idstring), array('uid'=>$spaceinfo['uid']));
	$mail_subject = cplang('get_passwd_subject');
	$mail_message = cplang('get_passwd_message', array($reseturl));

	include_once(S_ROOT.'./source/function_cp.php');
	smail(0, $spaceinfo['email'], $mail_subject, $mail_message);
	
	showmessage('getpasswd_send_succeed', 'do.php?ac='.$_SCONFIG['login_action'], 5);
	
} elseif(submitcheck('resetsubmit')) {
	
	$uid = empty($_POST['uid'])?0:intval($_POST['uid']);
	$id = empty($_POST['id'])?0:trim($_POST['id']);
	if($_POST['newpasswd1'] != $_POST['newpasswd2']) {
		showmessage('password_inconsistency');
	}
	if($_POST['newpasswd1'] != addslashes($_POST['newpasswd1'])) {
		showmessage('profile_passwd_illegal');
	}
	
	$query = $_SGLOBAL['db']->query('SELECT s.uid, s.username, s.groupid, s.flag, sf.email, sf.authstr FROM '.tname('space').' s, '.tname('spacefield')." sf WHERE s.uid='$uid' AND sf.uid=s.uid");
	$space = $_SGLOBAL['db']->fetch_array($query);
	checkuser($id, $space);
	
	//验证是否受保护、创始人、有站点设置权限的人禁止找回密码方式修改密码
	$founderarr = explode(',', $_SC['founder']);
	if($space['flag'] || in_array($space['uid'], $founderarr) || checkperm('admin')) {
		showmessage('reset_passwd_account_invalid');
	}
	
	if(!@include_once S_ROOT.'./uc_client/client.php') {
		showmessage('system_error');
	}
	if(uc_user_edit(addslashes($space['username']), $_POST['newpasswd1'], $_POST['newpasswd1'], $space['email'], 1)>0) {
		updatetable('spacefield', array('authstr'=>''), array('uid'=>$uid));
	}
	showmessage('getpasswd_succeed');
}

if($op == 'reset') {
	$query = $_SGLOBAL['db']->query('SELECT s.username, sf.email, sf.authstr FROM '.tname('space').' s, '.tname('spacefield')." sf WHERE s.uid='$_GET[uid]' AND sf.uid=s.uid");
	$space = $_SGLOBAL['db']->fetch_array($query);
	checkuser($_GET['id'], $space);
}

include template('do_lostpasswd');

//验证地址地否有效
function checkuser($id, $space) {
	global $_SGLOBAL;
	if(empty($space)) {
		showmessage('user_does_not_exist');
	}
	list($dateline, $operation, $idstring) = explode("\t", $space['authstr']);
	if($dateline < $_SGLOBAL['timestamp'] - 86400 * 3 || $operation != 1 || $idstring != $id) {
		showmessage('getpasswd_illegal');
	}
}
?>