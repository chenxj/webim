<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cleantrace.php 11954 2009-04-17 09:29:53Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//�����ӡ�����·ÿ�
$maxday = 90;//����90���
$deltime = $_SGLOBAL['timestamp'] - $maxday*3600*24;

//�����ӡ
$_SGLOBAL['db']->query("DELETE FROM ".tname('clickuser')." WHERE dateline < '$deltime'");

//���·ÿ�
$_SGLOBAL['db']->query("DELETE FROM ".tname('visitor')." WHERE dateline < '$deltime'");

?>