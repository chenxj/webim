<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: doing.php 13203 2009-08-20 02:26:58Z zhengqingpeng $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$wherearr = $doinglist = array();
$sql = '';
$uid = !empty($_GET['uid']) ? trim($_GET['uid']) : '';

$mood = !empty($_GET['mood']) ? intval($_GET['mood']) : 0;
$start = !empty($_GET['start']) ? intval($_GET['start']) : 0;
$limit = !empty($_GET['limit']) ? intval($_GET['limit']) : 10;

$uids = getdotstring($uid, 'int');
if($uids) $wherearr[] = 'uid IN ('.$uids.')';
if($mood)  $wherearr[] = 'mood>\'0\'';

if($wherearr)	$sql = 'WHERE '.implode(' AND ', $wherearr);

$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('doing')." $sql  ORDER BY dateline DESC LIMIT $start,$limit");
while($value = $_SGLOBAL['db']->fetch_array($query)) {
	$value['message'] = makeurl($value['message']);
	$value['userlink'] = $siteurl.'space.php?uid='.$value['uid'];
	$value['link'] = $siteurl.'space.php?uid='.$value['uid'].'&do=doing&doid='.$value['doid'];
	$value['photo'] = ckavatar($value['uid']) ? avatar($value['uid'], 'small',true) : UC_API.'/images/noavatar_small.gif';
	$value['dateline'] = sgmdate('m-d H:i', $value['dateline']);
	$value = sstripslashes($value);
	$doinglist[] = $value;
}

echo serialize($doinglist);
?>