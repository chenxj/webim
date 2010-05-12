<?php 
$configRoot = '..' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR ;
$platform = $_GET['platform'];
if($platform === "discuz"){
	include_once($configRoot . 'discuz.php');
	
	$today = $timestamp - ($timestamp + $timeoffset * 3600) % 86400;
	
	$ctime=$timestamp-3600*24*7;
	$sql="SELECT t.* FROM {$tablepre}threads t, {$tablepre}forumfields f, {$tablepre}members m WHERE m.uid='$discuz_uid' and (INSTR(f.viewperm,m.groupid)>0 OR f.viewperm='') AND t.replies !=0  and t.fid<>'$fid' AND f.fid=t.fid AND t.closed NOT LIKE 'moved|%'  AND t.dateline>$ctime AND f.fid not in (0) AND t.displayorder not in (-1,-2) ORDER BY t.replies DESC LIMIT 0, 10";
	$_SGLOBAL['db']->query("set NAMES ". UC_DBCHARSET);
	$query = $_SGLOBAL['db']->query($sql);
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
		$pmlist[]= array('from'=>$value['author'],'text'=>$value['subject'].'<span style=color:red>('.$value['replies'].')</span>','link'=>'viewthread.php?tid='.$value['tid'],'time'=>$value['time']);
	}
}else{
	include_once($configRoot . 'phpwind.php');
	$element = L::loadClass('element');
	$element->setDefaultNum(10);
	$article = $element->hitSort();
	foreach ($article as $key => $value) {
		#$article[$key] = array($value['addition']['tid'],substrs($value['title'],30),$value['value']);
		$pmlist[] = array('from'=>to_utf8($value['addition']['author']), 'text'=>$value['title'].'<span style=color:red>('.$value['addition']['hits'].')</span>', 'link'=>$value['url'], 'time'=>"");
	}
}
# var_dump($pmlist);
exit(json_encode($pmlist));
?>
