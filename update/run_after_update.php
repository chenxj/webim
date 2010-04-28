<?php

	//check the `uid` field
	include_once('../config.php');
	include_once('json.php');
	define('S_ROOT', substr(dirname(__FILE__), 0, -12));
	
	$platform = which_platform();
	$db_session = "";
	
	switch($platform)
	{
		case 'uchome':
			define('IN_UCHOME', TRUE);
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
    update_config();
    echo json_encode(array("isok"=>true));
	
	function update_config(){ # 修改配置文件版本号
		$fp = fopen('../config.php', 'r');
		$configfile = fread($fp, filesize('../config.php'));
		$configfile = trim($configfile);
		$configfile = substr($configfile, -2) == '?>' ? substr($configfile, 0, -2) : $configfile;
		$configfile = insertconfig($configfile, '/\$_IMC\["timestamp"\] =\s*.*?;/i', '$_IMC["timestamp"] = 10;');
		$fp = fopen('../config.php', 'w');
		@fwrite($fp, trim($configfile));
		@fclose($fp);
	}
	
////////////////	
	
	
	function update_history_table($db_cur)
	{
		/*
		 *  delete column 'uid' from table webim_histories
		 *  Written by Harvey.
		 *
		 */	
		$res = $db_cur->query("describe webim_histories");
	
		while ($value = $db_cur->fetch_array($res))
		{
			if ($value["Field"] === 'uid')
			{
				$db_cur->query("alter table webim_histories drop column uid");
			}
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

    function insertconfig($s, $find, $replace) {
        if(preg_match($find, $s)) {
            $s = preg_replace($find, $replace, $s);
        } else {
            $s .= "\r\n".$replace;
        }
        return $s;
    }
	
?>
