<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: function_image.php 7350 2008-05-12 09:36:04Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//������
$idtype = 'blogid';//ֻ����־����
magic_check_idtype($id, $idtype);

//��ֽ
if(submitcheck("usesubmit")) {

	//������ֽ����
	$_POST['paper'] = intval($_POST['paper']);
	updatetable('blogfield', array('magicpaper'=>$_POST['paper']), array('blogid'=>$id));

	magic_use($mid, array('id'=>$id, 'idtype'=>$idtype), true);
	showmessage('magicuse_success', $_POST['refer'], 0);
}

?>