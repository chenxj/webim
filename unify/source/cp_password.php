<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_password.php 12934 2009-07-29 02:35:59Z zhengqingpeng $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

if(submitcheck('pwdsubmit')) {
	
	if($_POST['newpasswd1'] != $_POST['newpasswd2']) {
		showmessage('password_inconsistency');
	}
	if($_POST['newpasswd1'] != addslashes($_POST['newpasswd1'])) {
		showmessage('profile_passwd_illegal');
	}
	@include_once(S_ROOT.'./uc_client/client.php');
	
	$ucresult = uc_user_edit($_SGLOBAL['supe_username'], $_POST['password'], $_POST['newpasswd1'], $space['email']);

	if($ucresult == -1) {
		showmessage('old_password_invalid');
	} elseif($ucresult == -4) {
		showmessage('email_format_is_wrong');
	} elseif($ucresult == -5) {
		showmessage('email_not_registered');
	} elseif($ucresult == -6) {
		showmessage('email_has_been_registered');
	} elseif($ucresult == -7) {
		showmessage('no_change');
	} elseif($ucresult == -8) {
		showmessage('protection_of_users');
	}
	clearcookie();
	showmessage('getpasswd_succeed', 'do.php?ac='.$_SCONFIG['login_action']);
}

$actives = array('profile' => ' class="active"');

include_once template("cp_password");

?>