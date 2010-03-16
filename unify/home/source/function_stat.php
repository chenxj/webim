<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: function_stat.php 11985 2009-04-24 05:09:27Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//日志回复数
function blog_replynum_stat($start, $perpage) {
	global $_SGLOBAL;
	
	$next = false;
	$updates = array();
	$query = $_SGLOBAL['db']->query("SELECT blogid, replynum FROM ".tname('blog')." LIMIT $start,$perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$next = true;
		$count = getcount('comment', array('id'=>$value['blogid'], 'idtype'=>'blogid'));
		if($count != $value['replynum']) {
			$updates[$value['blogid']] = $count;
		}
	}
	if(empty($updates)) return $next;

	$nums = renum($updates);
	foreach ($nums[0] as $count) {
		$_SGLOBAL['db']->query("UPDATE ".tname('blog')." SET replynum=$count WHERE blogid IN (".simplode($nums[1][$count]).")");
	}
	return $next;
}

//空间好友数
function space_friendnum_stat($start, $perpage) {
	global $_SGLOBAL;
	
	$next = false;
	$updates = array();
	$query = $_SGLOBAL['db']->query("SELECT uid, friendnum FROM ".tname('space')." LIMIT $start,$perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$next = true;
		$count = getcount('friend', array('uid'=>$value['uid'], 'status'=>1));
		if($count != $value['friendnum']) {
			$updates[$value['uid']] = $count;
		}
	}
	if(empty($updates)) return $next;

	$nums = renum($updates);
	foreach ($nums[0] as $count) {
		$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET friendnum=$count WHERE uid IN (".simplode($nums[1][$count]).")");
	}
	return $next;
}

//空间好友缓存
function space_friend_stat($start, $perpage) {
	global $_SGLOBAL;
	
	$next = false;
	$query = $_SGLOBAL['db']->query("SELECT uid, friend FROM ".tname('spacefield')." LIMIT $start,$perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$next = true;
		
		$fuids = array();
		$subquery = $_SGLOBAL['db']->query("SELECT fuid FROM ".tname('friend')." WHERE uid='$value[uid]' AND status='1'");
		while ($subvalue = $_SGLOBAL['db']->fetch_array($subquery)) {
			$fuids[$subvalue['fuid']] = $subvalue['fuid'];
		}
		$fuidstr = implode(',', $fuids);
		if($fuidstr != $value['friend']) {
			updatetable('spacefield', array('friend'=>$fuidstr), array('uid'=>$value['uid']));
		}
	}
	return $next;
}

//群组用户数
function mtag_membernum_stat($start, $perpage) {
	global $_SGLOBAL;
	
	$next = false;
	$updates = array();
	$query = $_SGLOBAL['db']->query("SELECT tagid, membernum FROM ".tname('mtag')." LIMIT $start,$perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$next = true;
		$count = getcount('tagspace', array('tagid'=>$value['tagid']));
		if($count != $value['membernum']) {
			$updates[$value['tagid']] = $count;
		}
	}
	if(empty($updates)) return $next;

	$nums = renum($updates);
	foreach ($nums[0] as $count) {
		$_SGLOBAL['db']->query("UPDATE ".tname('mtag')." SET membernum=$count WHERE tagid IN (".simplode($nums[1][$count]).")");
	}
	return $next;
}

//群组话题数
function mtag_threadnum_stat($start, $perpage) {
	global $_SGLOBAL;
	
	$next = false;
	$updates = array();
	$query = $_SGLOBAL['db']->query("SELECT tagid, threadnum FROM ".tname('mtag')." LIMIT $start,$perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$next = true;
		$count = getcount('thread', array('tagid'=>$value['tagid']));
		if($count != $value['threadnum']) {
			$updates[$value['tagid']] = $count;
		}
	}
	if(empty($updates)) return $next;

	$nums = renum($updates);
	foreach ($nums[0] as $count) {
		$_SGLOBAL['db']->query("UPDATE ".tname('mtag')." SET threadnum=$count WHERE tagid IN (".simplode($nums[1][$count]).")");
	}
	return $next;
}

//群组帖子数
function mtag_postnum_stat($start, $perpage) {
	global $_SGLOBAL;
	
	$next = false;
	$updates = array();
	$query = $_SGLOBAL['db']->query("SELECT tagid, postnum FROM ".tname('mtag')." LIMIT $start,$perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$next = true;
		$count = getcount('post', array('tagid'=>$value['tagid'], 'isthread'=>0));
		if($count != $value['postnum']) {
			$updates[$value['tagid']] = $count;
		}
	}
	if(empty($updates)) return $next;

	$nums = renum($updates);
	foreach ($nums[0] as $count) {
		$_SGLOBAL['db']->query("UPDATE ".tname('mtag')." SET postnum=$count WHERE tagid IN (".simplode($nums[1][$count]).")");
	}
	return $next;
}

//话题回复数
function thread_replynum_stat($start, $perpage) {
	global $_SGLOBAL;
	
	$next = false;
	$updates = array();
	$query = $_SGLOBAL['db']->query("SELECT tid, replynum FROM ".tname('thread')." LIMIT $start,$perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$next = true;
		$count = getcount('post', array('tid'=>$value['tid'], 'isthread'=>0));
		if($count != $value['replynum']) {
			$updates[$value['tid']] = $count;
		}
	}
	if(empty($updates)) return $next;

	$nums = renum($updates);
	foreach ($nums[0] as $count) {
		$_SGLOBAL['db']->query("UPDATE ".tname('thread')." SET replynum=$count WHERE tid IN (".simplode($nums[1][$count]).")");
	}
	return $next;
}

//相册图片数
function album_picnum_stat($start, $perpage) {
	global $_SGLOBAL;
	
	$next = false;
	$updates = array();
	$query = $_SGLOBAL['db']->query("SELECT albumid, picnum FROM ".tname('album')." LIMIT $start,$perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$next = true;
		$count = getcount('pic', array('albumid'=>$value['albumid']));
		if($count != $value['picnum']) {
			$updates[$value['albumid']] = $count;
		}
	}
	if(empty($updates)) return $next;

	$nums = renum($updates);
	foreach ($nums[0] as $count) {
		$_SGLOBAL['db']->query("UPDATE ".tname('album')." SET picnum=$count WHERE albumid IN (".simplode($nums[1][$count]).")");
	}
	return $next;
}

//TAG日志数
function tag_blognum_stat($start, $perpage) {
	global $_SGLOBAL;
	
	$next = false;
	$updates = array();
	$query = $_SGLOBAL['db']->query("SELECT tagid, blognum FROM ".tname('tag')." LIMIT $start,$perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$next = true;
		$count = getcount('tagblog', array('tagid'=>$value['tagid']));
		if($count != $value['blognum']) {
			$updates[$value['tagid']] = $count;
		}
	}
	if(empty($updates)) return $next;

	$nums = renum($updates);
	foreach ($nums[0] as $count) {
		$_SGLOBAL['db']->query("UPDATE ".tname('tag')." SET blognum=$count WHERE tagid IN (".simplode($nums[1][$count]).")");
	}
	return $next;
}

?>