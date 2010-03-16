<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: function_image.php 7350 2008-05-12 09:36:04Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//检查参数
magic_check_idtype($id, $idtype);

//救生圈
if(submitcheck("usesubmit")) {

	//修改信息时间
	$tablename = gettablebyidtype($idtype);
	$_SGLOBAL['db']->query("UPDATE ".tname($tablename)." SET dateline = '$_SGLOBAL[timestamp]' WHERE $idtype = '$id' AND uid = '$_SGLOBAL[supe_uid]'");

	//同时修改feed的时间
	$_SGLOBAL['db']->query("UPDATE ".tname('feed')." SET dateline = '$_SGLOBAL[timestamp]' WHERE id = '$id' AND idtype = '$idtype' AND uid = '$_SGLOBAL[supe_uid]'");

	magic_use($mid, array('id'=>$id, 'idtype'=>$idtype), true);
	showmessage('magicuse_success', $_POST['refer'], 0);
}

?>