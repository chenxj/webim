<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: admincp_task.php 12304 2009-06-03 07:29:34Z liguode $
*/

if(!defined('IN_UCHOME') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('managetask')) {
	cpmessage('no_authority_management_operation');
}

$list = $thevalue = array();
$taskid = empty($_GET['taskid'])?0:intval($_GET['taskid']);

if(submitcheck('tasksubmit')) {

	$_POST['name'] = shtmlspecialchars($_POST['name']);

	$_POST['filename'] = str_replace(array('..', '/', '\\'), array('', '', ''), $_POST['filename']);
	if(empty($_POST['filename']) || !is_readable(S_ROOT.'./source/task/'.$_POST['filename'])) {
		cpmessage('designated_script_file_incorrect');
	}
	
	$starttime = empty($_POST['starttime'])?0:sstrtotime($_POST['starttime']);
	$endtime = empty($_POST['endtime'])?0:sstrtotime($_POST['endtime']);
	
	$setarr = array(
		'name' => $_POST['name'],
		'note' => trim($_POST['note']),
		'filename' => $_POST['filename'],
		'image' => trim($_POST['image']),
		'available' => intval($_POST['available']),
		'starttime' => $starttime,
		'endtime' => $endtime,
		'nexttype' => trim($_POST['nexttype']),
		'credit' => intval($_POST['credit']),
		'maxnum' => intval($_POST['maxnum']),
		'displayorder' => intval($_POST['displayorder'])
	);
	$setarr['nexttime'] = $setarr['nexttype']=='time'?intval($_POST['nexttime']):0;
		
	if(empty($taskid)) {
		//ADD
		inserttable('task', $setarr);//返回taskid
	} else {
		//UPDATE
		updatetable('task', $setarr, array('taskid'=>$taskid));
	}

	//更新config
	include_once(S_ROOT.'./source/function_cache.php');
	task_cache();
		
	cpmessage('do_success', 'admincp.php?ac=task');
}

if($_GET['op'] == 'edit') {
	
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('task')." WHERE taskid='$taskid'");
	if($thevalue = $_SGLOBAL['db']->fetch_array($query)) {
		$thevalue['starttime'] = $thevalue['starttime']?sgmdate('Y-m-d H:i:s', $thevalue['starttime']):'';
		$thevalue['endtime'] = $thevalue['endtime']?sgmdate('Y-m-d H:i:s', $thevalue['endtime']):'';
	}

} elseif ($_GET['op'] == 'add') {
	
	$thevalue = array('taskid'=>0, 'available'=>1, 'nexttime'=>0, 'credit'=>0);
	
} elseif ($_GET['op'] == 'delete') {

	$_SGLOBAL['db']->query("DELETE FROM ".tname('task')." WHERE taskid='$taskid'");
	$_SGLOBAL['db']->query("DELETE FROM ".tname('usertask')." WHERE taskid='$taskid'");
	
	//更新缓存
	include_once(S_ROOT.'./source/function_cache.php');
	task_cache();
	
	cpmessage('do_success', 'admincp.php?ac=task');

} else {
	//列表
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('task')." ORDER BY displayorder");
	while($task = $_SGLOBAL['db']->fetch_array($query)) {
		$task['starttime'] = $task['starttime']?sgmdate("Y-m-d H:i:s", $task['starttime']) : 'N/A';
		$task['endtime'] = $task['endtime']?sgmdate("Y-m-d H:i:s", $task['endtime']) : 'N/A';
		$task['image'] = empty($task['image'])?'image/task.gif':$task['image'];
		$list[] = $task;
	}
	
	$actives = array('view' => ' class="active"');
}

$nexttypearr = array($thevalue['nexttype'] => ' selected');
$nextimestyle = $thevalue['nexttype']=='time'?'':'none';

$availables = array($thevalue['available'] => ' checked');

?>