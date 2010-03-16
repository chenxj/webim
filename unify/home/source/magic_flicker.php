<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: function_image.php 7350 2008-05-12 09:36:04Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//检查对象及重复使用
$idtype = 'cid';
$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('comment')." WHERE cid = '$id' AND authorid = '$_SGLOBAL[supe_uid]'");
$value = $_SGLOBAL['db']->fetch_array($query);
if(empty($value)) {
	showmessage('magicuse_bad_object');
} elseif($value['magicflicker']) {
	showmessage('magicuse_object_once_limit');
}

//彩虹炫
if(submitcheck("usesubmit")) {

	updatetable('comment', array('magicflicker'=>1), array('cid'=>$id, 'authorid'=>$_SGLOBAL['supe_uid']));
	magic_use($mid, array('id'=>$id, 'idtype'=>$idtype), true);
	showmessage('magicuse_success', $_POST['refer']);
}

?>