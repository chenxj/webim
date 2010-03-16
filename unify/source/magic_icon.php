<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: function_image.php 7350 2008-05-12 09:36:04Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$idtype = 'tid';
$thread = magic_check_idtype($id, $idtype);
if($thread['magicegg'] >= 8) {
	showmessage('magicuse_object_count_limit', '', '', array(8));//此道具对同一目标最多使用 \\1 次
}

//彩虹蛋
if(submitcheck('usesubmit')) {

	$_SGLOBAL['db']->query('UPDATE '.tname('thread')." SET magicegg = magicegg + 1 WHERE tid = '$id' AND uid = '$_SGLOBAL[supe_uid]'");
	magic_use($mid, array('id'=>$id, 'idtype'=>$idtype), true);
	showmessage('magicuse_success', $_POST['refer'], 0);
}

?>