<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_task.php 11506 2009-03-06 09:19:17Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$op = empty($_GET['op']) ? "view" : $_GET['op'];
$mid = empty($_GET['mid']) ? '' : trim($_GET['mid']);

if(!checkperm('allowmagic')) {
	ckspacelog();
	showmessage('magic_groupid_not_allowed');//您所在的用户组被禁止使用道具
}

//获得道具
$magic = $mid ? magic_get($mid) : array();

//提交购买
if (submitcheck("buysubmit")) {//购买
	
	if(!$mid) {
		showmessage('unknown_magic');
	}

	//获得道具信息
	$results = magic_buy_get($magic);
	extract($results);

	//购买道具
	$charge = magic_buy_post($magic, $magicstore, $coupon);

	if($magic['experience']) {
		showmessage('magicbuy_success_with_experence', $_POST['refer'], 0, array($charge, $magic['experience'] * intval($_POST['buynum'])));
	} else {
		showmessage('magicbuy_success', $_POST['refer'], 0, array($charge));
	}

} elseif (submitcheck("presentsubmit")) {//赠送
	
	if(!$mid) {
		showmessage('unknown_magic');
	}

	if($mid == 'license') {
		showmessage("magic_can_not_be_presented");//此道具不能转让
	}

	//好友
	$fuid = 0;
	$_POST['fusername'] = trim($_POST['fusername']);
	if(empty($_POST['fusername'])) {
		showmessage("bad_friend_username_given");//好友名字无效
	}
	$_POST['fusername'] = getstr($_POST['fusername'], 15);
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname("friend")." WHERE uid = '$_SGLOBAL[supe_uid]' AND fusername='$_POST[fusername]'");
	$value = $_SGLOBAL['db']->fetch_array($query);
	if(!$value) {
		showmessage("bad_friend_username_given");//好友名字无效
	}
	$fuid = $value['fuid'];
	$fusername = $value['fusername'];

	//赠予者的必须拥有该道具及转增卡
	$usermagics = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname("usermagic")." WHERE uid='$_SGLOBAL[supe_uid]' AND mid IN('license', '$mid')");
	while($value = $_SGLOBAL['db']->fetch_array($query)) {
		$usermagics[$value['mid']] = $value;
	}
	if(!$usermagics['license'] || !$usermagics['license']['count']) {
		showmessage('has_no_more_present_magic');
	}
	if(!$usermagics[$mid] || !$usermagics[$mid]['count']) {
		showmessage('has_no_more_magic', '', '',  array($magic['name'], 'a_buy_'.$mid, "cp.php?ac=magic&op=buy&mid=$mid"));
	}

	//赠予者
	$_SGLOBAL['db']->query('UPDATE '.tname('usermagic')." SET count = count - 1 WHERE uid = '$_SGLOBAL[supe_uid]' AND mid IN ('license', '$mid')");

	//受赠者
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('usermagic')." WHERE uid='$fuid' AND mid='$mid'");
	$value = $_SGLOBAL['db']->fetch_array($query);
	$count = $value ? $value['count'] + 1 : 1;
	inserttable('usermagic',
		array('uid'=>$fuid,
			'username'=>$fusername,
			'mid'=>$mid,
			'count'=>$count), 0, true);

	//收入（赠送）日志
	inserttable('magicinlog',
		array('uid'=>$fuid,
			'username'=>$fusername,
			'mid'=>$mid,
			'count'=>1,
			'type'=>2,
			'fromid'=>$_SGLOBAL['supe_uid'],
			'credit'=>0,
			'dateline'=>$_SGLOBAL['timestamp']));

	//通知被赠送者
	notification_add($fuid, 'magic', cplang('magic_present_note', array($magic['name'], "cp.php?ac=magic&view=me&mid=$mid")));
	showmessage("magicpresent_success", $_POST['refer'], '', array($fusername));
}

if($op == 'buy') {//购买

	$results = magic_buy_get($magic);
	extract($results);

} elseif ($op == "present") {//赠送

	if($mid == 'license') {
		showmessage("magic_can_not_be_presented");//此道具不能转让
	}

	//赠予者的必须拥有该道具及转增卡
	$usermagics = array();
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('usermagic')." WHERE uid='$_SGLOBAL[supe_uid]' AND mid IN('license', '$mid')");
	while($value = $_SGLOBAL['db']->fetch_array($query)) {
		$usermagics[$value['mid']] = $value;
	}
	if(!$usermagics['license'] || !$usermagics['license']['count']) {
		showmessage('has_no_more_present_magic');
	}
	if(!$usermagics[$mid] || !$usermagics[$mid]['count']) {
		showmessage('has_no_more_magic', '', '',  array($magic['name'], 'a_buy_'.$mid, "cp.php?ac=magic&op=buy&mid=$mid"));
	}
} elseif($op == 'showusage') {
	//显示使用帮助图片

	if(!$mid) {
		showmessage('unknown_magic');
	}
	
} elseif($op == 'receive') {//领取红包

	$uid = intval($_GET['uid']);
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('magicuselog')." WHERE uid='$uid' AND mid='gift' LIMIT 1");
	$value = $_SGLOBAL['db']->fetch_array($query);
	if($value && $value['data']) {
		$data = unserialize($value['data']);
		if($data['left'] <= 0) {
			showmessage("magic_gift_already_given_out");//红包已经被领完了
		}
		$data['receiver'] = is_array($data['receiver']) ? $data['receiver'] : array();
		if(in_array($_SGLOBAL['supe_uid'], $data['receiver'])) {
			showmessage("magic_had_got_gift");//您已经领取过此次红包了
		}
		$credit = $data['left'] > $data['chunk'] ? $data['chunk'] : $data['left'];
		$data['receiver'][] = $_SGLOBAL['supe_uid'];
		$data['left'] = $data['left'] - $credit;
		if($data['left'] > 0) {
			updatetable('magicuselog', array('data'=>serialize($data)), array('logid'=>$value['logid']));
		} else {
			$_SGLOBAL['db']->query('DELETE FROM '.tname('magicuselog')." WHERE logid = '$value[logid]'");
		}
		$_SGLOBAL['db']->query('UPDATE '.tname('space')." SET credit = credit + '$credit' WHERE uid='$_SGLOBAL[supe_uid]'");
		showmessage('magic_got_gift', '', '', array($credit));//您已经领取到红包了：获得 x 积分
	} else {
		showmessage('magic_has_no_gift');//空间主人没有设置红包
	}

} elseif($op == 'appear') {
	//取消隐身草效果

	if(!$_SGLOBAL['session']['magichidden']) {
		showmessage('magic_not_hidden_yet');
	}

	if(submitcheck('appearsubmit')) {
		updatetable('session', array('magichidden'=>'0'), array('uid'=>$_SGLOBAL['supe_uid']));
		updatetable('magicuselog', array('expire'=>$_SGLOBAL['timestamp']), array('uid'=>$_SGLOBAL['supe_uid'], 'mid'=>'invisible'));
		showmessage('do_success', $_POST['refer'], 0);
	}

} elseif($op == 'retrieve') {
	//回收红包卡

	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('magicuselog')." WHERE uid = '$_SGLOBAL[supe_uid]' AND mid = 'gift'");
	$value = $_SGLOBAL['db']->fetch_array($query);
	
	$leftcredit = 0;
	if(!$value) {
		showmessage('not_set_gift');//您当前没有设置红包
	} elseif($value['data']) {
		$data = unserialize($value['data']);
		$leftcredit = intval($data['left']);
	}

	if(submitcheck('retrievesubmit')) {
		$_SGLOBAL['db']->query('DELETE FROM '.tname('magicuselog')." WHERE uid = '$_SGLOBAL[supe_uid]' AND mid = 'gift'");
		$_SGLOBAL['db']->query('UPDATE '.tname('space')." SET credit = credit + $leftcredit WHERE uid = '$_SGLOBAL[supe_uid]'");
		showmessage('do_success', $_POST['refer'], 0);
	}
	
} elseif($op == 'cancelsuperstar') {//取消超级明星
	
	$mid = 'superstar';
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('spacefield')." WHERE uid = '$_SGLOBAL[supe_uid]'");
	$value = $_SGLOBAL['db']->fetch_array($query);
	if(!$value || !$value['magicstar']) {
		showmessage('not_superstar_yet');
	}
	
	if(submitcheck('cancelsubmit')) {
		updatetable('spacefield', array('magicstar'=>0), array('uid'=>$_SGLOBAL['supe_uid']));
		updatetable('magicuselog', array('expire'=>$_SGLOBAL['timestamp']), array('uid'=>$_SGLOBAL['supe_uid'], 'mid'=>'superstar'));
		showmessage('do_success', $_POST['refer'], 0);
	}
	
} elseif($op == 'cancelflicker') {//取消彩虹炫
	
	$mid = 'flicker';
	$_GET['idtype'] = 'cid';
	$_GET['id'] = intval($_GET['id']);
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('comment')." WHERE cid = '$_GET[id]' AND authorid = '$_SGLOBAL[supe_uid]'");
	$value = $_SGLOBAL['db']->fetch_array($query);
	if(!$value || !$value['magicflicker']) {
		showmessage('no_flicker_yet');
	}
	
	if(submitcheck('cancelsubmit')) {
		updatetable('comment', array('magicflicker'=>0), array('cid'=>$_GET['id'], 'authorid'=>$_SGLOBAL['supe_uid']));
		showmessage('do_success', $_POST['refer'], 0);
	}
	
} elseif($op == 'cancelcolor') {//取消彩色灯
	
	$mid = 'color';
	$_GET['id'] = intval($_GET['id']);
	//idtype到含有magiccolor字段的表映射
	$mapping = array('blogid'=>'blogfield', 'tid'=>'thread');
	$tablename = $mapping[$_GET['idtype']];
	if(empty($tablename)) {
		showmessage('no_color_yet');
	}
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname($tablename)." WHERE $_GET[idtype] = '$_GET[id]' AND uid = '$_SGLOBAL[supe_uid]'");
	$value = $_SGLOBAL['db']->fetch_array($query);
	if(!$value || !$value['magiccolor']) {
		showmessage('no_color_yet');
	}
	
	if(submitcheck('cancelsubmit')) {
		updatetable($tablename, array('magiccolor'=>0), array($_GET['idtype']=>$_GET[id]));
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('feed')." WHERE id = '$_GET[id]' AND idtype = '$_GET[idtype]'");
		$feed = $_SGLOBAL['db']->fetch_array($query);
		if($feed) {
			$feed['body_data'] = unserialize($feed['body_data']);
			if($feed['body_data']['magic_color']) {
				unset($feed['body_data']['magic_color']);
			}
			$feed['body_data'] = serialize($feed['body_data']);
			updatetable('feed', array('body_data'=>$feed['body_data']), array('feedid'=>$feed['feedid']));
		}
		showmessage('do_success', $_POST['refer'], 0);
	}
	
} elseif($op == 'cancelframe') {//取消相框
	
	$mid = 'frame';
	$_GET['idtype'] = 'picid';
	$_GET['id'] = intval($_GET['id']);
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('pic')." WHERE picid = '$_GET[id]' AND uid = '$_SGLOBAL[supe_uid]'");
	$value = $_SGLOBAL['db']->fetch_array($query);
	if(!$value || !$value['magicframe']) {
		showmessage('no_frame_yet');
	}
	
	if(submitcheck('cancelsubmit')) {
		updatetable('pic', array('magicframe'=>0), array('picid'=>$_GET['id']));
		showmessage('do_success', $_POST['refer'], 0);
	}

} elseif($op == 'cancelbgimage') {//取消信纸

	$mid = 'bgimage';
	$_GET['idtype'] = 'blogid';
	$_GET['id'] = intval($_GET['id']);
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('blogfield')." WHERE blogid = '$_GET[id]' AND uid = '$_SGLOBAL[supe_uid]'");
	$value = $_SGLOBAL['db']->fetch_array($query);
	if(!$value || !$value['magicpaper']) {
		showmessage('no_bgimage_yet');
	}
	
	if(submitcheck('cancelsubmit')) {
		updatetable('blogfield', array('magicpaper'=>0), array('blogid'=>$_GET['id']));
		showmessage('do_success', $_POST['refer'], 0);
	}
	
} else {//浏览

	if($_GET['view'] == 'me') {//我的道具

		//拥有的道具
		$types['list'] = ' class="active"';
		$list = $ids = $magics = array();
		if($mid) {
			$magics[$mid] = $magic;
			$ids[] = $mid;
		} else {
			//显示全部
			$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('magic')." WHERE close = '0'");
			while($value = $_SGLOBAL['db']->fetch_array($query)) {
				$value['forbiddengid'] = explode(',', $value['forbiddengid']);
				$magics[$value['mid']] = $value;
				$ids[] = $value['mid'];
			}
		}
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('usermagic')." WHERE uid='$_SGLOBAL[supe_uid]' AND mid IN (".simplode($ids).") AND count > 0");
		while($value=$_SGLOBAL['db']->fetch_array($query)) {
			$list[$value['mid']] = $value;
		}

	} elseif($_GET['view'] == 'log') {//记录

		$_GET['type'] = in_array($_GET['type'], array('in', 'out', 'present')) ? $_GET['type'] : 'in';
		$types = array($_GET['type']=>' class="active"');

		//分页
		$perpage = 20;
		$page = empty($_GET['page'])?0:intval($_GET['page']);
		if($page<1) $page = 1;
		$start = ($page-1)*$perpage;
		//检查开始数
		ckstart($start, $perpage);

		$list = array();
		if($_GET['type'] == 'in') {
			//获得记录
			$uids = array();//显示赠送者
			$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('magicinlog')." WHERE uid = '$_SGLOBAL[supe_uid]'"), 0);
			if($count) {
				$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('magicinlog')." WHERE uid = '$_SGLOBAL[supe_uid]' ORDER BY dateline DESC LIMIT $start, $perpage");
				while ($value = $_SGLOBAL['db']->fetch_array($query)) {
					if($value['type'] == 2) {
						$uids[] = $value['fromid'];
					}
					$list[] = $value;
				}
			}
			if($uids) {
				$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('member').' WHERE uid IN ('.simplode($uids).')');
				while($value = $_SGLOBAL['db']->fetch_array($query)) {
					realname_set($value['uid'], $value['username']);
				}
				realname_get();
			}
		} elseif($_GET['type'] == 'present') {
			//赠送记录
			$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('magicinlog')." WHERE type = 2 AND fromid = '$_SGLOBAL[supe_uid]'"), 0);
			if($count) {
				$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('magicinlog')." WHERE type = 2 AND fromid = '$_SGLOBAL[supe_uid]' ORDER BY dateline DESC LIMIT $start, $perpage");
				while ($value = $_SGLOBAL['db']->fetch_array($query)) {
					realname_set($value['uid'], $value['username']);
					$list[] = $value;
				}
			}
			realname_get();
		} else {
			//使用记录
			$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('magicuselog')." WHERE uid = '$_SGLOBAL[supe_uid]'"), 0);
			if($count) {
				$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('magicuselog')." WHERE uid = '$_SGLOBAL[supe_uid]' ORDER BY dateline DESC LIMIT $start, $perpage");
				while ($value = $_SGLOBAL['db']->fetch_array($query)) {
					$value['data'] = unserialize($value['data']);
					$list[] = $value;
				}
			}
		}

		//分页
		$theurl = 'cp.php?ac=magic&view=log&type='.$_GET['type'];
		$multi = multi($count, $perpage, $page, $theurl);

	} else {//道具市场
		$_GET['view'] = 'store';

		//显示顺序
		$_GET['order'] = $_GET['order'] == 'hot' ? 'hot' : 'default';
		$orders = array($_GET['order']=>' class="active"');

		$magics = $ids = $list = array();
		$blacklist = array('coupon');//道具商店屏蔽显示的道具
		if($mid) {
			//只显示单个
			$magics[$mid] = $magic;
			$ids[] = $mid;
		} else {
			//显示全部
			$orderby = $_GET['order'] == 'hot' ? '' : 'ORDER BY displayorder';
			$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('magic')." $orderby");
			while($value = $_SGLOBAL['db']->fetch_array($query)) {
				if($value['close'] || in_array($value['mid'], $blacklist)) {
					continue;
				}
				$value['forbiddengid'] = explode(',', $value['forbiddengid']);
				$magics[$value['mid']] = $value;
				$ids[] = $value['mid'];
			}
		}

		if(empty($magics)) {
			showmessage('magic_store_is_closed');//道具商店已经关闭（没有任何道具开放）
		}

		$oldids = array();//已经录入商店的道具
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('magicstore').' WHERE mid IN ('.simplode($ids).')');
		while($value = $_SGLOBAL['db']->fetch_array($query)) {
			$list[$value['mid']] = $value;
			$oldids[] = $value['mid'];
			//更新库存数
			if($value['storage'] < $magics[$value['mid']]['providecount'] &&
				$value['lastprovide'] + $magics[$value['mid']]['provideperoid'] < $_SGLOBAL['timestamp']) {

				$_SGLOBAL['db']->query('UPDATE '.tname('magicstore')." SET storage = '{$magics[$value[mid]][providecount]}', lastprovide = '$_SGLOBAL[timestamp]' WHERE mid = '$value[mid]'");
				$list[$value['mid']]['storage'] = $magics[$value['mid']]['providecount'];
			}
		}

		$newids = array_diff($ids, $oldids);//还未录入商店的道具
		if($newids) {
			$inserts = array();
			foreach ($newids as $id) {
				$inserts[] = "('$id', '{$magics[$id][providecount]}', '$_SGLOBAL[timestamp]')";
				$list[$id] = array("mid"=>$id,
								'storage'=>$magics[$id]['providecount'],
								'lastprovide'=>$_SGLOBAL['timestamp']);
			}
			$_SGLOBAL['db']->query('INSERT INTO '.tname('magicstore').'(mid, storage, lastprovide) VALUES '.implode(',',$inserts));
		}

		//排序
		if($_GET['order'] == 'hot') {
			//按售出数排序
			function hotsort($a, $b) {
				return ($a['sellcount'] > $b['sellcount']) ? -1 : ($a['sellcount'] < $b['sellcount']);
			}
			usort($list, 'hotsort');
			$order = array();
			foreach ($list as $value) {
				$order[$value['mid']] = $value;
			}
			$list = $order;
			unset($order);
		} else {
			//默认
			$order = array();
			foreach ($ids as $id) {
				$order[$id] = $list[$id];
			}
			$list = $order;
			unset($order);
		}
	}

	$actives = array($_GET['view']=>' class="active"');
}

include_once template('cp_magic');

?>