<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: function_space.php 13225 2009-08-24 02:30:24Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//开通空间
function space_open($uid, $username, $gid=0, $email='') {
	global $_SGLOBAL, $_SCONFIG;

	if(empty($uid) || empty($username)) return array();

	//验证空间是否被管理员删除
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('spacelog')." WHERE uid='$uid' AND flag='-1'");
	if($value = $_SGLOBAL['db']->fetch_array($query)) {
		showmessage('the_space_has_been_closed');
	}

	$space = array(
		'uid' => $uid,
		'username' => $username,
		'dateline' => $_SGLOBAL['timestamp'],
		'groupid' => $gid,
		'regip' => getonlineip()
	);
	//奖励积分
	$reward = getreward('register', 0, $uid);
	if($reward['credit']) {
		$space['credit'] = $reward['credit'];
	}
	if($reward['experience']) {
		$space['experience'] = $reward['experience'];
	}
	inserttable('space', $space, 0, true);
	inserttable('spacefield', array('uid'=>$uid, 'email'=>$email), 0, true);

	//发送PM
	if($_SGLOBAL['supe_uid'] && $_SGLOBAL['supe_uid'] != $uid) {
		include_once S_ROOT.'./uc_client/client.php';
		uc_pm_send($_SGLOBAL['supe_uid'], $uid, cplang('space_open_subject'), cplang('space_open_message', array(getsiteurl())), 1, 0, 0);
	}
	
	//发送邮箱验证邮件
	include_once(S_ROOT.'./source/function_cp.php');
	emailcheck_send($uid, $email);

	//产生feed
	$_uid = $_SGLOBAL['supe_uid'];
	$_username = $_SGLOBAL['supe_username'];
	
	$_SGLOBAL['supe_uid'] = $uid;
	$_SGLOBAL['supe_username'] = addslashes($username);
	
	if(ckprivacy('spaceopen', 1)) {
		feed_add('profile', cplang('feed_space_open'));
	}

	//更新最新会员
	if($_SCONFIG['newspacenum']>0) {
		$newspacelist = array();
		$wherearr = array('1');
		if($_SCONFIG['newspaceavatar']) $wherearr[] = "avatar='1'";
		if($_SCONFIG['newspacerealname']) $wherearr[] = "namestatus='1'";
		if($_SCONFIG['newspacevideophoto']) $wherearr[] = "videostatus='1'";
		$query = $_SGLOBAL['db']->query("SELECT uid,username,name,namestatus,videostatus,dateline FROM ".tname('space')." WHERE ".implode(' AND ', $wherearr)." ORDER BY uid DESC LIMIT 0,$_SCONFIG[newspacenum]");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$newspacelist[] = $value;
		}
		data_set('newspacelist', $newspacelist);
	}
	
	//统计更新
	include_once(S_ROOT.'./source/function_cp.php');
	updatestat('register');

	$_SGLOBAL['supe_uid'] = $_uid;
	$_SGLOBAL['supe_username'] = $_username;
		
	return $space;
}

//添加session
function insertsession($setarr) {
	global $_SGLOBAL, $_SCONFIG;

	$_SCONFIG['onlinehold'] = intval($_SCONFIG['onlinehold']);
	if($_SCONFIG['onlinehold'] < 300) $_SCONFIG['onlinehold'] = 300;
	$_SGLOBAL['db']->query("DELETE FROM ".tname('session')." WHERE uid='$setarr[uid]' OR lastactivity<'".($_SGLOBAL['timestamp']-$_SCONFIG['onlinehold'])."'");

	//添加在线
	$ip = getonlineip(1);
	$setarr['lastactivity'] = $_SGLOBAL['timestamp'];
	$setarr['ip'] = $ip;

	//检查是否使用了道具隐身草
	if($_SGLOBAL['magic']['invisible']) {
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('magicuselog')." WHERE uid='$setarr[uid]' AND mid='invisible'");
		$value = $_SGLOBAL['db']->fetch_array($query);
		if($value && $value['expire'] > $_SGLOBAL['timestamp']) {
			$setarr['magichidden'] = '1';
		}
	}

	inserttable('session', $setarr, 0, true, 1);

	$spacearr = array(
		'lastlogin'=>"lastlogin='$_SGLOBAL[timestamp]'",
		'ip' => "ip='$ip'"
	);
	$_SGLOBAL['supe_uid'] = $setarr['uid'];
	$experience = $credit = 0;
	//每天登陆奖励
	$reward = getreward('daylogin', 0, $setarr['uid']);
	$credit = $reward['credit'];
	$experience = $reward['experience'];

	if($credit) {
		$spacearr['credit'] = "credit=credit+$credit";
	}
	if($experience) {
		$spacearr['experience'] = "experience=experience+$experience";
	}
	//更新用户
	$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET ".implode(',', $spacearr)." WHERE uid='$setarr[uid]'");

	//验证用户组是否过期
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('spacelog')." WHERE uid='$setarr[uid]'");
	if($value = $_SGLOBAL['db']->fetch_array($query)) {
		if($value['expiration'] <= $_SGLOBAL['timestamp']) {//到期
			//清除用户组
			updatetable('space', array('groupid'=>0), array('uid'=>$setarr['uid']));
			//删除记录
			$_SGLOBAL['db']->query("DELETE FROM ".tname('spacelog')." WHERE uid='$setarr[uid]'");
		}
	}
	
	//统计更新
	include_once(S_ROOT.'./source/function_cp.php');
	updatestat('login', 1);
}

//获取任务
function gettask() {
	global $space, $_SGLOBAL;

	$tasks = array();
	if(!@include_once(S_ROOT.'./data/data_task.php')) {
		include_once(S_ROOT.'./source/function_cache.php');
		task_cache();
	}

	if($_SGLOBAL['task']) {
		//用户已经执行的任务
		$usertasks = array();
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('usertask')." WHERE uid='$_SGLOBAL[supe_uid]'");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$usertasks[$value['taskid']] = $value;
		}
		//需要执行的任务
		foreach ($_SGLOBAL['task'] as $value) {
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
			if($value['starttime'] <= $_SGLOBAL['timestamp'] && $allownext) {
				$value['image'] = empty($value['image'])?'image/task.gif':$value['image'];
				$tasks[] = $value;
			}
		}
	}

	if($tasks) {
		$r_key = array_rand($tasks, 1);
		return $tasks[$r_key];
	} else {
		return array();
	}
}

?>