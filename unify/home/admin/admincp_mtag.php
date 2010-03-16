<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: admincp_mtag.php 12740 2009-07-17 01:29:11Z liguode $
*/

if(!defined('IN_UCHOME') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('managemtag')) {
	cpmessage('no_authority_management_operation');
}

@include_once(S_ROOT.'./data/data_profield.php');
$managebatch = checkperm('managebatch');
if(submitcheck('opsubmit')) {
	if(!$managebatch && count($_POST['ids']) > 1) {
		cpmessage('no_authority_management_operation');
	}
	if($_POST['optype'] == 'delete') {
		include_once(S_ROOT.'./source/function_delete.php');
		if(!empty($_POST['ids']) && deletemtag($_POST['ids'])) {
			cpmessage('do_success', $_POST['mpurl']);
		} else {
			cpmessage('choose_to_delete_the_columns_tag');
		}
	} elseif($_POST['optype'] == 'merge') {
		
		$_POST['merge_newfieldid'] = intval($_POST['merge_newfieldid']);
		$_POST['newtagname'] = shtmlspecialchars(trim($_POST['newtagname']));
		//检索新tag存在否
		$newtagid = getcount('mtag', array('tagname'=>$_POST['newtagname'], 'fieldid'=>$_POST['merge_newfieldid']), 'tagid');
		if(empty($newtagid)) {
			cpmessage('designated_to_merge_the_columns_do_not_exist');
		}
		//开始合并
		include_once(S_ROOT.'./source/function_op.php');
		if(!empty($_POST['ids']) && mergemtag($_POST['ids'], $newtagid)) {
			cpmessage('the_successful_merger_of_the_designated_columns', $_POST['mpurl']);
		} else {
			cpmessage('columns_option_to_merge_the_tag', $_POST['mpurl']);
		}
		
	} elseif($_POST['optype'] == 'move') {
		
		$_POST['move_newfieldid'] = intval($_POST['move_newfieldid']);
		if(!empty($_POST['ids']) && $_POST['move_newfieldid']) {
			$_SGLOBAL['db']->query("UPDATE ".tname('mtag')." SET fieldid='$_POST[move_newfieldid]' WHERE tagid IN (".simplode($_POST['ids']).")");
		}
		cpmessage('do_success', $_POST['mpurl']);
		
	} elseif($_POST['optype'] == 'close' || $_POST['optype'] == 'open') {
		include_once(S_ROOT.'./source/function_op.php');
		if(!empty($_POST['ids']) && closemtag($_POST['ids'], $_POST['optype'])) {
			cpmessage('lock_open_designated_columns_tag_success', $_POST['mpurl']);
		} else {
			cpmessage('choose_to_operate_columns_tag');
		}
	} elseif($_POST['optype'] == 'recommend' || $_POST['optype'] == 'unrecommend') {
		include_once(S_ROOT.'./source/function_op.php');
		if(!empty($_POST['ids']) && recommendmtag($_POST['ids'], $_POST['optype'])) {
			cpmessage('recommend_designated_columns_tag_success', $_POST['mpurl']);
		} else {
			cpmessage('choose_to_operate_columns_tag');
		}
	}
}

if(empty($_GET['op'])) {
	$mpurl = 'admincp.php?ac=mtag';
	
	//处理搜索
	$intkeys = array('close', 'recommend', 'fieldid', 'joinperm', 'viewperm', 'threadperm', 'postperm', 'tagid');
	$strkeys = array();
	$randkeys = array(array('intval','membernum'), array('intval','threadnum'), array('intval','postnum'));
	$likekeys = array('tagname');
	$results = getwheres($intkeys, $strkeys, $randkeys, $likekeys);
	$wherearr = $results['wherearr'];
	
	$wheresql = empty($wherearr)?'1':implode(' AND ', $wherearr);
	$mpurl .= '&'.implode('&', $results['urls']);

	//排序
	$orders = getorders(array('membernum','threadnum','postnum'), 'tagid');
	$ordersql = $orders['sql'];
	if($orders['urls']) $mpurl .= '&'.implode('&', $orders['urls']);
	$orderby = array($_GET['orderby']=>' selected');
	$ordersc = array($_GET['ordersc']=>' selected');
	
	$joinperms = array($_GET['joinperm']=>' selected');
	$viewperms = array($_GET['viewperm']=>' selected');
	$threadperms = array($_GET['threadperm']=>' selected');
	$postperms = array($_GET['postperm']=>' selected');
	
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

	$list = array();
	$multi = '';
	
	$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('mtag')." WHERE $wheresql"), 0);
	if($count) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('mtag')." WHERE $wheresql $ordersql LIMIT $start,$perpage");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$list[] = $value;
		}
		$multi = multi($count, $perpage, $page, $mpurl);
	}
	
}
?>