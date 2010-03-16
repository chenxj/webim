<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: admincp_tag.php 12568 2009-07-08 07:38:01Z zhengqingpeng $
*/

if(!defined('IN_UCHOME') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('managetag')) {
	cpmessage('no_authority_management_operation');
}

if(submitcheck('opsubmit')) {
	
	//验证是否有批量操作的权限
	$allowmanage = checkperm('managetag');
	$managebatch = checkperm('managebatch');
	$newids = array();
	$opnum = 0;
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('tag')." WHERE tagid IN (".simplode($_POST['ids']).")");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if($allowmanage || $value['uid'] == $_SGLOBAL['supe_uid']) {
			$newids[] = $value['tagid'];
			if(!$managebatch && $value['uid'] != $_SGLOBAL['supe_uid']) {
				$opnum++;
			}
		}
	}
	
	if(!$managebatch && $opnum > 1) {
		cpmessage('choose_to_delete_the_tag', $_POST['mpurl']);
	}
	$_POST['ids'] = $newids;
	
	if($_POST['optype'] == 'delete') {
		include_once(S_ROOT.'./source/function_delete.php');
		if(!empty($_POST['ids']) && deletetags($_POST['ids'])) {
			cpmessage('do_success', $_POST['mpurl']);
		} else {
			cpmessage('choose_to_delete_the_tag', $_POST['mpurl']);
		}
		
	} elseif($_POST['optype'] == 'merge') {
		$_POST['newtagname'] = shtmlspecialchars(trim($_POST['newtagname']));
		if(strlen($_POST['newtagname']) < 1 || strlen($_POST['newtagname']) > 30) {
			cpmessage('to_merge_the_tag_name_of_the_length_discrepancies', $_POST['mpurl']);
		}
		//检索新tag存在否
		$newtagid = getcount('tag', array('tagname'=>$_POST['newtagname']), 'tagid');
		if(empty($newtagid)) {
			//添加新tag
			$setarr = array(
				'tagname' => $_POST['newtagname'],
				'uid' => $_SGLOBAL['supe_uid'],
				'dateline' => $_SGLOBAL['timestamp']
			);
			$newtagid = inserttable('tag', $setarr, 1);
		}
		//开始合并
		include_once(S_ROOT.'./source/function_op.php');
		if(!empty($_POST['ids']) && mergetag($_POST['ids'], $newtagid)) {
			cpmessage('do_success', $_POST['mpurl']);
		} else {
			cpmessage('the_tag_choose_to_merge', $_POST['mpurl']);
		}
		
	} elseif($_POST['optype'] == 'close' || $_POST['optype'] == 'open') {
		include_once(S_ROOT.'./source/function_op.php');
		if(!empty($_POST['ids']) && closetag($_POST['ids'], $_POST['optype'])) {
			cpmessage('do_success', $_POST['mpurl']);
		} else {
			cpmessage('choose_to_operate_tag', $_POST['mpurl']);
		}
	}
}

$mpurl = 'admincp.php?ac=tag';

//处理搜索
$intkeys = array('close');
$strkeys = array();
$randkeys = array(array('sstrtotime','dateline'), array('intval','blognum'));
$likekeys = array('tagname');
$results = getwheres($intkeys, $strkeys, $randkeys, $likekeys);
$wherearr = $results['wherearr'];

$wheresql = empty($wherearr)?'1':implode(' AND ', $wherearr);
$mpurl .= '&'.implode('&', $results['urls']);

//排序
$orders = getorders(array('dateline', 'blognum'), 'tagid');
$ordersql = $orders['sql'];
if($orders['urls']) $mpurl .= '&'.implode('&', $orders['urls']);
$orderby = array($_GET['orderby']=>' selected');
$ordersc = array($_GET['ordersc']=>' selected');

//显示分页
$perpage = empty($_GET['perpage'])?0:intval($_GET['perpage']);
if(!in_array($perpage, array(20,50,100))) $perpage = 20;
$mpurl .= '&perpage='.$perpage;
$perpages = array($perpage => ' selected');

$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page = 1;
$start = ($page-1)*$perpage;
//检查开始数
ckstart($start, $perpage);
$managebatch = checkperm('managebatch');
$allowbatch = true;
$list = array();
$multi = '';

$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('tag')." WHERE $wheresql"), 0);
if($count) {
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('tag')." WHERE $wheresql $ordersql LIMIT $start,$perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$list[] = $value;
		if(!$managebatch && $value['uid'] != $_SGLOBAL['supe_uid']) {
			$allowbatch = false;
		}
	}
	$multi = multi($count, $perpage, $page, $mpurl);
}

?>