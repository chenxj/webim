<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: function_image.php 7350 2008-05-12 09:36:04Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

magic_check_idtype($id, $idtype);
//检查重复使用
if($_SGLOBAL['db']->result($_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('magicuselog')." WHERE id = '$id' AND idtype = '$idtype' AND uid = '$_SGLOBAL[supe_uid]' AND mid = '$mid'"), 0)) {
	showmessage("magicuse_object_once_limit");//已经对该信息使用过此道具，不能重复使用
}

//热点灯
if(submitcheck("usesubmit")) {

	//增加信息热点值
	$hot = intval($_SCONFIG['feedhotmin']);
	$_SGLOBAL['db']->query('UPDATE '.tname('feed')." SET hot = hot + $hot WHERE id = '$id' AND idtype = '$idtype' AND uid = '$_SGLOBAL[supe_uid]'");
	//增加日志热点值
	$_SGLOBAL['db']->query('UPDATE '.tname('blog')." SET hot = hot + $hot WHERE blogid = '$id' AND uid = '$_SGLOBAL[supe_uid]'");

	magic_use($mid, array('id'=>$id, 'idtype'=>$idtype), true);
	showmessage('magicuse_success', $_POST['refer']);
}

?>