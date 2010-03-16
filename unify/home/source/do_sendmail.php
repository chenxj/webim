<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: do_login.php 8543 2008-08-21 05:51:48Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$pernum = 1;//一次发送邮件个数，太多容易超时和服务器被封杀

ssetcookie('sendmail', '1', 300);//用户每5分钟调用本程序
$lockfile = S_ROOT.'./data/sendmail.lock';
@$filemtime = filemtime($lockfile);

if($_SGLOBAL['timestamp'] - $filemtime < 5) exit();

touch($lockfile);

//防止超时
set_time_limit(0);

//获取发送队列
$list = $sublist = $cids = $touids = array();
$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('mailcron')." WHERE sendtime<='$_SGLOBAL[timestamp]' ORDER BY sendtime LIMIT 0,$pernum");
while ($value = $_SGLOBAL['db']->fetch_array($query)) {
	if($value['touid']) $touids[$value['touid']] = $value['touid'];
	$cids[] = $value['cid'];
	$list[$value['cid']] = $value;
}

if(empty($cids)) exit();

//邮件内容
$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('mailqueue')." WHERE cid IN (".simplode($cids).")");
while ($value = $_SGLOBAL['db']->fetch_array($query)) {
	$sublist[$value['cid']][] = $value;
}

//更新用户最后发送时间
if($touids) {
	$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET lastsend='$_SGLOBAL[timestamp]' WHERE uid IN (".simplode($touids).")");
}

//删除邮件
$_SGLOBAL['db']->query("DELETE FROM ".tname('mailcron')." WHERE cid IN (".simplode($cids).")");
$_SGLOBAL['db']->query("DELETE FROM ".tname('mailqueue')." WHERE cid IN (".simplode($cids).")");

//开始发送
include_once(S_ROOT.'./source/function_sendmail.php');
foreach ($list as $cid => $value) {
	$mlist = $sublist[$cid];
	if($value['email'] && $mlist) {
		$subject = getstr($mlist[0]['subject'], 80, 0, 0, 0, 0, -1);
		$message = '';
		foreach ($mlist as $subvalue) {
			if($subvalue['message']) {
				$message .= "<br><strong>$subvalue[subject]</strong><br>$subvalue[message]<br>";
			} else {
				$message .= $subvalue['subject'].'<br>';
			}
		}
		if(!sendmail($value['email'], $subject, $message)) {
			runlog('sendmail', "$value[email] sendmail failed.");
		}
	}
}

?>