<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: Profile.php 11928 2009-04-09 01:23:00Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

class Profile {

	function setMYML($uId, $appId, $markup, $actionMarkup) {
		global $_SGLOBAL;

		$fields = array('myml'	=> $markup,
						'profileLink'	=> $actionMarkup);
		$where = array('uid'	=> $uId,
					   'appid'	=> $appId
					  );
		updatetable('userappfield', $fields, $where);
		$result = $_SGLOBAL['db']->affected_rows();
		return new APIResponse($result);
	}

	function setActionLink($uId, $appId, $actionMarkup) {
		global $_SGLOBAL;

		$fields = array('profilelink'	=> $actionMarkup);
		$where = array('uid'	=> $uId,
					   'appid'	=> $appId
					  );
		updatetable('userappfield', $fields, $where);
		$result = $_SGLOBAL['db']->affected_rows();
		return new APIResponse($result);
	}

}

?>
