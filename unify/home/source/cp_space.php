<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_space.php 11928 2009-04-09 01:23:00Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

if(submitcheck('delappsubmit')) {
	
	$setarr = array();
	if($_POST['type'] == 'profilelink') {
		$setarr = array('allowprofilelink' => 0);
	} else {
		$setarr = array('privacy' => 5);
	}
	$appid = intval($_POST['appid']);
	updatetable('userapp', $setarr, array('uid' => $_SGLOBAL['supe_uid'], 'appid' => $appid));
	my_userapp_update($_SGLOBAL['supe_uid'], $appid, $setarr['privacy'], $setarr['allowprofilelink']);
	showmessage('do_success', $_POST['refer']);
}

if($_GET['op'] == 'delete') {
	$delid = $_GET['type'] == 'profilelink'? 'profilelink_'.$_GET['appid'] : $_GET['appid'];
}
$actives = array($ac => ' class="active"');

include_once template("cp_space");


/**
 * 更新Manyou端的UserApp信息
 *
 * @param integer $uId UCHome里的用户id
 * @param integer $appId 应用id
 * @param integer $privacy 隐私设置
 * @param boolean $allowProfileLink 是否允许在个人主页显示相应链接
 * @return array
 * 		- errCode 0 成功；其他代表失败
 *		- errMessage 出错信息
 */
function my_userapp_update($uId, $appId, $privacy = null, $allowProfileLink = null) {
	global $my_register_url, $_SC, $_SCONFIG;

	$mySiteId = $_SCONFIG['my_siteid'];
	$mySiteKey = $_SCONFIG['my_sitekey'];
	if (!$_SCONFIG['my_status']) {
		$res = array('errCode' =>  121,
					'errMessage' => 'Manyou Service Disabled',
					'result'	=> ''
					);
		return $res;
	}

	$data = array();

	if ($privacy !== null) {
		switch($privacy) {
			case 1:
				$data['privacy'] = 'friends';
				break;
			case 3:
				$data['privacy'] = 'me';
				break;
			case 5:
				$data['privacy'] = 'none';
				break;
			case 0:
			default:
				$data['privacy'] = 'public';
		}
	}

	if ($allowProfileLink !== null) {
		$data['allowProfileLink'] = $allowProfileLink ? true : false;
	}

	if (!$data) {
		return array('errCode' => 5, 'errMessage' => 'Post Data Cann\'t Be Empty!');
	}
	
	$data = serialize($data);
	$key = "$mySiteId|$mySiteKey|$uId|$appId|$data";
	$key = md5($key);
	$data = urlencode($data);

	$postString = sprintf('action=%s&key=%s&mySiteId=%d&uId=%d&appId=%d&data=%s', 'userappUpdate', $key, $mySiteId, $uId, $appId, $data);
	
	include_once(S_ROOT.'./uc_client/client.php');
	$url = 'http://api.manyou.com/uchome.php';
	$response = uc_fopen2($url, 0, $postString, '', false, $_SCONFIG['my_ip']);
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

?>
