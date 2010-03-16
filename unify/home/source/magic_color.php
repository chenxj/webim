<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: function_image.php 7350 2008-05-12 09:36:04Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//idtype到含有magiccolor字段的表映射
$mapping = array('blogid'=>'blogfield', 'tid'=>'thread');
if(!isset($mapping[$idtype])) {
	showmessage('magicuse_bad_object');
}
magic_check_idtype($id, $idtype);

//彩色灯
if(submitcheck("usesubmit")) {

	//颜色代号
	$tablename = $mapping[$idtype];
	$_POST['color'] = intval($_POST['color']);
	updatetable($tablename, array('magiccolor'=>$_POST['color']), array($idtype=>$id, 'uid'=>$_SGLOBAL['supe_uid']));

	//feed也加上颜色
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('feed')." WHERE id='$id' AND idtype='$idtype' AND uid='$_SGLOBAL[supe_uid]'");
	$feed = $_SGLOBAL['db']->fetch_array($query);
	if($feed) {
		$feed['body_data'] = unserialize($feed['body_data']);
		$feed['body_data'] = is_array($feed['body_data']) ? $feed['body_data'] : array();
		$feed['body_data']['magic_color'] = $_POST['color'];
		$feed['body_data'] = serialize($feed['body_data']);
		updatetable('feed', array('body_data'=>$feed['body_data']), array('feedid'=>$feed['feedid']));
	}

	magic_use($mid, array('id'=>$id, 'idtype'=>$idtype), true);
	showmessage('magicuse_success', $_POST['refer'], 0);
}

?>