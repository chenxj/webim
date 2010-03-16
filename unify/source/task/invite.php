<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: invite.php 12304 2009-06-03 07:29:34Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//用户任务完成标识变量 		$task['done']
//任务完成结果html存储变量 	$task['result']
//用户任务向导html存储变量 	$task['guide']

$query = $_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('invite')." WHERE uid='$space[uid]' AND fuid>'0'");
$count = $_SGLOBAL['db']->result($query, 0);

if($count >= 10) {
	
	$task['done'] = 1;//任务完成

} else {

	//任务完成向导
	if($count) {
		$task['guide'] .= '<p style="color:red;">哇，厉害，您现在已经邀请了 '.$count.' 个好友了。继续努力！</p><br>';
	}
	$task['guide'] .= '<strong>请按照以下的说明来完成本任务：</strong>
		<ul class="task">
		<li>在新窗口中打开<a href="cp.php?ac=invite" target="_blank">好友邀请页面</a>；</li>
		<li>通过QQ、MSN等IM工具，或者发送邮件，把邀请链接告诉你的好友，邀请他们加入进来吧；</li>
		<li>您需要邀请10个好友才算完成。</li>
		</ul>';

}

?>