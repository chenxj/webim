<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: function_image.php 7350 2008-05-12 09:36:04Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$idtype = 'picid';
magic_check_idtype($id, $idtype);

//Паїт
if(submitcheck("usesubmit")) {

	$_POST['frame'] = intval($_POST['frame']);
	updatetable('pic', array('magicframe'=>$_POST['frame']), array('picid'=>$id, 'uid'=>$_SGLOBAL['supe_uid']));

	magic_use($mid, array('id'=>$id, 'idtype'=>$idtype), true);
	showmessage('magicuse_success', $_POST['refer'], 0);
}

?>