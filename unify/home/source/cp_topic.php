<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_topic.php 12436 2009-06-25 09:07:38Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//检查信息
$topicid = empty($_GET['topicid'])?0:intval($_GET['topicid']);
$id = empty($_GET['id'])?0:intval($_GET['id']);
$idtype = empty($_GET['idtype'])?'':trim($_GET['idtype']);
$op = empty($_GET['op'])?'':$_GET['op'];

$topic = array();
if($topicid) {
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('topic')." WHERE topicid='$topicid'");
	$topic = $_SGLOBAL['db']->fetch_array($query);
}

//权限检查
if(empty($topic)) {

	if($_GET['op'] != 'join') {
		if(!checkperm('allowtopic')) {
			ckspacelog();
			showmessage('no_privilege');
		}
	}
	$topicid = 0;
	
} else {
	if($_GET['op'] != 'join') {
		if($_SGLOBAL['supe_uid'] != $topic['uid'] && !checkperm('managetopic')) {
			showmessage('no_privilege');
		}
	}
	
	$topic['pic'] = pic_get($topic['pic'], $topic['thumb'], $topic['remote'], 1);
}

//添加编辑操作
if(submitcheck('topicsubmit')) {

	$setarr = array(
		'subject' => getstr($_POST['subject'], 80, 1, 1),
		'message' => trim($_POST['message']),
		'jointype' => empty($_POST['jointype'])?'':implode(',', $_POST['jointype']),
		'joingid' => empty($_POST['joingid'])?'':implode(',', $_POST['joingid']),
		'endtime' => $_POST['endtime']?sstrtotime($_POST['endtime']):0
	);
	
	if(strlen($setarr['subject']) < 4) {
		showmessage('topic_subject_error');
	}
	
	//封面
	if($_FILES['pic']['size'] && $filearr = pic_save($_FILES['pic'], -1)) {
		$setarr['pic'] = $filearr['filepath'];
		$setarr['thumb'] = $filearr['thumb'];
		$setarr['remote'] = $filearr['remote'];
	}
	
	if(empty($topicid)) {
		$setarr['uid'] = $_SGLOBAL['supe_uid'];
		$setarr['username'] = $_SGLOBAL['supe_username'];
		$setarr['dateline'] = $setarr['lastpost'] = $_SGLOBAL['timestamp'];
		
		$topicid = inserttable('topic', $setarr, 1);
	} else {
		updatetable('topic', $setarr, array('topicid'=>$topicid));
	}
	
	showmessage('do_success', "space.php?do=topic&topicid=$topicid", 0);
}

if($_GET['op'] == 'delete') {
	//删除
	if(submitcheck('deletesubmit')) {
		include_once(S_ROOT.'./source/function_delete.php');
		if(deletetopics(array($topicid))) {
			showmessage('do_success', 'space.php?do=topic');
		} else {
			showmessage('failed_to_delete_operation');
		}
	}
	
} elseif($_GET['op'] == 'join') {
	
	$tablename = gettablebyidtype($idtype);
	$item = array();
	if($tablename && $id) {
		if($tablename == 'pic') {
			$query = $_SGLOBAL['db']->query("SELECT s.username, p.* FROM ".tname('pic')." p
				LEFT JOIN ".tname('space')." s ON s.uid=p.uid
				WHERE p.picid='$id'");
		} else {
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname($tablename)." WHERE $idtype='$id'");
		}
		$item = $_SGLOBAL['db']->fetch_array($query);
	}
	if(empty($item)) {
		showmessage('no_privilege');
	}
	
	if($_SGLOBAL['supe_uid'] != $item['uid'] && !checkperm('managetopic') && !checkperm('manage'.$tablename)) {
		showmessage('no_privilege');
	}
	
	$tlist = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('topic')." ORDER BY lastpost DESC LIMIT 0,50");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$value['jointype'] = $value['jointype']?explode(',', $value['jointype']):array();
		if($value['jointype'] && !in_array($tablename, $value['jointype'])) {
			continue;
		}
		if($_SGLOBAL['supe_uid'] == $item['uid']) {
			$value['joingid'] = $value['joingid']?explode(',', $value['joingid']):array();
			if($value['joingid'] && !in_array($space['groupid'], $value['joingid'])) {
				continue;
			}
		}
		if($value['endtime'] && $_SGLOBAL['timestamp']>$value['endtime']) {
			continue;
		}
		$tlist[$value['topicid']] = $value;
	}
	
	if(empty($tlist)) showmessage('topic_list_none');
	
	if(submitcheck('joinsubmit')) {
		if(empty($tlist[$_POST['newtopicid']])) $_POST['newtopicid'] = 0;
		updatetable($tablename, array('topicid'=>$_POST['newtopicid']), array($idtype=>$id));
		
		//参与用户
		if($_POST['newtopicid']) {
			topic_join($_POST['newtopicid'], $item['uid'], addslashes($item['username']));
		} else {
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('topicuser')." WHERE uid='$item[uid]' AND topicid='$item[topicid]'");
			if($value = $_SGLOBAL['db']->fetch_array($query)) {
				$_SGLOBAL['db']->query("DELETE FROM ".tname('topicuser')." WHERE id='$value[id]'");
				$_SGLOBAL['db']->query("UPDATE ".tname('topic')." SET joinnum=joinnum-1 WHERE topicid='$item[topicid]' AND joinnum>0");
			}
		}
		
		showmessage('do_success', $_POST['refer'], 0);
	}
	
} else {

	$jointypes = array();
	$topic['jointype'] = explode(',', $topic['jointype']);
	foreach ($topic['jointype'] as $value) {
		$jointypes[$value] = ' checked';
	}
	
	$joingids = array();
	$topic['joingid'] = explode(',', $topic['joingid']);
	foreach ($topic['joingid'] as $value) {
		$joingids[$value] = ' checked';
	}
	
	$topic['endtime'] = $topic['endtime']?sgmdate('Y-m-d H:i', $topic['endtime']):'';
	
	//用户组
	$usergroups = array(-1=>array(), 1=>array(), 0=>array());
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('usergroup'));
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$usergroups[$value['system']][$value['gid']] = $value;
	}
	
}

include_once template("cp_topic");

?>