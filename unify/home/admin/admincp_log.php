<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: admincp_ip.php 12776 2009-07-20 07:57:21Z zhengqingpeng $
*/

if(!defined('IN_UCHOME') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('managelog')) {
	cpmessage('no_authority_management_operation');
}

$logfiles = sreaddir(S_ROOT.'./data/log/', array('php'));
sort($logfiles);

if($_GET['op'] == 'view') {
	$log = array();
	if($_GET['file'] && in_array($_GET['file'], $logfiles)) {
		$_GET['line'] = intval($_GET['line']);		
		$fp = fopen(S_ROOT.'./data/log/'.$_GET['file'], 'r');
		$offset = 0;
		while($line = fgets($fp)) {
			if(($offset++) == $_GET['line']) {
				$log = parselog($line, true);
				$log['line'] = $_GET['line'];
				$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('space')." WHERE uid = '$log[uid]'");
				$value = $_SGLOBAL['db']->fetch_array($query);
				realname_set($value['uid'], $value['username']);
				realname_get();
				break;
			}
		}
		fclose($fp);
	}
} else {
	
	$perpage = 50;
	
	$_GET['uid'] = intval($_GET['uid']);
	$_GET['keysearch'] = stripsearchkey($_GET['keysearch']);
	$_GET['ip'] = trim($_GET['ip']);
	$mpurl = "admincp.php?ac=log&file=$_GET[file]&uid=$_GET[uid]&ip=$_GET[ip]&starttime=$_GET[starttime]&endtime=$_GET[endtime]&keysearch=$_GET[keysearch]";
	//用一个临时文件缓存搜索结果
	$tmpfile = S_ROOT.'./data/temp/logsearch_'.substr(md5($mpurl), 8, 8).'.tmp';
	if(!is_dir(S_ROOT.'./data/temp/')) {
		@mkdir(S_ROOT.'./data/temp/', 0777);
	}
		
	$page = empty($_GET['page'])?1:intval($_GET['page']);
	if($page<1) $page = 1;
	$start = ($page-1)*$perpage;
	//检查开始数
	ckstart($start, $perpage);
	
	$list = $uids = array();
	$fromcache = true;
	//如果没有缓存文件，全文件扫描
	if(!is_file($tmpfile)) {
		$fromcache = false;
		$lines = array();
		$fp = fopen(S_ROOT.'./data/log/'.$_GET['file'], 'r');
		$cursor = $offset = 0;
		while($line = fgets($fp)) {
			$loginfo = parselog($line);
			$loginfo['line'] = $cursor;
			$uids[] = $loginfo['uid'];			
			$valid = true;
			if( ($_GET['uid'] && $_GET['uid'] != $loginfo['uid']) || 
				($_GET['starttime'] && $_GET['starttime'] > $loginfo['dateline']) || 
				($_GET['endtime'] && $_GET['endtime'] < $loginfo['dateline']) ||				
				($_GET['ip'] && $_GET['ip'] != $loginfo['ip']) || 
				($_GET['keysearch'] && strpos($line, $_GET['keysearch']) == false)) {
				$valid = false;	
			}
			if($valid) {
				$n = strlen($line);
				$o = ftell($fp) - $n;
				$lines[] = $cursor.'-'.$o.'-'.$n;//记录信息：行号-起始位置-长度
				if($offset >= $start && $offset < $start + $perpage) {
					$list[] = $loginfo;
				}
				$offset++;
			}
			$cursor++;
		}
		fclose($fp);
		$count = count($lines);
		swritefile($tmpfile, implode(';', $lines));		
	}
	
	if($fromcache) {
		$data = explode(';', sreadfile($tmpfile));
		$count = count($data);
		$lines = array_slice($data, $start, $perpage);
		if($lines) {
			$fp = fopen(S_ROOT.'./data/log/'.$_GET['file'], 'r');
			foreach ($lines as $line) {
				list($l, $o, $n) = explode('-', $line);
				fseek($fp, $o);
				$line = $n?fread($fp, $n):'';
				$loginfo = parselog($line);
				$loginfo['line'] = $l;
				$uids[] = $loginfo['uid'];
				$list[] = $loginfo;
			}
			fclose($fp);
		}
	}
	
	if($uids) {
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('space').' WHERE uid IN ('.simplode($uids).')');
		while($value = $_SGLOBAL['db']->fetch_array($query)) {
			realname_set($value['uid'], $value['username']);
		}
		realname_get();
	}

	$multi = multi($count, $perpage, $page, $mpurl);	
	
}

function parselog($line, $detail=false) {
	$loginfo = array();
	list($tag, $dateline, $type, $ip, $uid, $link, $extra) = explode("\t", $line);				
	$uid = intval($uid);	
	$loginfo = array(
		'ip' => $ip,
		'uid' => $uid,
		'link' => $link,
		'dateline' => $dateline,
		'type' => $type
	);
	if($detail) {
		$m1 = $m2 = array();
		if(preg_match('/GET{(.*?);}/', $extra, $m1)) {
			$get = array();			
			$parts = explode(';', $m1[1]);
			foreach ($parts as $value) {
				if(strpos($value, '=')) {
					list($key, $value) = explode('=', $value);
					$get[$key] = $value;
				}
			}
			$loginfo['get'] = '<pre>'.(print_r($get,1)).'</pre>';
			$extra = str_replace($m1[0], '', $extra);
		}
		if(preg_match('/POST{(.*);}/', $extra, $m1)) {
			$post = array();
			$m1[1] = preg_replace("/;(\w+)=/", '||||$1=', $m1[1]);			
			$parts = explode('||||', $m1[1]);
			foreach ($parts as $value) {
				if(strpos($value, '=')) {
					list($key, $value) = explode('=', $value);
					if(preg_match('/^a:\d+:{/', $value)) {
						$value = unserialize($value);
					}
					$post[$key] = $value;
				}
			}
			$loginfo['post'] = '<pre>'.(print_r($post,1)).'</pre>';
			$extra = str_replace($m1[0], '', $extra);
		}
		$loginfo['extra'] = trim($extra);
	}
	return $loginfo;
}

?>