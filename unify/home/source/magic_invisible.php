<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: function_image.php 7350 2008-05-12 09:36:04Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//ÒþÉí¿¨
if(submitcheck("usesubmit")) {

	$expire = $_SGLOBAL['timestamp'] + ($magic['custom']['effectivetime'] ? $magic['custom']['effectivetime'] : 86400);
	$_SGLOBAL['db']->query("UPDATE ".tname("session")." SET magichidden = 1 WHERE uid='$_SGLOBAL[supe_uid]'");

	magic_use($mid, array('expire'=>$expire), true);
	showmessage('magicuse_success', $_POST['refer'], 0);
}

?>