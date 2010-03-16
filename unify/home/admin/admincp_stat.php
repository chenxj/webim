<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: admincp_stat.php 8390 2008-08-06 05:50:42Z liguode $
*/

if(!defined('IN_UCHOME') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('managestat')) {
	cpmessage('no_authority_management_operation');
}

$turl = 'admincp.php?ac=stat';

//处理统计更新
$perpage = empty($_GET['perpage'])?0:intval($_GET['perpage']);
$counttype = empty($_GET['counttype'])?'':$_GET['counttype'].'_stat';
if($perpage && $counttype) {
	$start = empty($_GET['start'])?0:intval($_GET['start']);
	if($start<0) $start = 0;
	
	include_once(S_ROOT.'./source/function_stat.php');
	if(!function_exists($counttype)) {
		cpmessage('choose_to_reconsider_statistical_data_types');
	}
	if($counttype($start, $perpage)) {
		$jump = $turl.'&counttype='.$_GET['counttype'].'&perpage='.$_GET['perpage'].'&start='.($start+$perpage);
		cpmessage('data_processing_please_wait_patiently', $jump, 0, array($jump, $start, $turl));//debug
	} else {
		cpmessage('do_success', $turl);
	}
}

?>