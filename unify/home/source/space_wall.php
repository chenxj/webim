<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: space_wall.php 12880 2009-07-24 07:20:24Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//分页
$perpage = 20;
$perpage = mob_perpage($perpage);

$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page=1;
$start = ($page-1)*$perpage;

//检查开始数
ckstart($start, $perpage);

//处理查询
$theurl = "space.php?uid=$space[uid]&do=$do";

$cid = empty($_GET['cid'])?0:intval($_GET['cid']);
$csql = $cid?"cid='$cid' AND":'';
	
$list = array();
$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('comment')." WHERE $csql id='$space[uid]' AND idtype='uid'"),0);
if($count) {
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('comment')." WHERE $csql id='$space[uid]' AND idtype='uid' ORDER BY dateline DESC LIMIT $start,$perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		realname_set($value['authorid'], $value['author']);
		$list[] = $value;
	}
}

//分页
$multi = multi($count, $perpage, $page, $theurl);

realname_get();

include_once template("space_wall");

?>