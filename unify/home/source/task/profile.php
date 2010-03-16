<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: profile.php 13217 2009-08-21 06:57:53Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//判断用户是否全部设置了个人资料
$nones = array();
$profile_lang = array(
	'name' => '姓名',
	'sex' => '性别',
	'birthyear' => '生日(年)',
	'birthmonth' => '生日(月)',
	'birthday' => '生日(日)',
	'blood' => '血型',
	'marry' => '婚恋状态',
	'birthprovince' => '家乡(省)',
	'birthcity' => '家乡(市)',
	'resideprovince' => '居住地(省)',
	'residecity' => '居住地(市)'
);
foreach (array('name','sex','birthyear','birthmonth','birthday','marry','birthprovince','birthcity','resideprovince','residecity') as $key) {
	$value = trim($space[$key]);
	if(empty($value)) {
		$nones[] = $profile_lang[$key];
	}
}
//站长扩展
@include_once(S_ROOT.'./data/data_profilefield.php');
foreach ($_SGLOBAL['profilefield'] as $field => $value) {
	if($value['required'] && empty($space['field_'.$field])) {
		$nones[] = $value['title'];
	}
}

if(empty($nones)) {

	$task['done'] = 1;//任务完成
	
	//自动找好友
	$findmaxnum = 10;
	$space['friends'][] = $space['uid'];
	$nouids = implode(',', $space['friends']);

	//居住地好友
	$residelist = array();
	$warr = array();
	$warr[] = "sf.resideprovince='".addslashes($space['resideprovince'])."'";
	$warr[] = "sf.residecity='".addslashes($space['residecity'])."'";
	$query = $_SGLOBAL['db']->query("SELECT s.uid,s.username,s.name,s.namestatus FROM ".tname('spacefield')." sf
		LEFT JOIN ".tname('space')." s ON s.uid=sf.uid
		WHERE ".implode(' AND ', $warr)." AND sf.uid NOT IN ($nouids)
		LIMIT 0,$findmaxnum");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		realname_set($value['uid'], $value['username'], $value['name'], $value['namestatus']);
		$residelist[] = $value;
	}

	//性别好友
	$sexlist = array();
	$warr = array();
	if(empty($space['marry']) || $space['marry'] < 2) {//单身
		$warr[] = "sf.marry='1'";//单身
	}
	if(empty($space['sex']) || $space['sex'] < 2) {//男生
		$warr[] = "sf.sex='2'";//女生
	} else {
		$warr[] = "sf.sex='1'";//男生
	}
	$query = $_SGLOBAL['db']->query("SELECT s.uid,s.username,s.name,s.namestatus FROM ".tname('spacefield')." sf
		LEFT JOIN ".tname('space')." s ON s.uid=sf.uid
		WHERE ".implode(' AND ', $warr)." AND sf.uid NOT IN ($nouids)
		LIMIT 0,$findmaxnum");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		realname_set($value['uid'], $value['username'], $value['name'], $value['namestatus']);
		$sexlist[] = $value;
	}
	
	realname_get();
	
	if($residelist) {
		$task['result'] .= '<p>为您找到同城的会员，赶快加为好友吧：</p>';
		$task['result'] .= '<ul class="avatar_list">';
		foreach ($residelist as $key => $value) {
			$task['result'] .= '<li>
				<div class="avatar48"><a href="space.php?uid='.$value['uid'].'" target="_blank">'.avatar($value['uid'], 'small').'</a></div>
				<p><a href="space.php?uid='.$value['uid'].'" target="_blank">'.$_SN[$value['uid']].'</a></p>
				<p><a href="cp.php?ac=friend&op=add&uid='.$value['uid'].'" id="a_reside_friend_'.$key.'" onclick="ajaxmenu(event, this.id, 1)">加为好友</a></p>
				</li>';
		}
		$task['result'] .= '</ul>';
	}
	if($sexlist) {
		$task['result'] .= '<p>为您找到异性热门会员，赶快加为好友吧：</p>';
		$task['result'] .= '<ul class="avatar_list">';
		foreach ($sexlist as $key => $value) {
			$task['result'] .= '<li>
				<div class="avatar48"><a href="space.php?uid='.$value['uid'].'" target="_blank">'.avatar($value['uid'], 'small').'</a></div>
				<p><a href="space.php?uid='.$value['uid'].'" target="_blank">'.$_SN[$value['uid']].'</a></p>
				<p><a href="cp.php?ac=friend&op=add&uid='.$value['uid'].'" id="a_sex_friend_'.$key.'" onclick="ajaxmenu(event, this.id, 1)">加为好友</a></p>
				</li>';
		}
		$task['result'] .= '</ul>';
	}

} else {

	//任务完成向导
	$task['guide'] = '
		<strong>您还有以下个人资料项需要补充完整：</strong><br>
		<span style="color:red;">'.implode('<br>', $nones).'</span><br><br>
		<strong>请按照以下的说明来完成本任务：</strong>
		<ul>
		<li><a href="cp.php?ac=profile" target="_blank">新窗口打开个人资料设置页面</a>；</li>
		<li>在新打开的设置页面中，将上述个人资料补充完整。</li>
		</ul>';

}

?>