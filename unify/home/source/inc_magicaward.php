<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: function_common.php 12588 2009-07-09 04:47:07Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//是否已经获取过
$value = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT * FROM ".tname('magicinlog')." WHERE uid='$_SGLOBAL[supe_uid]' AND type='3' AND fromid='$gid'"), 0);
if(!$value) {
	$inserts_magicinlog = $inserts_mymagic = $note_award =array();
	$ids = array();
	foreach ($_SGLOBAL['usergroup'][$gid]['magicaward'] as $value) {
		$ids[] = $value['mid'];
	}
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname("usermagic")." WHERE uid='$_SGLOBAL[supe_uid]' AND mid IN (".simplode($ids).")");
	$mymagics = array();
	while($value=$_SGLOBAL['db']->fetch_array($query)) {
		$mymagics[$value['mid']] = $value;
	}
	include_once(S_ROOT.'./data/data_magic.php');
	foreach ($_SGLOBAL['usergroup'][$gid]['magicaward'] as $value) {
		$inserts_magicinlog[] = "('$_SGLOBAL[supe_uid]', '$_SGLOBAL[supe_username]', '$value[mid]', '$value[num]', '3', '$gid', '0', '$_SGLOBAL[timestamp]')";
		$note_award[] = '<a href="cp.php?ac=magic&view=me&mid='.$value['mid'].'" target="_blank">'.$_SGLOBAL['magic'][$value['mid']].'</a>('.$value['num'].cplang('magicunit').')';
		$inserts_mymagic[] = "('$_SGLOBAL[supe_uid]', '$_SGLOBAL[supe_username]', '$value[mid]', '".($value['num'] + intval($mymagics[$value['mid']]['count']))."')";
	}
	$_SGLOBAL['db']->query("REPLACE INTO ".tname('usermagic')."(uid, username, mid, count) VALUES ".implode(',', $inserts_mymagic));
	$_SGLOBAL['db']->query("INSERT INTO ".tname('magicinlog')."(uid, username, mid, count, type, fromid, credit, dateline) VALUES ".implode(',', $inserts_magicinlog));
	$note_award = implode('; ', $note_award);
	$supe_uid = $_SGLOBAL['supe_uid'];
	$_SGLOBAL['supe_uid'] = 0;
	include_once(S_ROOT.'./source/function_cp.php');
	notification_add($supe_uid, '', cplang('upgrade_magic_award', array($_SGLOBAL['usergroup'][$gid]['grouptitle'], $note_award)));
	$_SGLOBAL['supe_uid'] = $supe_uid;
}
					
?>