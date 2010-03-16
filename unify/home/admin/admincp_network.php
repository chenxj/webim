<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: admincp_network.php 12304 2009-06-03 07:29:34Z liguode $
*/

if(!defined('IN_UCHOME') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(!checkperm('managenetwork')) {
	cpmessage('no_authority_management_operation');
}

include_once(S_ROOT.'./data/data_network.php');

if(submitcheck('networksubmit')) {
	
	//批量处理
	foreach ($_POST['network'] as $type => $values) {
		foreach ($values as $key => $value) {
			$value = trim($value);
			if(strexists($value, ',')) {
				$narr = array();
				$arr = explode(',', $value);
				foreach ($arr as $v) {
					$v = trim($v);
					$v = intval($v);
					if($v) $narr[$v] = $v;
				}
				$value = implode(',', $narr);
			} elseif (preg_match("/[^a-z]/i", $value)) {
				$value = intval($value);
			}
			$values[$key] = $value;
		}
		$_POST['network'][$type] = $values;
	}
	
	data_set('network', $_POST['network']);
	
	//更新缓存
	include_once(S_ROOT.'./source/function_cache.php');
	network_cache();
	
	cpmessage('do_success', 'admincp.php?ac=network');
}

$network = $_SGLOBAL['network'];

//排序
$orders = $scs = array();
foreach (array('blog','pic','thread','poll','event') as $value) {
	$orders[$value] = array($network[$value]['order'] => ' selected');
	$scs[$value] = array($network[$value]['sc'] => ' selected');
}

?>