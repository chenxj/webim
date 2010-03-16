<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: admincp_profilefield.php 11954 2009-04-17 09:29:53Z liguode $
*/

if(!defined('IN_UCHOME') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('manageprofilefield')) {
	cpmessage('no_authority_management_operation');
}

//取得单个数据
$thevalue = $list = array();
$_GET['fieldid'] = empty($_GET['fieldid'])?0:intval($_GET['fieldid']);
if($_GET['fieldid']) {
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('profilefield')." WHERE fieldid='$_GET[fieldid]'");
	$thevalue = $_SGLOBAL['db']->fetch_array($query);
}
if(!empty($_GET['op']) && $_GET['op'] != 'add' && empty($thevalue)) {
	cpmessage('there_is_no_designated_users_columns');
}

if(submitcheck('fieldsubmit')) {
	$setarr = array(
		'title' => shtmlspecialchars(trim($_POST['title'])),
		'note' => shtmlspecialchars(trim($_POST['note'])),
		'formtype' => shtmlspecialchars(trim($_POST['formtype'])),
		'maxsize' => intval($_POST['maxsize']),
		'required' => intval($_POST['required']),
		'invisible' => intval($_POST['invisible']),
		'allowsearch' => intval($_POST['allowsearch']),
		'choice' => shtmlspecialchars(trim($_POST['choice'])),
		'displayorder' => intval($_POST['displayorder'])
	);
	if($setarr['maxsize'] < 1 || $setarr['maxsize'] > 255) $setarr['maxsize'] = 50;
	$_POST['fieldid'] = intval($_POST['fieldid']);
	if(empty($thevalue['fieldid'])) {
		$fieldid = inserttable('profilefield', $setarr, 1);
		
		//更改表结构
		if(!$_SGLOBAL['db']->query("ALTER TABLE ".tname('spacefield')." ADD `field_$fieldid` varchar($setarr[maxsize]) NOT NULL default ''", 'SILENT')) {
			$_SGLOBAL['db']->query("DELETE FROM ".tname('profilefield')." WHERE fieldid='$fieldid'");//表结构操作失败
		}
	} else {
		//更改表结构
		if(!$_SGLOBAL['db']->query("ALTER TABLE ".tname('spacefield')." CHANGE `field_$thevalue[fieldid]` `field_$thevalue[fieldid]` varchar($setarr[maxsize]) NOT NULL default ''", 'SILENT')) {
			cpmessage('failed_to_change_the_length_of_columns', 'admincp.php?ac=profilefield');
		}
		updatetable('profilefield', $setarr, array('fieldid'=>$thevalue['fieldid']));
	}
	
	//更新缓存
	include_once(S_ROOT.'./source/function_cache.php');
	profilefield_cache();
	
	cpmessage('do_success', 'admincp.php?ac=profilefield');
	
} elseif (submitcheck('ordersubmit')) {
	foreach ($_POST['displayorder'] as $fieldid => $value) {
		updatetable('profilefield', array('displayorder'=>intval($value)), array('fieldid'=>intval($fieldid)));
	}
	
	//更新缓存
	include_once(S_ROOT.'./source/function_cache.php');
	profilefield_cache();
	
	cpmessage('do_success', 'admincp.php?ac=profilefield');
}

if(empty($_GET['op'])) {
	//列表
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('profilefield')." ORDER BY displayorder");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$list[] = $value;
	}
	
	$actives = array('view' => ' class="active"');

} elseif($_GET['op'] == 'add') {
	//添加
	$thevalue = array('filedid'=>0, 'formtype'=>'text', 'maxsize'=>50);
	$formtypearr = array();

} elseif($_GET['op'] == 'edit') {

	$formtypearr = array($thevalue['formtype'] => ' selected');
	
} elseif($_GET['op'] == 'delete') {
	include_once(S_ROOT.'./source/function_delete.php');
	if($_GET['fieldid'] && deleteprofilefield(array($_GET['fieldid']))) {
		
		//更新缓存
		include_once(S_ROOT.'./source/function_cache.php');
		profilefield_cache();
	
		cpmessage('do_success', 'admincp.php?ac=profilefield');
	} else {
		cpmessage('choose_to_delete_the_columns', 'admincp.php?ac=profilefield');
	}
}

?>