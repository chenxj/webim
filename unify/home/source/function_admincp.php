<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: function_admincp.php 12872 2009-07-24 01:55:54Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//获取限制条件
function getwheres($intkeys, $strkeys, $randkeys, $likekeys, $pre='') {
	
	$wherearr = array();
	$urls = array();
	
	foreach ($intkeys as $var) {
		$value = isset($_GET[$var])?$_GET[$var]:'';
		if(strlen($value)) {
			$wherearr[] = "{$pre}{$var}='".intval($value)."'";
			$urls[] = "$var=$value";
		}
	}
	
	foreach ($strkeys as $var) {
		$value = isset($_GET[$var])?trim($_GET[$var]):'';
		if(strlen($value)) {
			$wherearr[] = "{$pre}{$var}='$value'";
			$urls[] = "$var=".rawurlencode($value);
		}
	}
	
	foreach ($randkeys as $vars) {
		$value1 = isset($_GET[$vars[1].'1'])?$vars[0]($_GET[$vars[1].'1']):'';
		$value2 = isset($_GET[$vars[1].'2'])?$vars[0]($_GET[$vars[1].'2']):'';
		if($value1) {
			$wherearr[] = "{$pre}{$vars[1]}>='$value1'";
			$urls[] = "{$vars[1]}1=".rawurlencode($_GET[$vars[1].'1']);
		}
		if($value2) {
			$wherearr[] = "{$pre}{$vars[1]}<='$value2'";
			$urls[] = "{$vars[1]}2=".rawurlencode($_GET[$vars[1].'2']);
		}
	}
	
	foreach ($likekeys as $var) {
		$value = isset($_GET[$var])?stripsearchkey($_GET[$var]):'';
		if(strlen($value)>1) {
			$wherearr[] = "{$pre}{$var} LIKE BINARY '%$value%'";
			$urls[] = "$var=".rawurlencode($value);
		}
	}
	
	return array('wherearr'=>$wherearr, 'urls'=>$urls);
}

//获取排序
function getorders($alloworders, $default, $pre='') {
	$orders = array('sql'=>'', 'urls'=>array());
	if(empty($_GET['orderby']) || !in_array($_GET['orderby'], $alloworders)) {
		$_GET['orderby'] = $default;
		if(empty($_GET['ordersc'])) $_GET['ordersc'] = 'desc';
	}
	
	$orders['sql'] = " ORDER BY {$pre}$_GET[orderby] ";
	$orders['urls'][] = "orderby=$_GET[orderby]";

	if(!empty($_GET['ordersc']) && $_GET['ordersc'] == 'desc') {
		$orders['urls'][] = 'ordersc=desc';
		$orders['sql'] .= ' DESC ';
	} else {
		$orders['urls'][] = 'ordersc=asc';
	}
	return $orders;
}

//对话框
function cpmessage($msgkey, $url_forward='', $second=1, $values=array()) {
	global $_SGLOBAL, $_SC, $_SCONFIG, $_TPL, $_SN, $space;
	
	//去掉广告
	$_SGLOBAL['ad'] = array();

	include_once(S_ROOT.'./language/lang_cpmessage.php');
	if(isset($_SGLOBAL['cplang'][$msgkey])) {
		$message = lang_replace($_SGLOBAL['cplang'][$msgkey], $values);
	} else {
		$message = $msgkey;
	}
	
	//显示
	obclean();
	
	//菜单激活
	$menuactive = array('index' => ' class="active"');
	
	if(!empty($url_forward)) {
		$second = $second * 1000;
		$message .= "<script>setTimeout(\"window.location.href ='$url_forward';\", $second);</script>";
	}
	include template('admin/tpl/message');
	exit();
}

//生成站点key
function mksitekey() {
	global $_SERVER, $_SC, $_SGLOBAL;
	//16位
	$sitekey = substr(md5($_SERVER['SERVER_ADDR'].$_SERVER['HTTP_USER_AGENT'].$_SC['dbhost'].$_SC['dbuser'].$_SC['dbpw'].$_SC['dbname'].substr($_SGLOBAL['timestamp'], 0, 6)), 8, 6).random(10);
	return $sitekey;
}

//统计数据
function getstatistics() {
	global $_SGLOBAL, $_SC, $_SCONFIG;
	
	$dbsize = 0;
	$query = $_SGLOBAL['db']->query("SHOW TABLE STATUS LIKE '$_SC[tablepre]%'", 'SILENT');
	while($table = $_SGLOBAL['db']->fetch_array($query)) {
		$dbsize += $table['Data_length'] + $table['Index_length'];
	}
	$sitekey = trim($_SCONFIG['sitekey']);
	if(empty($sitekey)) {
		$sitekey = mksitekey();
		$_SGLOBAL['db']->query("REPLACE INTO ".tname('config')." (var, datavalue) VALUES ('sitekey', '$sitekey')");
		include_once(S_ROOT.'./source/function_cache.php');
		config_cache(false);
	}
	$statistics = array(
		'sitekey' => $sitekey,
		'version' => X_VER,
		'release' => X_RELEASE,
		'php' => PHP_VERSION,
		'mysql' => $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT VERSION()"), 0),
		'dbsize' => $dbsize,
		'charset' => $_SC['charset'],
		'sitename' => preg_replace('/[\'\"\s]/s', '', $_SCONFIG['sitename']),
		'feednum' => getcount('feed', array()),
		'blognum' => getcount('blog', array()),
		'albumnum' => getcount('pic', array()),
		'threadnum' => getcount('thread', array()),
		'sharenum' => getcount('share', array()),
		'commentnum' => getcount('comment', array()),
		'myappnum' => getcount('myapp', array()),
		'spacenum' => getcount('space', array())
	);
	$statistics['update'] = rawurlencode(serialize($statistics)).'&h='.substr(md5($_SERVER['HTTP_USER_AGENT'].'|'.implode('|', $statistics)), 8, 8);

	return $statistics;
}

//日志
function admincp_log() {
	global $_GET, $_POST;
	
	$log_message = '';
	if($_GET) {
		$log_message .= 'GET{';
		foreach ($_GET as $g_k => $g_v) {
			$g_v = is_array($g_v)?serialize($g_v):$g_v;
			$log_message .= "{$g_k}={$g_v};";
		}
		$log_message .= '}';
	}
	if($_POST) {
		$log_message .= 'POST{';
		foreach ($_POST as $g_k => $g_v) {
			$g_v = is_array($g_v)?serialize($g_v):$g_v;
			$log_message .= "{$g_k}={$g_v};";
		}
		$log_message .= '}';
	}
	runlog('admincp', $log_message);
}

//颜色交叉
function mkcolor($color1='#FFFFFF', $color2='#FCF9E6') {
	global $_SGLOBAL;

	$_SGLOBAL['_color'] == $color1?$_SGLOBAL['_color'] = $color2:$_SGLOBAL['_color'] = $color1;
	return $_SGLOBAL['_color'];
}

?>