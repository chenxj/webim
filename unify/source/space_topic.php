<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: space_feed.php 12432 2009-06-25 07:31:34Z xupeng $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$page = intval($_GET['page']);
if($page<1) $page = 1;

$perpage = 10;
$start = ($page-1)*$perpage;

$topicid = empty($_GET['topicid'])?0:intval($_GET['topicid']);

$managetopic = checkperm('managetopic');

if(empty($topicid)) {
	
	$list = array();
	$multi = '';
		
	if($_GET['view'] == 'hot') {
		
		$count = getcount('topic', array());
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('topic')." ORDER BY joinnum DESC LIMIT $start,$perpage");
		
	} elseif($_GET['view'] == 'me') {
		
		$count = getcount('topicuser', array('uid'=>$_SGLOBAL['supe_uid']));
		$query = $_SGLOBAL['db']->query("SELECT t.* FROM ".tname('topicuser')." tu
			LEFT JOIN ".tname('topic')." t ON t.topicid=tu.topicid
			WHERE tu.uid='$_SGLOBAL[supe_uid]'
			ORDER BY tu.dateline DESC LIMIT $start,$perpage");
		
	} else {
		$_GET['view'] = 'new';
		
		$count = getcount('topic', array());
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('topic')." ORDER BY lastpost DESC LIMIT $start,$perpage");
	}
	$actives = array($_GET['view'] => ' class="active"');
	
	if($count) {
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$value['pic'] = pic_get($value['pic'], $value['thumb'], $value['remote']);
			$value['lastpost'] = sgmdate('m-d H:i', $value['lastpost']);
			$value['dateline'] = sgmdate('m-d H:i', $value['dateline']);
			$value['endtime'] = $value['endtime']?sgmdate('m-d H:i', $value['endtime']):'';
			$value['message'] = getstr($value['message'], 200, 0, 0, 0, 0, -1);
			realname_set($value['uid'], $value['username']);
			$list[] = $value;
		}
		$multi = multi($count, $perpage, $page, 'space.php?do=topic');
	}
	
	realname_get();
	
	$_TPL['css'] = 'event';
	include_once template('space_topic_list');
	
} else {
	
	if(!$topic = topic_get($topicid)) {
		showmessage('topic_no_found');
	}
	
	if($topic['uid'] == $_SGLOBAL['supe_uid']) $managetopic = 1;
	
	$page = intval($_GET['page']);
	if($page<1) $page = 1;
	
	realname_set($topic['uid'], $topic['username']);

	$bloglist = $piclist = $threadlist = $polllist = $eventlist = $sharelist = $spacelist = array();
	
	$perpages = array();
	$start = 0;
	if($_GET['view'] && in_array($_GET['view'], $topic['jointype'])) {
		$perpages[$_GET['view']] = 30;
		$start = ($page-1)*$perpages[$_GET['view']];
	} elseif($_GET['view'] == 'space') {
		$perpages['space'] = 20;
		$start = ($page-1)*$perpages[$_GET['view']];
	} else {
		$_GET['view'] = 'index';
		$perpages = array(
			'blog' => 10,
			'pic' => 15,
			'thread' => 10,
			'poll' => 10,
			'event' => 10,
			'share' => 10,
			'space' => 21
		);
	}
	
	$count = $listquery = 0;
	if($perpages['blog'] && in_array('blog', $topic['jointype'])) {
		if($_GET['view'] == 'blog') {
			$count = getcount('blog', array('topicid'=>$topicid));
		} else {
			$listquery = 1;
		}
		if($count || $listquery) {
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('blog')." WHERE topicid='$topicid' ORDER BY dateline DESC LIMIT $start,$perpages[blog]");
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				realname_set($value['uid'], $value['username']);
				$bloglist[] = $value;
			}
		}
	}
	
	if($perpages['pic'] && in_array('pic', $topic['jointype'])) {
		if($_GET['view'] == 'pic') {
			$count = getcount('pic', array('topicid'=>$topicid));
		} else {
			$listquery = 1;
		}
		if($count || $listquery) {
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('pic')." WHERE topicid='$topicid' ORDER BY dateline DESC LIMIT $start,$perpages[pic]");
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				realname_set($value['uid'], $value['username']);
				$value['pic'] = pic_get($value['filepath'], $value['thumb'], $value['remote']);
				$piclist[] = $value;
			}
		}
	}
	
	if($perpages['thread'] && in_array('thread', $topic['jointype'])) {
		if($_GET['view'] == 'thread') {
			$count = getcount('thread', array('topicid'=>$topicid));
		} else {
			$listquery = 1;
		}
		if($count || $listquery) {
			$query = $_SGLOBAL['db']->query("SELECT t.*, m.tagname
				FROM ".tname('thread')." t
				LEFT JOIN ".tname('mtag')." m ON m.tagid=t.tagid
				WHERE t.topicid='$topicid'
				ORDER BY t.dateline DESC LIMIT $start,$perpages[thread]");
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				realname_set($value['uid'], $value['username']);
				$threadlist[] = $value;
			}
		}
	}
	
	if($perpages['poll'] && in_array('poll', $topic['jointype'])) {
		if($_GET['view'] == 'poll') {
			$count = getcount('poll', array('topicid'=>$topicid));
		} else {
			$listquery = 1;
		}
		if($count || $listquery) {
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('poll')." WHERE topicid='$topicid' ORDER BY dateline DESC LIMIT $start,$perpages[poll]");
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				realname_set($value['uid'], $value['username']);
				$polllist[] = $value;
			}
		}
	}
	
	if($perpages['event'] && in_array('event', $topic['jointype'])) {
		if($_GET['view'] == 'event') {
			$count = getcount('event', array('topicid'=>$topicid));
		} else {
			$listquery = 1;
		}
		if($count || $listquery) {
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('event')." WHERE topicid='$topicid' ORDER BY dateline DESC LIMIT $start,$perpages[event]");
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				realname_set($value['uid'], $value['username']);
				$eventlist[] = $value;
			}
		}
	}
	
	if($perpages['share'] && in_array('share', $topic['jointype'])) {
		if($_GET['view'] == 'share') {
			$count = getcount('share', array('topicid'=>$topicid));
		} else {
			$listquery = 1;
		}
		if($count || $listquery) {
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('share')." WHERE topicid='$topicid' ORDER BY dateline DESC LIMIT $start,$perpages[share]");
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				$value = mkshare($value);
				realname_set($value['uid'], $value['username']);
				$sharelist[] = $value;
			}
		}
	}
	
	//参与的人
	if($perpages['space']) {
		if($_GET['view'] == 'space') {
			$count = getcount('topicuser', array('topicid'=>$topicid));
			$query = $_SGLOBAL['db']->query("SELECT s.* FROM ".tname('topicuser')." tu
				LEFT JOIN ".tname('space')." s ON s.uid=tu.uid
				WHERE tu.topicid='$topicid' ORDER BY tu.dateline DESC LIMIT $start,$perpages[space]");
		} else {
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('topicuser')." WHERE topicid='$topicid' ORDER BY dateline DESC LIMIT $start,$perpages[space]");
		}
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			realname_set($value['uid'], $value['username'], $value['name'], $value['namestatus']);
			$value['isfriend'] = ($value['uid']==$space['uid'] || ($space['friends'] && in_array($value['uid'], $space['friends'])))?1:0;
			$spacelist[] = $value;
		}
	}
	
	$multi = '';
	if($count) {
		$multi = multi($count, $perpages[$_GET['view']], $page, "space.php?do=topic&topicid=$topicid&view=$_GET[view]");
	}
	
	realname_get();
	
	$sub_actives = array($_GET['view'] => ' style="font-weight:bold;"');
	
	$_TPL['css'] = 'event';
	include_once template('space_topic_view');
}

?>