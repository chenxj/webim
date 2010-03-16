<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: userapp.php 13003 2009-08-05 06:46:06Z liguode $
*/

include_once('./common.php');

$appid = empty($_GET['id'])?'':intval($_GET['id']);

//是否关闭站点
checkclose();

//需要登录
checklogin();

//空间信息
$space = getspace($_SGLOBAL['supe_uid']);

//空间被锁定
if($space['flag'] == -1) {
	showmessage('space_has_been_locked');
}

if(checkperm('banvisit')) {
	ckspacelog();
	showmessage('you_do_not_have_permission_to_visit');
}
if(empty($_SCONFIG['my_status'])) {
	showmessage('no_privilege_my_status');
}

if($appid == '1036584') {
	//视频认证
} else {
	//验证是否有权限玩应用
	if(!checkperm('allowmyop')) {
		showmessage('no_privilege');
	}
	
	//实名认证
	include_once(S_ROOT.'./source/function_cp.php');
	ckrealname('userapp');
	
	//视频认证
	ckvideophoto('userapp');
	
	//更新状态
	updatetable('session', array('lastactivity' => $_SGLOBAL['timestamp']), array('uid'=>$_SGLOBAL['supe_uid']));
}

$app = array();
$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('myapp')." WHERE appid='$appid' LIMIT 1");
if($app = $_SGLOBAL['db']->fetch_array($query)) {
	if($app['flag']<0) {
		showmessage('no_privilege_myapp');
	}
}
	
//漫游
$my_appId = $appid;
$my_suffix = base64_decode(urldecode($_GET['my_suffix']));

$my_prefix = getsiteurl();

//奖励积分
getreward('useapp', 1, 0, $appid);

if (!$my_suffix) {
    header('Location: userapp.php?id='.$my_appId.'&my_suffix='.urlencode(base64_encode('/')));
    exit;
}

if (preg_match('/^\//', $my_suffix)) {
    $url = 'http://apps.manyou.com/'.$my_appId.$my_suffix;
} else {
    if ($my_suffix) {
        $url = 'http://apps.manyou.com/'.$my_appId.'/'.$my_suffix;
    } else {
        $url = 'http://apps.manyou.com/'.$my_appId; 
    }
}
if (strpos($my_suffix, '?')) {
    $url = $url.'&my_uchId='.$_SGLOBAL['supe_uid'].'&my_sId='.$_SCONFIG['my_siteid'];
} else {
    $url = $url.'?my_uchId='.$_SGLOBAL['supe_uid'].'&my_sId='.$_SCONFIG['my_siteid'];
}
$url .= '&my_prefix='.urlencode($my_prefix).'&my_suffix='.urlencode($my_suffix);
$current_url = getsiteurl().'userapp.php';
if ($_SERVER['QUERY_STRING']) {
    $current_url = $current_url.'?'.$_SERVER['QUERY_STRING'];
}
$extra = $_GET['my_extra'];
$timestamp = $_SGLOBAL['timestamp'];
$url .= '&my_current='.urlencode($current_url);
$url .= '&my_extra='.urlencode($extra);
$url .= '&my_ts='.$timestamp;
$url .= '&my_appVersion='.$app['version'];
$hash = $_SCONFIG['my_siteid'].'|'.$_SGLOBAL['supe_uid'].'|'.$appid.'|'.$current_url.'|'.$extra.'|'.$timestamp.'|'.$_SCONFIG['my_sitekey'];
$hash = md5($hash);
$url .= '&my_sig='.$hash;
$my_suffix = urlencode($my_suffix);

include_once template("userapp");

?>
