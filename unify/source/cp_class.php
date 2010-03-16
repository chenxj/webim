<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_class.php 7690 2008-06-18 06:18:39Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//检查信息
$classid = empty($_GET['classid'])?0:intval($_GET['classid']);
$op = empty($_GET['op'])?'':$_GET['op'];

$class = array();
if($classid) {
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('class')." WHERE classid='$classid' AND uid='$_SGLOBAL[supe_uid]'");
	$class = $_SGLOBAL['db']->fetch_array($query);
}
if(empty($class)) showmessage('did_not_specify_the_type_of_operation');

if ($op == 'edit') {
	
	if(submitcheck('editsubmit')) {
		
		$_POST['classname'] = getstr($_POST['classname'], 40, 1, 1, 1);
		if(strlen($_POST['classname']) < 1) {
			showmessage('enter_the_correct_class_name');
		}
		updatetable('class', array('classname'=>$_POST['classname']), array('classid'=>$classid));
		showmessage('do_success', $_POST['refer'], 0);
	}

} elseif ($op == 'delete') {
	//删除分类
	if(submitcheck('deletesubmit')) {
		//更新日志分类
		updatetable('blog', array('classid'=>0), array('classid'=>$classid));
		$_SGLOBAL['db']->query("DELETE FROM ".tname('class')." WHERE classid='$classid'");
		
		showmessage('do_success', $_POST['refer'], 0);
	}
}

//模版
include_once template("cp_class");
	
?>