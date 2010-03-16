<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: admincp_post.php 12568 2009-07-08 07:38:01Z zhengqingpeng $
*/

if(!defined('IN_UCHOME') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$tagid = empty($_GET['tagid'])?0:intval($_GET['tagid']);

if(submitcheck('deletesubmit')) {
	include_once(S_ROOT.'./source/function_delete.php');
	if(!empty($_POST['ids']) && deleteposts($tagid, $_POST['ids'])) {
		cpmessage('do_success', $_POST['mpurl']);
	} else {
		cpmessage('choose_to_delete_the_topic', $_POST['mpurl']);
	}
}

//Ȩ��
$managebatch = checkperm('managebatch');
$allowbatch = true;
$allowmanage = 0;
if(checkperm('managethread')) {
	$allowmanage = 1;
} else {
	//Ⱥ��
	if($tagid) {
		$grade = getcount('tagspace', array('tagid'=>$tagid, 'uid'=>$_SGLOBAL['supe_uid']), 'grade');
		if($grade >= 8) {
			//�Ƿ��Ա
			$allowmanage = 1;
			$managebatch = 1;
		}
	}
}
if(!$allowmanage) {
	$_GET['uid'] = $_SGLOBAL['supe_uid'];//ֻ�ܲ������˵�
	$_GET['username'] = '';
}

$mpurl = 'admincp.php?ac=post';

//��������
$intkeys = array('pid','uid', 'tagid', 'tid', 'isthread');
$strkeys = array('username', 'ip');
$randkeys = array(array('sstrtotime','dateline'));
$likekeys = array('message');
$results = getwheres($intkeys, $strkeys, $randkeys, $likekeys);
$wherearr = $results['wherearr'];

$wheresql = empty($wherearr)?'1':implode(' AND ', $wherearr);
$mpurl .= '&'.implode('&', $results['urls']);

//����
$orders = getorders(array('dateline'), 'pid');
$ordersql = $orders['sql'];
if($orders['urls']) $mpurl .= '&'.implode('&', $orders['urls']);
$orderby = array($_GET['orderby']=>' selected');
$ordersc = array($_GET['ordersc']=>' selected');

//��ʾ��ҳ
$perpage = empty($_GET['perpage'])?0:intval($_GET['perpage']);
if(!in_array($perpage, array(20,50,100,1000))) $perpage = 20;

$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page = 1;
$start = ($page-1)*$perpage;
//��鿪ʼ��
ckstart($start, $perpage);

//��ʾ��ҳ
if($perpage > 100) {
	$count = 1;
	$selectsql = 'pid';
} else {
	$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('post')." WHERE $wheresql"), 0);
	$selectsql = '*';
}
$mpurl .= '&perpage='.$perpage;
$perpages = array($perpage => ' selected');

$list = array();
$multi = '';

$threads = $tids = array();
$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('post')." WHERE $wheresql"), 0);
if($count) {
	$query = $_SGLOBAL['db']->query("SELECT $selectsql FROM ".tname('post')." WHERE $wheresql $ordersql LIMIT $start,$perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if(!empty($value['message']) && empty($_GET['pid'])) {
			$value['message'] = getstr($value['message'], 150);
		}
		if(!$managebatch && $value['uid'] != $_SGLOBAL['supe_uid']) {
			$allowbatch = false;
		}
		if(!empty($value['tid'])) $tids[$value['tid']] = $value['tid'];
		$list[] = $value;
	}
	if($tids) {
		$query = $_SGLOBAL['db']->query("SELECT tid, subject FROM ".tname('thread')." WHERE tid IN (".simplode($tids).")");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$threads[$value['tid']] = $value['subject'];
		}
	}
	$multi = multi($count, $perpage, $page, $mpurl);
}

//��ʾ��ҳ
if($perpage > 100) {
	$count = count($list);
}

?>