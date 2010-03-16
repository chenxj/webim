<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: function_userapp.php 11928 2009-04-09 01:23:00Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//获取当前环境信息
function _my_env_get($var) {
	global $_SGLOBAL, $space;
	
	if($var == 'owner') {
		return $space['uid'];
	} elseif($var == 'viewer') {
		return $_SGLOBAL['supe_uid'];
	} elseif($var == 'prefix_url') {
		if(!isset($_SGLOBAL['prefix_url'])) {
			$_SGLOBAL['prefix_url'] = getsiteurl();
		}
		return $_SGLOBAL['prefix_url'];
	} else {
		return '';
	}
}

//获取指定用户的好友的uid列表
function _my_get_friends($uid) {
	global $_SGLOBAL, $space;
	
	$var = "my_get_friends_$uid";
	if(!isset($_SGLOBAL[$var])) {
		$_SGLOBAL[$var] = array();
		if($uid == $space['uid']) {
			$_SGLOBAL[$var] = $space['friends'];
		} elseif ($uid == $_SGLOBAL['member']['uid']) {
			$_SGLOBAL[$var] = $_SGLOBAL['member']['friends'];
		} else {
			$query = $_SGLOBAL['db']->query("SELECT fuid FROM ".tname('friend')." WHERE uid='$uid' AND status='1' ORDER BY dateline DESC");
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				$_SGLOBAL[$var][] = $value['fuid'];
			}
		}
	}
	return $_SGLOBAL[$var];
}

//获取指定用户显示的名字
function _my_get_name($uid) {
	global $_SGLOBAL, $space, $_SCONFIG;
	
	$var = "my_get_name_$uid";
	if(!isset($_SGLOBAL[$var])) {
		$_SGLOBAL[$var] = '';
		if($uid == $space['uid']) {
			$_SGLOBAL[$var] = $_SCONFIG['realname'] && $space['name'] && $space['namestatus'] ?$space['name']:$space['username'];
		} elseif ($uid == $_SGLOBAL['member']['uid']) {
			$_SGLOBAL[$var] = $_SCONFIG['realname'] && $_SGLOBAL['member']['name'] && $_SGLOBAL['member']['namestatus']?$_SGLOBAL['member']['name']:$_SGLOBAL['member']['username'];
		} else {
			$query = $_SGLOBAL['db']->query("SELECT username,name,namestatus FROM ".tname('space')." WHERE uid='$uid'");
			if($value = $_SGLOBAL['db']->fetch_array($query)) {
				$_SGLOBAL[$var] = $_SCONFIG['realname'] && $value['name'] && $value['namestatus']?$value['name']:$value['username'];
			}
		}
	}
	return $_SGLOBAL[$var];
}

//获取指定用户头像的url
function _my_get_profilepic($uid, $size='small') {
	return UC_API.'/avatar.php?uid='.$uid.'&size='.$size;
}

//判断uid1和uid2是否为好友
function _my_are_friends($uid1, $uid2) {
	global $_SGLOBAL, $space, $_SCONFIG;
	
	$var = "my_are_friends_{$uid1}_{$uid2}";
	if(!isset($_SGLOBAL[$var])) {
		$_SGLOBAL[$var] = false;
		if($uid1 == $space['uid']) {
			if($space['friends'] && in_array($uid2, $space['friends'])) {
				$_SGLOBAL[$var] = true;
			}
		} elseif($uid2 == $space['uid']) {
			if($space['friends'] && in_array($uid1, $space['friends'])) {
				$_SGLOBAL[$var] = true;
			}
		} else {
			$query = $_SGLOBAL['db']->query("SELECT uid FROM ".tname('friend')." WHERE uid='$uid1' AND fuid='$uid2' AND status='1' LIMIT 1");
			if($value = $_SGLOBAL['db']->fetch_array($query)) {
				$_SGLOBAL[$var] = true;
			}
		}
	}
	return $_SGLOBAL[$var];
}

//指定用户是否安装了应用
function _my_user_is_added_app($uid, $appid) {
	global $_SGLOBAL, $space, $_SCONFIG;
	
	$var = "my_user_is_added_app_{$uid}_{$appid}";
	if(!isset($_SGLOBAL[$var])) {
		$_SGLOBAL[$var] = false;
		$query = $_SGLOBAL['db']->query("SELECT uid FROM ".tname('userapp')." WHERE uid='$uid' AND appid='$appid' LIMIT 1");
		if($value = $_SGLOBAL['db']->fetch_array($query)) {
			$_SGLOBAL[$var] = true;
		}
	}
	return $_SGLOBAL[$var];
}

//获取应用在uchome上的访问地址
function _my_get_app_url($appid, $suffix) {
	global $_SGLOBAL, $space, $_SCONFIG;
	
	if(!isset($_SGLOBAL['prefix_url'])) {
		$_SGLOBAL['prefix_url'] = getsiteurl();
	}
	return $_SGLOBAL['prefix_url']."userapp.php?appid=$appid";
}

//获取应用显示位置
function _my_get_app_position($appid) {
	global $_SGLOBAL, $space, $_SCONFIG;
	
	$var = "my_get_app_position_{$appid}";
	if(!isset($_SGLOBAL[$var])) {
		$_SGLOBAL[$var] = 'wide';
		$query = $_SGLOBAL['db']->query("SELECT narrow FROM ".tname('myapp')." WHERE appid='$appid' LIMIT 1");
		if($value = $_SGLOBAL['db']->fetch_array($query)) {
			if($value['narrow']) $_SGLOBAL[$var] = 'narrow';
		}
	}
	return $_SGLOBAL[$var];
}

?>