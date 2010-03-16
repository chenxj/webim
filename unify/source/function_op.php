<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: function_op.php 12754 2009-07-17 08:57:12Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//合并tag
function mergetag($tagids, $newtagid) {
	global $_SGLOBAL;
	
	if(!checkperm('managetag')) return false;
	
	//清空
	$_SGLOBAL['db']->query("DELETE FROM ".tname('tag')." WHERE tagid IN (".simplode($tagids).") AND tagid <> '$newtagid'");

	$tagids[] = $newtagid;
	$tagids = array_unique($tagids);
	
	//更新关联表
	$blogids = array();
	$query = $_SGLOBAL['db']->query("SELECT blogid FROM ".tname('tagblog')." WHERE tagid IN (".simplode($tagids).")");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if(empty($blogids[$value['blogid']])) $blogids[$value['blogid']] = $value;
	}
	if(empty($blogids)) return true;
	
	//关联
	$_SGLOBAL['db']->query("DELETE FROM ".tname('tagblog')." WHERE tagid IN (".simplode($tagids).")");
	//插入
	$inserts = array();
	foreach ($blogids as $blogid => $value) {
		$inserts[]= "('$newtagid', '$blogid')";
	}
	$_SGLOBAL['db']->query("INSERT INTO ".tname('tagblog')." (tagid, blogid) VALUES ".implode(',', $inserts));
	//更新统计
	updatetable('tag', array('blognum'=>count($blogids)), array('tagid'=>$newtagid));
	
	return true;
}

//锁定/开放tag
function closetag($tagids, $optype) {
	global $_SGLOBAL;
	
	if(!checkperm('managetag')) return false;
	
	$newtagids = array();
	if($optype == 'close') {
		$close = 0;
	} else {
		$close = 1;
	}
	$query = $_SGLOBAL['db']->query("SELECT tagid FROM ".tname('tag')." WHERE tagid IN (".simplode($tagids).") AND close='$close'");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$newtagids[] = $value['tagid'];
	}
	if(empty($newtagids)) return false;

	//更新状态
	if($optype == 'close') {
		//关联
		$_SGLOBAL['db']->query("DELETE FROM ".tname('tagblog')." WHERE tagid IN (".simplode($newtagids).")");
		$_SGLOBAL['db']->query("UPDATE ".tname('tag')." SET blognum='0', close='1' WHERE tagid IN (".simplode($newtagids).")");
	} else {
		$_SGLOBAL['db']->query("UPDATE ".tname('tag')." SET close='0' WHERE tagid IN (".simplode($newtagids).")");
	}
	
	return true;
}

//合并mtag
function mergemtag($tagids, $newtagid) {
	global $_SGLOBAL;
	
	if(!checkperm('managemtag')) return false;
	
	//重新组合
	$cktagids = array();
	foreach ($tagids as $value) {
		if($value && $value != $newtagid) {
			$cktagids[$value] = $value;
		}
	}
	if(empty($cktagids)) return false;
	
	$tagids = $cktagids;
	
	//清空
	$_SGLOBAL['db']->query("DELETE FROM ".tname('mtag')." WHERE tagid IN (".simplode($tagids).")");
	//更新话题/回复
	$_SGLOBAL['db']->query("UPDATE ".tname('thread')." SET tagid='$newtagid' WHERE tagid IN (".simplode($tagids).")");
	$_SGLOBAL['db']->query("UPDATE ".tname('post')." SET tagid='$newtagid' WHERE tagid IN (".simplode($tagids).")");
	
	//已有的成员
	$olduids = $newuids = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('tagspace')." WHERE tagid='$newtagid'");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$olduids[$value['uid']] = $value;
	}
	
	//更新关联表
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('tagspace')." WHERE tagid IN (".simplode($tagids).")");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if(empty($olduids[$value['uid']])) $newuids[$value['uid']] = $value;
	}
	
	//关联
	$_SGLOBAL['db']->query("DELETE FROM ".tname('tagspace')." WHERE tagid IN (".simplode($tagids).")");
	//插入
	$inserts = array();
	foreach ($newuids as $uid => $value) {
		$inserts[]= "('$newtagid', '$uid', '".addslashes($value['username'])."')";
	}
	if($inserts) {
		$_SGLOBAL['db']->query("REPLACE INTO ".tname('tagspace')." (tagid,uid,username) VALUES ".implode(',', $inserts));
	}

	//更新统计
	$setarr = array(
		'membernum' => getcount('tagspace', array('tagid'=>$newtagid)),
		'threadnum' => getcount('thread', array('tagid'=>$newtagid)),
		'postnum' => getcount('post', array('tagid'=>$newtagid, 'isthread'=>'0'))
	);
	updatetable('mtag', $setarr, array('tagid'=>$newtagid));
	
	return true;
}


//锁定/开放tag
function closemtag($tagids, $optype) {
	global $_SGLOBAL;
	
	if(!checkperm('managemtag')) return false;
	
	$newtagids = array();
	if($optype == 'close') {
		$close = 0;
	} else {
		$close = 1;
	}
	$query = $_SGLOBAL['db']->query("SELECT tagid FROM ".tname('mtag')." WHERE tagid IN (".simplode($tagids).") AND close='$close'");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$newtagids[] = $value['tagid'];
	}
	if(empty($newtagids)) return false;

	//更新状态
	if($optype == 'close') {
		//关联
		$_SGLOBAL['db']->query("UPDATE ".tname('mtag')." SET close='1' WHERE tagid IN (".simplode($newtagids).")");
	} else {
		$_SGLOBAL['db']->query("UPDATE ".tname('mtag')." SET close='0' WHERE tagid IN (".simplode($newtagids).")");
	}
	
	return true;
}


//推荐/取消tag
function recommendmtag($tagids, $optype) {
	global $_SGLOBAL;
	
	if(!checkperm('managemtag')) return false;
	
	$newtagids = array();
	if($optype == 'recommend') {
		$recommend = 0;
	} else {
		$recommend = 1;
	}
	$query = $_SGLOBAL['db']->query("SELECT tagid FROM ".tname('mtag')." WHERE tagid IN (".simplode($tagids).") AND recommend='$recommend'");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$newtagids[] = $value['tagid'];
	}
	if(empty($newtagids)) return false;

	//更新状态
	if($optype == 'recommend') {
		//关联
		$_SGLOBAL['db']->query("UPDATE ".tname('mtag')." SET recommend='1' WHERE tagid IN (".simplode($newtagids).")");
	} else {
		$_SGLOBAL['db']->query("UPDATE ".tname('mtag')." SET recommend='0' WHERE tagid IN (".simplode($newtagids).")");
	}
	
	return true;
}

//话题精华
function digestthreads($tagid, $tids, $v) {
	global $_SGLOBAL;
	
	$mtag = getmtag($tagid);
	if($mtag['grade']<8) {
		return array();
	}
	
	if(empty($v)) {
		$wheresql = " AND t.digest='1'";
		$v = 0;
	} else {
		$wheresql = " AND t.digest='0'";
		$v = 1;
	}
	$newtids = $threads = array();
	$allowmanage = checkperm('managethread');
	$query = $_SGLOBAL['db']->query("SELECT t.* FROM ".tname('thread')." t WHERE t.tagid='$tagid' AND t.tid IN (".simplode($tids).") $wheresql");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$newtids[] = $value['tid'];
		$threads[] = $value;
	}
	
	//数据
	if($newtids) {
		$_SGLOBAL['db']->query("UPDATE ".tname('thread')." SET digest='$v' WHERE tid IN (".simplode($newtids).")");
	}

	return $threads;
}

//话题置顶
function topthreads($tagid, $tids, $v) {
	global $_SGLOBAL;
	
	$mtag = getmtag($tagid);
	if($mtag['grade']<8) {
		return array();
	}
	
	if(empty($v)) {
		$wheresql = " AND t.displayorder='1'";
		$v = 0;
	} else {
		$wheresql = " AND t.displayorder='0'";
		$v = 1;
	}
	$newtids = $threads = array();
	$query = $_SGLOBAL['db']->query("SELECT t.* FROM ".tname('thread')." t WHERE t.tagid='$tagid' AND t.tid IN (".simplode($tids).") $wheresql");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$newtids[] = $value['tid'];
		$threads[] = $value;
	}
	
	//数据
	if($newtids) {
		$_SGLOBAL['db']->query("UPDATE ".tname('thread')." SET displayorder='$v' WHERE tid IN (".simplode($newtids).")");
	}

	return $threads;
}

?>