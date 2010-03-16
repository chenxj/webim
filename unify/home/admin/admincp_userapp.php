<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: admincp_userapp.php 12376 2009-06-16 07:10:38Z zhouguoqiang $
*/

if(!defined('IN_UCHOME') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

include_once S_ROOT.'./ver.php';
include_once S_ROOT.'./api/class/MyBase.php';

//权限
if(!checkperm('manageapp')) {
	cpmessage('no_authority_management_operation');
}

//MY设置
$my_url = 'http://api.manyou.com/uchome.php';//设置页面
$my_register_url = 'http://api.manyou.com/uchome.php';//注册接口

$_SC['language'] = $_SC['language'] ? $_SC['language'] : 'zh_CN';
	
if(empty($_SCONFIG['my_siteid']) || empty($_SCONFIG['my_sitekey'])) {
	$_SCONFIG['my_status'] = 0;
}

if(submitcheck('mysubmit')) {
	//启用服务
	$sitekey = trim($_SCONFIG['sitekey']);
	if(empty($sitekey)) {
		$sitekey = mksitekey();
		$_SGLOBAL['db']->query("REPLACE INTO ".tname('config')." (var, datavalue) VALUES ('sitekey', '$sitekey')");
		include_once(S_ROOT.'./source/function_cache.php');
		config_cache(false);
	}
	
	//如果漫游关闭再开启则直接调用更新接口
	if(empty($_SCONFIG['my_status']) && !empty($_SCONFIG['my_siteid']) && !empty($_SCONFIG['my_sitekey'])) {
		$_SCONFIG['my_status'] = 1;
	}
	$register = 0;
	if(empty($_SCONFIG['my_status'])) {
		$register = 1;
		$res = my_site_register($sitekey, $_SCONFIG['sitename'], getsiteurl(), UC_API, $_SC['charset'], $_SCONFIG['timeoffset'], $_SCONFIG['realname'], $_SCONFIG['avatarreal'], $_SC['language'], X_VER, MY_VER);
	} else {
		$res = my_site_refresh($_SCONFIG['my_siteid'], $_SCONFIG['sitename'], getsiteurl(), UC_API, $_SC['charset'], $_SCONFIG['timeoffset'], $_SCONFIG['realname'], $_SCONFIG['avatarreal'], $_SCONFIG['my_sitekey'], $sitekey, $_SC['language'], X_VER, MY_VER);
	}
	if($res['errCode']) {
		//启用失败
		cpmessage('my_register_error', '', 1, array($res['errCode'], $res['errMessage']));
	} else {
		include_once(S_ROOT.'./source/function_cache.php');
		if($register) {
			//启用成功
			$_SGLOBAL['db']->query("REPLACE INTO ".tname('config')." (var, datavalue) VALUES ('my_siteid', '{$res[result][mySiteId]}'), ('my_sitekey', '{$res[result][mySiteKey]}'), ('my_status', '1')");
			config_cache(false);
			cpmessage('my_register_sucess', 'admincp.php?ac=userapp');
		} else {
			//更新成功
			$_SGLOBAL['db']->query("REPLACE INTO ".tname('config')." (var, datavalue) VALUES ('my_status', '1')");
			config_cache(false);
			cpmessage('do_success', 'admincp.php?ac=userapp');
		}
		
	}
} else if(submitcheck('closemysubmit')) {
	//启用成功
	$res = my_site_close($_SCONFIG['my_siteid'], $_SCONFIG['my_sitekey']);
	$_SGLOBAL['db']->query("REPLACE INTO ".tname('config')." (var, datavalue) VALUES ('my_status', '0')");
	include_once(S_ROOT.'./source/function_cache.php');
	config_cache(false);
	if($res['errCode']) {
		//启用失败
		cpmessage('my_register_error', '', 1, array($res['errCode'], $res['errMessage']));
	} else {
		cpmessage('do_success', 'admincp.php?ac=userapp');
	}
}

$uch_prefix= getsiteurl() . 'admincp.php?ac=userapp';
$uch_suffix= '';
$uchUrl    = $uch_prefix . $uch_suffix;

//manyou
$my_prefix = 'http://uchome.manyou.com';
$my_suffix = urlencode($_GET['my_suffix']) ;

if (!$my_suffix) {
    header('Location: admincp.php?ac=userapp&my_suffix=' . urlencode('/appadmin/list'));
    exit;
}
$tmp_suffix= $_GET['my_suffix'] ? urldecode($_GET['my_suffix']) : '/appadmin/list';
$myUrl     = $my_prefix . $tmp_suffix;

$timestamp = time();
$hash = md5($_SCONFIG['my_siteid'] . '|' . $_SGLOBAL['supe_uid'] . '|' . $_SCONFIG['my_sitekey'] . '|' . $timestamp);

$delimiter = strrpos($myUrl, '?') ? '&' : '?';

$url = $myUrl . $delimiter .  's_id=' . $_SCONFIG['my_siteid'] . '&uch_id=' . $_SGLOBAL['supe_uid'] .'&uch_url=' . urlencode($uchUrl) . '&my_suffix=' . $my_suffix . '&timestamp=' . $timestamp . '&my_sign=' . $hash;


//my注册
function my_site_register($siteKey, $siteName, $siteUrl, $ucUrl, $siteCharset, $siteTimeZone, $siteRealNameEnable, $siteRealAvatarEnable, $siteLanguage, $siteVersion, $myVersion) {
	global $my_register_url, $_SC, $_SCONFIG;
	
	$siteName = urlencode($siteName);
	$postString = sprintf('action=%s&siteKey=%s&siteName=%s&siteUrl=%s&ucUrl=%s&siteCharset=%s&siteTimeZone=%s&siteRealNameEnable=%s&siteRealAvatarEnable=%s&siteLanguage=%s&siteVersion=%s&myVersion=%s', 'siteRegister', $siteKey, $siteName, $siteUrl, $ucUrl, $siteCharset, $siteTimeZone, $siteRealNameEnable, $siteRealAvatarEnable, $siteLanguage, $siteVersion, $myVersion);
	
	include_once(S_ROOT.'./uc_client/client.php');
	$response = uc_fopen2($my_register_url, 0, $postString, '', false, $_SCONFIG['my_ip']);
	$res = unserialize($response);
	if (!$response) {
		$res['errCode'] = 111;
		$res['errMessage'] = 'Empty Response';
		$res['result'] = $response;
	} elseif(!$res) {
		$res['errCode'] = 110;
		$res['errMessage'] = 'Error Response';
		$res['result'] = $response;
	}
	
	return $res;
}
//漫游注册更新
function my_site_refresh($mySiteId, $siteName, $siteUrl, $ucUrl, $siteCharset, $siteTimeZone, $siteEnableRealName, $siteEnableRealAvatar, $mySiteKey, $siteKey, $siteLanguage, $siteVersion, $myVersion) {
	global $my_register_url, $_SCONFIG;
	
	$key = $mySiteId . $siteName . $siteUrl . $ucUrl . $siteCharset . $siteTimeZone . $siteEnableRealName . $mySiteKey . $siteKey;
	$key = md5($key);

	$siteName = urlencode($siteName);
	$postString = sprintf('action=%s&key=%s&mySiteId=%d&siteName=%s&siteUrl=%s&ucUrl=%s&siteCharset=%s&siteTimeZone=%s&siteEnableRealName=%s&siteEnableRealAvatar=%s&siteKey=%s&siteLanguage=%s&siteVersion=%s&myVersion=%s', 'siteRefresh', $key, $mySiteId, $siteName, $siteUrl, $ucUrl, $siteCharset, $siteTimeZone, $siteEnableRealName, $siteEnableRealAvatar, $siteKey, $siteLanguage, $siteVersion, $myVersion);
	
	include_once(S_ROOT.'./uc_client/client.php');
	$response = uc_fopen2($my_register_url, 0, $postString, '', false, $_SCONFIG['my_ip']);
	$res = unserialize($response);
	if (!$response) {
		$res['errCode'] = 111;
		$res['errMessage'] = 'Empty Response';
		$res['result'] = $response;
	} elseif(!$res) {
		$res['errCode'] = 110;
		$res['errMessage'] = 'Error Response';
		$res['result'] = $response;
	}

	return $res;

}

function my_site_close($mySiteId, $mySiteKey) {
	global $my_register_url, $_SCONFIG;
	
	$key = $mySiteId . $mySiteKey;
	$key = md5($key);
	$postString = sprintf('action=%s&key=%s&mySiteId=%d', 'siteClose', $key, $mySiteId);
	include_once(S_ROOT.'./uc_client/client.php');
	$response = uc_fopen2($my_register_url, 0, $postString, '', false, $_SCONFIG['my_ip']);
	$res = unserialize($response);
	if (!$response) {
		$res['errCode'] = 111;
		$res['errMessage'] = 'Empty Response';
		$res['result'] = $response;
	} elseif(!$res) {
		$res['errCode'] = 110;
		$res['errMessage'] = 'Error Response';
		$res['result'] = $response;
	}
	return $res['result'];

}

?>
