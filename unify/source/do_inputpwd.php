<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: do_inputpwd.php 10298 2008-11-28 07:57:44Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

if(submitcheck('pwdsubmit')) {

	$blogid = empty($_POST['blogid'])?0:intval($_POST['blogid']);
	$albumid = empty($_POST['albumid'])?0:intval($_POST['albumid']);
	
	$itemarr = array();
	if($blogid) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('blog')." WHERE blogid='$blogid'");
		$itemarr = $_SGLOBAL['db']->fetch_array($query);
		$itemurl = "space.php?uid=$itemarr[uid]&do=blog&id=$itemarr[blogid]";
		$cookiename = 'view_pwd_blog_'.$blogid;
	} elseif($albumid) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('album')." WHERE albumid='$albumid'");
		$itemarr = $_SGLOBAL['db']->fetch_array($query);
		$itemurl = "space.php?uid=$itemarr[uid]&do=album&id=$itemarr[albumid]";
		$cookiename = 'view_pwd_album_'.$albumid;
	}
	
	if(empty($itemarr)) {
		showmessage('news_does_not_exist');
	}
	
	if($itemarr['password'] && $_POST['viewpwd'] == $itemarr['password']) {
		ssetcookie($cookiename, md5(md5($itemarr['password'])));
		showmessage('proved_to_be_successful', $itemurl);
	} else {
		showmessage('password_is_not_passed', $itemurl);
	}
}

?>