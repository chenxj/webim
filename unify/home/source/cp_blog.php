<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_blog.php 13026 2009-08-06 02:17:33Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//检查信息
$blogid = empty($_GET['blogid'])?0:intval($_GET['blogid']);
$op = empty($_GET['op'])?'':$_GET['op'];

$blog = array();
if($blogid) {
	$query = $_SGLOBAL['db']->query("SELECT bf.*, b.* FROM ".tname('blog')." b 
		LEFT JOIN ".tname('blogfield')." bf ON bf.blogid=b.blogid 
		WHERE b.blogid='$blogid'");
	$blog = $_SGLOBAL['db']->fetch_array($query);
}

//权限检查
if(empty($blog)) {
	if(!checkperm('allowblog')) {
		ckspacelog();
		showmessage('no_authority_to_add_log');
	}
	
	//实名认证
	ckrealname('blog');
	
	//视频认证
	ckvideophoto('blog');
	
	//新用户见习
	cknewuser();
	
	//判断是否发布太快
	$waittime = interval_check('post');
	if($waittime > 0) {
		showmessage('operating_too_fast','',1,array($waittime));
	}
	
	//接收外部标题
	$blog['subject'] = empty($_GET['subject'])?'':getstr($_GET['subject'], 80, 1, 0);
	$blog['message'] = empty($_GET['message'])?'':getstr($_GET['message'], 5000, 1, 0);
	
} else {
	
	if($_SGLOBAL['supe_uid'] != $blog['uid'] && !checkperm('manageblog')) {
		showmessage('no_authority_operation_of_the_log');
	}
}

//添加编辑操作
if(submitcheck('blogsubmit')) {

	if(empty($blog['blogid'])) {
		$blog = array();
	} else {
		if(!checkperm('allowblog')) {
			ckspacelog();
			showmessage('no_authority_to_add_log');
		}
	}
	
	//验证码
	if(checkperm('seccode') && !ckseccode($_POST['seccode'])) {
		showmessage('incorrect_code');
	}
	
	include_once(S_ROOT.'./source/function_blog.php');
	if($newblog = blog_post($_POST, $blog)) {
		if(empty($blog) && $newblog['topicid']) {
			$url = 'space.php?do=topic&topicid='.$newblog['topicid'].'&view=blog';
		} else {
			$url = 'space.php?uid='.$newblog['uid'].'&do=blog&id='.$newblog['blogid'];
		}
		showmessage('do_success', $url, 0);
	} else {
		showmessage('that_should_at_least_write_things');
	}
}

if($_GET['op'] == 'delete') {
	//删除
	if(submitcheck('deletesubmit')) {
		include_once(S_ROOT.'./source/function_delete.php');
		if(deleteblogs(array($blogid))) {
			showmessage('do_success', "space.php?uid=$blog[uid]&do=blog&view=me");
		} else {
			showmessage('failed_to_delete_operation');
		}
	}
	
} elseif($_GET['op'] == 'goto') {
	
	$id = intval($_GET['id']);
	$uid = $id?getcount('blog', array('blogid'=>$id), 'uid'):0;

	showmessage('do_success', "space.php?uid=$uid&do=blog&id=$id", 0);
	
} elseif($_GET['op'] == 'edithot') {
	//权限
	if(!checkperm('manageblog')) {
		showmessage('no_privilege');
	}
	
	if(submitcheck('hotsubmit')) {
		$_POST['hot'] = intval($_POST['hot']);
		updatetable('blog', array('hot'=>$_POST['hot']), array('blogid'=>$blog['blogid']));
		if($_POST['hot']>0) {
			include_once(S_ROOT.'./source/function_feed.php');
			feed_publish($blog['blogid'], 'blogid');
		} else {
			updatetable('feed', array('hot'=>$_POST['hot']), array('id'=>$blog['blogid'], 'idtype'=>'blogid'));
		}
		
		showmessage('do_success', "space.php?uid=$blog[uid]&do=blog&id=$blog[blogid]", 0);
	}
	
} else {
	//添加编辑
	//获取个人分类
	$classarr = $blog['uid']?getclassarr($blog['uid']):getclassarr($_SGLOBAL['supe_uid']);
	//获取相册
	$albums = getalbums($_SGLOBAL['supe_uid']);
	
	$tags = empty($blog['tag'])?array():unserialize($blog['tag']);
	$blog['tag'] = implode(' ', $tags);
	
	$blog['target_names'] = '';
	
	$friendarr = array($blog['friend'] => ' selected');
	
	$passwordstyle = $selectgroupstyle = 'display:none';
	if($blog['friend'] == 4) {
		$passwordstyle = '';
	} elseif($blog['friend'] == 2) {
		$selectgroupstyle = '';
		if($blog['target_ids']) {
			$names = array();
			$query = $_SGLOBAL['db']->query("SELECT username FROM ".tname('space')." WHERE uid IN ($blog[target_ids])");
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				$names[] = $value['username'];
			}
			$blog['target_names'] = implode(' ', $names);
		}
	}
	
	
	$blog['message'] = str_replace('&amp;', '&amp;amp;', $blog['message']);
	$blog['message'] = shtmlspecialchars($blog['message']);
	
	$allowhtml = checkperm('allowhtml');
	
	//好友组
	$groups = getfriendgroup();
	
	//参与热点
	$topic = array();
	$topicid = $_GET['topicid'] = intval($_GET['topicid']);
	if($topicid) {
		$topic = topic_get($topicid);
	}
	if($topic) {
		$actives = array('blog' => ' class="active"');
	}
	
	//菜单激活
	$menuactives = array('space'=>' class="active"');
}

include_once template("cp_blog");

?>