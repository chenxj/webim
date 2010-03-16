<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: admincp_comment.php 12986 2009-08-04 04:24:33Z xupeng $
*/

if(!defined('IN_UCHOME') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!$allowmanage = checkperm('managecomment')) {
	$_GET['uid'] = $_SGLOBAL['supe_uid'];//只能操作本人的
}

if(submitcheck('deletesubmit')) {
	include_once(S_ROOT.'./source/function_delete.php');
	if(!empty($_POST['ids']) && deletecomments($_POST['ids'])) {
		cpmessage('do_success', $_POST['mpurl']);
	} else {
		cpmessage('the_correct_choice_to_delete_comments', $_POST['mpurl']);
	}
}

$mpurl = 'admincp.php?ac=comment';

//处理搜索
$intkeys = array('cid', 'id', 'authorid', 'uid');
$strkeys = array('author', 'ip', 'idtype');
$randkeys = array(array('sstrtotime','dateline'));
$likekeys = array('title', 'message');
$results = getwheres($intkeys, $strkeys, $randkeys, $likekeys);
$wherearr = $results['wherearr'];

$wheresql = empty($wherearr)?'1':implode(' AND ', $wherearr);
$mpurl .= '&'.implode('&', $results['urls']);

//排序
$orders = getorders(array('dateline'), 'cid');
$ordersql = $orders['sql'];
if($orders['urls']) $mpurl .= '&'.implode('&', $orders['urls']);
$orderby = array($_GET['orderby']=>' selected');
$ordersc = array($_GET['ordersc']=>' selected');

//显示分页
$perpage = empty($_GET['perpage'])?0:intval($_GET['perpage']);
if(!in_array($perpage, array(20,50,100,1000))) $perpage = 20;

$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page = 1;
$start = ($page-1)*$perpage;
//检查开始数
ckstart($start, $perpage);

//显示分页
if($perpage > 100) {
	$count = 1;
	$selectsql = 'cid';
} else {
	$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('comment')." WHERE $wheresql"), 0);
	$selectsql = '*';
}
$mpurl .= '&perpage='.$perpage;
$perpages = array($perpage => ' selected');

$idtype = array($_GET['idtype'] => ' selected');
$managebatch = checkperm('managebatch');
$allowbatch = true;
$list = array();
$multi = '';

if($count) {
	$query = $_SGLOBAL['db']->query("SELECT $selectsql FROM ".tname('comment')." WHERE $wheresql $ordersql LIMIT $start,$perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if(!empty($value['message']) && empty($_GET['cid'])) {
			$value['message'] = getstr(strip_tags($value['message']), 200);
		}
		if(!$managebatch && $value['uid'] != $_SGLOBAL['supe_uid']) {
			$allowbatch = false;
		}
		realname_set($value['authorid'], $value['author']);
		$list[] = $value;
	}
	realname_get();
	$multi = multi($count, $perpage, $page, $mpurl);
}

//显示分页
if($perpage > 100) {
	$count = count($list);
}

?>