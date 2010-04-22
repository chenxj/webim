<?php

	//check the `uid` field
	include_once('../config.php');
	define('S_ROOT', substr(dirname(__FILE__), 0, -12));
	
	$platform = which_platform();
	$db_session = "";
	
	switch($platform)
	{
		case 'uchome':
			include_once(S_ROOT.'./config.php');
			include_once(S_ROOT.'./source/function_common.php');
			dbconnect();
			$db_session = $_SGLOBAL['db'];
			break;
		case 'discuz':
			include_once(S_ROOT.'./include/common.inc.php');
			$db_session = $db;
			break;
	}

	
	// add your code here
	update_history_table($db_session);
	
	
	
	
	
	
	function update_history_table($db_cur)
	{
		/*
		 *  delete column 'uid' from table webim_histories
		 *  Written by Harvey.
		 *
		 */	
		$res	= $db_cur->query("SELECT * FROM webim_histories LIMIT 1");
		$value	= $db_cur->fetch_array($res)
	
		if (array_key_exists('uid'))
		{
			$db_cur->query("alter table webim_histories drop column uid");
		}
	}
	
	function which_platform(){
		/*
		 *  check the platform 
		 *  Uchome ? Discuz ?  PhpWind?
		 *
		 */
		if(file_exists(S_ROOT.'./data')){
			return "uchome";
		}
		if(file_exists(S_ROOT.'./forumdata')){
			return "discuz";
		}
	}	
	
?>