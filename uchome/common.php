<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: common.php 13217 2009-08-21 06:57:53Z liguode $
*/

@define('IN_UCHOME', TRUE);
define('D_BUG', '0');

D_BUG?error_reporting(7):error_reporting(0);
set_magic_quotes_runtime(0);

$_SGLOBAL = $_SCONFIG = $_SBLOCK = $_TPL = $_SCOOKIE = $_SN = $space = array();

//����Ŀ¼
define('S_ROOT', dirname(__FILE__).DIRECTORY_SEPARATOR);

//�����ļ�
include_once(S_ROOT.'./ver.php');
if(!@include_once(S_ROOT.'./config.php')) {
	header("Location: install/index.php");//��װ
	exit();
}
include_once(S_ROOT.'./source/function_common.php');

//ʱ��
$mtime = explode(' ', microtime());
$_SGLOBAL['timestamp'] = $mtime[1];
$_SGLOBAL['supe_starttime'] = $_SGLOBAL['timestamp'] + $mtime[0];

//GPC����
$magic_quote = get_magic_quotes_gpc();
if(empty($magic_quote)) {
	$_GET = saddslashes($_GET);
	$_POST = saddslashes($_POST);
}

//��վURL
if(empty($_SC['siteurl'])) $_SC['siteurl'] = getsiteurl();

//�������ݿ�
dbconnect();

//�����ļ�
if(!@include_once(S_ROOT.'./data/data_config.php')) {
	include_once(S_ROOT.'./source/function_cache.php');
	config_cache();
	include_once(S_ROOT.'./data/data_config.php');
}
foreach (array('app', 'userapp', 'ad', 'magic') as $value) {
	@include_once(S_ROOT.'./data/data_'.$value.'.php');
}

//COOKIE
$prelength = strlen($_SC['cookiepre']);
foreach($_COOKIE as $key => $val) {
	if(substr($key, 0, $prelength) == $_SC['cookiepre']) {
		$_SCOOKIE[(substr($key, $prelength))] = empty($magic_quote) ? saddslashes($val) : $val;
	}
}

//����GIP
if ($_SC['gzipcompress'] && function_exists('ob_gzhandler')) {
	ob_start('ob_gzhandler');
} else {
	ob_start();
}

//��ʼ��
$_SGLOBAL['supe_uid'] = 0;
$_SGLOBAL['supe_username'] = '';
$_SGLOBAL['inajax'] = empty($_GET['inajax'])?0:intval($_GET['inajax']);
$_SGLOBAL['mobile'] = empty($_GET['mobile'])?'':trim($_GET['mobile']);
$_SGLOBAL['ajaxmenuid'] = empty($_GET['ajaxmenuid'])?'':$_GET['ajaxmenuid'];
$_SGLOBAL['refer'] = empty($_SERVER['HTTP_REFERER'])?'':$_SERVER['HTTP_REFERER'];
if(empty($_GET['m_timestamp']) || $_SGLOBAL['mobile'] != md5($_GET['m_timestamp']."\t".$_SCONFIG['sitekey'])) $_SGLOBAL['mobile'] = '';

//��¼ע�����ˮ��
if(empty($_SCONFIG['login_action'])) $_SCONFIG['login_action'] = md5('login'.md5($_SCONFIG['sitekey']));
if(empty($_SCONFIG['register_action'])) $_SCONFIG['register_action'] = md5('register'.md5($_SCONFIG['sitekey']));

//��վ���
if(empty($_SCONFIG['template'])) {
	$_SCONFIG['template'] = 'default';
}
if($_SCOOKIE['mytemplate']) {
	$_SCOOKIE['mytemplate'] = str_replace('.','',trim($_SCOOKIE['mytemplate']));
	if(file_exists(S_ROOT.'./template/'.$_SCOOKIE['mytemplate'].'/style.css')) {
		$_SCONFIG['template'] = $_SCOOKIE['mytemplate'];
	} else {
		ssetcookie('mytemplate', '', 365000);
	}
}

//����REQUEST_URI
if(!isset($_SERVER['REQUEST_URI'])) {  
	$_SERVER['REQUEST_URI'] = $_SERVER['PHP_SELF'];
	if(isset($_SERVER['QUERY_STRING'])) $_SERVER['REQUEST_URI'] .= '?'.$_SERVER['QUERY_STRING'];
}
if($_SERVER['REQUEST_URI']) {
	$temp = urldecode($_SERVER['REQUEST_URI']);
	if(strexists($temp, '<') || strexists($temp, '"')) {
		$_GET = shtmlspecialchars($_GET);//XSS
	}
}
	
//�ж��û���¼״̬
checkauth();
$_SGLOBAL['uhash'] = md5($_SGLOBAL['supe_uid']."\t".substr($_SGLOBAL['timestamp'], 0, 6));

//�û��˵�
getuserapp();

//����UCӦ��
$_SCONFIG['uc_status'] = 0;
$_SGLOBAL['appmenus'] = $_SGLOBAL['appmenu'] = array();
if($_SGLOBAL['app']) {
	foreach ($_SGLOBAL['app'] as $appid => $value) {
		if(UC_APPID != $appid) {
			$_SCONFIG['uc_status'] = 1;
		}
		if($value['open']) {
			if(empty($_SGLOBAL['appmenu'])) {
				$_SGLOBAL['appmenu'] = $value;
			} else {
				$_SGLOBAL['appmenus'][] = $value;
			}
		}
	}
}

?>