<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: function_image.php 7350 2008-05-12 09:36:04Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

magic_check_idtype($id, $idtype);
//����ظ�ʹ��
if($_SGLOBAL['db']->result($_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('magicuselog')." WHERE id = '$id' AND idtype = '$idtype' AND uid = '$_SGLOBAL[supe_uid]' AND mid = '$mid'"), 0)) {
	showmessage("magicuse_object_once_limit");//�Ѿ��Ը���Ϣʹ�ù��˵��ߣ������ظ�ʹ��
}

//�ȵ��
if(submitcheck("usesubmit")) {

	//������Ϣ�ȵ�ֵ
	$hot = intval($_SCONFIG['feedhotmin']);
	$_SGLOBAL['db']->query('UPDATE '.tname('feed')." SET hot = hot + $hot WHERE id = '$id' AND idtype = '$idtype' AND uid = '$_SGLOBAL[supe_uid]'");
	//������־�ȵ�ֵ
	$_SGLOBAL['db']->query('UPDATE '.tname('blog')." SET hot = hot + $hot WHERE blogid = '$id' AND uid = '$_SGLOBAL[supe_uid]'");

	magic_use($mid, array('id'=>$id, 'idtype'=>$idtype), true);
	showmessage('magicuse_success', $_POST['refer']);
}

?>