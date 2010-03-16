<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: log.php 11425 2009-03-05 05:11:17Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//һ�δ���ĸ�������ֹ��ʱ
$perbatch = 200;

//����ͳ��
$logs = array();
$maxnum = $maxlogid = 0;
$query = $_SGLOBAL['db']->query("SELECT logid, id, idtype FROM ".tname('log')." ORDER BY logid ASC LIMIT 0,$perbatch");
while ($value = $_SGLOBAL['db']->fetch_array($query)) {
	$logs[$value['idtype']][$value['id']]++;
	$maxnum++;
	$maxlogid = $value['logid'];
}

//���log��
if($maxnum) {
	if($maxnum < $perbatch) {//�������
		$_SGLOBAL['db']->query("TRUNCATE TABLE ".tname('log'));
	} else {//δ������
		$_SGLOBAL['db']->query("DELETE FROM ".tname('log')." WHERE logid<='$maxlogid'");
	}
}

//�ռ�鿴��
if($logs['uid']) {
	$nums = renum($logs['uid']);
	foreach ($nums[0] as $num) {
		$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET viewnum=viewnum+$num WHERE uid IN (".simplode($nums[1][$num]).")");
	}
}

//Ⱥ�黰��鿴��
if($logs['tid']) {
	$nums = renum($logs['tid']);
	foreach ($nums[0] as $num) {
		$_SGLOBAL['db']->query("UPDATE ".tname('thread')." SET viewnum=viewnum+$num WHERE tid IN (".simplode($nums[1][$num]).")");
	}
}

//��־�鿴��
if($logs['blogid']) {
	$nums = renum($logs['blogid']);
	foreach ($nums[0] as $num) {
		$_SGLOBAL['db']->query("UPDATE ".tname('blog')." SET viewnum=viewnum+$num WHERE blogid IN (".simplode($nums[1][$num]).")");
	}
}

?>