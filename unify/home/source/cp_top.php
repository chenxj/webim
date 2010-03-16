<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_credit.php 12210 2009-05-21 07:05:38Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

if(submitcheck('friendsubmit')) {
	$showcredit = intval($_POST['stakecredit']);
	if($showcredit > $space['credit']) $showcredit = $space['credit'];
	if($showcredit < 1) {
		showmessage('showcredit_error');
	}
	
	//检测好友
	$_POST['fusername'] = trim($_POST['fusername']);
	$fuid = getcount('friend', array('uid'=>$space['uid'], 'fusername'=>$_POST['fusername'], 'status'=>1), 'fuid');
	if(empty($_POST['fusername']) || empty($fuid) || $fuid == $space['uid']) {
		showmessage('showcredit_fuid_error');
	}
	
	//赠送
	$count = getcount('show', array('uid'=>$fuid));
	if($count) {
		$_SGLOBAL['db']->query("UPDATE ".tname('show')." SET credit=credit+$showcredit WHERE uid='$fuid'");
	} else {
		inserttable('show', array('uid'=>$fuid, 'username'=>$_POST['fusername'], 'credit'=>$showcredit), 0, true);
	}
	
	//减少自己的积分
	$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET credit=credit-$showcredit WHERE uid='$space[uid]'");
	
	//给好友通知
	notification_add($fuid, 'credit', cplang('note_showcredit', array($showcredit)));
	
	//实名
	realname_set($fuid, $_POST['fusername']);
	realname_get();
	
	//feed
	if(ckprivacy('show', 1)) {
		feed_add('show', cplang('feed_showcredit'), array(
			'fusername'=>"<a href=\"space.php?uid=$fuid\">{$_SN[$fuid]}</a>",
			'credit'=>$showcredit));
	}
	
	showmessage('showcredit_friend_do_success', "space.php?do=top");
	
} elseif(submitcheck('showsubmit')) {
	
	$showcredit = intval($_POST['showcredit']);
	if($showcredit > $space['credit']) $showcredit = $space['credit'];
	if($showcredit < 1) {
		showmessage('showcredit_error');
	}
	$_POST['note'] = getstr($_POST['note'], 100, 1, 1, 1);
	
	//增加
	$count = getcount('show', array('uid'=>$_SGLOBAL['supe_uid']));
	if($count) {
		$notesql = $_POST['note']?", note='$_POST[note]'":'';
		$_SGLOBAL['db']->query("UPDATE ".tname('show')." SET credit=credit+$showcredit $notesql WHERE uid='$_SGLOBAL[supe_uid]'");
	} else {
		inserttable('show', array('uid'=>$_SGLOBAL['supe_uid'], 'username'=>$_SGLOBAL['supe_username'], 'credit'=>$showcredit, 'note'=>$_POST['note']), 0, true);
	}

	//减少自己的积分
	$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET credit=credit-$showcredit WHERE uid='$space[uid]'");
	
	//feed
	if(ckprivacy('show', 1)) {
		feed_add('show', cplang('feed_showcredit_self'), array('credit'=>$showcredit), '', array(), $_POST['note']);
	}
		
	showmessage('showcredit_do_success', "space.php?do=top");
}

?>