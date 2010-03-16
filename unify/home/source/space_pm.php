<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: space_pm.php 12880 2009-07-24 07:20:24Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

include_once(S_ROOT.'./uc_client/client.php');

$list = array();

$pmid = empty($_GET['pmid'])?0:floatval($_GET['pmid']);
$touid = empty($_GET['touid'])?0:intval($_GET['touid']);
$daterange = empty($_GET['daterange'])?1:intval($_GET['daterange']);
	
if($_GET['subop'] == 'view') {

	if($touid) {
		$list = uc_pm_view($_SGLOBAL['supe_uid'], 0, $touid, $daterange);
		$pmid = empty($list)?0:$list[0]['pmid'];
	} elseif($pmid) {
		$list = uc_pm_view($_SGLOBAL['supe_uid'], $pmid);
	}

	$actives = array($daterange=>' class="active"');

} elseif($_GET['subop'] == 'ignore') {
	
	$ignorelist = uc_pm_blackls_get($_SGLOBAL['supe_uid']);
	$actives = array('ignore'=>' class="active"');
	
} else {
	
	$filter = in_array($_GET['filter'], array('newpm', 'privatepm', 'systempm', 'announcepm'))?$_GET['filter']:($space['newpm']?'newpm':'privatepm');
	
	//分页
	$perpage = 10;
	$perpage = mob_perpage($perpage);
	
	$page = empty($_GET['page'])?0:intval($_GET['page']);
	if($page<1) $page = 1;
	
	$result = uc_pm_list($_SGLOBAL['supe_uid'], $page, $perpage, 'inbox', $filter, 100);
	
	$count = $result['count'];
	$list = $result['data'];

	$multi = multi($count, $perpage, $page, "space.php?do=pm&filter=$filter");
	
	if($_SGLOBAL['member']['newpm']) {
		//取消新短消息提示
		updatetable('space', array('newpm'=>0), array('uid'=>$_SGLOBAL['supe_uid']));
		//UCenter
		uc_pm_ignore($_SGLOBAL['supe_uid']);
	}

	$actives = array($filter=>' class="active"');
}

//实名
if($list) {
	$today = $_SGLOBAL['timestamp'] - ($_SGLOBAL['timestamp'] + $_SCONFIG['timeoffset'] * 3600) % 86400;
	foreach ($list as $key => $value) {
		
		realname_set($value['msgfromid'], $value['msgfrom']);
		
		$value['daterange'] = 5;
		if($value['dateline'] >= $today) {
			$value['daterange'] = 1;
		} elseif($value['dateline'] >= $today - 86400) {
			$value['daterange'] = 2;
		} elseif($value['dateline'] >= $today - 172800) {
			$value['daterange'] = 3;
		}
		$list[$key] = $value;
	}
	realname_get();
}

include_once template("space_pm");

?>