<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: do_login.php 8543 2008-08-21 05:51:48Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$uid = 0;
$email = '';
$_GET['hash'] = empty($_GET['hash']) ? '' : trim($_GET['hash']);
if($_GET['hash']) {
	list($uid, $email) = explode("\t", authcode($_GET['hash'], 'DECODE'));
	$uid = intval($uid);
}

if($uid && isemail($email)) {
	//�������Ψһ��
	if($_SCONFIG['uniqueemail']) {
		if(getcount('spacefield', array('email'=>$email, 'emailcheck'=>1))) {
			showmessage('uniqueemail_recheck');
		}
	}
	//��������
	getreward('realemail', 1, $uid);
	//�޸�����
	updatetable('spacefield', array('email'=>addslashes($email), 'emailcheck'=>'1', 'newemail'=>''), array('uid'=>$uid));

	//��תҳ��
	showmessage('email_check_sucess', '', 1, array($email));
} else {
	showmessage('email_check_error');
}

?>