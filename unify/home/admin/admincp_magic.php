<?php

if(!defined('IN_UCHOME') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('managemagic')) {
	cpmessage('no_authority_management_operation');
}

$_GET['view'] = ($_GET['view'] == 'disabled') ? $_GET['view'] : 'enabled';
$actives = array();
$actives[$_GET['view']] = ' class="active"';

if(submitcheck("editsubmit")){
	//编辑道具
	$_POST['forbiddengid'] = array_map('intval', $_POST['forbiddengid']);
	$arr = array(
		'description' => getstr($_POST['description'], '', 1, 1),
		'charge' => intval($_POST['charge']),
		'forbiddengid' => implode(',', $_POST['forbiddengid']),
		'experience' => intval($_POST['experience']),
		'provideperoid' => intval($_POST['provideperoid']),
		'providecount' => intval($_POST['providecount']),
		'useperoid' => intval($_POST['useperoid']),
		'usecount' => intval($_POST['usecount']),
		'displayorder' => intval($_POST['displayorder']),
		'close' => intval($_POST['close']) ? 1 : 0,
		'custom' => is_array($_POST['custom']) ? serialize($_POST['custom']) : ''
	);
	updatetable('magic', $arr, array('mid'=>$_POST['mid']));

	$_POST['storage'] = intval($_POST['storage']);
	$_SGLOBAL['db']->query("UPDATE ".tname('magicstore')." SET storage = '$_POST[storage]' WHERE mid='$_POST[mid]'");

	//更新缓存
	include_once(S_ROOT.'./source/function_cache.php');
	magic_cache();
	cpmessage('do_success', "admincp.php?ac=magic&view=$_GET[view]", 2);

} elseif(submitcheck('ordersubmit')) {
	// 排序
	if(is_array($_POST['displayorder'])){
		$orders = $charge = array();
		$query = $_SGLOBAL['db']->query('SELECT mid, charge, displayorder FROM '.tname('magic'));
		while($value = $_SGLOBAL['db']->fetch_array($query)) {
			$orders[$value['mid']] = $value['displayorder'];
			$charge[$value['mid']] = $value['charge'];
		}
		foreach($_POST['displayorder'] as $mid=>$order){
			if($orders[$mid] != $_POST['displayorder'][$mid] 
				|| $charge[$mid] != $_POST['charge'][$mid]) {
					
				updatetable('magic', array('displayorder'=>$_POST['displayorder'][$mid], 'charge'=>$_POST['charge'][$mid]), array('mid'=>$mid));
			}
		}
	}
	cpmessage('do_success', "admincp.php?ac=magic&view=$_GET[view]", 2);
}

if($_GET['op'] == 'edit') {

	//用户组
	$usergroups = array(-1=>array(), 1=>array(), 0=>array());
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('usergroup'));
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$usergroups[$value['system']][$value['gid']] = $value;
	}

	//编辑
	$query = $_SGLOBAL['db']->query("SELECT m.*, ms.storage FROM ".tname("magic")." m LEFT JOIN ".tname('magicstore')." ms ON m.mid = ms.mid WHERE m.mid = '$_GET[mid]'");
	$thevalue = $_SGLOBAL['db']->fetch_array($query);
	$thevalue['forbiddengid'] = explode(',', $thevalue['forbiddengid']);
	$thevalue['custom'] = $thevalue['custom'] ? unserialize($thevalue['custom']) : array();

} else {
	//道具列表
	$close = ($_GET['view'] == 'disabled') ? 1 : 0;

	$list = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname("magic")." WHERE close = '$close' ORDER BY displayorder");
	while($value = $_SGLOBAL['db']->fetch_array($query)) {
		$list[$value['mid']] = $value;
	}
}

?>