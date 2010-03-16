<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cleanfeed.php 12681 2009-07-15 05:24:47Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//清理feed
if($_SCONFIG['feedday'] < 3) $_SCONFIG['feedday'] = 3;
$deltime = $_SGLOBAL['timestamp'] - $_SCONFIG['feedday']*3600*24;
$f_deltime = $_SGLOBAL['timestamp'] - 3*3600*24;//应用动态

//执行
$_SGLOBAL['db']->query("DELETE FROM ".tname('feed')." WHERE (dateline < '$deltime' AND hot=0) OR (dateline < '$f_deltime' AND appid=0)");
$_SGLOBAL['db']->query("OPTIMIZE TABLE ".tname('feed'), 'SILENT');//优化表


?>