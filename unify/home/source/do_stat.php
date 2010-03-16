<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: do_inputpwd.php 10298 2008-11-28 07:57:44Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

if(empty($_SCONFIG['updatestat'])) {
	showmessage('not_open_updatestat');
}

if($_GET['hash']) {
	//ÉèÖÃhash
	ssetcookie('stat_hash', $_GET['hash']);
	showmessage('do_success', 'do.php?ac=stat', 0);
}

$stat_hash = md5($_SCONFIG['sitekey']."\t".substr($_SGLOBAL['timestamp'], 0, 6));
if(!checkperm('allowstat') && $_SCOOKIE['stat_hash'] != $stat_hash) {
	showmessage('no_privilege');
}

$cols = array();
$cols['login'] = array('login','register','invite','appinvite');
$cols['add'] = array('doing','blog','pic','poll','event','share','thread');
$cols['comment'] = array('docomment','blogcomment','piccomment','pollcomment','pollvote','eventcomment','eventjoin','sharecomment','post','click');
$cols['space'] = array('wall','poke');

$type = empty($_GET['type'])?'all':$_GET['type'];

if(!empty($_GET['xml'])) {
	$xaxis = '';
	$graph = array();
	$count = 1;
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('stat')." ORDER BY daytime");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$xaxis .= "<value xid='$count'>".substr($value['daytime'], 4, 4)."</value>";
		if($type == 'all') {
			foreach ($cols as $ck => $cvs) {
				if($ck == 'login') {
					$graph['login'] .= "<value xid='$count'>$value[login]</value>";
					$graph['register'] .= "<value xid='$count'>$value[register]</value>";
				} else {
					$num = 0;
					foreach ($cvs as $cvk) {
						$num = $value[$cvk] + $num;
					}
					$graph[$ck] .= "<value xid='$count'>".$num."</value>";
				}
			}
		} else {
			$graph[$type] .= "<value xid='$count'>".$value[$type]."</value>";
		}
		$count++;
	}
	$xml = '';
	$xml .= '<'."?xml version=\"1.0\" encoding=\"utf-8\"?>";
	$xml .= '<chart><xaxis>';
	$xml .= $xaxis;
	$xml .= "</xaxis><graphs>";
	$count = 0;
	foreach ($graph as $key => $value) {
		$xml .= "<graph gid='$count' title='".siconv(cplang("do_stat_$key"), 'utf8')."'>";
		$xml .= $value;
		$xml .= '</graph>';
		$count++;
	}
	$xml .= '</graphs></chart>';
	
	@header("Expires: -1");
	@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
	@header("Pragma: no-cache");
	@header("Content-type: application/xml; charset=utf-8");
	echo $xml;
	exit();
}

$siteurl = getsiteurl();
$statuspara = "path=&settings_file=data/stat_setting.xml&data_file=".urlencode("do.php?ac=stat&xml=1&type=$type");

$actives = array($type => ' style="font-weight:bold;"');

include template('do_stat');

?>