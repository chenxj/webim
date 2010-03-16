<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: function_image.php 7350 2008-05-12 09:36:04Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//╨цсятЖхщ©╗
if(submitcheck("usesubmit")) {
	
	$rate = $magic['custom']['addsize'] ? intval($magic['custom']['addsize']) : 10;
	$addsize = 1048576 * $rate;
	$_SGLOBAL['db']->query('UPDATE '.tname('space')." SET addsize = addsize + $addsize WHERE uid='$_SGLOBAL[supe_uid]'");

	magic_use($mid, array(), true);
	showmessage('magicuse_success', $_POST['refer'], 0);

}

?>