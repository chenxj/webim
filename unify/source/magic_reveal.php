<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: function_image.php 7350 2008-05-12 09:36:04Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//检查是否使用了匿名卡
if(!$_SGLOBAL['db']->result($_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('magicuselog')." WHERE id = '$id' AND idtype = '$idtype' AND mid = 'anonymous'"), 0)) {
	showmessage('magicuse_bad_object');
}

if(submitcheck("usesubmit")) {
	
	magic_use($mid, array('id'=>$id, 'idtype'=>$idtype), true);
	
	$op = 'show';
	$list = array();
	$query = $_SGLOBAL['db']->query('SELECT uid, username FROM '.tname('magicuselog')." WHERE id = '$id' AND idtype = '$idtype' AND mid = 'anonymous'");
	while($value = $_SGLOBAL['db']->fetch_array($query)) {
		realname_set($value['uid'], $value['username']);
		$list[] = $value;
	}
}

?>