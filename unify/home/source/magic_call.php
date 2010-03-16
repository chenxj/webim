<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: function_image.php 7350 2008-05-12 09:36:04Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//可以使用点名卡的信息
$mapping = array('blogid'=>'blog', 'tid'=>'thread', 'eventid'=>'event');
if(!isset($mapping[$idtype])) {
	showmessage('magicuse_bad_object');
}
magic_check_idtype($id, $idtype);

$magic['custom']['maxcall'] = $magic['custom']['maxcall'] ? intval($magic['custom']['maxcall']) : 10;

//点名卡
if(submitcheck("usesubmit")) {

	$name = $mapping[$idtype];
	$list = $ids = $note_inserts = array();
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('friend')." WHERE uid='$_SGLOBAL[supe_uid]' AND fusername IN (".simplode($_POST['fusername']).") LIMIT {$magic['custom']['maxcall']}");
	$note = cplang("magic_call", array(lang($name), "space.php?uid=$_SGLOBAL[supe_uid]&do=$name&id=$id"));
	while($value = $_SGLOBAL['db']->fetch_array($query)) {
		realname_set($value['fuid'], $value['fusername']);
		$ids[] = $value['fuid'];
		$list[] = $value;
		$note_inserts[] = "('$value[fuid]', '$name', '1', '$_SGLOBAL[supe_uid]', '$_SGLOBAL[supe_username]', '$note', '$_SGLOBAL[timestamp]')";
	}
	if(empty($ids)) {
		showmessage('magicuse_has_no_valid_friend');//道具使用失败，没有任何合法的好友
	}
	//发送通知
	$_SGLOBAL['db']->query('INSERT INTO '.tname('notification').'(uid, type, new, authorid, author, note, dateline) VALUES '.implode(',',$note_inserts));
	$_SGLOBAL['db']->query('UPDATE '.tname('space').' SET notenum = notenum + 1 WHERE uid IN ('.simplode($ids).')');
	magic_use($mid, array('id'=>$id, 'idtype'=>$idtype), true);

	realname_get();
	
	//显示通知成功的好友
	$op = 'show';
}

?>