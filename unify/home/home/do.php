<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: do.php 12354 2009-06-11 08:14:06Z liguode $
*/

include_once('./common.php');

//获取方法
$ac = empty($_GET['ac'])?'':$_GET['ac'];

//自定义登录
if($ac == $_SCONFIG['login_action']) {
	$ac = 'login';
} elseif($ac == 'login') {
	$ac = '';
}
if($ac == $_SCONFIG['register_action']) {
	$ac = 'register';
} elseif($ac == 'register') {
	$ac = '';
}

//允许的方法
$acs = array('login', 'register', 'lostpasswd', 'swfupload', 'inputpwd',
	'ajax', 'seccode', 'sendmail', 'stat', 'emailcheck');
if(empty($ac) || !in_array($ac, $acs)) {
	showmessage('enter_the_space', 'index.php', 0);
}

//链接
$theurl = 'do.php?ac='.$ac;

include_once(S_ROOT.'./source/do_'.$ac.'.php');

?>