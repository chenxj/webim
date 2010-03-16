<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: Friends.php 12766 2009-07-20 04:26:21Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

class Friends extends MyBase {
	
	function areFriends($uId1, $uId2) {
		global $_SGLOBAL;
		$query = $_SGLOBAL['db']->query("SELECT uid FROM ".tname('friend')."  WHERE uid='$uId1' AND fuid='$uId2' AND status='1'");
		$result = false;
		if($friend = $_SGLOBAL['db']->fetch_array($query)) {
			$result = true;
		}
		return new APIResponse($result);
	}

	function get($uIds, $friendNum = MY_FRIEND_NUM_LIMIT) {
		global $_SGLOBAL;
		$result = array();
		if ($uIds) {
			foreach($uIds as $uId) {
				$result[$uId] = $this->_getFriends($uId, $friendNum);
			}
		}
		return new APIResponse($result);
	}

}

?>
