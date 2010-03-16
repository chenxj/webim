<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: avatar.php 13217 2009-08-21 06:57:53Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//判断用户是否设置了头像
include_once(S_ROOT.'./source/function_cp.php');
$avatar_exists = trim(ckavatar($space['uid']));
if(strlen($avatar_exists) < 1) {
	showmessage('这个功能要求您的UCenter的Server端的 avatar.php 程序需要进行升级。<br>如果您是本站管理员，请通过下面的地址下载 avatar.php 文件的压缩包，并覆盖您的UCenter根目录中的同名文件即可。<br><a href="http://u.discuz.net/download/avatar.zip">http://u.discuz.net/download/avatar.zip</a>');
}
	
if($avatar_exists) {

	//任务完成
	$task['done'] = 1;
	
	//更新用户头像标识位
	updatetable('space', array('avatar'=>1), array('uid'=>$space['uid']));
	
	//找热门异性有头像的用户
	$wherearr = array();
	$wherearr[] = "s.uid=sf.uid";
	$wherearr[] = "s.avatar='1'";
	if($space['sex'] == 2) {
		$title = '帅哥';
		$wherearr[] = "sf.sex='1'";
	} else {
		$title = '美女';
		$wherearr[] = "sf.sex='2'";
	}
	
	$space['friends'][] = $space['uid'];
	$nouids = implode(',', $space['friends']);
	$wherearr[] = "s.uid NOT IN ($nouids)";
	
	$query = $_SGLOBAL['db']->query("SELECT s.uid,s.username,s.name,s.namestatus
		FROM ".tname('space')." s, ".tname('spacefield')." sf
		WHERE ".implode(' AND ', $wherearr)."
		ORDER BY s.friendnum DESC LIMIT 0,10");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		realname_set($value['uid'], $value['username'], $value['name'], $value['namestatus']);
		$spaces[] = $value;
	}
	
	realname_get();
	
	if($spaces) {
		$task['result'] = '<p>找到'.$title.'朋友，推荐给您：</p>';
		$task['result'] .= '<ul class="avatar_list">';
		foreach ($spaces as $key => $value) {
			$task['result'] .= '<li>
			<div class="avatar48"><a href="space.php?uid='.$value['uid'].'" target="_blank">'.avatar($value['uid'], 'small').'</a></div>
			<p><a href="space.php?uid='.$value['uid'].'" target="_blank" target="_blank">'.$_SN[$value['uid']].'</a></p>
			<p class=\"time\"><a href="cp.php?ac=friend&op=add&uid='.$value['uid'].'" id="a_reside_friend_'.$key.'" onclick="ajaxmenu(event, this.id, 1)">加为好友</a></p>
			</li>';
		}
		$task['result'] .= '</ul>';
	}

} else {

	//任务完成向导
	$task['guide'] = '请按照以下的说明来参与本任务：
		<ul>
		<li>1. <a href="cp.php?ac=avatar" target="_blank">新窗口打开个人头像设置页面</a>；</li>
		<li>2. 在新打开的设置页面中，请选择您的照片进行上传编辑。</li>
		</ul>';

}

?>