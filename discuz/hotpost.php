<?php 
include_once('common.php');
/*//DISUCZ获取新贴SQL

$query = $db->query("SELECT t.*, f.name FROM {$tablepre}threads t, {$tablepre}forums f WHERE t.fid<>'$fid' AND f.fid=t.fid AND f.fid not in (0) AND t.displayorder not in (-1,-2) ORDER BY t.dateline DESC LIMIT 0, 10");

//DISUCZ获取新回复SQL

$query = $db->query("SELECT t.*, f.name FROM {$tablepre}threads t, {$tablepre}forums f WHERE f.fid=t.fid AND t.closed NOT LIKE 'moved|%' AND t.replies !=0 AND f.fid not in (0) AND t.displayorder not in (-1,-2) ORDER BY t.lastpost DESC LIMIT 0, 10");

//DISUCZ获取热帖SQL

$new_hot_threadlist = array();

$mthread = array();

$ctime=$timestamp-3600*24*7;//最后7是天数为本周 

$query = $db->query("SELECT t.*, f.name FROM {$tablepre}threads t, {$tablepre}forums f WHERE t.fid<>'$fid' AND f.fid=t.fid AND t.closed NOT LIKE 'moved|%' AND t.replies !=0 AND t.dateline>$ctime AND f.fid not in (0) AND t.displayorder not in (-1,-2) ORDER BY t.replies DESC LIMIT 0, 10");

//DISUCZ获取今日发贴排行SQL

$tomonth=date(n);

$todate=date(j);

$toyear=date(Y);

$time=mktime(0,0,0,$tomonth,$todate,$toyear);

$query=$db->query("select count(pid) as num,authorid,author from $tablepre"."posts where dateline>=$time group by authorid order by num desc limit 0,10");
		*/	
	$today = $timestamp - ($timestamp + $timeoffset * 3600) % 86400;
	
	$ctime=$timestamp-3600*24*7;
	$sql="SELECT t.* FROM {$tablepre}threads t, {$tablepre}forumfields f, {$tablepre}members m WHERE m.uid='$discuz_uid' and (INSTR(f.viewperm,m.groupid)>0 OR f.viewperm='') AND t.replies !=0  and t.fid<>'$fid' AND f.fid=t.fid AND t.closed NOT LIKE 'moved|%'  AND t.dateline>$ctime AND f.fid not in (0) AND t.displayorder not in (-1,-2) ORDER BY t.replies DESC LIMIT 0, 10";
	$query = $_SGLOBAL['db']->query($sql);//
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
$pm['daterange'] = 5;
		if($value['lastpost'] >= $today) {
			$value['daterange'] = 1;
		} elseif($value['lastpost'] >= $today - 86400) {
			$value['daterange'] = 2;
		} elseif($value['lastpost'] >= $today - 172800) {
			$value['daterange'] = 3;
		}
		$value['date'] = gmdate($dateformat, $value['lastpost'] + $timeoffset * 3600);
		$value['time'] = gmdate($timeformat, $value['lastpost'] + $timeoffset * 3600);
				$pmlist[]= array('from'=>to_utf8($value['author']),'text'=>to_utf8($value['subject']).'<span style=color:red>('.$value['replies'].')</span>','link'=>'viewthread.php?tid='.$value['tid'],'time'=>$value['time']);
			}

	die(json_encode($pmlist));
?>