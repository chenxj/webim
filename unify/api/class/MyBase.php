<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: MyBase.php 13209 2009-08-20 06:37:28Z zhouguoqiang $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

define('MY_VER', '0.4');
define('MY_FRIEND_NUM_LIMIT', 2000);

class MyBase {

	function _spaceInfo2Extra($rows) {
		$res = array();
		foreach($rows as $value) {
			$info = array();
			switch($value['friend']) {
				case 1:
					$info['privacy'] = 'friends';
					break;
				case 3:
					$info['privacy'] = 'me';
					break;
				case 0:
				default:
					$info['privacy'] = 'public';
			}
			if (in_array($value['type'], array('info', 'base', 'contact'))) {
				$fields = array('trainwith', 'interest', 'book', 'movie', 'tv', 'music', 'game', 'sport', 
								'idol', 'motto', 'wish', 'intro');
				if (in_array($value['subtype'], $fields)) {
					$info['value'] = $value['title'];
					$res[$value['subtype']] = $info;
				}
			} elseif ($value['type'] == 'edu') {
				$eduInfo = array('school' => $value['title'],
								 'dept' => $value['subtitle'],
								 'beginYear' => $value['startyear'],
								 'beginMonth' => $value['startmonth'],
								 'endYear' => $value['endyear'],
								 'endMonth' => $value['endmonth'],
								);
				$res['edu'][] = $info + $eduInfo;
			} elseif ($value['type'] == 'work') {
				$workInfo = array('company' => $value['title'],
								 'dept' => $value['subtitle'],
								 'beginYear' => $value['startyear'],
								 'beginMonth' => $value['startmonth'],
								 'endYear' => $value['endyear'],
								 'endMonth' => $value['endmonth'],
								);
				$res['work'][] = $info + $workInfo;
			} else {
				$res[] = $info;
			}
		}
		return $res;
	}

	function _friends2friends($friends , $num, $isOnlyReturnId = false, $isFriendIdKey = false) {
		$i = 1;
		$res = array();
		foreach($friends as $friend) {
			if ($num) {
				if ($i > $num) {
					continue;
				}
			}
			if ($isOnlyReturnId) {
				$row  = $friend['fuid'];
			} else {
				$row = array('uId' => $friend['fuid'],
							   'handle' => $friend['fusername']
							  );
			}
			if ($isFriendIdKey) {
				$res[$friend['fuid']] = $row;
			} else {
				$res[] = $row;
			}
			$i++;
		}
		return $res;
	}

	function _space2user($space, $spaceInfos = array()) {
		global $_SC, $_SGLOBAL;

		if (!$space) {
			return array();
		}
		$founders = explode(',', $_SC['founder']);
		$adminLevel = 'none';
		if (in_array($space['uid'], $founders)) {
			$adminLevel = 'founder';
		} else {
			$_SGLOBAL['supe_uid'] = $space['uid'];
			if(checkperm('manageconfig')) {
				$adminLevel = 'manager';
			}
		}

		// profile privacy
		$profilePrivacy = array();
		if (!$spaceInfos) {
			$query = $_SGLOBAL['db']->query(sprintf('SELECT * FROM  %s where uid = %d', tname('spaceinfo'), $space['uid']));
			while ($row = $_SGLOBAL['db']->fetch_array($query)) {
				$spaceInfos[] = $row;
			}
		}
		foreach($spaceInfos as $value) {
			$_PP = array();
			switch($value['friend']) {
				case 1:
					$_PP  = 'friends';
					break;
				case 3:
					$_PP = 'me';
					break;
				case 0:
				default:
					$_PP  = 'public';
			}
			$fields = array('marry' => 'relationshipStatus', 
							'birth' => 'birthday', 
							'blood' => 'bloodType',
							'birthcity' => 'birthPlace', 
							'residecity' => 'residePlace', 
							'mobile' => 'mobile',
							'qq' => 'qq',
							'msn' => 'msn',
						   );
			if (array_key_exists($value['subtype'], $fields)) {
				$profilePrivacy[$fields[$value['subtype']]] = $_PP;
			}
		}

		$privacy = unserialize($space['privacy']);
		if (!$privacy) {
			$privacy = array();
		}

		$user = array(
			'uId'		=> $space['uid'],
			'handle'	=> $space['username'],
			'action'	=> $space['action'],
			'realName'	=> $space['name'],
			'realNameChecked' => $space['namestatus'] ? true : false,
			'gender'	=> $space['sex'] == 1 ? 'male' : ($space['sex'] == 2 ? 'female' : 'unknown'),
			'email'		=> $space['email'],
			'qq'		=> $space['qq'],
			'msn'		=> $space['msn'],
			'birthday'	=> sprintf('%04d-%02d-%02d', $space['birthyear'], $space['birthmonth'], $space['birthday']),
			'bloodType'	=> empty($space['blood']) ? 'unknown' : $space['blood'],
			'relationshipStatus' => $space['marry'] == 1 ? 'single' : ($space['marry'] == 2 ? 'notSingle' : 'unknown'),
			'birthProvince' => $space['birthprovince'],
			'birthCity'	=> $space['birthcity'],
			'resideProvince' => $space['resideprovince'],
			'resideCity'	=> $space['residecity'],
			'viewNum'	=> $space['viewnum'],
			'friendNum'	=> $space['friendnum'],
			'myStatus'	=> $space['note'],
			'lastActivity' => $space['updatetime'],
			'created'	=> $space['dateline'],
			'credit'	=> $space['credit'],
			'isUploadAvatar'	=> $space['avatar'] ? true : false,
			'adminLevel'		=> $adminLevel,
			'homepagePrivacy'	=> $privacy['view']['index'] == 1 ? 'friends' : ($privacy['view']['index'] == 2 ? 'me' : 'public'),
			'profilePrivacyList'	=> $profilePrivacy,
			'friendListPrivacy'	=> $privacy['view']['friend'] == 1 ? 'friends' : ($privacy['view']['friend'] == 2 ? 'me' : 'public')
		);
		return $user;
	}

	function _getFriends($uId, $num = null) {
		global $_SGLOBAL;

		$sql = sprintf('SELECT fuid FROM %s WHERE uid = %d AND status = 1 ORDER BY fuid ', tname('friend'), $uId);
		if ($num) {
			$sql .= ' LIMIT 0, ' . $num;
		}
		$fquery = $_SGLOBAL['db']->query($sql);
		$friends = array();
		while($friend = $_SGLOBAL['db']->fetch_array($fquery)) {
			$friends[] = $friend['fuid'];
		}
		return $friends;
	}

	function refreshApplication($appId, $appName, $version, $displayMethod, $narrow, $flag, $displayOrder) {
		global $_SGLOBAL;
		$fields = array();
		if ($appName !== null && strlen($appName)>1) {
			$fields['appname'] = $appName;
		}
		if ($version !== null) {
			$fields['version'] = $version;
		}
		if ($displayMethod !== null) {
			// todo: remove
			$fields['displaymethod'] = $displayMethod;
		}
		if ($narrow !== null) {
			$fields['narrow'] = $narrow;
		}
		if ($flag !== null) {
			$fields['flag'] = $flag;
		}
		if ($displayOrder !== null) {
			$fields['displayorder'] = $displayOrder;
		}
		$sql = sprintf('SELECT * FROM %s WHERE appid = %d', tname('myapp'), $appId);
		$query = $_SGLOBAL['db']->query($sql);
		if($application = $_SGLOBAL['db']->fetch_array($query)) {
			$where = sprintf('appid = %d', $appId);
			updatetable('myapp', $fields, $where);
		} else {
			$fields['appid'] = $appId;
			$result = inserttable('myapp', $fields, 1);
		}
		
		//update cache
		include_once(S_ROOT.'./source/function_cache.php');
		userapp_cache();
	}

	/**
	 * getUsers
	 *
	 * @param array $uIds
	 * @param array $spaces space表中的信息
	 * @param boolean $isReturnSpaceField 是否返回spacefield表中的信息
	 * @param boolean $isReturnSpaceInfo 是否返回spaceinfo表中的信息
	 * @param boolean $isReturnFriends 是否返回好友信息
	 * @param integer $friendNum 好友数目
	 * @param boolean $isOnlyReturnFriendId 是否仅返回好友id
	 * @param boolean $isFriendIdKey 是否friendId作为数组的key
	 * @access public
	 * @return array
	 */
	function getUsers($uIds, $spaces = array(), $isReturnSpaceField = true, $isReturnSpaceInfo = false, $isReturnFriends = false, $friendNum = MY_FRIEND_NUM_LIMIT, $isOnlyReturnFriendId = false, $isFriendIdKey = false) {
		global $_SGLOBAL;

		if (!$uIds) {
			return array();
		}

		if (!$spaces) {
			$sql = sprintf('SELECT * FROM %s WHERE uid IN (%s)', tname('space'), implode(', ', $uIds));
			$query = $_SGLOBAL['db']->query($sql);
			$users2 = array();
			while($row = $_SGLOBAL['db']->fetch_array($query)) {
				$spaces[$row['uid']] = $row;
			}
		}

		$spaceFields = $spaceInfos = array();
		if ($isReturnSpaceField) {
			$sql = sprintf('SELECT * FROM %s WHERE uid IN (%s)', tname('spacefield'), implode(', ', $uIds));
			$query = $_SGLOBAL['db']->query($sql);
			while($row = $_SGLOBAL['db']->fetch_array($query)) {
				$spaceFields[$row['uid']] = $row;
			}

			// 由于spacefield表中一些字段的隐私存放在spaceinfo表，在这里一块取出来
			$sql = sprintf('SELECT * FROM %s WHERE uid IN (%s)', tname('spaceinfo'), implode(', ', $uIds));
			$query = $_SGLOBAL['db']->query($sql);
			while($row = $_SGLOBAL['db']->fetch_array($query)) {
				$spaceInfos[$row['uid']][] = $row;
			}
		}

		$friends = array();
		if ($isReturnFriends) {
			$sql = sprintf('SELECT * FROM %s WHERE uid IN (%s) AND status = 1 ORDER BY fuid', tname('friend'), implode(', ', $uIds));
			$query = $_SGLOBAL['db']->query($sql);
			while($row = $_SGLOBAL['db']->fetch_array($query)) {
				$friends[$row['uid']][] = $row;
			}
		}

		$users = array();
		foreach($uIds as $uId) {
			$space = $spaces[$uId];
			if ($isReturnSpaceField) {
				$space = array_merge($spaceFields[$uId], $space);
			}
			$user = $this->_space2user($space, $spaceInfos[$uId]);
			if (!$user) {
				continue;
			}
			if ($isReturnSpaceInfo) {
				$user['extra'] = $this->_spaceInfo2Extra($spaceInfos[$uId]);
			}
			if ($isReturnFriends) {
				$user['friends'] = $this->_friends2friends($friends[$uId], $friendNum, $isOnlyReturnFriendId, $isFriendIdKey);
			}
			$users[] = $user;
		}
		return $users;
	}

	function getExtraByUsers($uIds) {
		global $_SGLOBAL;

		if (!$uIds) {
			return array();
		}
		$spaceInfos = array();
		$sql = sprintf('SELECT * FROM %s WHERE uid IN (%s)', tname('spaceinfo'), implode(', ', $uIds));
		$query = $_SGLOBAL['db']->query($sql);
		$spaceInfos = array();
		while($row = $_SGLOBAL['db']->fetch_array($query)) {
			$spaceInfos[$row['uid']][] = $row;
		}

		$users = array();
		foreach($uIds as $uId) {
			$user = array('uId' => $uId,
						  'extra' => $this->_spaceInfo2Extra($spaceInfos[$uId]),
						  );
			$users[] = $user;
		}
		return $users;
	}
}

class my{

	function parseRequest() {
		global $_SCONFIG;

		include_once(S_ROOT.'./source/function_common.php');
		
		$request = $_POST;
		$module = $request['module'];
		$method = $request['method'];
		
		$errCode = 0;
		$errMessage = '';
		if ($_SCONFIG['close']) {
			$errCode = 2;
			$errMessage = 'Site Closed';
		} elseif (!$_SCONFIG['my_status']) {
			$errCode = 2;
			$errMessage = 'Manyou Service Disabled';
		} elseif (!$_SCONFIG['sitekey']) {
			$errCode = 11;
			$errMessage = 'Client SiteKey NOT Exists';
		} elseif (!$_SCONFIG['my_sitekey']) {
			$errCode = 12;
			$errMessage = 'My SiteKey NOT Exists';
		} elseif (empty($module) || empty($method)) {
			$errCode = '3';
			$errMessage = 'Invalid Method: ' . $moudle . '.' . $method;
		}

		if (get_magic_quotes_gpc()) {
			$request['params'] = sstripslashes($request['params']);
		}
		$mySign = $module . '|' . $method . '|' . $request['params'] . '|' . $_SCONFIG['my_sitekey'];
		$mySign = md5($mySign);
		if ($mySign != $request['sign']) {
			$errCode = '10';
			$errMessage = 'Error Sign';
		}

		if ($errCode) {
			return new APIErrorResponse($errCode, $errMessage);
		}

		$params = unserialize($request['params']);

		$params = $this->myAddslashes($params);
		if ($module == 'Batch' && $method == 'run') {
			$response = array();
			foreach($params as $param) {
				$response[] = $this->callback($param['module'], $param['method'], $param['params']);
			}
			return new APIResponse($response, 'Batch');
		}
		return $this->callback($module, $method, $params);
	}

	function callback($module, $method, $params) {
		global $_SGLOBAL;
		if (isset($params['uId'])) {
			$space = getspace($params['uId']);
			if ($this->_needCheckUserId($module, $method)) {
				if (!$space['uid']) {
					$errCode = 1;
					$errMessage = "User($params[uId]) Not Exists";
					return new APIErrorResponse($errCode, $errMessage);
				}
			}
		}
		$_SGLOBAL['supe_uid'] = $space['uid'];
		$_SGLOBAL['supe_username'] = $space['username'];

		@include_once S_ROOT . './api/class/' . $module . '.php';
		if (!class_exists($module)) {
			$errCode = 3;
			$errMessage = "Class($module) Not Exists";
			return new APIErrorResponse($errCode, $errMessage);
		}

		$class = new $module();
		$response = @call_user_func_array(array(&$class, $method), $params);

		return $response;
	}

	//格式化返回结果
	function formatResponse($data) {
		global $_SCONFIG, $_SC;
		//返回结果要参加一些统一的返回信息
		$res = array(
			'timezone'	=> $_SCONFIG['timeoffset'],
			'version'   => X_VER,
			'my_version'   => MY_VER,
			'charset'	=> $_SC['charset'],
			'language'	=> $_SC['language'] ? $_SC['language'] : 'zh_CN',
		);
		if (strtolower(get_class($data)) == 'apiresponse' ) {
			if (is_array($data->result) && $data->getMode() == 'Batch') {
				foreach($data->result as $result) {
					if (strtolower(get_class($result)) == 'apiresponse') {
						$res['result'][]  = $result->getResult();
					} else {
						$res['result'][] = array('errCode' => $result->getErrCode(),
												 'errMessage' =>  $result->getErrMessage()
												);
					}
				}
			} else {
				$res['result']  = $data->getResult();
			}
		} else {
			$res['errCode'] = $data->getErrCode();
			$res['errMessage'] = $data->getErrMessage();
		}
		return serialize($res);
	}

	function _needCheckUserId($module, $method) {
		$myMethod = $module . '.' . $method;
		switch($myMethod) {
			case 'Notifications.send':
			case 'Request.send':
				$res = false;
				break;
			default:
				$res = true;
		}
		return $res;
	}

	function myAddslashes($string) {
		if(is_array($string)) {
			foreach($string as $key => $val) {
				$string[$key] = $this->myAddslashes($val);
			}
		} else {
			$string = ($string === null) ? null : addslashes($string);
		}
		return $string;
	}

}
?>
