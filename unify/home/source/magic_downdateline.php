<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: function_image.php 7350 2008-05-12 09:36:04Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//������
$blog = magic_check_idtype($id, $idtype);

//ʱ�ջ�
if(submitcheck("usesubmit")) {

	$newdateline = sstrtotime($_POST['newdateline']);
	if(!$_POST['newdateline'] || $newdateline < sstrtotime('1970-1-1') || $newdateline > $blog['dateline']) {
		showmessage('magicuse_bad_dateline');//�����ʱ����Ч
	}

	//�޸Ķ���ʱ��
	$tablename = gettablebyidtype($idtype);
	$_SGLOBAL['db']->query("UPDATE ".tname($tablename)." SET dateline='$newdateline' WHERE $idtype='$id' AND uid='$_SGLOBAL[supe_uid]'");

	//ͬʱ�޸�feed��ʱ��
	$_SGLOBAL['db']->query("UPDATE ".tname('feed')." SET dateline='$newdateline' WHERE id='$id' AND idtype='$idtype' AND uid='$_SGLOBAL[supe_uid]'");

	magic_use($mid, array('id'=>$id, 'idtype'=>$idtype), true);
	showmessage('magicuse_success', $_POST['refer'], 0);
}

?>