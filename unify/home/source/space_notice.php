<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: space_notice.php 12880 2009-07-24 07:20:24Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//分页
$perpage = 30;
$perpage = mob_perpage($perpage);

$page = empty($_GET['page'])?0:intval($_GET['page']);
if($page<1) $page = 1;
$start = ($page-1)*$perpage;

//检查开始数
ckstart($start, $perpage);

$list = array();
$count = 0;
$multi = '';
	
$view = (!empty($_GET['view']) && in_array($_GET['view'], array('userapp')))?$_GET['view']:'notice';
$actives = array($view=>' class="active"');

if($view == 'userapp') {
	
	if($_GET['op'] == 'del') {
		$appid = intval($_GET['appid']);
		$_SGLOBAL['db']->query("DELETE FROM ".tname('myinvite')." WHERE appid='$appid' AND touid='$_SGLOBAL[supe_uid]'");
		
		showmessage('do_success', "space.php?do=notice&view=userapp", 0);
	}
	
	$start = empty($_GET['start'])?0:intval($_GET['start']);
	$filtrate = $start ? intval($start) : 0;
	$type = intval($_GET['type']);
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('myinvite')." WHERE touid='$_SGLOBAL[supe_uid]' ORDER BY dateline DESC");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$key = md5($value['typename'].$value['type']);
		$apparr[$key][] = $value;
		if($filtrate) {
			$filtrate--;
		} else {
			if($count < $perpage) {
				if($type && $value['appid'] == $type) {
					$list[$key][] = $value;
					$count++;
				} elseif(!$type) {
					$list[$key][] = $value;
					$count++;
				}
			}
		}
	}
	
	//统计更新
	$myinvitenum = getcount('myinvite', array('touid'=>$space['uid']));
	if($myinvitenum != $space['myinvitenum']) {
		updatetable('space', array('myinvitenum'=>$myinvitenum), array('uid'=>$space['uid']));
	}

	//分页
	$multi = smulti($start, $perpage, $count, "space.php?do=$do&view=userapp");
	
} else {
	
	if(!empty($_GET['ignore'])) {
		updatetable('notification', array('new'=>'0'), array('new'=>'1', 'uid'=>$_SGLOBAL['supe_uid']));
		updatetable('space', array('notenum'=>0), array('uid'=>$_SGLOBAL['supe_uid']));
		$space['notenum'] = 0;
	}
	
	//通知类型
	$noticetypes = array(
		'wall' => lang('wall'),
		'piccomment' => lang('pic_comment'),
		'blogcomment' => lang('blog_comment'),
		'clickblog' => lang('clickblog'),
		'clickpic' => lang('clickpic'),
		'clickthread' => lang('clickthread'),
		'sharecomment' => lang('share_comment'),
		'sharenotice' => lang('share_notice'),
		'doing' => lang('doing_comment'),
		'friend' => lang('friend_notice'),
		'post' => lang('thread_comment'),
		'credit' => lang('credit'),
		'mtag' => lang('mtag'),
		'event' => lang('event'),
		'eventcomment' => lang('event_comment'),
		'eventmember' => lang('event_member'),
		'eventmemberstatus' => lang('event_memberstatus'),
		'poll' => lang('poll'),
		'pollcomment' => lang('poll_comment'),
		'pollinvite' => lang('poll_invite')
	);
	
	$type = trim($_GET['type']);
	$typesql = $type?"AND type='$type'":'';
	
	$newids = array();
	$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('notification')." WHERE uid='$_SGLOBAL[supe_uid]' $typesql"), 0);
	if($count) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('notification')." WHERE uid='$_SGLOBAL[supe_uid]' $typesql ORDER BY dateline DESC LIMIT $start,$perpage");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			if($value['authorid']) {
				realname_set($value['authorid'], $value['author']);
				if($value['authorid']!=$space['uid'] && $space['friends'] && !in_array($value['authorid'], $space['friends'])) {
					$value['isfriend'] = 0;
				} else {
					$value['isfriend'] = 1;
				}
			}
			if($value['new']) {
				$newids[] = $value['id'];
				$value['style'] = 'color:#000;font-weight:bold;';
			} else {
				$value['style'] = '';
			}
			$list[] = $value;
		}
		//分页
		$multi = multi($count, $perpage, $page, "space.php?do=$do");
	}

	
	//更新状态为已看
	if($newids) {
		$_SGLOBAL['db']->query("UPDATE ".tname('notification')." SET new='0' WHERE id IN (".simplode($newids).")");
		
		//更新未读的
		$newcount = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('notification')." WHERE uid='$_SGLOBAL[supe_uid]' AND new='1'"), 0);
		$space['notenum'] = $newcount = intval($newcount);
		updatetable('space', array('notenum'=>$newcount), array('uid'=>$_SGLOBAL['supe_uid']));
	}
	 
	$newnum = 0;
	$space['pmnum'] = $_SGLOBAL['member']['newpm'];
	foreach (array('notenum','pokenum','addfriendnum','mtaginvitenum','eventinvitenum','myinvitenum') as $value) {
		$newnum = $newnum + $space[$value];
	}
	
	$_SGLOBAL['member']['notenum'] = $space['notenum'];
	$_SGLOBAL['member']['allnotenum'] = $newnum;
	
	realname_get();
}
include_once template("space_notice");

?>