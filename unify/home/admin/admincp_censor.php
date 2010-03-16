<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: admincp_censor.php 8390 2008-08-06 05:50:42Z liguode $
*/

if(!defined('IN_UCHOME') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

//х╗оч
if(!checkperm('managecensor')) {
	cpmessage('no_authority_management_operation');
}

if(submitcheck('censorsubmit')) {
	$censorarr = explode("\n", $_POST['censor']);
	$newarr = array();
	foreach($censorarr as $censor) {
		list($newfind, $newreplace) = explode('=', $censor);
		$newfind = trim($newfind);
		if(strlen($newfind) >= 3) {
			$newreplace = trim($newreplace);
			if(strlen($newreplace)<1) $newreplace = '**';
			$newarr[] = "$newfind=$newreplace";
		}
	}

	data_set('censor', $newarr?implode("\n", $newarr):'');
	include_once(S_ROOT.'./source/function_cache.php');
	censor_cache();

	cpmessage('do_success', 'admincp.php?ac=censor');
}

$censor = data_get('censor');
$banflag = '{BANNED}';

?>