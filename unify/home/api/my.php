<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: my.php 12376 2009-06-16 07:10:38Z zhouguoqiang $
*/

error_reporting(0);
define('IN_UCHOME', TRUE);
define('S_ROOT', substr(dirname(__FILE__), 0, -3));

$_SGLOBAL['timestamp'] = time();
$space = array();
include_once S_ROOT.'./config.php';
include_once S_ROOT.'./ver.php';
include_once S_ROOT.'./data/data_config.php';
include_once S_ROOT.'./source/function_common.php';
include_once S_ROOT.'./api/class/MyBase.php';
include_once S_ROOT.'./api/class/APIErrorResponse.php';
include_once S_ROOT.'./api/class/APIResponse.php';

dbconnect();

$server = new my();
$response = $server->parseRequest();
echo $server->formatResponse($response);
?>
