<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: app.php 9055 2008-10-21 06:22:45Z liguode $
*/

include_once('./common.php');

//空间信息
$space = getspace($_SGLOBAL['supe_uid']);

//更新活动状态
updatetable('session', array('lastactivity' => $_SGLOBAL['timestamp']), array('uid'=>$_SGLOBAL['supe_uid']));

$appid = empty($_GET['id'])?'':intval($_GET['id']);
$app = empty($_SGLOBAL['app'][$appid])?array():$_SGLOBAL['app'][$appid];
if(empty($app)) {
	$url = trim($_GET['url']);
	if(empty($url)) {
		showmessage('correct_choice_for_application_show');
	}
} else {
	//链接添加参数
	$url = $app['url'];
	if($_GET['uid']) {
		switch ($app['type']) {
			case 'DISCUZ':
				$url .= '/space.php?uid='.$_GET['uid'];
				break;
			case 'SUPESITE':
				$url .= '/index.php?'.$_GET['uid'];
				break;
			case 'UCHOME':
				$url .= '/space.php?uid='.$_GET['uid'];
				break;
			case 'SUPEV':
				$url .= '/vspace.php?mid='.$_GET['uid'];
				break;
			case 'ECMALL':
				$url .= '/index.php?app=store&store_id='.$_GET['uid'];
				break;
			default:
				$url .= '/space.php?uid='.$_GET['uid'];
				break;
		}
	}
}
if($_GET['href']) {
	$url = $_GET['href'];
}

if(!$_SCONFIG['linkguide']) {
	showmessage('do_success', $url, 0);//直接跳转
}

$_TPL['titles'] = array($app['name']);

include_once template("iframe");

?>