<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cleannotification.php 8888 2008-10-09 02:01:11Z zhengqingpeng $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//����֪ͨ
$deltime = $_SGLOBAL['timestamp'] - 2*3600*24;//ֻ����2��

//ִ��
$_SGLOBAL['db']->query("DELETE FROM ".tname('notification')." WHERE dateline < '$deltime' AND new='0'");
$_SGLOBAL['db']->query("OPTIMIZE TABLE ".tname('notification'), 'SILENT');//�Ż���

?>