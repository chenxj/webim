<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_credit.php 12930 2009-07-28 09:05:09Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$perpage = 20;
$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page = 1;
$start = ($page-1)*$perpage;
//检查开始数
ckstart($start, $perpage);
	
if(empty($_GET['op'])) {

	//空间大小
	$maxattachsize = checkperm('maxattachsize');
	if(empty($maxattachsize)) {
		$percent = 0;
		$maxattachsize = '-';
	} else {
		$maxattachsize = $maxattachsize + $space['addsize'];//额外空间
		$percent = intval($space['attachsize']/$maxattachsize*100);
		$maxattachsize = formatsize($maxattachsize);
	}
	$space['attachsize'] = formatsize($space['attachsize']);
	
	//用户组
	$space['grouptitle'] = checkperm('grouptitle');

	$theurl = 'cp.php?ac=credit&perpage='.$perpage;
	//积分获得记录
	$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT count(*) FROM ".tname('creditlog')." WHERE uid='$space[uid]'"), 0);
	if($count) {
		$query = $_SGLOBAL['db']->query("SELECT r.rulename, c.* FROM ".tname('creditlog')." c LEFT JOIN ".tname('creditrule')." r ON r.rid=c.rid WHERE c.uid='$space[uid]' ORDER BY dateline DESC LIMIT $start,$perpage");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$list[] = $value;
		}
		$multi = multi($count, $perpage, $page, $theurl);
	}

} elseif ($_GET['op'] == 'exchange') {
	
	@include_once(S_ROOT.'./uc_client/data/cache/creditsettings.php');
	if(submitcheck('exchangesubmit')) {
		$netamount = $tocredits = 0;
		$tocredits = $_POST['tocredits'];
		$outexange = strexists($tocredits, '|');
		if(!$outexange && !$_CACHE['creditsettings'][$tocredits]['ratio']) {
			showmessage('credits_exchange_invalid');
		}
		$amount = intval($_POST['amount']);
		if($amount <= 0) {
			showmessage('credits_transaction_amount_invalid');
		}
		@include_once(S_ROOT.'./uc_client/client.php');
		$ucresult = uc_user_login($_SGLOBAL['supe_username'], $_POST['password']);
		list($tmp['uid']) = saddslashes($ucresult);
		
		if($tmp['uid'] <= 0) {
			showmessage('credits_password_invalid');
		} elseif($space['credit']-$amount < 0) {
			showmessage('credits_balance_insufficient');
		}
		$netamount = floor($amount * 1/$_CACHE['creditsettings'][$tocredits]['ratio']);
		list($toappid, $tocredits) = explode('|', $tocredits);
		
		$ucresult = uc_credit_exchange_request($_SGLOBAL['supe_uid'], $_CACHE['creditsettings'][$tocredits]['creditsrc'], $tocredits, $toappid, $netamount);
		if(!$ucresult) {
			showmessage('extcredits_dataerror');
		}
		$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET credit=credit-$amount WHERE uid='$_SGLOBAL[supe_uid]'");
		
		showmessage('do_success', 'cp.php?ac=credit&op=exchange');
	} elseif(empty($_CACHE['creditsettings'])) {
		showmessage('integral_convertible_unopened');
	}
	
} elseif ($_GET['op'] == 'rule') {
	
	$wherearr = array();
	$wheresql = '';
	
	$theurl = 'cp.php?ac=credit&op=rule&perpage='.$perpage;
	$perpages = array($perpage => ' selected');
	if($_GET['rid']) {
		$rid = intval($_GET['rid']);
		$wherearr[] = "rid='$rid'";
	}
	
	if(isset($_GET['rewardtype'])) {
		$rewardtype = intval($_GET['rewardtype']);
		$wherearr[] = "rewardtype='$rewardtype'";
		$theurl .= '&rewardtype='.$rewardtype;
	}
	
	if($wherearr) {
		$wheresql = ' WHERE '.implode(' AND ', $wherearr);
	}
	
	$list = $list2 = array();

	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('creditrule')." $wheresql ORDER BY rid DESC");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if($value['rewardtype']) {
			$list[] = $value;
		} else {
			$list2[] = $value;
		}
	}
} elseif ($_GET['op'] == 'usergroup') {
	
	$space['grouptitle'] = checkperm('grouptitle');
	
	$groups = $s_groups = array();
	$highest = true;
	$lower = '';
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('usergroup')." ORDER BY explower DESC");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		//是否是最高上限
		if(empty($value['system'])) {
			if($highest) {
				$value['exphigher'] = 999999999;
				$highest = false;
				$lower = $value['explower'];
			} else {
				$value['exphigher'] = $lower - 1;
				$lower = $value['explower'];
			}
			$groups[] = $value;
		} else {
			$s_groups[] = $value;
		}
	}
}

$cat_actives = empty($_GET['op'])?array('base' => ' class="active"'):array($_GET['op'] => ' class="active"');

include_once template("cp_credit");

?>