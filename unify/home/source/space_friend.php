<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: space_friend.php 12880 2009-07-24 07:20:24Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//分页
$perpage = 24;
$perpage = mob_perpage($perpage);

$list = $ols = $fuids = array();
$count = 0;
$page = empty($_GET['page'])?0:intval($_GET['page']);
if($page<1) $page = 1;
$start = ($page-1)*$perpage;

//检查开始数
ckstart($start, $perpage);

if($_GET['view'] == 'online') {
	$theurl = "space.php?uid=$space[uid]&do=friend&view=online";
	$actives = array('me'=>' class="active"');

	$wheresql = '';
	if($_GET['type']=='near') {
		$theurl = "space.php?uid=$space[uid]&do=friend&view=online&type=near";
		$wheresql = " WHERE main.ip='".getonlineip(1)."'";
	} elseif($_GET['type']=='friend' && $space['feedfriend']) {
		$theurl = "space.php?uid=$space[uid]&do=friend&view=online&type=friend";
		$wheresql = " WHERE main.uid IN ($space[feedfriend])";
	} else {
		$_GET['type']=='all';
		$theurl = "space.php?uid=$space[uid]&do=friend&view=online&type=all";
		$wheresql = ' WHERE 1';
	}

	$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('session')." main $wheresql"), 0);
	if($count) {
		$query = $_SGLOBAL['db']->query("SELECT f.resideprovince, f.residecity, f.sex, f.note, f.spacenote, main.*
			FROM ".tname('session')." main
			LEFT JOIN ".tname('spacefield')." f ON f.uid=main.uid
			$wheresql
			ORDER BY main.lastactivity DESC
			LIMIT $start,$perpage");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {			
			if($value['magichidden']) {
				$count = $count - 1;
				continue;
			}
			if($_GET['type']=='near') {
				if($value['uid'] == $space['uid']) {
					$count = $count-1;
					continue;
				}
			}
			realname_set($value['uid'], $value['username']);
			$value['p'] = rawurlencode($value['resideprovince']);
			$value['c'] = rawurlencode($value['residecity']);
			$value['isfriend'] = ($value['uid']==$space['uid'] || ($space['friends'] && in_array($value['uid'], $space['friends'])))?1:0;
			$ols[$value['uid']] = $value['lastactivity'];			
			$value['note'] = getstr($value['note'], 35, 0, 0, 0, 0, -1);
			$list[$value['uid']] = $value;
		}
	}
	$multi = multi($count, $perpage, $page, $theurl);

} elseif($_GET['view'] == 'visitor' || $_GET['view'] == 'trace') {

	$theurl = "space.php?uid=$space[uid]&do=friend&view=$_GET[view]";
	$actives = array('me'=>' class="active"');

	if($_GET['view'] == 'visitor') {//访客
		$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('visitor')." main WHERE main.uid='$space[uid]'"), 0);
		$query = $_SGLOBAL['db']->query("SELECT f.resideprovince, f.residecity, f.note, f.spacenote, f.sex, main.vuid AS uid, main.vusername AS username, main.dateline
			FROM ".tname('visitor')." main
			LEFT JOIN ".tname('spacefield')." f ON f.uid=main.vuid
			WHERE main.uid='$space[uid]'
			ORDER BY main.dateline DESC
			LIMIT $start,$perpage");
	} else {//足迹
		$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('visitor')." main WHERE main.vuid='$space[uid]'"), 0);
		$query = $_SGLOBAL['db']->query("SELECT s.username, s.name, s.namestatus, s.groupid, f.resideprovince, f.residecity, f.note, f.spacenote, f.sex, main.uid AS uid, main.dateline
			FROM ".tname('visitor')." main
			LEFT JOIN ".tname('space')." s ON s.uid=main.uid
			LEFT JOIN ".tname('spacefield')." f ON f.uid=main.uid
			WHERE main.vuid='$space[uid]'
			ORDER BY main.dateline DESC
			LIMIT $start,$perpage");
	}
	if($count) {
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			realname_set($value['uid'], $value['username'], $value['name'], $value['namestatus']);
			$value['p'] = rawurlencode($value['resideprovince']);
			$value['c'] = rawurlencode($value['residecity']);
			$value['isfriend'] = ($value['uid']==$space['uid'] || ($space['friends'] && in_array($value['uid'], $space['friends'])))?1:0;
			$fuids[] = $value['uid'];
			$value['note'] = getstr($value['note'], 28, 0, 0, 0, 0, -1);
			$list[$value['uid']] = $value;
		}
	}
	$multi = multi($count, $perpage, $page, $theurl);

} elseif($_GET['view'] == 'blacklist') {

	$theurl = "space.php?uid=$space[uid]&do=friend&view=$_GET[view]";
	$actives = array('me'=>' class="active"');

	$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('blacklist')." main WHERE main.uid='$space[uid]'"), 0);
	if($count) {
		$query = $_SGLOBAL['db']->query("SELECT s.username, s.name, s.namestatus, s.groupid, main.dateline, main.buid AS uid
			FROM ".tname('blacklist')." main
			LEFT JOIN ".tname('space')." s ON s.uid=main.buid
			WHERE main.uid='$space[uid]'
			ORDER BY main.dateline DESC
			LIMIT $start,$perpage");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$value['isfriend'] = 0;
			realname_set($value['uid'], $value['username'], $value['name'], $value['namestatus']);
			$fuids[] = $value['uid'];
			$list[$value['uid']] = $value;
		}
	}
	$multi = multi($count, $perpage, $page, $theurl);

} else {

	//处理查询
	$theurl = "space.php?uid=$space[uid]&do=$do";
	$actives = array('me'=>' class="active"');
	
	$_GET['view'] = 'me';

	//好友分组
	$wheresql = '';
	if($space['self']) {
		$groups = getfriendgroup();
		$group = !isset($_GET['group'])?'-1':intval($_GET['group']);
		if($group > -1) {
			$wheresql = "AND main.gid='$group'";
			$theurl .= "&group=$group";
		}
	}
	if($_GET['searchkey']) {
		$wheresql = "AND main.fusername='$_GET[searchkey]'";
		$theurl .= "&searchkey=$_GET[searchkey]";
	}

	if($space['friendnum']) {
		if($wheresql) {
			$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('friend')." main WHERE main.uid='$space[uid]' AND main.status='1' $wheresql"), 0);
		} else {
			$count = $space['friendnum'];
		}
		if($count) {
			$query = $_SGLOBAL['db']->query("SELECT s.*, f.resideprovince, f.residecity, f.note, f.spacenote, f.sex, main.gid, main.num
				FROM ".tname('friend')." main
				LEFT JOIN ".tname('space')." s ON s.uid=main.fuid
				LEFT JOIN ".tname('spacefield')." f ON f.uid=main.fuid
				WHERE main.uid='$space[uid]' AND main.status='1' $wheresql
				ORDER BY main.num DESC, main.dateline DESC
				LIMIT $start,$perpage");
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				realname_set($value['uid'], $value['username'], $value['name'], $value['namestatus']);
				$value['p'] = rawurlencode($value['resideprovince']);
				$value['c'] = rawurlencode($value['residecity']);
				$value['group'] = $groups[$value['gid']];
				$value['isfriend'] = 1;
				$fuids[] = $value['uid'];
				$value['note'] = getstr($value['note'], 28, 0, 0, 0, 0, -1);
				$list[$value['uid']] = $value;
			}
		}

		//分页
		$multi = multi($count, $perpage, $page, $theurl);
		$friends = array();
		//取100好友用户名
		$query = $_SGLOBAL['db']->query("SELECT f.fusername, s.name, s.namestatus, s.groupid FROM ".tname('friend')." f
			LEFT JOIN ".tname('space')." s ON s.uid=f.fuid
			WHERE f.uid=$_SGLOBAL[supe_uid] AND f.status='1' ORDER BY f.num DESC, f.dateline DESC LIMIT 0,100");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$fusername = ($_SCONFIG['realname'] && $value['name'] && $value['namestatus'])?$value['name']:$value['fusername'];
			$friends[] = addslashes($fusername);
		}
		$friendstr = implode(',', $friends);
	}

	if($space['self']) {
		$groupselect = array($group => ' class="current"');

		//好友个数
		$maxfriendnum = checkperm('maxfriendnum');
		if($maxfriendnum) {
			$maxfriendnum = checkperm('maxfriendnum') + $space['addfriend'];
		}
	}
}

//在线状态
if($fuids) {
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('session')." WHERE uid IN (".simplode($fuids).")");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if(!$value['magichidden']) {
			$ols[$value['uid']] = $value['lastactivity'];
		} elseif($list[$value['uid']] && !in_array($_GET['view'], array('me', 'trace', 'blacklist'))) {
			unset($list[$value['uid']]);
			$count = $count - 1;
		}
	}
}

realname_get();

if(empty($_GET['view']) || $_GET['view'] == 'all') $_GET['view'] = 'me';
$a_actives = array($_GET['view'].$_GET['type'] => ' class="current"');

include_once template("space_friend");

?>