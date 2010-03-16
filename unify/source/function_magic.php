<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: function_cp.php 12398 2009-06-24 08:26:38Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//获得道具
function magic_get($mid) {
	global $_SGLOBAL, $space;

	//获得道具
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('magic')." WHERE mid = '$mid'");
	if(!$magic = $_SGLOBAL['db']->fetch_array($query)) {
		showmessage('unknown_magic');
	} else {
		$magic['forbiddengid'] = empty($magic['forbiddengid']) ? array() : explode(',', $magic['forbiddengid']);
		$magic['custom'] = $magic['custom'] ? unserialize($magic['custom']) : array();
	}

	if($magic['close']) {
		showmessage('magic_is_closed');//道具已禁用
	}

	return $magic;
}

//购买前道具检查
function magic_buy_get($magic) {
	global $_SGLOBAL, $space;

	//购买
	if(!$magic) {
		showmessage('unknown_magic');//请选择道具
	} else {
		$mid = $magic['mid'];
	}
	
	$blacklist = array('coupon');//道具商店屏蔽购买的道具
	if(in_array($mid, $blacklist)) {
		showmessage('magic_not_for_sale');//此道具不能通过购买获得
	}

	if(!checkperm('allowmagic')) {
		ckspacelog();
		showmessage('magic_groupid_not_allowed');//您所在的用户组被禁止使用道具
	}

	//用户组限制
	if($magic['forbiddengid'] && in_array($space['groupid'], $magic['forbiddengid'])) {
		showmessage('magic_groupid_limit');
	}

	$setarr = array(
		'mid' => $mid,
		'storage' => $magic['providecount'],
		'lastprovide' => $_SGLOBAL['timestamp']
	);
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('magicstore')." WHERE mid = '$mid'");
	$magicstore = $_SGLOBAL['db']->fetch_array($query);
	if(!$magicstore) {
		inserttable('magicstore', $setarr);
		$magicstore['storage'] = $magic['providecount'];
	} elseif($magicstore['storage'] < $magic['providecount'] &&
		$magicstore['lastprovide'] + $magic['provideperoid'] < $_SGLOBAL['timestamp']) {

		unset($setarr['mid']);
		updatetable('magicstore', $setarr, array('mid'=>$mid));
		$magicstore['storage'] = $magic['providecount'];
	}

	if($magicstore['storage'] < 1) {
		$nexttime = sgmdate('m-d H:i', $magicstore['lastprovide'] + $magic['provideperoid']);
		showmessage('not_enough_storage', '', '', array($nexttime));//库存不足
	}
	
	//折扣
	$discount = checkperm('magicdiscount');
	$charge = $magic['charge'];
	if($discount > 0) {
		$charge = intval($magic['charge'] * $discount / 10);
		if($charge < 1) {
			$charge = 1;
		}
	} elseif($discount < 0) {
		$charge = 0;
	}

	//最多可以购买数
	$magicstore['maxbuy'] = $charge ? min( $magicstore['storage'], floor($space['credit'] / $charge)) : $magicstore['storage'];

	//代金券
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname("usermagic")." WHERE uid='$_SGLOBAL[supe_uid]' AND mid = 'coupon'");
	$coupon = $_SGLOBAL['db']->fetch_array($query);

	return array(
		'magicstore' => $magicstore,
		'coupon' => $coupon,
		'discount' => $discount,
		'charge' => $charge
	);
}

function magic_buy_post($magic, $magicstore, $coupon) {
	global $_SGLOBAL, $space;

	if(!$magic) {
		showmessage('unknown_magic');//请选择道具
	} else {
		$mid = $magic['mid'];
	}

	$_POST['buynum'] = intval($_POST['buynum']);
	if($_POST['buynum'] < 1) {
		showmessage('bad_buynum');
	}

	//库存不足
	if($magicstore['storage'] < $_POST['buynum']) {
		$nexttime = sgmdate('m-d H:i', $magicstore['lastprovide'] + $magic['provideperoid']);
		showmessage('not_enough_storage', '', '', array($nexttime));//库存不足
	}

	$_POST['coupon'] = intval($_POST['coupon']);

	$discard = 0;
	if($_POST['coupon']) {//代金券
		if($coupon['count'] < $_POST['coupon']) {
			showmessage('not_enough_coupon');//代金券数目不够
		}
		$discard = 100 * $_POST['coupon'];
	}
	
	$discount = checkperm('magicdiscount');
	if($discount > 0) {
		$magic['charge'] = intval($magic['charge'] * $discount / 10);
		if($magic['charge'] < 1) {
			$magic['charge'] = 1;
		}
	} elseif($discount < 0) {
		$magic['charge'] = 0;
	}
	$charge = $_POST['buynum'] * $magic['charge'] - $discard;
	$charge = $charge > 0 ? $charge : 0;//避免代金券折现
	if($charge > $space['credit']) {
		showmessage('credit_is_not_enough');//积分不够
	}

	//商店库存
	$_SGLOBAL['db']->query("UPDATE ".tname("magicstore")." SET storage = storage - $_POST[buynum], sellcount = sellcount + $_POST[buynum], sellcredit = sellcredit + $charge WHERE mid = '$mid'");

	//积分和经验
	$experience = $_POST['buynum'] * intval($magic['experience']);
	$_SGLOBAL['db']->query("UPDATE ".tname("space")." SET credit = credit - $charge, experience = experience + '$experience' WHERE uid = '$_SGLOBAL[supe_uid]'");

	//个人道具
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname("usermagic")." WHERE uid='$_SGLOBAL[supe_uid]' AND mid='$mid'");
	if($value = $_SGLOBAL['db']->fetch_array($query)) {
		$count = $value['count'] + $_POST['buynum'];
	} else {
		$count = $_POST['buynum'];
	}
	$_SGLOBAL['db']->query("REPLACE ".tname('usermagic')."(uid, username, mid, count) VALUES ('$_SGLOBAL[supe_uid]', '$_SGLOBAL[username]', '$mid', '$count')");

	//购买日志
	inserttable('magicinlog',
		array(
			'uid'=>$_SGLOBAL['supe_uid'],
			'username'=>$_SGLOBAL['supe_username'],
			'mid'=>$mid,
			'count'=>$_POST['buynum'],
			'type'=>1,
			'credit'=>$charge,
			'dateline'=>$_SGLOBAL['timestamp']));

	//代金券
	if($_POST['coupon']) {
		$_SGLOBAL['db']->query("UPDATE ".tname("usermagic")." SET count = count - $_POST[coupon] WHERE uid='$_SGLOBAL[supe_uid]' AND mid = 'coupon'");
	}
	
	return $charge;
}

//检查输入参数
function magic_check_idtype($id, $idtype) {
	global $_SGLOBAL;

	//检查场合
	$value = '';
	$tablename = gettablebyidtype($idtype);
	if($tablename) {
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname($tablename)." WHERE $idtype='$id' AND uid = '$_SGLOBAL[supe_uid]'");
		$value = $_SGLOBAL['db']->fetch_array($query);
	}
	if(empty($value)) {
		showmessage('magicuse_bad_object');
	}
	return $value;
}

//使用道具
function magic_use($mid, $magicuselog=array(), $replace=0) {
	global $_SGLOBAL;

	//道具减少
	$_SGLOBAL['db']->query('UPDATE '.tname('usermagic')." SET count = count - 1 WHERE uid = '$_SGLOBAL[supe_uid]' AND mid = '$mid' AND count > 0");

	//使用记录
	$value = array();
	if($replace) {
		$where = '';
		if($magicuselog['id']) {
			$where = " AND id='$magicuselog[id]' AND idtype='$magicuselog[idtype]'";
		}
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('magicuselog')." WHERE uid = '$_SGLOBAL[supe_uid]' AND mid = '$mid' $where");
		$value = $_SGLOBAL['db']->fetch_array($query);
	}
	$magicuselog['mid'] = $mid;
	$magicuselog['uid'] = $_SGLOBAL['supe_uid'];
	$magicuselog['username'] = $_SGLOBAL['supe_username'];
	$magicuselog['dateline'] = $_SGLOBAL['timestamp'];
	$magicuselog['count'] = $value['count'] ? $value['count'] + 1 : 1;
	
	if($value['logid']) {
		updatetable('magicuselog', $magicuselog, array('logid'=>$value['logid']));
	} else {
		inserttable('magicuselog', $magicuselog);
	}
}

?>