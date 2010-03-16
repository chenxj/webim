<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: invite.php 12161 2009-05-12 07:20:09Z zhengqingpeng $
*/

include_once('./common.php');

//是否关闭站点
checkclose();

$invite = array();

$get = array();
//积分
$reward = getreward('invitecode', 0);

//参数
$_GET['u'] = empty($_GET['u'])?0:intval($_GET['u']);
$_GET['c'] = empty($_GET['c'])?'':trim($_GET['c']);
$_GET['app'] = empty($_GET['app'])?'':intval($_GET['app']);
$get = empty($_SERVER['QUERY_STRING'])?'':$_SERVER['QUERY_STRING'];

if($_GET['app']) {
	$reward['credit'] = '';
}
if($_GET['u'] && empty($reward['credit'])) {
	//免费邀请
	$invite['uid'] = $_GET['u'];
	$theurl = "invite.php?u=$_GET[u]&app=$_GET[app]&c=$_GET[c]";
	$url_plus = "uid=$invite[uid]&app=$_GET[app]&code=$_GET[c]";
} else {
	$invite = getinvite($get);
	$theurl = "invite.php?$get";
	$url_plus = "uid=$invite[uid]&invite=$invite[code]";
}

//获取邀请人
$space = getspace($invite['uid']);
if(empty($space)) {
	showmessage('space_does_not_exist');
}
//检查验证
if($_GET['u'] && empty($reward['credit'])) {
	if($_GET['c'] != space_key($space, $_GET['app'])) {
		showmessage('invite_code_error');
	}
}

//是否好友
if($space['self']) {
	showmessage('should_not_invite_your_own');
}
$space['isfriend'] = 0;
if($_SGLOBAL['supe_uid'] && $space['friends'] && in_array($_SGLOBAL['supe_uid'], $space['friends'])) {
	$space['isfriend'] = 1;//是好友
}
$jumpurl = $_GET['app']?"userapp.php?id=$_GET[app]&my_extra=invitedby_bi_$_GET[u]_$_GET[c]&my_suffix=Lw%3D%3D":"space.php?uid=$space[uid]";
if($space['isfriend']) {
	showmessage('you_have_friends', $jumpurl, 1);
}

if(submitcheck('invitesubmit')) {
	if(empty($_SGLOBAL['supe_uid'])) {
		showmessage('invite_code_error');
	}
	include_once(S_ROOT.'./source/function_cp.php');
	invite_update($invite['id'], $_SGLOBAL['supe_uid'], $_SGLOBAL['supe_username'], $space['uid'], $space['username'], $_GET['app']);

	showmessage('friends_add', $jumpurl, 1, array($_SN[$space['uid']]));
}

//好友列表
$flist = array();
$query = $_SGLOBAL['db']->query("SELECT fuid AS uid, fusername AS username FROM ".tname('friend')." WHERE uid='$invite[uid]' AND status='1' ORDER BY num DESC, dateline DESC LIMIT 0,12");
while ($value = $_SGLOBAL['db']->fetch_array($query)) {
	realname_set($value['uid'], $value['username']);
	$flist[] = $value;
}

realname_get();

$albumnum = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('album')." WHERE uid='$invite[uid]'"), 0);
$doingnum = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('doing')." WHERE uid='$invite[uid]'"), 0);
$blognum = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('blog')." WHERE uid='$invite[uid]'"), 0);
$threadnum = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('thread')." WHERE uid='$invite[uid]'"), 0);
$tagspacenum = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('tagspace')." WHERE uid='$invite[uid]'"), 0);


//获取应用
$userapp = array();
if($_GET['app']) {
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('myapp')." WHERE appid='$_GET[app]'");
	$userapp = $_SGLOBAL['db']->fetch_array($query);
}

include_once template('invite');

function getinvite($invite) {
	global $_SGLOBAL;
	
	$id = 0;
	$code = '';

	$invite_len = strlen($invite);
	if($invite_len > 6) {
		$code = addslashes(substr($invite, -6));
		$id = str_replace($code, '', $invite);
		$id = intval($id);
	}
	if(empty($id)) {
		showmessage('invite_code_error');
	}
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('invite')." WHERE id='$id' AND code='$code'");
	if(!$invite = $_SGLOBAL['db']->fetch_array($query)) {
		showmessage('invite_code_error');
	}
	if($invite['fuid']) {
		showmessage('invite_code_fuid');
	}
	if($_SGLOBAL['supe_uid'] == $invite['uid']) {
		showmessage('should_not_invite_your_own');
	}
	return $invite;
}

?>