<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: MiniBlog.php 7952 2008-07-04 07:14:25Z zhouguoqiang $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

class MiniBlog {

	function post($uId, $message, $clientIdentify, $ip = '') {
		$fields = array('uid'		=> $uId,
						'message'	=> $message,
						'from'		=> $clientIdentify,
						'dateline'	=> time()
					   );
		if ($ip) {
			$fields['ip'] = $ip;
		}
		$result = inserttable('doing', $fields, 1);
		return new APIResponse($result);
	}

	function get($uId, $num) {
		global $_SGLOBAL;
		$sql = 'SELECT * FROM %s WHERE uid = %d LIMIT %d';
		$sql = sprintf($sql, tname('doing'), $uId, $num);
		$query = $_SGLOBAL['db']->query($sql);

		$result = array();
		while($doing = $_SGLOBAL['db']->fetch_array($query)) {
			$result[] = array('created' => $doing['dateline'],
							  'message'	=> $doing['message'],
							  'ip'		=> $doing['ip'],
							  'clientIdentify'	=> $doing['from']
							 );
		}
		return new APIResponse($result);
	}

}
?>
