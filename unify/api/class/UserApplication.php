<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: UserApplication.php 12211 2009-05-21 07:41:36Z zhengqingpeng $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

class UserApplication extends MyBase {

	function add($uId, $appId, $appName, $privacy, $allowSideNav, $allowFeed, $allowProfileLink,  $defaultBoxType, $defaultMYML, $defaultProfileLink, $version, $displayMethod, $displayOrder = null) {
		global $_SGLOBAL;

		$sql = sprintf('SELECT appid FROM %s WHERE uid = %d AND appid = %d', tname('userapp'), $uId, $appId);
		$query = $_SGLOBAL['db']->query($sql);
		$row = $_SGLOBAL['db']->fetch_array($query);
		if ($row['appid']) {
			$errCode = '170';
			$errMessage = 'Application has been already added';
			return new APIErrorResponse($errCode, $errMessage);
		}

		switch($privacy) {
			case 'public':
				$privacy = 0;
				break;
			case 'friends':
				$privacy = 1;
				break;
			case 'me':
				$privacy = 3;
				break;
			case 'none':
				$privacy = 5;
				break;
			default:
				$privacy = 0;
		}

		$narrow = ($defaultBoxType == 'narrow') ? 1 : 0;
		
		$setarr = array('uid'		=> $uId,
						'appid'	=> $appId,
						'appname'	=> $appName,
						'privacy'	=> $privacy,
						'allowsidenav'	=> $allowSideNav,
						'allowfeed'		=> $allowFeed,
						'allowprofilelink'	=> $allowProfileLink,
						'narrow'		=> $narrow
					   );
		if ($displayOrder !== null) {
			$setarr['displayorder'] = $displayOrder;
		}
		inserttable('userapp', $setarr);
		
		$fields = array('uid'		=> $uId,
						'appid'	=> $appId,
						'profilelink'	=> $defaultProfileLink,
						'myml'			=> $defaultMYML
					   );
		$result = inserttable('userappfield', $fields, 1);
		
		$reward = getreward('installapp', 0, $uId, $appId, 0);
		
		//更新用户
		$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET updatetime='$_SGLOBAL[timestamp]', credit=credit+$reward[credit], experience=experience+$reward[experience] WHERE uid='$uId'");

		$displayMethod = ($displayMethod == 'iframe') ? 1 : 0;
		$this->refreshApplication($appId, $appName, $version, $displayMethod, $narrow, null, null);
		return new APIResponse($result);
	}

	function update($uId, $appIds, $appName, $privacy, $allowSideNav, $allowFeed, $allowProfileLink, $version, $displayMethod, $displayOrder = null) {
		global $_SGLOBAL;

		switch($privacy) {
			case 'public':
				$privacy = 0;
				break;
			case 'friends':
				$privacy = 1;
				break;
			case 'me':
				$privacy = 3;
				break;
			case 'none':
				$privacy = 5;
				break;
			default:
				$privacy = 0;
		}

		$where = sprintf('uid = %d AND appid IN (%s)', $uId, simplode($appIds));
		$setarr = array(
			'appname'	=> $appName,
			'privacy'	=> $privacy,
			'allowsidenav'	=> $allowSideNav,
			'allowfeed'		=> $allowFeed,
			'allowprofilelink'	=> $allowProfileLink
		);
		if ($displayOrder !== null) {
			$setarr['displayorder'] = $displayOrder;
		}
		updatetable('userapp', $setarr, $where);

		$result = $_SGLOBAL['db']->affected_rows();

		$displayMethod = ($displayMethod == 'iframe') ? 1 : 0;
		if (is_array($appIds)) {
			foreach($appIds as $appId) {
				$this->refreshApplication($appId, $appName, $version, $displayMethod, null, null, null);
			}
		}

		return new APIResponse($result);
	}

	function remove($uId, $appIds) {
		global $_SGLOBAL;

		$sql = sprintf('DELETE FROM %s WHERE uid = %d AND appid IN (%s)', tname('userapp'), $uId, simplode($appIds));
		$res = $_SGLOBAL['db']->query($sql);
		
		$result = $_SGLOBAL['db']->affected_rows();
		
		$sql = sprintf('DELETE FROM %s WHERE uid = %d AND appid IN (%s)', tname('userappfield'), $uId, simplode($appIds));
		$res = $_SGLOBAL['db']->query($sql);

		return new APIResponse($result);
	}

	function getInstalled($uId) {
		global $_SGLOBAL;
		$sql = sprintf('SELECT appid FROM %s WHERE uid = %d', tname('userapp'), $uId);
		$query = $_SGLOBAL['db']->query($sql);
		$result = array();
		while ($userApp  = $_SGLOBAL['db']->fetch_array($query)) {
			$result[] = $userApp['appid'];
		}
		return new APIResponse($result);
	}

	function get($uId, $appIds) {
		global $_SGLOBAL;
		$sql = sprintf('SELECT * FROM %s WHERE uid = %d AND appid IN (%s)', tname('userapp'), $uId, simplode($appIds));
		$query = $_SGLOBAL['db']->query($sql);

		$result = array();
		while($userApp = $_SGLOBAL['db']->fetch_array($query)) {
			switch($userApp['privacy']) {
				case 0:
					$privacy = 'public';
					break;
				case 1:
					$privacy = 'friends';
					break;
				case 3:
					$privacy = 'me';
					break;
				case 5:
					$privacy = 'none';
					break;
				default:
					$privacy = 'public';
			}
			$result[] = array(
						'appId'		=> $userApp['appid'],
						'privacy'	=> $privacy,
						'allowSideNav'		=> $userApp['allowsidenav'],
						'allowFeed'			=> $userApp['allowfeed'],
						'allowProfileLink'	=> $userApp['allowprofilelink'],
						'displayOrder'		=> $userApp['displayorder']
						);
		}
		return new APIResponse($result);
	}
}
?>
