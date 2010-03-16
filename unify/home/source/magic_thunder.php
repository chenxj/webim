<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: function_image.php 7350 2008-05-12 09:36:04Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//À×ÃùÖ®Éù
if(submitcheck("usesubmit")) {

	magic_use($mid, array(), true);

	//·¢È«Õ¾feed
	$uid = $_SGLOBAL['supe_uid'];
	realname_set($_SGLOBAL['supe_uid'], $_SGLOBAL['supe_username']);
	realname_get();

	$_SGLOBAL['supe_uid'] = 0;
	include_once(S_ROOT.'./source/function_cp.php');
	$avatar = ckavatar($uid) ? avatar($uid, 'middle',true) : UC_API.'/images/noavatar_middle.gif';
	feed_add(
		'thunder',
		cplang('magicuse_thunder_announce_title'),
		array(
			'uid' => $uid,
			'username' => "<a href=\"space.php?uid=$uid\">{$_SN[$uid]}</a>"),
		cplang('magicuse_thunder_announce_body'),
		array(
			'uid' => $uid,
			'magic_thunder' =>1),
		'',
		array($avatar),
		array("space.php?uid=$uid")
	);
	showmessage('magicuse_success', $_POST['refer'], 0);
}

?>