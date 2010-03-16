<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: space_doing.php 12998 2009-08-05 03:29:54Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//分页
$perpage = 20;
$perpage = mob_perpage($perpage);

$page = empty($_GET['page'])?0:intval($_GET['page']);
if($page<1) $page=1;
$start = ($page-1)*$perpage;

//检查开始数
ckstart($start, $perpage);

$dolist = array();
$count = 0;

if(empty($_GET['view']) && ($space['friendnum']<$_SCONFIG['showallfriendnum'])) {
	$_GET['view'] = 'all';//默认显示
}
	
//处理查询
$f_index = '';
if($_GET['view'] == 'all') {
	
	$wheresql = "1";
	$theurl = "space.php?uid=$space[uid]&do=$do&view=all";
	$f_index = 'USE INDEX(dateline)';
	$actives = array('all'=>' class="active"');
	
} else {
	
	if(empty($space['feedfriend'])) $_GET['view'] = 'me';
	
	if($_GET['view'] == 'me') {
		$wheresql = "uid='$space[uid]'";
		$theurl = "space.php?uid=$space[uid]&do=$do&view=me";
		$actives = array('me'=>' class="active"');
	} else {
		$wheresql = "uid IN ($space[feedfriend],$space[uid])";
		$theurl = "space.php?uid=$space[uid]&do=$do&view=we";
		$f_index = 'USE INDEX(dateline)';
		$actives = array('we'=>' class="active"');
	}
}

$doid = empty($_GET['doid'])?0:intval($_GET['doid']);
if($doid) {
	$count = 1;
	$f_index = '';
	$wheresql = "doid='$doid'";
	$theurl .= "&doid=$doid";
}


$doids = $clist = $newdoids = array();
if(empty($count)) {
	$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('doing')." WHERE $wheresql"), 0);
	//更新统计
	if($wheresql == "uid='$space[uid]'" && $space['doingnum'] != $count) {
		updatetable('space', array('doingnum' => $count), array('uid'=>$space['uid']));
	}
}
if($count) {
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('doing')." $f_index
		WHERE $wheresql
		ORDER BY dateline DESC
		LIMIT $start,$perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		realname_set($value['uid'], $value['username']);
		$doids[] = $value['doid'];
		$dolist[] = $value;
	}
}

//单条处理
if($doid) {
	$dovalue = empty($dolist)?array():$dolist[0];
	if($dovalue) {
		if($dovalue['uid'] == $_SGLOBAL['supe_uid']) {
			$actives = array('me'=>' class="active"');
		} else {
			$space = getspace($dovalue['uid']);//对方的空间
			$actives = array('all'=>' class="active"');
		}
	}
}

//回复
if($doids) {
	
	include_once(S_ROOT.'./source/class_tree.php');
	$tree = new tree();
	
	$values = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('docomment')." USE INDEX(dateline) WHERE doid IN (".simplode($doids).") ORDER BY dateline");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		realname_set($value['uid'], $value['username']);
		$newdoids[$value['doid']] = $value['doid'];
		if(empty($value['upid'])) {
			$value['upid'] = "do$value[doid]";
		}
		$tree->setNode($value['id'], $value['upid'], $value);
	}
}

foreach ($newdoids as $cdoid) {
	$values = $tree->getChilds("do$cdoid");
	foreach ($values as $key => $id) {
		$one = $tree->getValue($id);
		$one['layer'] = $tree->getLayer($id) * 2 - 2;
		$one['style'] = "padding-left:{$one['layer']}em;";
		if($_GET['highlight'] && $one['id'] == $_GET['highlight']) {
			$one['style'] .= 'color:red;font-weight:bold;';
		}
		$clist[$cdoid][] = $one;
	}
}

//分页
$multi = multi($count, $perpage, $page, $theurl);

//同心情的
$moodlist = array();
if($space['mood'] && empty($start)) {
	$query = $_SGLOBAL['db']->query("SELECT s.uid,s.username,s.name,s.namestatus,s.mood,s.updatetime,s.groupid,sf.note,sf.sex
		FROM ".tname('space')." s
		LEFT JOIN ".tname('spacefield')." sf ON sf.uid=s.uid
		WHERE s.mood='$space[mood]' ORDER BY s.updatetime DESC LIMIT 0,13");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if($value['uid'] != $space['uid']) {
			realname_set($value['uid'], $value['username'], $value['name'], $value['namestatus']);
			$moodlist[] = $value;
			if(count($moodlist)==12) break;
		}
	}
}

$upid = 0;

//实名
realname_get();

$_TPL['css'] = 'doing';
include_once template("space_doing");

?>