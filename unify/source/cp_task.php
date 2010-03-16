<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_task.php 12804 2009-07-21 03:27:31Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

@include_once(S_ROOT.'./data/data_task.php');

$taskid = empty($_GET['taskid'])?0:intval($_GET['taskid']);

if($taskid) {

	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('task')." WHERE taskid='$taskid'");
	if(!$task = $_SGLOBAL['db']->fetch_array($query)) {
		showmessage('task_unavailable');
	} else {
		$task['image'] = empty($task['image'])?'image/task.gif':$task['image'];
	}
	if($task['starttime'] > $_SGLOBAL['timestamp']) {
		showmessage('task_unavailable');
	}
		
	if($_GET['view'] == 'member') {
				
		//分页
		$perpage = 20;
		$page = empty($_GET['page'])?1:intval($_GET['page']);
		if($page<1) $page=1;
		$start = ($page-1)*$perpage;
		$list = array();

		//检查开始数
		ckstart($start, $perpage);
			
		$theurl = "cp.php?ac=task&taskid=$taskid&view=$_GET[view]";
		
		$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('usertask')." main WHERE main.taskid='$taskid' AND main.isignore='0'"),0);
		if($count) {
			$query = $_SGLOBAL['db']->query("SELECT s.*, sf.sex, main.dateline
				FROM ".tname('usertask')." main
				LEFT JOIN ".tname('space')." s ON s.uid=main.uid LEFT JOIN ".tname('spacefield')." sf ON sf.uid=s.uid 
				WHERE main.taskid='$taskid' AND main.isignore='0'
				ORDER BY main.dateline DESC
				LIMIT $start,$perpage");
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				realname_set($value['uid'], $value['username'], $value['name'], $value['namestatus']);
				$value['isfriend'] = ($value['uid']==$space['uid'] || ($space['friends'] && in_array($value['uid'], $space['friends'])))?1:0;
				$fuids[] = $value['uid'];
				$list[] = $value;
			}
		}
		$multi = multi($count, $perpage, $page, $theurl);
		
	} else {

		//用户执行情况
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('usertask')." WHERE uid='$_SGLOBAL[supe_uid]' AND taskid='$taskid'");
		if($usertask = $_SGLOBAL['db']->fetch_array($query)) {
			if($task['maxnum'] && $task['maxnum']<=$task['num']) {
				$task['done'] = 1;//最大次数
			} else {
				$allownext = 0;
				$lasttime = $usertask['dateline'];
				if($task['nexttype'] == 'day') {
					if(sgmdate('Ymd', $_SGLOBAL['timestamp']) != sgmdate('Ymd', $lasttime)) {
						$allownext = 1;
					}
				} elseif ($task['nexttype'] == 'hour') {
					if(sgmdate('YmdH', $_SGLOBAL['timestamp']) != sgmdate('YmdH', $lasttime)) {
						$allownext = 1;
					}
				} elseif ($task['nexttime']) {
					if($_SGLOBAL['timestamp']-$lasttime >= $task['nexttime']) {
						$allownext = 1;
					}
				}
				if($allownext) {
					$task['done'] = 0;
				} else {
					$task['done'] = 1;
				}
			}
			$task['dateline'] = $usertask['dateline'];
			$task['ignore'] = $task['done']?$usertask['isignore']:0;
		}
		
		//重新执行任务
		if($task['done'] && $task['ignore'] && $_GET['op']=='redo') {
			$_SGLOBAL['db']->query("DELETE FROM ".tname('usertask')." WHERE uid='$_SGLOBAL[supe_uid]' AND taskid='$taskid'");
			showmessage('do_success', 'cp.php?ac=task&taskid='.$taskid, 0);
		}
		
		$_SGLOBAL['task_maxnum'] = $_SGLOBAL['task_available'] = 0;
		if(empty($task['done'])) {
			$task['maxnum'] = intval($task['maxnum']);
			if($task['maxnum'] && $task['maxnum'] <= $task['num']) {
				$task['done'] = 1;
				$_SGLOBAL['task_maxnum'] = 1;
			} elseif(empty($task['available'])) {
				$task['done'] = 1;
				$_SGLOBAL['task_available'] = 1;
			}
			if(($_SGLOBAL['task_maxnum'] || $_SGLOBAL['task_available']) && $_SGLOBAL['task'][$task['taskid']]) {
				include_once(S_ROOT.'./source/function_cache.php');
				task_cache();
			}
		}
			
		//最大任务数
		if(empty($task['done'])) {
			//执行任务
			$task['result'] = '';
			$task['guide'] = '';
		
			//添加
			$setarr = array(
				'uid' => $_SGLOBAL['supe_uid'],
				'username' => $_SGLOBAL['supe_username'],
				'taskid' => $task['taskid'],
				'dateline' => $_SGLOBAL['timestamp'],
				'credit' => $task['credit']
			);
				
			if($_GET['op'] == 'ignore') {
				//放弃任务
				$setarr['isignore'] = 1;
				inserttable('usertask', $setarr, 0, true);
				showmessage('do_success', 'cp.php?ac=task&taskid='.$taskid, 0);
			}
			
			//执行任务脚本
			include_once(S_ROOT.'./source/task/'.$task['filename']);
			
			if($task['done']) {
				
				$task['dateline'] = $_SGLOBAL['timestamp'];
				inserttable('usertask', $setarr, 0, true);
				
				//更新任务完成数
				$_SGLOBAL['db']->query("UPDATE ".tname('task')." SET num=num+1 WHERE taskid='$task[taskid]'");
				
				//增加积分
				if($task['credit']) {
					$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET credit=credit+$task[credit] WHERE uid='$_SGLOBAL[supe_uid]'");
					$space['credit'] = $space['credit'] + $task['credit'];
				}
				
				//产生feed
				if(ckprivacy('task', 1)) {
					$fs = array(
						'title_template' => $task['credit']?cplang('feed_task_credit'):cplang('feed_task'),
						'title_data' => array(
								'task'=>'<a href="cp.php?ac=task&taskid='.$task['taskid'].'">'.$task['name'].'</a>',
								'credit' => $task['credit']
							),
					);
					feed_add('task', $fs['title_template'], $fs['title_data']);
				}
				
				//判读是否完成
				if($task['maxnum'] && $task['maxnum'] <= $task['num']+1) {
					include_once(S_ROOT.'./source/function_cache.php');
					task_cache();
				}
			}
		} else {
			include_once(S_ROOT.'./source/task/'.$task['filename']);
		}
		
		//刚刚参与任务
		$taskspacelist = array();
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('usertask')." WHERE taskid='$taskid' AND isignore='0' ORDER BY dateline DESC LIMIT 0,15");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			realname_set($value['uid'], $value['username']);
			$taskspacelist[] = $value;
		}
		
		realname_get();
	}
	$actives = array('do' => ' class="active"');
	
} else {
	
	//获取用户执行任务情况
	$done_per = $todo_num = $all_num = 0;
	$usertasks = array();
	$taskids = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('usertask')." WHERE uid='$_SGLOBAL[supe_uid]'");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$usertasks[$value['taskid']] = $value;
		$taskids[$value['taskid']] = $value['taskid'];
		$done_num++;
	}

	//全部任务列表
	$tasklist = array();
	$query = '';
	if($_GET['view'] == 'done') {
		if($taskids) {
			//已经完成
			$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('task')." WHERE taskid IN (".simplode($taskids).") ORDER BY displayorder");
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				$value['image'] = empty($value['image'])?'image/task.gif':$value['image'];
				$value['done'] = 1;
				$value['ignore'] = $usertasks[$value['taskid']]['isignore'];
				$tasklist[$value['taskid']] = $value;
			}
		}
	} else {
		//没有完成
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('task')." WHERE available='1' ORDER BY displayorder");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			if((empty($value['maxnum']) || $value['maxnum']>$value['num']) &&
				(empty($value['starttime']) || $value['starttime'] <= $_SGLOBAL['timestamp']) && 
				(empty($value['endtime']) || $value['endtime'] >= $_SGLOBAL['timestamp'])) {

				$all_num++;
				
				$allownext = 0;
				$lasttime = $usertasks[$value['taskid']]['dateline'];
				if(empty($lasttime)) {
					$allownext = 1;//从未执行过
				} elseif($value['nexttype'] == 'day') {
					if(sgmdate('Ymd', $_SGLOBAL['timestamp']) != sgmdate('Ymd', $lasttime)) {
						$allownext = 1;
					}
				} elseif ($value['nexttype'] == 'hour') {
					if(sgmdate('YmdH', $_SGLOBAL['timestamp']) != sgmdate('YmdH', $lasttime)) {
						$allownext = 1;
					}
				} elseif ($value['nexttime']) {
					if($_SGLOBAL['timestamp']-$lasttime >= $value['nexttime']) {
						$allownext = 1;
					}
				}
				
				if($allownext) {
					$todo_num++;
					$value['image'] = empty($value['image'])?'image/task.gif':$value['image'];
					$value['done'] = 0;
					$tasklist[$value['taskid']] = $value;
				}
			}
		}
		$done_per = empty($all_num)?100:intval(($all_num-$todo_num)*100/$all_num);
	}
	
	//刚刚参与任务
	$taskspacelist = array();
	@include_once(S_ROOT.'./data/data_task.php');
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('usertask')." WHERE isignore='0' ORDER BY dateline DESC LIMIT 0,20");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		realname_set($value['uid'], $value['username']);
		$value['taskname'] = $_SGLOBAL['task'][$value['taskid']]['name'];
		if($value['taskname']) {
			$taskspacelist[$value['uid']] = $value;
		}
	}
	
	realname_get();

	if($_GET['view'] == 'done') {
		$actives = array('done' => ' class="active"');
	} else {
		$actives = array('task' => ' class="active"');
	}
}

include template('cp_task');

?>