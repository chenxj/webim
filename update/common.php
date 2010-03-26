<?php
# ./webim/update/common.php
/*
 * 提供更新所需的函数
 * Written by Jinyu
 */
define('IM_ROOT', substr(dirname(__FILE__), 0, -6)); # webim 平台根目录
define('STATE_FILE', dirname(__FILE__).DIRECTORY_SEPARATOR.'current_state');
include_once(IM_ROOT . "json.php"); # json 类
include_once(IM_ROOT . "config.php"); # webim 配置文件

if( !function_exists('json_encode') ) { # json 编码函数
	function json_encode($data) {
		$json = new Services_JSON();
		return($json->encode($data));
	}
}

if( !function_exists('json_decode') ) { # json 解码函数
	function json_decode($data) {
		$json = new Services_JSON();
		return($json->decode($data));
	}
}

function g($key = '') { # 获取页面 GET 变量
	return $key === '' ? $_GET : (isset($_GET[$key]) ? $_GET[$key] : null);
}

function p($key = '') { # 获取页面 POST 变量
	return $key === '' ? $_POST : (isset($_POST[$key]) ? $_POST[$key] : null);
}

function gp($key = '',$def = null) { # 获取页面 GET\POST 变量
	$v = g($key);
	if(is_null($v)){
		$v = p($key);
	}
	if(is_null($v)){
		$v = $def;
	}
	return $v;
}

function clearState(){ # 清空 current_state 文件
	$fp = @fopen(STATE_FILE, 'w');
	if(!$fp){
		return false;
		exit();
	}
	fwrite($fp, "");
	fclose($fp);
	return true;
}// func clear State

function setState($status){ # 设置 current_state 文件
	$fp = @fopen(STATE_FILE, 'w');
	if(!$fp){
		return false;
		exit();
	}
	fwrite($fp, $status);
	fclose($fp);
	return true;
}// func setState

function getCurrentState(){ # 获取 current_state 内容
	$fp = @fopen(STATE_FILE, 'r');
	if(!$fp){
		return false;
		exit();
	}
	$state = fread($fp, filesize(STATE_FILE));
	fclose($fp);
	return $state; //返回 json 形式
}// func getCurrentState

function setStatus($action, $mark, $ret_array = array()){ # 设置状态反馈变量
	$status = array($action => array($mark => $ret_array));
	return json_encode($status);
}

function getNewestVersionInfo(){ # 获取更新索引信息
	/* $download_index 为 json 形式 */
	global $_IMC;
	if(!setState(setStatus("GetNewestVersion", "Waiting"))){
		//echo json_encode(array("Error"=>"GetNewestVersion :: Set current_state failed"));
		exit();
	}
	$new_version = file_get_contents($_IMC['update_url']."public/NewestVersion");
	if($new_version > $_IMC['version']){// if new version
		$download_index = file_get_contents($_IMC['update_url'].$_IMC['version']."/index");
		if($download_index){
			$fp = @fopen(dirname(__FILE__).DIRECTORY_SEPARATOR.'temp_download'.DIRECTORY_SEPARATOR.'download_index');
			if(!$fp){
				exit();
			}
			fwrite($fp, $download_index);// write ./update/temp_download/download_index
			fclose($fp);
			if(!setState(setStatus("GetNewestVersion", "Successful", array('VersionInfo' => $download_index)))){
				//echo json_encode(array("Error"=>"GetNewestVersion :: Set current_state failed"));
				exit();
			}
		}// if download success
	}else if($new_version <= $_IMC['version']){// if none new version
		if(!setState(setStatus("GetNewestVersion", "Invalid"))){
			//echo json_encode(array("Error"=>"GetNewestVersion :: Set current_state failed"));
			exit();
		}
	}
}// func getNewestVersion


?>