<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_friend.php 13178 2009-08-17 02:36:39Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$op = empty($_GET['op'])?'':$_GET['op'];
$uid = empty($_GET['uid'])?0:intval($_GET['uid']);

$space['key'] = space_key($space);

$actives = array($op=>' class="active"');

if($op == 'add') {

	if(!checkperm('allowfriend')) {
		ckspacelog();
		showmessage('no_privilege');
	}

	//检测用户
	if($uid == $_SGLOBAL['supe_uid']) {
		showmessage('friend_self_error');
	}
	
	if($space['friends'] && in_array($uid, $space['friends'])) {
		showmessage('you_have_friends');
	}
	
	//实名认证
	ckrealname('friend');

	$tospace = getspace($uid);
	if(empty($tospace)) {
		showmessage('space_does_not_exist');
	}

	//黑名单
	if(isblacklist($tospace['uid'])) {
		showmessage('is_blacklist');
	}

	//用户组
	$groups = getfriendgroup();

	//检测现在状态
	$status = getfriendstatus($_SGLOBAL['supe_uid'], $uid);
	if($status == 1) {
		showmessage('you_have_friends');
	} else {
		//检查数目
		$maxfriendnum = checkperm('maxfriendnum');
		if($maxfriendnum && $space['friendnum'] >= $maxfriendnum + $space['addfriend']) {
			if($_SGLOBAL['magic']['friendnum']) {
				showmessage('enough_of_the_number_of_friends_with_magic');
			} else {
				showmessage('enough_of_the_number_of_friends');
			}
		}
				
		//对方是否把自己加为了好友
		$fstatus = getfriendstatus($uid, $_SGLOBAL['supe_uid']);
		if($fstatus == -1) {
			//对方没有加好友，我加别人
			if($status == -1) {
				
				//视频认证
				if($tospace['videostatus']) {
					ckvideophoto('friend', $tospace);
				}
				
				//添加单向好友
				if(submitcheck('addsubmit')) {
					$setarr = array(
						'uid' => $_SGLOBAL['supe_uid'],
						'fuid' => $uid,
						'fusername' => addslashes($tospace['username']),
						'gid' => intval($_POST['gid']),
						'note' => getstr($_POST['note'], 50, 1, 1),
						'dateline' => $_SGLOBAL['timestamp']
					);
					inserttable('friend', $setarr);
					
					//发送邮件通知
					smail($uid, '', cplang('friend_subject',array($_SN[$space['uid']], getsiteurl().'cp.php?ac=friend&amp;op=request')), '', 'friend_add');

					//增加对方好友申请数
					$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET addfriendnum=addfriendnum+1 WHERE uid='$uid'");
					
					showmessage('request_has_been_sent');
				} else {
					include_once template('cp_friend');
					exit();
				}
			} else {
				showmessage('waiting_for_the_other_test');
			}
		} else {
			//对方加了我为好友，我审核通过
			if(submitcheck('add2submit')) {
				//成为好友
				$gid = intval($_POST['gid']);

				friend_update($space['uid'], $space['username'], $tospace['uid'], $tospace['username'], 'add', $gid);

				//事件发布
				//加好友不发布事件
				if(ckprivacy('friend', 1)) {
					$fs = array();
					$fs['icon'] = 'friend';
	
					$fs['title_template'] = cplang('feed_friend_title');
					$fs['title_data'] = array('touser'=>"<a href=\"space.php?uid=$tospace[uid]\">".$_SN[$tospace['uid']]."</a>");
	
					$fs['body_template'] = '';
					$fs['body_data'] = array();
					$fs['body_general'] = '';

					feed_add($fs['icon'], $fs['title_template'], $fs['title_data'], $fs['body_template'], $fs['body_data'], $fs['body_general']);
				}
				
				//我的好友申请数进行变化
				$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET addfriendnum=addfriendnum-1 WHERE uid='$space[uid]' AND addfriendnum>0");

				//通知
				notification_add($uid, 'friend', cplang('note_friend_add'));

				showmessage('friends_add', $_POST['refer'], 1, array($_SN[$tospace['uid']]));
			} else {
				$op = 'add2';
				include_once template('cp_friend');
				exit();
			}
		}
	}

} elseif($op == 'ignore') {

	//检测用户
	if($uid) {
		if(submitcheck('friendsubmit')) {
			//对方与我的关系
			$fstatus = getfriendstatus($uid, $space['uid']);
			if($fstatus == 1) {
				//取消双向好友关系
				friend_update($_SGLOBAL['supe_uid'], $_SGLOBAL['supe_username'], $uid, '', 'ignore');
			} elseif ($fstatus == 0) {
				request_ignore($uid);
			}
			showmessage('do_success', 'cp.php?ac=friend&op=request', 0);
		}
	} elseif($_GET['key'] == $space['key']) {
		//批量忽略好友请求
		$query = $_SGLOBAL['db']->query("SELECT uid FROM ".tname('friend')." WHERE fuid='$space[uid]' AND status='0' LIMIT 0,1");
		if($value = $_SGLOBAL['db']->fetch_array($query)) {
			//删除
			$uid = $value['uid'];
			$username = getcount('space', array('uid'=>$uid), 'username');
			request_ignore($uid);
			
			showmessage('friend_ignore_next', 'cp.php?ac=friend&op=ignore&confirm=1&key='.$space['key'], 1, array($username));
		} else {
			showmessage('do_success', 'cp.php?ac=friend&op=request', 0);
		}
	}
	
} elseif($op == 'addconfirm') {

	if($_GET['key'] == $space['key']) {
		
		//检查数目
		$maxfriendnum = checkperm('maxfriendnum');
		if($maxfriendnum && $space['friendnum'] >= $maxfriendnum + $space['addfriend']) {
			if($_SGLOBAL['magic']['friendnum']) {
				showmessage('enough_of_the_number_of_friends_with_magic');
			} else {
				showmessage('enough_of_the_number_of_friends');
			}
		}
		
		//批量审核
		$query = $_SGLOBAL['db']->query("SELECT uid FROM ".tname('friend')." WHERE fuid='$space[uid]' AND status='0' LIMIT 0,1");
		if($value = $_SGLOBAL['db']->fetch_array($query)) {
			
			$uid = $value['uid'];
			$username = getcount('space', array('uid'=>$uid), 'username');
			
			friend_update($space['uid'], $space['username'], $uid, $tospace['username'], 'add', 0);
			
			//我的好友申请数进行变化
			$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET addfriendnum=addfriendnum-1 WHERE uid='$space[uid]' AND addfriendnum>0");

			//不产生feed
			showmessage('friend_addconfirm_next', 'cp.php?ac=friend&op=addconfirm&key='.$space['key'], 1, array($username));
		}
	}

	showmessage('do_success', 'cp.php?ac=friend&op=request', 0);

} elseif($op == 'syn') {

	//获取用户中心我的fans列表
	if(isset($_SCOOKIE['synfriend']) || empty($_SCONFIG['uc_status'])) {
		exit();
	}

	include_once S_ROOT.'./uc_client/client.php';
	$buddylist = uc_friend_ls($_SGLOBAL['supe_uid'], 1, 999, 999, 2);//别人加了我

	$havas = array();
	if($buddylist && is_array($buddylist)) {
		foreach($buddylist as $key => $buddy) {
			$uids[] = $buddy['uid'];
		}
		$members = array();
		if($uids) {
			$query = $_SGLOBAL['db']->query("SELECT uid FROM ".tname('space')." WHERE uid IN (".simplode($uids).")");
			while($member = $_SGLOBAL['db']->fetch_array($query)) {
				$members[] = $member['uid'];
			}
		}
		if($members) {
			foreach($buddylist as $key => $buddy) {
				if(in_array($buddy['uid'], $members)) {
					$havas[$buddy['uid']] = $buddy;
				}
			}
		}
	}

	//查找当前好友
	if($havas) {
		$query = $_SGLOBAL['db']->query("SELECT uid FROM ".tname('friend')." WHERE fuid='$_SGLOBAL[supe_uid]'");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			if(isset($havas[$value['uid']])) {
				unset($havas[$value['uid']]);
			}
		}
	}
	
	//我的黑名单
	$blacklist = array();
	$query = $_SGLOBAL['db']->query("SELECT buid FROM ".tname('blacklist')." WHERE uid='$_SGLOBAL[supe_uid]'");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$blacklist[$value['buid']] = $value['buid'];
	}

	//添加好友
	$addnum = 0;
	$inserts = array();
	if($havas) {
		foreach ($havas as $value) {
			if($_SGLOBAL['supe_uid'] != $value['uid'] && empty($blacklist[$value['uid']])) {
				$value['username'] = addslashes($value['username']);
				if($value['direction'] == 3) {//双向
					$inserts[] = "('$_SGLOBAL[supe_uid]','$value[uid]','$value[username]','1','$_SGLOBAL[timestamp]')";
					$inserts[] = "('$value[uid]','$_SGLOBAL[supe_uid]','$_SGLOBAL[supe_username]','1','$_SGLOBAL[timestamp]')";
				} else {//别人加我
					$addnum++;
					$inserts[] = "('$value[uid]','$_SGLOBAL[supe_uid]','$_SGLOBAL[supe_username]','0','$_SGLOBAL[timestamp]')";
				}
			}
		}
	}
	if($inserts) {
		$_SGLOBAL['db']->query("REPLACE INTO ".tname('friend')." (uid,fuid,fusername,status,dateline) VALUES ".implode(',',$inserts));
		friend_cache($_SGLOBAL['supe_uid']);
	}
	if($addnum) {
		$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET addfriendnum=addfriendnum+$addnum WHERE uid='$_SGLOBAL[supe_uid]'");
	}

	ssetcookie('synfriend', 1, 1800);//30分钟检查一次
	exit();

} elseif($op == 'find') {

	//自动找好友
	$maxnum = 18;
	
	$nouids = $space['friends'];
	$nouids[] = $space['uid'];

	//就在您附近的
	$nearlist = array();
	$i=0;
	$myip = getonlineip(1);
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('session')."
		WHERE ip='$myip' LIMIT 0,200");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if(!in_array($value['uid'], $nouids)) {
			realname_set($value['uid'], $value['username']);
			$nearlist[] = $value;
			$i++;
			if($i>=$maxnum) break;
		}
	}
	
	//好友的好友
	$i = 0;
	$friendlist = array();
	if($space['feedfriend']) {
		$query = $_SGLOBAL['db']->query("SELECT fuid AS uid, fusername AS username FROM ".tname('friend')."
			WHERE uid IN (".$space['feedfriend'].") LIMIT 0,200");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			if(!in_array($value['uid'], $nouids) && $value['username']) {
				realname_set($value['uid'], $value['username']);
				$friendlist[$value['uid']] = $value;
				$i++;
				if($i>=$maxnum) break;
			}
		}
	}

	//当前在线的好友
	$i = 0;
	$onlinelist = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('session')." LIMIT 0,200");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if(!in_array($value['uid'], $nouids)) {
			realname_set($value['uid'], $value['username']);
			$onlinelist[] = $value;
			$i++;
			if($i>=$maxnum) break;
		}
	}

	//实名
	realname_get();

} elseif($op == 'changegroup') {

	if(submitcheck('changegroupsubmit')) {
		updatetable('friend', array('gid'=>intval($_POST['group'])), array('uid'=>$_SGLOBAL['supe_uid'], 'fuid'=>$uid));
		friend_cache($_SGLOBAL['supe_uid']);
		showmessage('do_success', $_SGLOBAL['refer']);
	}

	//获得当前用户group
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('friend')." WHERE uid='$_SGLOBAL[supe_uid]' AND fuid='$uid'");
	if(!$friend = $_SGLOBAL['db']->fetch_array($query)) {
		showmessage('specified_user_is_not_your_friend');
	}
	$groupselect = array($friend['gid'] => ' checked');

	$groups = getfriendgroup();

} elseif($op == 'changenum') {

	if(submitcheck('changenumsubmit')) {
		updatetable('friend', array('num'=>intval($_POST['num'])), array('uid'=>$_SGLOBAL['supe_uid'], 'fuid'=>$uid));
		friend_cache($_SGLOBAL['supe_uid']);
		showmessage('do_success', $_SGLOBAL['refer'], 0);
	}

	//获得当前用户group
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('friend')." WHERE uid='$_SGLOBAL[supe_uid]' AND fuid='$uid'");
	if(!$friend = $_SGLOBAL['db']->fetch_array($query)) {
		showmessage('specified_user_is_not_your_friend');
	}
	
} elseif($op == 'group') {

	if(submitcheck('groupsubmin')) {
		if(empty($_POST['fuids'])) {
			showmessage('please_correct_choice_groups_friend');
		}
		$ids = simplode($_POST['fuids']);
		$groupid = intval($_POST['group']);
		updatetable('friend', array('gid'=>$groupid), "uid='$_SGLOBAL[supe_uid]' AND fuid IN ($ids) AND status='1'");
		friend_cache($_SGLOBAL['supe_uid']);
		showmessage('do_success', $_SGLOBAL['refer']);
	}

	$perpage = 50;
	$page = empty($_GET['page'])?1:intval($_GET['page']);
	if($page<1) $page = 1;
	$start = ($page-1)*$perpage;

	$list = array();
	$multi = $wheresql = '';
	if($space['friendnum']) {
		$groups = getfriendgroup();

		$theurl = 'cp.php?ac=friend&op=group';
		$group = !isset($_GET['group'])?'-1':intval($_GET['group']);
		if($group > -1) {
			$wheresql = "AND main.gid='$group'";
			$theurl .= "&group=$group";
		}

		$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('friend')." main
			WHERE main.uid='$space[uid]' AND main.status='1' $wheresql"), 0);
		$query = $_SGLOBAL['db']->query("SELECT main.fuid AS uid,main.fusername AS username, main.gid, main.num FROM ".tname('friend')." main
			WHERE main.uid='$space[uid]' AND main.status='1' $wheresql
			ORDER BY main.dateline DESC
			LIMIT $start,$perpage");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			realname_set($value['uid'], $value['username']);
			$value['group'] = $groups[$value['gid']];
			$list[] = $value;
		}
		$multi = multi($count, $perpage, $page, $theurl);
	}
	$groups = getfriendgroup();

	$actives = array('group'=>' class="active"');

	//实名
	realname_get();

} elseif($op == 'request') {

	if(submitcheck('requestsubmin')) {
		showmessage('do_success', $_SGLOBAL['refer']);
	}
	
	$maxfriendnum = checkperm('maxfriendnum');
	if($maxfriendnum) {
		$maxfriendnum = $maxfriendnum + $space['addfriend'];
	}

	//好友请求
	$perpage = 20;
	$page = empty($_GET['page'])?0:intval($_GET['page']);
	if($page<1) $page = 1;
	$start = ($page-1)*$perpage;
	
	$friend1 = $space['friends'];
	$list = array();
	
	$count = getcount('friend', array('fuid'=>$space['uid'], 'status'=>0));
	
	if($count) {
		$query = $_SGLOBAL['db']->query("SELECT s.*, sf.friend, f.* FROM ".tname('friend')." f
			LEFT JOIN ".tname('space')." s ON s.uid=f.uid
			LEFT JOIN ".tname('spacefield')." sf ON sf.uid=f.uid
			WHERE f.fuid='$space[uid]' AND f.status='0'
			ORDER BY f.dateline DESC
			LIMIT $start,$perpage");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			realname_set($value['uid'], $value['username']);
			//共有的好友
			$cfriend = array();
			$friend2 = empty($value['friend'])?array():explode(',',$value['friend']);
			if($friend1 && $friend2) {
				$cfriend = array_intersect($friend1, $friend2);
			}
			$value['cfriend'] = implode(',', $cfriend);
			$value['cfcount'] = count($cfriend);
			$list[] = $value;
		}
	}
	
	//统计更新
	if($count != $space['addfriendnum']) {
		updatetable('space', array('addfriendnum'=>$count), array('uid'=>$space['uid']));
	}
		
	//分页
	$multi = multi($count, $perpage, $page, "cp.php?ac=friend&op=request");
	
	realname_get();

} elseif($op == 'groupname') {

	$groups = getfriendgroup();
	$group = intval($_GET['group']);
	if(!isset($groups[$group])) {
		showmessage('change_friend_groupname_error');
	}

	if(submitcheck('groupnamesubmit')) {
		$space['privacy']['groupname'][$group] = getstr($_POST['groupname'], 20, 1, 1);
		privacy_update();
		showmessage('do_success', $_POST['refer']);
	}
} elseif($op == 'groupignore') {

	$groups = getfriendgroup();
	$group = intval($_GET['group']);
	if(!isset($groups[$group])) {
		showmessage('change_friend_groupname_error');
	}

	if(submitcheck('groupignoresubmit')) {
		if(isset($space['privacy']['filter_gid'][$group])) {
			unset($space['privacy']['filter_gid'][$group]);
		} else {
			$space['privacy']['filter_gid'][$group] = $group;
		}
		privacy_update();
		friend_cache($_SGLOBAL['supe_uid']);//缓存更新

		showmessage('do_success', $_POST['refer'], 0);
	}

} elseif($op == 'blacklist') {

	if($_GET['subop'] == 'delete') {
		$_GET['uid'] = intval($_GET['uid']);
		$_SGLOBAL['db']->query("DELETE FROM ".tname('blacklist')." WHERE uid='$space[uid]' AND buid='$_GET[uid]'");
		showmessage('do_success', "space.php?do=friend&view=blacklist&start=$_GET[start]", 0);
	}

	if(submitcheck('blacklistsubmit')) {
		$_POST['username'] = trim($_POST['username']);
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('space')." WHERE username='$_POST[username]'");
		if(!$tospace = $_SGLOBAL['db']->fetch_array($query)) {
			showmessage('space_does_not_exist');
		}
		if($tospace['uid'] == $space['uid']) {
			showmessage('unable_to_manage_self');
		}
		//删除好友
		if($space['friends'] && in_array($tospace['uid'], $space['friends'])) {
			friend_update($_SGLOBAL['supe_uid'], $_SGLOBAL['supe_username'], $tospace['uid'], '', 'ignore');
		}
		inserttable('blacklist', array('uid'=>$space['uid'], 'buid'=>$tospace['uid'], 'dateline'=>$_SGLOBAL['timestamp']), 0, true);

		showmessage('do_success', "space.php?do=friend&view=blacklist&start=$_GET[start]", 0);
	}
	
} elseif($op == 'rand') {
	
	$randuids = array();
	if($space['friendnum']<5) {
		//附近在线的朋友
		$onlinelist = array();
		$query = $_SGLOBAL['db']->query("SELECT uid FROM ".tname('session')." LIMIT 0,100");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			if($value['uid'] != $space['uid']) {
				$onlinelist[] = $value['uid'];
			}
		}
		$randuids = sarray_rand(array_merge($onlinelist, $space['friends']), 1);
	} else {
		$randuids = sarray_rand($space['friends'], 1);
	}
	showmessage('do_success', "space.php?uid=".array_pop($randuids), 0);
	
} elseif ($op == 'getcfriend') {
	
	$fuids = empty($_GET['fuid'])?array():explode(',', $_GET['fuid']);
	$newfuids = array();
	foreach ($fuids as $value) {
		$value = intval($value);
		if($value) $newfuids[$value] = $value;
	}
	
	//共同的好友
	$list = array();
	if($newfuids) {
		$query = $_SGLOBAL['db']->query("SELECT uid,username,name,namestatus FROM ".tname('space')." WHERE uid IN (".simplode($newfuids).") LIMIT 0,15");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			realname_set($value['uid'], $value['username'], $value['name'], $value['namestatus']);
			$list[] = $value;
		}
		realname_get();
	}
} elseif($op == 'search') {

	@include_once(S_ROOT.'./data/data_profilefield.php');
	$fields = empty($_SGLOBAL['profilefield'])?array():$_SGLOBAL['profilefield'];
		
	if(!empty($_GET['searchsubmit']) || !empty($_GET['searchmode'])) {
		$_GET['searchsubmit'] = $_GET['searchmode'] = 1;
		//找人
		$wherearr = $fromarr = $uidjoin = array();
		$fsql = '';
		
		$fromarr['space'] = tname('space').' s';
		
		if($searchkey = stripsearchkey($_GET['searchkey'])) {
			$wherearr[] = "(s.name='$searchkey' OR s.username='$searchkey')";
		} else {
			foreach (array('uid','username','name','videostatus','avatar') as $value) {
				if($_GET[$value]) {
					$wherearr[] = "s.$value='{$_GET[$value]}'";
				}
			}
		}
		//附表
		foreach (array('sex','qq','msn','birthyear','birthmonth','birthday','blood','marry','birthprovince','birthcity','resideprovince','residecity') as $value) {
			if($_GET[$value]) {
				$fromarr['spacefield'] = tname('spacefield').' sf';
				$wherearr['spacefield'] = "sf.uid=s.uid";
				$wherearr[] = "sf.$value='{$_GET[$value]}'";
				$fsql .= ", sf.$value";
			}
		}
		//转换成实际的年份
		$startage = $endage = 0;
		if($_GET['endage']) {
			$startage = sgmdate('Y') - intval($_GET['endage']);
		}
		if($_GET['startage']) {
			$endage = sgmdate('Y') - intval($_GET['startage']);
		}
		if($startage || $endage) {
			$fromarr['spacefield'] = tname('spacefield').' sf';
			$wherearr['spacefield'] = "sf.uid=s.uid";
		}
		if($startage && $endage && $endage > $startage) {
			$wherearr[] = '(sf.birthyear>='.$startage.' AND sf.birthyear<='.$endage.')';
		} else if($startage && empty($endage)) {
			$wherearr[] = 'sf.birthyear>='.$startage;
		} else if(empty($startage) && $endage) {
			$wherearr[] = 'sf.birthyear<='.$endage;
		}
		//自定义
		$havefield = 0;
		foreach ($fields as $fkey => $fvalue) {
			if($fvalue['allowsearch']) {
				$_GET['field_'.$fkey] = empty($_GET['field_'.$fkey])?'':stripsearchkey($_GET['field_'.$fkey]);
				if($_GET['field_'.$fkey]) {
					$havefield = 1;
					$wherearr[] = "sf.field_$fkey LIKE '%".$_GET['field_'.$fkey]."%'";
				}
			}
		}
		if($havefield) {
			$fromarr['spacefield'] = tname('spacefield').' sf';
			$wherearr['spacefield'] = "sf.uid=s.uid";
		}
		
		//扩展
		if($_GET['type'] == 'edu' || $_GET['type'] == 'work') {
			foreach (array('type','title','subtitle','startyear') as $value) {
				if($_GET[$value]) {
					$fromarr['spaceinfo'] = tname('spaceinfo').' si';
					$wherearr['spaceinfo'] = "si.uid=s.uid";
					$wherearr[] = "si.$value='{$_GET[$value]}'";
				}
			}
		}
		
		$list = array();
		if($wherearr) {
			$query = $_SGLOBAL['db']->query("SELECT s.* $fsql FROM ".implode(',', $fromarr)." WHERE ".implode(' AND ', $wherearr)." LIMIT 0,500");
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				realname_set($value['uid'], $value['username'], $value['name'], $value['namestatus']);
				$value['isfriend'] = ($value['uid']==$space['uid'] || ($space['friends'] && in_array($value['uid'], $space['friends'])))?1:0;
				$list[$value['uid']] = $value;
			}
		}
		
		realname_get();
		
	} else {
		
		$yearhtml = '';
		$nowy = sgmdate('Y');
		for ($i=0; $i<50; $i++) {
			$they = $nowy - $i;
			$yearhtml .= "<option value=\"$they\">$they</option>";
		}
		
		//性别
		$sexarr = array($space['sex']=>' checked');
		
		//生日:年
		$birthyeayhtml = '';
		$nowy = sgmdate('Y');
		for ($i=0; $i<100; $i++) {
			$they = $nowy - $i;
			if(empty($_GET['all'])) $selectstr = $they == $space['birthyear']?' selected':'';
			$birthyeayhtml .= "<option value=\"$they\"$selectstr>$they</option>";
		}
		//生日:月
		$birthmonthhtml = '';
		for ($i=1; $i<13; $i++) {
			if(empty($_GET['all'])) $selectstr = $i == $space['birthmonth']?' selected':'';
			$birthmonthhtml .= "<option value=\"$i\"$selectstr>$i</option>";
		}
		//生日:日
		$birthdayhtml = '';
		for ($i=1; $i<29; $i++) {
			if(empty($_GET['all'])) $selectstr = $i == $space['birthday']?' selected':'';
			$birthdayhtml .= "<option value=\"$i\"$selectstr>$i</option>";
		}
		//血型
		$bloodhtml = '';
		foreach (array('A','B','O','AB') as $value) {
			if(empty($_GET['all'])) $selectstr = $value == $space['blood']?' selected':'';
			$bloodhtml .= "<option value=\"$value\"$selectstr>$value</option>";
		}
		//婚姻
		$marryarr = array($space['marry'] => ' selected');
		
		//自定义
		foreach ($fields as $fkey => $fvalue) {
			if($fvalue['allowsearch']) {
				if($fvalue['formtype'] == 'text') {
					$fvalue['html'] = '<input type="text" name="field_'.$fkey.'" value="'.$gets["field_$fkey"].'" class="t_input">';
				} else {
					$fvalue['html'] = "<select name=\"field_$fkey\"><option value=\"\">---</option>";
					$optionarr = explode("\n", $fvalue['choice']);
					foreach ($optionarr as $ov) {
						$ov = trim($ov);
						if($ov) {
							$selectstr = $gets["field_$fkey"]==$ov?' selected':'';
							$fvalue['html'] .= "<option value=\"$ov\"$selectstr>$ov</option>";
						}
					}
					$fvalue['html'] .= "</select>";
				}
				$fields[$fkey] = $fvalue;
			} else {
				unset($fields[$fkey]);
			}
		}

	}
	
}

include template('cp_friend');

?>