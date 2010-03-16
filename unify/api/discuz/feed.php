<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: feed.php 13203 2009-08-20 02:26:58Z zhengqingpeng $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}
$wherearr = $feedlist = array();
$sql = '';
$uids = !empty($_GET['uids']) ? trim($_GET['uids']) : '';

$friend = !empty($_GET['friend']) ? intval($_GET['friend']) : 0;
$start = !empty($_GET['start']) ? intval($_GET['start']) : 0;
$limit = !empty($_GET['limit']) ? intval($_GET['limit']) : 10;

$uids = getdotstring($uids, 'int');
$wherearr[] = "friend='0'";
if($uids) {
	$wherearr[] = 'uid IN ('.$uids.')';
} elseif($friend) {
	$query = $_SGLOBAL['db']->query("SELECT feedfriend FROM ".tname('spacefield')." WHERE uid='$friend'");
	$myspace = $_SGLOBAL['db']->fetch_array($query);
	$wherearr[] = "uid IN ('0',$space[feedfriend])";
} 

if($wherearr)	$sql = 'WHERE '.implode(' AND ', $wherearr);

$feed_list = array();
$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('feed')." $sql ORDER BY dateline DESC LIMIT $start,$limit");
while($value = $_SGLOBAL['db']->fetch_array($query)) {
	if(ckfriend($value['uid'], $value['friend'], $value['target_ids'])) {
		realname_set($value['uid'], $value['username']);
		$feed_list[] = $value;
	}
}

//实名处理
realname_get();

foreach ($feed_list as $value) {
	$value = mkfeed($value);
	$value['dateline'] = sgmdate('m-d H:i', $value['dateline']);
	if(!$value['appid']) {
		$value['iconurl'] = "http://appicon.manyou.com/icons/$value[icon]";
	} else {
		$value['iconurl'] = $siteurl.'image/icon/'.$value['icon'].'.gif';
	}
	$value['title_template'] = makeurl($value['title_template']);
	$value['body_template'] = makeurl($value['body_template']);
	$value['userlink'] = $siteurl.'space.php?uid='.$value['uid'];	
	$value['photo'] = ckavatar($value['uid']) ? avatar($value['uid'], 'small',true) : UC_API.'/images/noavatar_small.gif';
	$value = sstripslashes($value);
	$feedlist[] = $value;
}

echo serialize($feedlist);
?>