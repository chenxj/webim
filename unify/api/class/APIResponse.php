<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: APIResponse.php 7582 2008-06-13 03:43:20Z zhouguoqiang $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

class APIResponse {

	var $result;

	var $mode;

	function APIResponse($res, $mode = null) {
		$this->result = $res;
		$this->mode = $mode;
	}

	function getResult() {
		return $this->result;
	}

	function getMode() {
		return $this->mode;
	}
}

?>
