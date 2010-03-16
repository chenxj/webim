<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: APIErrorResponse.php 7952 2008-07-04 07:14:25Z zhouguoqiang $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

// 服务器返回结果对象
class APIErrorResponse {

	var $errCode = 0;
	
	var $errMessage = '';

	function APIErrorResponse($errCode, $errMessage) {
		$this->errCode = $errCode;
		$this->errMessage = $errMessage;
	}

	function getErrCode() {
		return $this->errCode;
	}

	function getErrMessage() {
		return $this->errMessage;
	}

	function getResult() {
		return null;
	}
}
?>
