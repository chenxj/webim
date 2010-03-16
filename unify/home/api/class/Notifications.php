<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: Notifications.php 9878 2008-11-19 07:07:58Z zhouguoqiang $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

class Notifications extends MyBase {

	function get($uId) {
		global $_SGLOBAL;
		$notify = $result = array();
		$result = array(
			'message' => array(
				'unread' => 0,
				'mostRecent' => 0
			),
			'notification'   => array(
				'unread' => 0 ,
				'mostRecent' => 0
			),
			'friendRequest' => array(
				'uIds' => array()
			)
		);

		//通知
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('notification')."  WHERE uid='$uId' AND new='1' ORDER BY id DESC");
		$i = 0;
		while($value = $_SGLOBAL['db']->fetch_array($query)) {
			$i++;
			if(!$result['notification']['mostRecent']) $result['notification']['mostRecent'] = $value['dateline'];
		}
		$result['notification']['unread'] = $i;
		
		//短消息
		include_once S_ROOT.'./uc_client/client.php';
		$pmarr = uc_pm_list($uId, 1, 1, 'newbox', 'newpm');
		if($pmarr['count']) {
			$result['message']['unread'] = $pmarr['count'];
			$result['message']['mostRecent'] = $pmarr['data'][0]['dateline'];
		}

		// 好友
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('friend')."  WHERE fuid='$uId' AND status='0' ORDER BY dateline DESC");
		$fIds = array();
		while($value = $_SGLOBAL['db']->fetch_array($query)) {
			if(!$result['friendRequest']['mostRecent']) {
				$result['friendRequest']['mostRecent'] = $value['dateline'];
			}
			$fIds[] = $value['uid'];
		}
		$result['friendRequest']['uIds'] = $fIds;

		return new APIResponse($result);
	}

	function send($uId, $recipientIds, $appId, $notification) {
		global $_SGLOBAL;

		//过滤黑名单中的用户
		$blacklist = $result = array();

		// 允许匿名发送
		if ($uId) {
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('blacklist')."  WHERE uid IN ('".implode("','", $recipientIds)."') AND buid='$uId'");
			while($value = $_SGLOBAL['db']->fetch_array($query)) {
				$blacklist[$value['uid']] = $value['uid'];
			}
		}

		include_once(S_ROOT.'./source/function_cp.php');
		foreach($recipientIds as $recipientId) {
			$val = intval($recipientId);
			if($val && empty($blacklist[$val])) {
				$result[$val] = notification_add($val, $appId, $notification, 1);
			} else {
				$result[$recipientId] = null;
			}
		}
		return new APIResponse($result);
	}

}


?>
