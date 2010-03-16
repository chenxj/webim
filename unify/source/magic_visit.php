<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: function_image.php 7350 2008-05-12 09:36:04Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//互访卡
if(submitcheck("usesubmit")) {
	
	//随机选出最多 10 个好友
	$count = count($space['friends']);
	if(!$count) {
		showmessage("magicuse_has_no_valid_friend");//道具使用失败，还没有好友
	} elseif($count == 1) {
		$fids = array($space['friends'][0]);
	} else {
		$magic['custom']['maxvisit'] = $magic['custom']['maxvisit'] ? intval($magic['custom']['maxvisit']) : 10;
		$keys = array_rand($space['friends'], min($magic['custom']['maxvisit'], $count));
		$fids = array();
		foreach ($keys as $key) {
			$fids[] = $space['friends'][$key];
		}
	}
	
	$inserts = array();
	if($_POST['visitway'] == 'poke') {
		//打招呼
		$note = '';
		$icon = intval($_POST['visitpoke']);
		foreach ($fids as $fid) {
			$inserts[] = "('$fid', '$_SGLOBAL[supe_uid]', '$_SGLOBAL[supe_username]', '$note', '$_SGLOBAL[timestamp]', '$icon')";
		}
		$repokeids = array();
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('poke')." WHERE fromuid = '$_SGLOBAL[supe_uid]' AND uid IN (".simplode($fids).")");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$repokeids[] = $value['uid'];
		}
		$_SGLOBAL['db']->query('REPLACE INTO '.tname('poke').'(uid, fromuid, fromusername, note, dateline, iconid) VALUES '.implode(',',$inserts));
		$ids = array_diff($fids, $repokeids);
		$_SGLOBAL['db']->query('UPDATE '.tname('space').' SET pokenum = pokenum + 1 WHERE uid IN ('.simplode($ids).')');
		
	} elseif($_POST['visitway'] == 'comment') {
		//留言
		$message = getstr($_POST['visitmsg'], 255, 1, 1, 1);
		$ip = getonlineip();
		$note_inserts = array();
		foreach ($fids as $fid) {
			$inserts[] = "('$fid', '$fid', 'uid', '$_SGLOBAL[supe_uid]', '$_SGLOBAL[supe_username]','$ip', '$_SGLOBAL[timestamp]', '$message')";
			$note = cplang("magic_note_wall", array("space.php?uid=$fid&do=wall"));
			$note_inserts[] = "('$fid', 'comment', '1', '$_SGLOBAL[supe_uid]', '$_SGLOBAL[supe_username]', '$note', '$_SGLOBAL[timestamp]')";
		}
		$_SGLOBAL['db']->query('INSERT INTO '.tname('comment')."(uid, id, idtype, authorid, author, ip, dateline, message) VALUES ".implode(",", $inserts));
		$_SGLOBAL['db']->query('INSERT INTO '.tname('notification')."(uid, type, new, authorid, author, note, dateline) VALUES ".implode(",",$note_inserts));
		$_SGLOBAL['db']->query('UPDATE '.tname('space')." SET notenum = notenum + 1 WHERE uid IN (".simplode($fids).")");
		
	} else {
		//访问空间
		foreach ($fids as $fid) {
			$inserts[] = "('$fid', '$_SGLOBAL[supe_uid]', '$_SGLOBAL[supe_username]', '$_SGLOBAL[timestamp]')";
		}
		$_SGLOBAL['db']->query('REPLACE INTO '.tname('visitor')."(uid, vuid, vusername, dateline) VALUES ".implode(",",$inserts));
	}
	
	magic_use($mid, array(), 1);
	
	//显示
	$users = array();
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('member')." WHERE uid IN (".simplode($fids).")");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$users[$value['uid']] = $value;
		realname_set($value['uid'], $value['username']);
	}
	realname_get();
	$op = 'show';
}

?>