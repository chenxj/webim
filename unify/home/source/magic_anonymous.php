<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: function_image.php 7350 2008-05-12 09:36:04Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//检查作用对象及是否重复使用
if($idtype == 'uid') {
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('visitor')." WHERE uid = '$id' AND vuid = '$_SGLOBAL[supe_uid]'");
	$value = $_SGLOBAL['db']->fetch_array($query);
	if(empty($value)) {
		showmessage('magicuse_bad_object');
	} elseif($value['vusername'] == '') {
		showmessage('magicuse_object_once_limit');
	}
} elseif($idtype == 'cid') {
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('comment')." WHERE cid = '$id' AND authorid = '$_SGLOBAL[supe_uid]'");
	$value = $_SGLOBAL['db']->fetch_array($query);
	if(empty($value)) {
		showmessage('magicuse_bad_object');
	} elseif($value['author'] == '') {
		showmessage('magicuse_object_once_limit');
	}
} else {
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('clickuser')." WHERE id = '$id' AND idtype = '$idtype' AND uid = '$_SGLOBAL[supe_uid]'");
	$value = $_SGLOBAL['db']->fetch_array($query);
	if(empty($value)) {
		showmessage('magicuse_bad_object');
	} elseif($value['username'] == '') {
		showmessage('magicuse_object_once_limit');
	}
}

//匿名卡
if(submitcheck("usesubmit")) {

	$second = 1;
	if($idtype == 'uid') {
		//空间脚印
		ssetcookie('anonymous_visit_'.$_SGLOBAL['supe_uid'].'_'.$id, '1');
		updatetable('visitor', array('vusername'=>''), array('uid'=>$id, 'vuid'=>$_SGLOBAL['supe_uid']));
		$second = 0;
	} elseif($idtype == 'cid') {
		//评论/留言
		updatetable('comment', array('author'=>''), array('cid'=>$id, 'authorid'=>$_SGLOBAL['supe_uid']));
	} else {
		//表态
		updatetable('clickuser', array('username'=>''), array('id'=>$id, 'idtype'=>$idtype, 'uid'=>$_SGLOBAL['supe_uid']));
	}

	magic_use($mid, array('id'=>$id, 'idtype'=>$idtype), true);
	showmessage('magicuse_success', $_POST['refer'], $second);
}

?>