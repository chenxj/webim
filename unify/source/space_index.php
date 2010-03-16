<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: space_index.php 13159 2009-08-13 06:32:28Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//实名认证
if($space['namestatus']) {
	include_once(S_ROOT.'./source/function_cp.php');
	if(!ckrealname('viewspace', 1)) {
		$_SGLOBAL['realname_privacy'] = 1;
		include template('space_privacy');
		exit();
	}
}

//风格
$_SGLOBAL['space_theme'] = $space['theme'];
$_SGLOBAL['space_css'] = $space['css'];

//是否好友
$space['isfriend'] = $space['self'];
if($space['friends'] && in_array($_SGLOBAL['supe_uid'], $space['friends'])) {
	$space['isfriend'] = 1;//是好友
}

//个人资料
//性别
$space['sex_org'] = $space['sex'];
$space['sex'] = $space['sex']=='1'?'<a href="cp.php?ac=friend&op=search&sex=1&searchmode=1">'.lang('man').'</a>':($space['sex']=='2'?'<a href="cp.php?ac=friend&op=search&sex=2&searchmode=1">'.lang('woman').'</a>':'');
$space['birth'] = ($space['birthyear']?"$space[birthyear]".lang('year'):'').($space['birthmonth']?"$space[birthmonth]".lang('month'):'').($space['birthday']?"$space[birthday]".lang('day'):'');
$space['marry'] = $space['marry']=='1'?'<a href="cp.php?ac=friend&op=search&marry=1&searchmode=1">'.lang('unmarried').'</a>':($space['marry']=='2'?'<a href="cp.php?ac=friend&op=search&marry=2&searchmode=1">'.lang('married').'</a>':'');
$space['birthcity'] = trim(($space['birthprovince']?"<a href=\"cp.php?ac=friend&op=search&birthprovince=".rawurlencode($space['birthprovince'])."&searchmode=1\">$space[birthprovince]</a>":'').($space['birthcity']?" <a href=\"cp.php?ac=friend&op=search&birthcity=".rawurlencode($space['birthcity'])."&searchmode=1\">$space[birthcity]</a>":''));
$space['residecity'] = trim(($space['resideprovince']?"<a href=\"cp.php?ac=friend&op=search&resideprovince=".rawurlencode($space['resideprovince'])."&searchmode=1\">$space[resideprovince]</a>":'').($space['residecity']?" <a href=\"cp.php?ac=friend&op=search&residecity=".rawurlencode($space['residecity'])."&searchmode=1\">$space[residecity]</a>":''));
$space['qq'] = empty($space['qq'])?'':"<a target=\"_blank\" href=\"http://wpa.qq.com/msgrd?V=1&Uin=$space[qq]&Site=$space[username]&Menu=yes\">$space[qq]</a>";

//隐私
$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('spaceinfo')." WHERE uid='$space[uid]' AND type IN ('base', 'contact')");
while ($value = $_SGLOBAL['db']->fetch_array($query)) {
	$v_friend = ckfriend($value['uid'], $value['friend']);
	if(!$v_friend) $space[$value['subtype']] = '';
}

@include_once(S_ROOT.'./data/data_usergroup.php');

//积分
$space['star'] = getstar($space['experience']);

//域名
$space['domainurl'] = space_domain($space);


//个人动态
$feedlist = array();
if(ckprivacy('feed')) {
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('feed')." WHERE uid='$space[uid]' ORDER BY dateline DESC LIMIT 0,20");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if(ckfriend($value['uid'], $value['friend'], $value['target_ids'])) {
			realname_set($value['uid'], $value['username']);
			$feedlist[] = $value;
		}
	}
	$feednum = count($feedlist);
}

//好友列表
$oluids = array();
$friendlist = array();
if(ckprivacy('friend')) {
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('friend')." WHERE uid='$space[uid]' AND status='1' ORDER BY num DESC, dateline DESC LIMIT 0,16");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		realname_set($value['fuid'], $value['fusername']);
		$oluids[$value['fuid']] = $value['fuid'];
		$friendlist[] = $value;
	}
	if($friendlist && empty($space['friendnum'])) {
		//更新好友缓存
		include_once(S_ROOT.'./source/function_cp.php');
		friend_cache($space['uid']);
	}
}

//最近访客列表
$visitorlist = array();
$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('visitor')." WHERE uid='$space[uid]' ORDER BY dateline DESC LIMIT 0,16");
while ($value = $_SGLOBAL['db']->fetch_array($query)) {
	if($value['vusername']) {
		realname_set($value['vuid'], $value['vusername']);
	}
	$value['isfriend'] = 0;
	if($space['friends'] && in_array($value['vuid'], $space['friends'])) {
		$value['isfriend'] = 1;
	}
	$oluids[$value['vuid']] = $value['vuid'];
	$visitorlist[$value['vuid']] = $value;
}

//访问统计
$viewuids = $_SCOOKIE['viewuids']?explode('_', $_SCOOKIE['viewuids']):array();
if($_SGLOBAL['supe_uid'] && !$space['self'] && !in_array($space['uid'], $viewuids)) {
	$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET viewnum=viewnum+1 WHERE uid='$space[uid]'");
	//访刷新
	$viewuids[$space['uid']] = $space['uid'];
	ssetcookie('viewuids', implode('_', $viewuids));
}

//日志
$bloglist = array();
if($space['blognum'] && ckprivacy('blog')) {
	$query = $_SGLOBAL['db']->query("SELECT b.uid, b.blogid, b.subject, b.dateline, b.pic, b.picflag, b.viewnum, b.replynum, b.friend, b.password, bf.message, bf.target_ids
		FROM ".tname('blog')." b
		LEFT JOIN ".tname('blogfield')." bf ON bf.blogid=b.blogid
		WHERE b.uid='$space[uid]'
		ORDER BY b.dateline DESC LIMIT 0,5");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if(ckfriend($value['uid'], $value['friend'], $value['target_ids'])) {
			if($value['pic']) $value['pic'] = pic_cover_get($value['pic'], $value['picflag']);
			$value['message'] = $value['friend']==4?'':getstr($value['message'], 150, 0, 0, 0, 0, -1);
			$bloglist[] = $value;
		}
	}
	$blognum = count($bloglist);
}

//相册
$albumlist = array();
if($space['albumnum'] && ckprivacy('album')) {
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('album')." WHERE uid='$space[uid]' ORDER BY updatetime DESC LIMIT 0,6");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if(ckfriend($value['uid'], $value['friend'], $value['target_ids'])) {
			$value['pic'] = pic_cover_get($value['pic'], $value['picflag']);
			$albumlist[] = $value;
		}
	}
}

//留言板
$walllist = array();
if(ckprivacy('wall')) {
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('comment')." WHERE id='$space[uid]' AND idtype='uid' ORDER BY dateline DESC LIMIT 0,5");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		realname_set($value['authorid'], $value['author']);
		$value['message'] = strlen($value['message'])>500?getstr($value['message'], 500, 0, 0, 0, 0, -1).' ...':$value['message'];
		$walllist[] = $value;
	}
}

//是否在线
$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('session')." WHERE uid = '$space[uid]'");
$value = $_SGLOBAL['db']->fetch_array($query);
$isonline = (empty($value) || $value['magichidden']) ? 0 : sgmdate('H:i:s', $value['lastactivity'], 1);

//风格
$theme = empty($_GET['theme'])?'':preg_replace("/[^0-9a-z]/i", '', $_GET['theme']);
if($theme == 'uchomedefault') {
	$_SGLOBAL['space_theme'] = $_SGLOBAL['space_css'] = '';
} elseif($theme) {
	$cssfile = S_ROOT.'./theme/'.$theme.'/style.css';
	if(file_exists($cssfile)) {
		$_SGLOBAL['space_theme'] = $theme;
		$_SGLOBAL['space_css'] = '';
	}
} else {
	if(!$space['self'] && $_SGLOBAL['member']['nocss']) {
		$_SGLOBAL['space_theme'] = $_SGLOBAL['space_css'] = '';
	}
}

//最近访客记录
if(!$space['self'] && $_SGLOBAL['supe_uid']) {
	$query = $_SGLOBAL['db']->query("SELECT dateline FROM ".tname('visitor')." WHERE uid='$space[uid]' AND vuid='$_SGLOBAL[supe_uid]'");
	$visitor = $_SGLOBAL['db']->fetch_array($query);
	$is_anonymous = empty($_SCOOKIE['anonymous_visit_'.$_SGLOBAL['supe_uid'].'_'.$space['uid']]) ? 0 : 1;
	if(empty($visitor['dateline'])) {
		$setarr = array(
			'uid' => $space['uid'],
			'vuid' => $_SGLOBAL['supe_uid'],
			'vusername' => $is_anonymous ? '' : $_SGLOBAL['supe_username'],
			'dateline' => $_SGLOBAL['timestamp']
		);
		inserttable('visitor', $setarr, 0, true);
		show_credit();//竞价排名
	} else {
		if($_SGLOBAL['timestamp'] - $visitor['dateline'] >= 300) {
			updatetable('visitor', array('dateline'=>$_SGLOBAL['timestamp'], 'vusername'=>$is_anonymous ? '' : $_SGLOBAL['supe_username']), array('uid'=>$space['uid'], 'vuid'=>$_SGLOBAL['supe_uid']));
		}
		if($_SGLOBAL['timestamp'] - $visitor['dateline'] >= 3600) {
			show_credit();//1小时后竞价排名
		}
	}
	//奖励访客
	getreward('visit', 1, 0, $space['uid']);
}

//红包道具
$space['magiccredit'] = 0;
if($_SGLOBAL['magic']['gift'] && $_SGLOBAL['supe_uid']) {
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('magicuselog')." WHERE uid='$space[uid]' AND mid='gift' LIMIT 1");
	if($value = $_SGLOBAL['db']->fetch_array($query)) {
		$data = empty($value['data'])?array():unserialize($value['data']);
		if($data['left'] <= 0) {
			$_SGLOBAL['db']->query('DELETE FROM '.tname('magicuselog')." WHERE uid = '$space[uid]' AND mid = 'gift'");
		}
		if(!$data['receiver'] || !in_array($_SGLOBAL['supe_uid'], $data['receiver'])) {
			$space['magiccredit'] = $data['left'] >= $data['chunk'] ? $data['chunk'] : $data['left'];
		}
	}
}
	
//是否在线
$ols = array();
if($oluids) {
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('session')." WHERE uid IN (".simplode($oluids).")");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if(!$value['magichidden']) {
			$ols[$value['uid']] = 1;
		} elseif($visitorlist[$value['uid']]) {
			unset($visitorlist[$value['uid']]);
		}
	}
}

//应用显示
$narrowlist = $widelist = $guidelist = $space['userapp'] = array();
if ($_SCONFIG['my_status']) {
	$query = $_SGLOBAL['db']->query("SELECT main.*, field.*
		FROM ".tname('userapp')." main
		LEFT JOIN ".tname('userappfield')." field
		ON field.uid=main.uid AND field.appid=main.appid
		WHERE main.uid='$space[uid]'
		ORDER BY main.displayorder DESC");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$space['userapp'][$value['appid']] = $value;
	}
}
if($space['userapp']) {
	include_once(S_ROOT.'./source/function_userapp.php');
	foreach ($space['userapp'] as $value) {
		if($value['allowprofilelink'] && $value['profilelink']) {
			$guidelist[] = $value;
		}
		if(app_ckprivacy($value['privacy']) && $value['myml']) {
			$value['appurl'] = 'userapp.php?id='.$value['appid'];
			if($value['narrow']) {
				$narrowlist[] = $value;
			} else {
				$widelist[] = $value;
			}
		}
	}
}

//实名
realname_get();

//feed
foreach ($feedlist as $key => $value) {
	$feedlist[$key] = mkfeed($value);
}

//更新好友热度
if(!$space['self'] && $_SGLOBAL['supe_uid']) {
	include_once(S_ROOT.'./source/function_cp.php');
	addfriendnum($space['uid'], $space['username']);
}

//去掉广告
$_SGLOBAL['ad'] = array();

$_GET['view'] = 'me';

$_TPL['css'] = 'space';
include_once template("space_index");

//竞价排名
function show_credit() {
	global $_SGLOBAL, $space;
	$showcredit = getcount('show', array('uid'=>$space['uid']), 'credit');
	if($showcredit>0) {
		if($showcredit == 1) {
			//下榜通知
			notification_add($space['uid'], 'show', cplang('note_show_out'));
		}
		$_SGLOBAL['db']->query("UPDATE ".tname('show')." SET credit=credit-1 WHERE uid='$space[uid]' AND credit>0");
	}
}

?>
