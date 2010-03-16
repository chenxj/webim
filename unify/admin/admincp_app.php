<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: admincp_app.php 9398 2008-11-04 06:16:34Z liguode $
*/

if(!defined('IN_UCHOME') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('manageapp')) {
	cpmessage('no_authority_management_operation');
}

if(submitcheck('appsubmit')) {
	include_once S_ROOT.'./source/function_cache.php';

	data_set('relatedtag', $_POST['relatedtag']);
	tagtpl_cache();
	app_cache();//应用列表缓存
	cpmessage('do_success', 'admincp.php?ac=app');
}

if(empty($_GET['op'])) {
	include_once S_ROOT.'./uc_client/client.php';
	$applist = uc_app_ls();
	$relatedtag = data_get('relatedtag');
	$relatedtag = unserialize($relatedtag);
	if(empty($relatedtag)) $relatedtag = array();
}

//更新tag模板文件
function tagtpl_cache() {
	$relatedtag = unserialize(data_get('relatedtag'));
	if(empty($relatedtag)) $relatedtag = array();
	foreach($relatedtag['data'] as $appid => $data) {
		$relatedtag['limit'][$appid] = empty($relatedtag['limit'][$appid])?0:intval($relatedtag['limit'][$appid]);
		$data['template'] = trim($data['template']);
		if(empty($relatedtag['limit'][$appid]) || empty($data['template'])) {
			unset($relatedtag['data'][$appid]);
			unset($relatedtag['limit'][$appid]);
		}
	}
	cache_write('tagtpl', "_SGLOBAL['tagtpl']", $relatedtag);
}

?>