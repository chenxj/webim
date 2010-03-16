<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: space_index.php 12256 2009-05-27 03:57:32Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

if(empty($_SCONFIG['videophoto'])) {
	showmessage('no_open_videophoto');
}

//视频认证
include_once(S_ROOT.'./source/function_cp.php');
ckvideophoto('viewphoto', $space);

$videophoto = getvideopic($space['videopic']);

//个人头像
include_once template("space_videophoto");

?>
