<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: admincp_ad.php 10765 2008-12-18 09:24:34Z liguode $
*/

if(!defined('IN_UCHOME') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('manageclick')) {//debug
	cpmessage('no_authority_management_operation');
}

$clickid = empty($_GET['clickid'])?0:intval($_GET['clickid']);
$click = array();
if($clickid) {
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('click')." WHERE clickid='$clickid'");
	$click = $_SGLOBAL['db']->fetch_array($query);
}

if(submitcheck('clicksubmit')) {

	$setarr = array(
		'name' => trim($_POST['name']),
		'icon' => trim($_POST['icon']),
		'displayorder' => intval($_POST['displayorder'])
	);
	
	if(empty($_POST['clickid'])) {
		if(!in_array($_POST['idtype'], array('blogid', 'picid', 'tid'))) $_POST['idtype'] = 'blogid';
		$setarr['idtype'] = $_POST['idtype'];
		$clickid = inserttable('click', $setarr, 1);
		//增加字段
		switch ($_POST['idtype']) {
			case 'picid':
				$tablename = tname('pic');
				break;
			case 'tid':
				$tablename = tname('thread');
				break;
			default:
				$tablename = tname('blog');
				break;
		}
		$_SGLOBAL['db']->query("ALTER TABLE $tablename ADD click_$clickid smallint(6) unsigned NOT NULL default '0'");
	} else {
		updatetable('click', $setarr, array('clickid'=>$_POST['clickid']));
	}
	
	//更新缓存
	include_once(S_ROOT.'./source/function_cache.php');
	click_cache();
	
	cpmessage('do_success', 'admincp.php?ac=click');

} elseif (submitcheck('ordersubmit')) {
	
	foreach ($_POST['displayorder'] as $key => $value) {
		updatetable('click', array('displayorder'=>intval($value)), array('clickid'=>$key));
	}
	
	//更新缓存
	include_once(S_ROOT.'./source/function_cache.php');
	click_cache();
	
	cpmessage('do_success', 'admincp.php?ac=click');
}

if(empty($_GET['op'])) {
	
	if($_GET['idtype']) {
		$where = "WHERE idtype='$_GET[idtype]'";
		$actives = array($_GET['idtype'] => ' class="active"');
	} else {
		$where = '';
		$actives = array('view' => ' class="active"');
	}
	
	$list = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('click')." $where ORDER BY displayorder");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$list[] = $value;
	}
	
} elseif ($_GET['op'] == 'add') {
	
	$click = array();
	
} elseif ($_GET['op'] == 'delete') {
	//删除
	if($click) {		
		//删除字段
		//增加字段
		switch ($click['idtype']) {
			case 'picid':
				$tablename = tname('pic');
				break;
			case 'tid':
				$tablename = tname('thread');
				break;
			default:
				$tablename = tname('blog');
				break;
		}
		$_SGLOBAL['db']->query("ALTER TABLE $tablename DROP click_$clickid", 'SILENT');
		
		$_SGLOBAL['db']->query("DELETE FROM ".tname('click')." WHERE clickid='$clickid'");
		$_SGLOBAL['db']->query("DELETE FROM ".tname('clickuser')." WHERE clickid='$clickid'");

		//更新缓存
		include_once(S_ROOT.'./source/function_cache.php');
		click_cache();
	}
	
	cpmessage('do_success', 'admincp.php?ac=click');
}

$idtypearr = $click?array($click['idtype'] => ' selected'):array();

?>