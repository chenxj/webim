<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: space_tag.php 10944 2009-01-09 01:56:13Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$id = empty($_GET['id'])?0:intval($_GET['id']);
$name = empty($_GET['name'])?0:stripsearchkey($_GET['name']);
$start = empty($_GET['start'])?0:intval($_GET['start']);

$list = array();
$count = 0;

if($id || $name) {
	//分页
	$perpage = 30;
	//检查开始数
	ckstart($start, $perpage);
	
	//获取TAG
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('tag')." WHERE ".($id?"tagid='$id'":"tagname='$name'")." LIMIT 1");
	$tag = $_SGLOBAL['db']->fetch_array($query);
	if(empty($tag)) {
		showmessage('tag_does_not_exist');
	} elseif ($tag['close']) {
		showmessage('tag_locked');
	}
	
	//获取tag关联
	$prinum = 0;
	$query = $_SGLOBAL['db']->query("SELECT blog.* FROM ".tname('tagblog')." tb , ".tname('blog')." blog WHERE tb.tagid='$tag[tagid]' AND blog.blogid=tb.blogid LIMIT $start,$perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if(empty($value['friend'])) {
			realname_set($value['uid'], $value['username']);
			$list[] = $value;
		} else {
			$prinum++;
		}
		$count++;
	}
	
	//实名
	realname_get();
	
	//分页
	$multi = smulti($start, $perpage, $count, "space.php?uid=$space[uid]&do=$do&id=$id");

	include_once template("space_tag_view");
	
} else {

	//分页
	$perpage = 100;
	//检查开始数
	ckstart($start, $perpage);
	
	//处理查询
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('tag')." ORDER BY blognum DESC LIMIT $start,$perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$list[] = $value;
		$count++;
	}
	
	//分页
	$multi = smulti($start, $perpage, $count, "space.php?uid=$space[uid]&do=$do");

	include_once template("space_tag_list");
}

?>