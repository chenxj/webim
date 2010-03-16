<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: admincp_credit.php 12304 2009-06-03 07:29:34Z liguode $
*/

if(!defined('IN_UCHOME') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('managecredit')) {
	cpmessage('no_authority_management_operation');
}


if(submitcheck('creditsubmit')) {
	
	$rid = intval($_POST['rid']);
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('creditrule')." WHERE rid='$rid'");
	$rule = $_SGLOBAL['db']->fetch_array($query);
	if(empty($rule)) {
		cpmessage('rules_do_not_exist_points', 'admincp.php?ac=credit');
	}
	$rewardtype = intval($rule['rewardtype']);
	$cycletype = intval($_POST['cycletype']);
	$setarr = array(
		'credit' => intval($_POST['credit']),
		'experience' => intval($_POST['experience']),
		'cycletype' => $cycletype,
		'cycletime' => intval($_POST['cycletime']),
		'rewardnum' => intval($_POST['rewardnum'])
	);
	//加分
	if($rewardtype) {
		//一次性
		if(!$cycletype) {
			$setarr['cycletime'] = 0;
			$setarr['rewardnum'] = 1;
		}
	} else {
		$setarr['cycletype'] = 0;
		$setarr['cycletime'] = 0;
		$setarr['rewardnum'] = 1;
	}
	updatetable('creditrule', $setarr, array('rid'=>intval($_POST['rid'])));

	include_once(S_ROOT.'./source/function_cache.php');
	creditrule_cache();
	
	cpmessage('do_success', 'admincp.php?ac=credit');
}

$list = array();
$multi = '';
$mpurl = 'admincp.php?ac=credit';

if($_GET['op']=='edit') {
	
	$rule = array();
	$rid = intval($_GET['rid']);
	if($rid) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('creditrule')." WHERE rid='$rid'");
		$rule = $_SGLOBAL['db']->fetch_array($query);
	}
	if(empty($rule)) {
		cpmessage('rules_do_not_exist_points', 'admincp.php?ac=credit');
	}
	
} else {
	$_GET['rewardtype'] = isset($_GET['rewardtype']) ? intval($_GET['rewardtype']) : 1;
	$actives = array($_GET['rewardtype'] => ' class="active"');
	
	$intkeys = array('rewardtype', 'cycletype');
	$strkeys = array();
	$randkeys = array(array('intval','credit'), array('intval', 'experience'));
	$likekeys = array('rulename');
	$results = getwheres($intkeys, $strkeys, $randkeys, $likekeys, '');
	$wherearr = $results['wherearr'];
	$wheresql = empty($wherearr)?'1':implode(' AND ', $wherearr);

	if($_GET['rewardtype'] || $_GET['rewardtype']=='0') {
	} else {
		$actives = array('-1' => ' class="active"');
	}
	
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('creditrule')." WHERE $wheresql");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$list[] = $value;
	}

}

?>