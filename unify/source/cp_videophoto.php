<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_feed.php 12304 2009-06-03 07:29:34Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

if(empty($_SCONFIG['videophoto'])) {
	showmessage('no_open_videophoto');
}

$videophoto = $space['videostatus'] ? getvideopic($space['videopic']) : '';

include template("cp_videophoto");
?>