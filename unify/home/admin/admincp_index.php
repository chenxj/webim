<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: admincp_index.php 10804 2008-12-23 06:37:19Z zhengqingpeng $
*/

if(!defined('IN_UCHOME') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(!empty($menus[0]['config'])) {
	
	//管理员
	if(@file_exists(S_ROOT.'./install/index.php') && !@file_exists(S_ROOT.'./data/install.lock')) {
		@touch(S_ROOT.'./data/install.lock');
	}
	
	//统计
	$statistics = getstatistics();
	$os = PHP_OS.' / PHP v'.$statistics['php'].(@ini_get('safe_mode') ? ' Safe Mode' : NULL);
	
	if(@ini_get('file_uploads')) {
		$fileupload = ini_get('upload_max_filesize');
	} else {
		$fileupload = '<font color="red">Prohibition</font>';
	}

	$dbsize = $statistics['dbsize'] ? formatsize($statistics['dbsize']) : 'unknown';

	if(isset($_GET['attachsize'])) {
		$attachsize = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT SUM(size) FROM {$_SC[tablepre]}pic"), 0);
		$attachsize = is_numeric($attachsize) ? '>= '.formatsize($attachsize) : 'unknown';
	} else {
		$attachsize = '<a href="admincp.php?attachsize">------</a>';
	}
	include_once S_ROOT.'./uc_client/client.php';
}

?>