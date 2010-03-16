<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: index.php 13003 2009-08-05 06:46:06Z liguode $
*/

include_once('./common.php');

if(is_numeric($_SERVER['QUERY_STRING'])) {
	showmessage('enter_the_space', "space.php?uid=$_SERVER[QUERY_STRING]", 0);
}

//二级域名
if(!isset($_GET['do']) && $_SCONFIG['allowdomain']) {
	$hostarr = explode('.', $_SERVER['HTTP_HOST']);
	$domainrootarr = explode('.', $_SCONFIG['domainroot']);
	if(count($hostarr) > 2 && count($hostarr) > count($domainrootarr) && $hostarr[0] != 'www' && !isholddomain($hostarr[0])) {
		showmessage('enter_the_space', $_SCONFIG['siteallurl'].'space.php?domain='.$hostarr[0], 0);
	}
}

if($_SGLOBAL['supe_uid']) {
	//已登录，直接跳转个人首页
	showmessage('enter_the_space', 'space.php?do=home', 0);
}

if(empty($_SCONFIG['networkpublic'])) {
	
	$cachefile = S_ROOT.'./data/cache_index.txt';
	$cachetime = @filemtime($cachefile);
	
	$spacelist = array();
	if($_SGLOBAL['timestamp'] - $cachetime > 900) {
		//20位热门用户
		$query = $_SGLOBAL['db']->query("SELECT s.*, sf.resideprovince, sf.residecity
			FROM ".tname('space')." s
			LEFT JOIN ".tname('spacefield')." sf ON sf.uid=s.uid
			ORDER BY s.friendnum DESC LIMIT 0,20");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$spacelist[] = $value;
		}
		swritefile($cachefile, serialize($spacelist));
	} else {
		$spacelist = unserialize(sreadfile($cachefile));
	}
	
	//应用
	$myappcount = 0;
	$myapplist = array();
	if($_SCONFIG['my_status']) {
		$myappcount = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('myapp')." WHERE flag>='0'"), 0);
		if($myappcount) {
			$query = $_SGLOBAL['db']->query("SELECT appid,appname FROM ".tname('myapp')." WHERE flag>=0 ORDER BY flag DESC, displayorder LIMIT 0,7");
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				$myapplist[] = $value;
			}
		}
	}
		
	//实名
	foreach ($spacelist as $key => $value) {
		realname_set($value['uid'], $value['username'], $value['name'], $value['namestatus']);
	}
	realname_get();
	
	$_TPL['css'] = 'network';
	include_once template("index");
} else {
	include_once(S_ROOT.'./source/network.php');
}

?>