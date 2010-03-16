<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: Users.php 12766 2009-07-20 04:26:21Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

class Users extends MyBase {

	function getInfo($uIds, $fields = array(), $isExtra = false) {
		$users = $this->getUsers($uIds, false, true, $isExtra, false);
		$result = array();
		if ($users) {
			if ($fields) {
				foreach($users as $key => $user) {
					foreach($user as $k => $v) {
						if (in_array($k, $fields)) {
							$result[$key][$k] = $v;
						}
					}
				}
			}
		}

		if (!$result) {
			$result = $users;
		}
		
		return new APIResponse($result);
	}

	function getFriendInfo($uId, $num = MY_FRIEND_NUM_LIMIT, $isExtra = false) {

		$users = $this->getUsers(array($uId), false, true, $isExtra, true, $num, false, true);

		$where = array('uId' => $uId,
					   'status' => 1
					  );
		$totalNum = getcount('friend', $where);
		$friends = $users[0]['friends'];
		unset($users[0]['friends']);
		$result = array('totalNum'	=> $totalNum,
						'friends' => $friends,
						'me'	=> $users[0],
						);
		return new APIResponse($result);
	}

	function getExtraInfo($uIds) {
		$result = $this->getExtraByUsers($uIds);
		return new APIResponse($result);
	}

}
?>
