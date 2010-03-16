<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: Site.php 12766 2009-07-20 04:26:21Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

class Site extends MyBase {

	function getUpdatedUsers($num) {
		global $_SGLOBAL;

		$totalNum = getcount('userlog', '');
		$users = array();
		if ($totalNum) {
			$sql = sprintf('SELECT uid, action, type FROM %s ORDER BY dateline LIMIT %d', tname('userlog'), $num);
			$query = $_SGLOBAL['db']->query($sql);
			$deletedUsers = $userLogs = $uIds = array();
			$undeletedUserIds = array( 0 => array(),
									   1 => array(),
									   2 => array(),
									 );
			while($row = $_SGLOBAL['db']->fetch_array($query)) {
				$uIds[] = $row['uid'];
				if ($row['action'] == 'delete') {
					$deletedUsers[] = array('uId' => $row['uid'],
											'action' => $row['action'],
										   );
				} else {
					$undeletedUserIds[$row['type']][] = $row['uid'];
				}
				$userLogs[$row['uid']] = $row;
			}

			$updatedUsers2 = $updatedUsers3 = array();
			// extra updated
			if ($undeletedUserIds[2]) {
				$updatedUsers2 = $this->getExtraByUsers($undeletedUserIds[2]);
			}

			// basic updated + all updated
			$uIds3 = array_merge($undeletedUserIds[0], $undeletedUserIds[1]);
			$updatedUsers3 = $this->getUsers($uIds3, false, true, true, false);

			$updatedUsers = array_merge($updatedUsers2, $updatedUsers3);
			foreach($updatedUsers as $k => $v) {
				$updatedUsers[$k]['action'] = $userLogs[$v['uId']]['action'];
				switch($userLogs[$v['uId']]['type']) {
					case 2:
						$updatedUsers[$k]['updateType'] = 'extra';
						break;
					case 1:
						$updatedUsers[$k]['updateType'] = 'basic';
						break;
					case 0:
					default:
						$updatedUsers[$k]['updateType'] = 'all';
				}
			}

			$users = array_merge($updatedUsers, $deletedUsers);

			if ($uIds) {
				$sql = sprintf('DELETE FROM %s WHERE uid IN (%s)', tname('userlog'), simplode($uIds));
				$_SGLOBAL['db']->query($sql);
			}
		}

		$result = array('totalNum'	=> $totalNum,
						'users'		=> $users,
					   );
		return new APIResponse($result);
	}

	function getUpdatedFriends($num) {
		global $_SGLOBAL;

		$friends = array();
		$totalNum = getcount('friendlog', '');

		if ($totalNum) {
			$sql = sprintf('SELECT * FROM %s ORDER BY dateline LIMIT %d', tname('friendlog'), $num);
			$query = $_SGLOBAL['db']->query($sql);
			while ($friend = $_SGLOBAL['db']->fetch_array($query)) {
				$friends[] = array('uId'	=> $friend['uid'],
								   'uId2'	=> $friend['fuid'],
								   'action'	=> $friend['action']
								  );

				$sql = sprintf('DELETE FROM %s WHERE uid = %d AND fuid = %d', tname('friendlog'), $friend['uid'], $friend['fuid']);
				$_SGLOBAL['db']->query($sql);
			}

		}

		$result = array('totalNum'	=> $totalNum,
						'friends'	=> $friends
					   );
		return new APIResponse($result);

	}

	function getAllUsers($from, $userNum, $friendNum = MY_FRIEND_NUM_LIMIT, $isExtra = false) {
		global $_SGLOBAL;

		$totalNum = getcount('space', '');

		// space
		$sql = 'SELECT s.*
				FROM %s s 
				ORDER BY s.uid
				LIMIT %d, %d';
		$sql = sprintf($sql, tname('space'), $from, $userNum);
		$query = $_SGLOBAL['db']->query($sql);

		$spaces = $uIds = array();
		while($row = $_SGLOBAL['db']->fetch_array($query)) {
			$spaces[$row['uid']] = $row;
			$uIds[] = $row['uid'];
		}

		$users = $this->getUsers($uIds, $spaces, true, $isExtra, true, $friendNum, true);


		$result = array('totalNum'	=> $totalNum,
						'users'		=> $users
					   );
		return new APIResponse($result);
	}

	function getStat($beginDate = null, $num = null, $orderType = 'ASC') {
		global $_SGLOBAL;

		$sql = 'SELECT * FROM ' . tname('stat');
		if ($beginDate) {
			$sql .= sprintf(' WHERE daytime >= %d', $beginDate);
		}
		$sql .= " ORDER BY daytime $orderType";
		if ($num) {
			$sql .= " LIMIT $num ";
		}
		$query = $_SGLOBAL['db']->query($sql);
		$result = array();
		$fields = array('login' => 'loginUserNum',
						'doing' => 'doingNum',
						'blog'	=> 'blogNum',
						'pic'	=> 'photoNum',
						'poll'	=> 'pollNum',
						'event'	=> 'eventNum',
						'share'	=> 'shareNum',
						'thread' => 'threadNum',
						'docomment' => 'doingCommentNum',
						'blogcomment' => 'blogCommentNum',
						'piccomment' => 'photoCommentNum',
						'pollcomment' => 'pollCommentNum',
						'eventcomment' => 'eventCommentNum',
						'sharecomment'	=> 'shareCommentNum',
						'pollvote'	=> 'pollUserNum',
						'eventjoin'	=> 'eventUserNum',
						'post'	=> 'postNum',
						'wall'	=> 'wallNum',
						'poke'	=> 'pokeNum',
						'click'	=> 'clickNum',
					   );
		while($row = $_SGLOBAL['db']->fetch_array($query)) {
			$stat = array('date' => $row['daytime']);
			foreach($row as $k => $v) {
				if (array_key_exists($k, $fields)) {
					$stat[$fields[$k]] = $v;
				}
			}
			$result[] = $stat;
		}
		return new APIResponse($result);
	}
}

?>
