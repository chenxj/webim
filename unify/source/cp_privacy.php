<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_privacy.php 12210 2009-05-21 07:05:38Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

if(submitcheck('privacysubmit')) {

	//��˽
	foreach ($_POST['privacy']['view'] as $key => $value) {
		$space['privacy']['view'][$key] = intval($value);
	}
	//���Ͷ�̬
	$space['privacy']['feed'] = array();
	foreach ($_POST['privacy']['feed'] as $key => $value) {
		$space['privacy']['feed'][$key] = 1;
	}
	privacy_update();

	//�����¼
	if($_SCONFIG['my_status']) inserttable('userlog', array('uid'=>$_SGLOBAL['supe_uid'], 'action'=>'update', 'dateline'=>$_SGLOBAL['timestamp']), 0, true);
	showmessage('do_success', 'cp.php?ac=privacy');

} elseif(submitcheck('privacy2submit')) {

	//����ɸѡ
	$space['privacy']['filter_icon'] = array();
	foreach ($_POST['privacy']['filter_icon'] as $key => $value) {
		$space['privacy']['filter_icon'][$key] = 1;
	}
	//�û�������
	$space['privacy']['filter_gid'] = array();
	foreach ($_POST['privacy']['filter_gid'] as $key => $value) {
		$space['privacy']['filter_gid'][$key] = intval($value);
	}
	
	//֪ͨɸѡ
	$space['privacy']['filter_note'] = array();
	foreach ($_POST['privacy']['filter_note'] as $key => $value) {
		$space['privacy']['filter_note'][$key] = 1;
	}
		
	privacy_update();

	//���º��ѻ���
	friend_cache($_SGLOBAL['supe_uid']);

	showmessage('do_success', 'cp.php?ac=privacy&op=view');
}

if($_GET['op'] == 'view') {
	//������
	$groups = getfriendgroup();

	//����
	$filter_icons = empty($space['privacy']['filter_icon'])?array():$space['privacy']['filter_icon'];
	$filter_note = empty($space['privacy']['filter_note'])?array():$space['privacy']['filter_note'];
	$iconnames = $appids = $icons = $uids = $users = array();
	foreach ($filter_icons as $key => $value) {
		list($icon, $uid) = explode('|', $key);
		$icons[$key] = $icon;
		$uids[$key] = $uid;
		if(is_numeric($icon)) {
			$appids[$key] = $icon;
		}
	}
	//֪ͨ����
	foreach ($filter_note as $key => $value) {
		list($type, $uid) = explode('|', $key);
		$types[$key] = $type;
		$uids[$key] = $uid;
		if(is_numeric($type)) {
			$appids[$key] = $type;
		}
	}
	if($uids) {
		$query = $_SGLOBAL['db']->query("SELECT uid, username FROM ".tname('space')." WHERE uid IN (".simplode($uids).")");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$users[$value['uid']] = $value['username'];
		}
	}
	//��ȡӦ������
	if($appids) {
		$query = $_SGLOBAL['db']->query("SELECT appid, appname FROM ".tname('myapp')." WHERE appid IN (".simplode($appids).")");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$iconnames[$value['appid']] = $value['appname'];
		}
	}
	
	$cat_actives = array('view' => ' class="active"');

} elseif ($_GET['op'] == 'getgroup') {

	$gid = empty($_GET['gid'])?0:intval($_GET['gid']);
	$users = array();
	$query = $_SGLOBAL['db']->query("SELECT fusername FROM ".tname('friend')." WHERE uid='$_SGLOBAL[supe_uid]' AND status='1' AND gid='$gid'");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$users[] = $value['fusername'];
	}
	$ustr = empty($users)?'':shtmlspecialchars(implode(' ', $users));
	showmessage($ustr);//����

} else {

	//ҳ��ѡ��
	$_GET['op'] = '';

	$sels = array();
	foreach ($space['privacy']['view'] as $key => $value) {
		$sels['view'][$key] = array($value => ' selected');
	}
	foreach ($space['privacy']['feed'] as $key => $value) {
		$sels['feed'][$key] = ' checked';
	}
	
	$cat_actives = array('base' => ' class="active"');
}

$actives = array('privacy' =>' class="active"');

include template('cp_privacy');

?>