<?php
# ./webim/update/common.php
/*
 * 提供更新所需的函数
 * Written by Jinyu
 */
define('IM_ROOT', substr(dirname(__FILE__), 0, -6)); # webim 平台根目录
define('STATE_FILE', dirname(__FILE__).DIRECTORY_SEPARATOR.'current_state'); # ./webim/update/current_state [file]
define('INDEX', dirname(__FILE__).DIRECTORY_SEPARATOR.'temp_download'.DIRECTORY_SEPARATOR.'download_index'); # ./webim/update/temp_download/download_index [file]
//include_once(IM_ROOT . "lib".DIRECTORY_SEPARATOR."json.php"); # further structure
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
	try{
		$fp = @fopen(STATE_FILE, 'w');
	}catch(Exception $e){
		echo json_encode(array("state"=>"clearState", "isok"=>false, "iswait"=>false, "errmsg"=>"Clear current_state file error! Check your permission", "percent"=>""));
	}
	if(!$fp){
		return false;
		exit();
	}
	fwrite($fp, "");
	fclose($fp);
	return true;
}// func clear State

function setState($status){ # 设置 current_state 文件
	try{
		$fp = @fopen(STATE_FILE, 'w');
	}catch(Exception $e){
		echo json_encode(array("state"=>"clearState", "isok"=>false, "iswait"=>false, "errmsg"=>"Set current_state file error! Check your permission", "percent"=>""));
	}
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
	
	$state_info = json_decode($state);
	$ori_state = array();
	foreach($state_info as $key=>$value){
		$ori_state['state'] = $key;
		foreach($value as $mark=>$info){
			$ori_state['isok'] = $mark === "Successful"?true:false;
			$ori_state['iswait'] = $mark === "Waiting"?true:false;
			$ori_state['errmsg'] = $mark === "Invalid"?"Invalid":"";
			foreach($info as $detail){
				if(is_numeric($detail)){
					$ori_state['percent'] = $detail;
				}else{
					$ori_state['errmsg'] = $detail;
				}
			}
		}
	}
	if(!isset($ori_state['percent'])){
		$ori_state['percent'] = "";
	}
	if(!isset($ori_state['errmsg'])){
		$ori_state['errmsg'] = "";
	}
	$ret = json_encode($ori_state);
	return $ret; //返回 json 形式
}// func getCurrentState

function setStatus($action, $mark, $ret_array = array()){ # 设置状态反馈变量
	$status = array($action => array($mark => $ret_array));
	return json_encode($status);
}

function getNewestVersionInfo(){ # 获取更新信息, 下载更新索引, 成功返回更新信息(json), 失败或无更新返回 false
	/* $download_index 为 json 形式 */
	global $_IMC, $_IMC_LOG_FILE;
	
	$version_info = file_get_contents($_IMC['update_url']."publish/NewestVersionInfo");
	if($version_info){
		$new_version = array();
		$temp_info = json_decode($version_info);
		foreach($temp_info as $key=>$value){
			$new_version[$key] = $value;
		}
	}
	if($new_version['Version'] > $_IMC['version']){// if new version
		$download_index = file_get_contents($_IMC['update_url'].'version_'.$_IMC['version']."/index");
		if($download_index){
			if(!file_exists('./temp_download')){
				mkdir('./temp_download');
			}
			try{
				$fp = @fopen(INDEX, 'w');
			}catch(Exception $e){
				echo json_encode(array("state"=>"Update", "isok"=>false, "iswait"=>false, "errmsg"=>"Write download_index file error! Check your permission", "percent"=>""));
			}
			if(!$fp){
				logto_file($_IMC_LOG_FILE["name"], "Write download_index", "写入更新列表:写入失败！\n");
			}
			fwrite($fp, $download_index);// write ./update/temp_download/download_index
			fclose($fp);
			return $version_info;
		}// if download success
	}else if($new_version['Version'] <= $_IMC['version']){// if none new version
		echo json_encode(array("state"=>"Update", "isok"=>false, "iswait"=>false, "errmsg"=>"No updates available", "percent"=>""));
		return false;
	}
}// func getNewestVersion

function update($version){ # 执行更新, 参数是将更新到的版本(新版)
	global $_IMC, $_IMC_LOG_FILE;
	set_time_limit(0);// 防止超时
	if(!setState(setStatus("Download", "Waiting", array("Download"=>0)))){
		logto_file($_IMC_LOG_FILE["name"], "SetState", "下载更新文件:写入状态失败！\n");
		return false;
	}
	
	$fp = @fopen(INDEX, 'r');
	if(!$fp){
		echo json_encode(array("state"=>"Update", "isok"=>false, "iswait"=>false, "errmsg"=>"Read download_index file error! Check your permission", "percent"=>""));
		return false;
	}
	$tmp = fread($fp, filesize(INDEX));
	if(!$tmp){
		if(!setState(setStatus("Download", "Invalid"))){
			logto_file($_IMC_LOG_FILE["name"], "SetState", "载入更新列表失败:写入状态失败！\n");
			return false;
		}
	}
	fclose($fp);
	
	$tmp = json_decode($tmp);
	$index = array();// 文件下载列表
	foreach($tmp as $install=>$download){// 获取下载文件列表
		$index[$download] = $install;
	}

	removeDir(dirname(__FILE__).DIRECTORY_SEPARATOR.'temp_download');// 删除临时目录下所有文件
	mkdir(dirname(__FILE__).DIRECTORY_SEPARATOR.'temp_download');// 存放下载的临时更新文件
	
	$total = count($index);// 下载文件总数
	$update_list = array();// 更新路径列表
	$num = 0;
	$remain = 3;// 下载失败尝试次数
	$success = false;
	$complete = true;
	foreach($index as $key=>$value){// 下载更新文件 $key--download路径, $value--install路径
		while($remain > 0 && !$success){
			if(is_media($key)){// multimedia files
				$fc = file_get_contents($_IMC['update_url'].$key.'_d');
				if(!$fc){// if download failed
					if(-- $remain > 0){
						continue;// break while-loop
					}else{
						echo json_encode(array("state"=>"Update", "isok"=>false, "iswait"=>false, "errmsg"=>"Download update file error!", "percent"=>""));
						$complete = false;
						break;
					}
				}
				if($num % 2 == 0){
					echo json_encode(array("state"=>"Update", "isok"=>true, "iswait"=>false, "errmsg"=>"Downloading update file", "percent"=>$num*100/$total));
				}
				$value = ($value[0] === '/')?substr($value, 1):$value;
				$update_list[IM_ROOT.substr($value, 6)] = dirname(__FILE__).DIRECTORY_SEPARATOR.'temp_download'.DIRECTORY_SEPARATOR.substr(strrchr($key, '/'), 1);
				try{
					$fp = @fopen(dirname(__FILE__).DIRECTORY_SEPARATOR.'temp_download'.DIRECTORY_SEPARATOR.substr(strrchr($key, '/'), 1), 'wb');
				}catch(Exception $e){
					echo json_encode(array("state"=>"Update", "isok"=>false, "iswait"=>false, "errmsg"=>"Write media file error! Check your permission", "percent"=>""));
				}
				if(!$fp){
					logto_file($_IMC_LOG_FILE["name"], "DownloadMediaFile", "写入媒体文件失败！\n");
					return false;
				}
				fwrite($fp, $fc);
				fclose($fp);
				$num ++;
				if(!setState(setStatus("Download", "Waiting", array("Download"=>$num*100/$total)))){
					logto_file($_IMC_LOG_FILE["name"], "SetState", "下载文件过程:写入状态失败！\n");
					return false;
				}
				$success = true;
			}else{// php, css, js files
				$fc = file_get_contents($_IMC['update_url'].$key.'_d');
				if(!$fc){// if download failed
					if(-- $remain > 0){
						continue;// break while-loop
					}else{
						echo json_encode(array("state"=>"Update", "isok"=>false, "iswait"=>false, "errmsg"=>"Download update file error!", "percent"=>""));
						$complete = false;
						break;
					}
				}
				if($num % 2 == 0){
					echo json_encode(array("state"=>"Update", "isok"=>true, "iswait"=>false, "errmsg"=>"Downloading update file", "percent"=>$num*100/$total));
				}
				$value = ($value[0] === '/')?substr($value, 1):$value;
				$update_list[IM_ROOT.substr($value, 6)] = dirname(__FILE__).DIRECTORY_SEPARATOR.'temp_download'.DIRECTORY_SEPARATOR.substr(strrchr($key, '/'), 1);
				try{
					$fp = @fopen(dirname(__FILE__).DIRECTORY_SEPARATOR.'temp_download'.DIRECTORY_SEPARATOR.substr(strrchr($key, '/'), 1), 'w');
				}catch(Exception $e){
					echo json_encode(array("state"=>"Update", "isok"=>false, "iswait"=>false, "errmsg"=>"Write script file error! Check your permission", "percent"=>""));
				}
				if(!$fp){
					logto_file($_IMC_LOG_FILE["name"], "DownloadUpdateFile", "写入更新文件失败！\n");
					return false;
				}
				fwrite($fp, $fc);
				fclose($fp);
				$num ++;
				if(!setState(setStatus("Download", "Waiting", array("Download"=>$num*100/$total)))){
					logto_file($_IMC_LOG_FILE["name"], "SetState", "下载文件过程:写入状态失败！\n");
					return false;
				}
				$success = true;
			}
		}// while-loop
		$success = false;
	}// foreach-loop
	if(!$complete){
		echo json_encode(array("state"=>"Update", "isok"=>false, "iswait"=>false, "errmsg"=>"Download isn't complete!", "percent"=>""));
		exit();
	}
	
	if(!setState(setStatus("Download", "Successful"))){ # 下载并保存临时文件完毕
		logto_file($_IMC_LOG_FILE["name"], "SetState", "下载更新文件成功:写入状态失败！\n");
		return false;
	}
	
	if(!file_exists('./temp_backup')){
		mkdir('./temp_backup');
	}
	if(!setState(setStatus("Backup", "Waiting", array("Backup"=>0)))){
		logto_file($_IMC_LOG_FILE["name"], "SetState", "备份工程开始:写入状态失败！\n");
		return false;
	}
	if(backup_project()){ # 备份 webim
		if(!setState(setStatus("Backup", "Successful"))){
			logto_file($_IMC_LOG_FILE["name"], "SetState", "备份成功:写入状态失败！\n");
			return false;
		}
	}else{
		if(!setState(setStatus("Backup", "Failed"))){
			logto_file($_IMC_LOG_FILE["name"], "SetState", "备份失败:写入状态失败！\n");
			return false;
		}
		logto_file($_IMC_LOG_FILE["name"], "BuckupProject", "备份工程:update函数返回失败！\n");
		return false;
	}
	
	if(!setState(setStatus("Update", "Waiting", array("Update"=>0)))){
		logto_file($_IMC_LOG_FILE["name"], "SetState", "更新文件开始:写入状态失败！\n");
		return false;
	}
	if(!update_file($update_list)){ # 更新 webim
		logto_file($_IMC_LOG_FILE["name"], "SetState", "更新文件失败:写入状态失败！\n");
		return false;
	}
	update_config($version); # 更新配置文件中版本号
	try{
		$dp = opendir(IM_ROOT.'update'); # 删除更新锁
	}catch(Exception $e){
		echo json_encode(array("state"=>"Update", "isok"=>false, "iswait"=>false, "errmsg"=>"Open update[dir] error! Check your permission", "percent"=>""));
	}
	while($file = readdir($dp) !== false){
		if($file != '.' && $file != '..' && substr($file, -4) != 'lock'){
			try{
				unlink(IM_ROOT.'update'.DIRECTORY_SEPARATOR.$file);
			}catch(Exception $e){
				echo json_encode(array("state"=>"Update", "isok"=>false, "iswait"=>false, "errmsg"=>"Delete lock file error! Check your permission", "percent"=>""));
			}
		}
	}
	return true;
}// func update

function is_media($filename){ # 判断给定文件是否为媒体文件，是返回 true
	// .swf .png .mp3 .jpg .gif
	if(preg_match('/^(http:\/\/)?([A-Za-z]*[.]?(\/)?)+[A-Za-z0-9_\.]*[\/][a-zA-z0-9_-]*[.](swf|png|mp3|jpg|gif)?$/', $filename)){
		return true;
	}else{
		return false;
	}
}// func is_media

function insertconfig($s, $find, $replace) { # 添加配置文件
	if(preg_match($find, $s)) {
		$s = preg_replace($find, $replace, $s);
	} else {
		$s .= "\r\n".$replace;
	}
	return $s;
}// func insertconfig

function update_config($version){ # 修改配置文件版本号
	$fp = fopen(IM_ROOT.'config.php', 'r');
	$configfile = fread($fp, filesize(IM_ROOT.'config.php'));
	$configfile = trim($configfile);
	$configfile = substr($configfile, -2) == '?>' ? substr($configfile, 0, -2) : $configfile;
	$configfile = insertconfig($configfile, '/\$_IMC\["version"\] =\s*.*?;/i', '$_IMC["version"] = "'.$version.'";');
	$fp = fopen(IM_ROOT.'config.php', 'w');
	@fwrite($fp, trim($configfile));
	@fclose($fp);
}// func update_config

/*
 * 记录日志
 * @$file_name : 日志文件
 * $type_string : 日志类型字符串
 * $content_string : 日志内容
 */
function logto_file($file_name, $type_string, $content_string){
	global $_IMC_LOG_FILE;
	global $_IMC_LOG_TYPE;
	if (!$handle = fopen($file_name, 'a')){
		echo "不能打开文件 $file_name";
		return;
	}
	
	if (fwrite($handle, "[" . date("Y-m-d H:i:m") . "]") === FALSE){
		echo "不能写入到文件 $file_name";
		return;
	}
	if (fwrite($handle, "[" . $type_string . "]") === FALSE){
		echo "不能写入到文件 $file_name";
		return;
	}
	if (fwrite($handle, ": \n" . $content_string . "\n") === FALSE){
		echo "不能写入到文件 $file_name";
		return;
	}
	fclose($handle);
}// func logto_file

/*
 * 备份旧工程
 * @$project_path : 需要备份的工程路径，如果为空，则默认使用$_IMC["install_path"] . DIRECTORY_SEPARATOR . 'webim'。
 * @return : 成功返回ture，否则返回false
 * 注意：仅适用于Linux/Unix平台。
 */
$__errorString__ = "";
function backup_project($project_path = null){
	global $_IMC;/* Webim 的绝对路径 */
	global $_IMC_LOG_FILE;/* 日志文件信息 */
	global $_IMC_LOG_TYPE;/* 日志文件的类型索引 */
	global $__errorString__;
	
	if ($project_path === null)
	{
		$project_path = $_IMC["install_path"].'webim';
	}
	
	if ($project_path[strlen($project_path)-1] !== DIRECTORY_SEPARATOR)
	{
		$project_path .= DIRECTORY_SEPARATOR;
	}
	
	//webim
	//....update
	//.........temp_download
	//.........temp_backup
	//.............webim
	$backup_dir = $project_path . "update" . DIRECTORY_SEPARATOR . 'temp_backup' . DIRECTORY_SEPARATOR . 'webim';
	
	$res = copyDir($project_path,$backup_dir,'Backup');
	
	if ($res !== false)
	{
		$status = array('Backup' => array('Successful' => Array('Download' => 100)));
		setState(json_encode($status));
		logto_file($_IMC_LOG_FILE["name"], $_IMC_LOG_TYPE["backup_project"], "备份文件成功！文件夹：$res[0]，文件数：$res[1]");
		return true;
	}
	else
	{
		$status = array('Backup' => array('Failed' => Array('Error' => $__errorString__)));
		setState(json_encode($status)); 
		logto_file($_IMC_LOG_FILE["name"], $_IMC_LOG_TYPE["backup_project"], "备份文件失败！");
		return false;
	}
}// func backup_project

/*
 * 更新文件
 * @$file_list : 需要更新的文件列表以及内容，格式：Array('dst_path'=>'tmp_path', 'dst_path_2'=>'temp_path_2')
 * @return : 成功返回ture，否则返回false
 */
function update_file($file_list)
{
	global $_IMC_LOG_FILE;
	global $_IMC_LOG_TYPE;
	global $__errorString__;
	
	if( ($count = __update_file__($file_list)) === false )
	{
		logto_file($_IMC_LOG_FILE["name"], $_IMC_LOG_TYPE["update_file"], $__errorString__);
		$status = array('Update' => array('Failed' => Array('Error' => $__errorString__)));
		setState(json_encode($status));
		return false;
	}
	else
	{
		logto_file($_IMC_LOG_FILE["name"], $_IMC_LOG_TYPE["update_file"], "更新文件成功，总计：$count");
		$status = array('Update' => array('Successful' => Array('Update' => 100)));
		setState(json_encode($status));
		return true;
	}
}
/*
 * 更新文件
 * @$file_list : 需要更新的文件列表，格式：Array('InstallPathName'=>'TempPathName')
 * @return : 成功返回ture，否则返回false
 */
function __update_file__($file_list){
	global $__errorString__;
	
	$status = array('Update' => array('Waiting' => Array('Update' => 0)));
	setState(json_encode($status)); 
	
	$updateCountAll = count($file_list);
	$updateCountCur = 0;
	$rate = 0;
	var_dump($file_list);
	
	foreach($file_list as $installPathName => $Tempfile){
		
		$pathpart = pathinfo($installPathName);
		if (!is_dir($pathpart["dirname"]))
		{
			if(!mkdir($pathpart["dirname"], 0777, true))
			{
				$path = $pathpart["dirname"];
				$__errorString__ = "创建文件夹：$path 失败！";
				return false;
			}
		}
		if(!$handle = fopen($installPathName, 'w')){
			$__errorString__ = "不能打开文件 $installPathName";
			return false;
		}
		if(!($content = file_get_contents($Tempfile))){
			$__errorString__ = "不能打开文件 $Tempfile";
			return false;
		}
		if(fwrite($handle, $content) === FALSE){
			$__errorString__ = "不能写入到文件 $installPathName";
			return false;
		}
		fclose($handle);
		$updateCountCur ++;
		$tempRate = ((int)$updateCountCur) / $updateCountAll;
		
		//增加统计间隔，减少file IO
		if ($tempRate - $rate > 0.5)
		{
			$status = array('Update' => array('Waiting' => Array('Update' => $tempRate*100)));
			setState(json_encode($status));
			$rate = $tempRate;					
		}			
	}
	return $updateCountAll;
}// func update_file

/*
 * 复制文件夹
 * $dirFrom : 源文件夹（忽略temp_download 和 temp_backup），最后不带DIRECTORY_SEPARATOR
 * $dirTo :目标文件夹（如果已经存在则删除），最后不带DIRECTORY_SEPARATOR
 * $noticeString : 写入current_state的提示信息。
 * @return : array("文件夹数"，"文件数目")
 */
 $__countFile__ = 0;
 $__countDir__  = 0;
 $__rate__ = 0;
function copyDir($dirFrom,$dirTo,$noticeString = null){
	global $_IMC_LOG_FILE;
	global $_IMC_LOG_TYPE;
	global $__countFile__;
	global $__countDir__;
	global $__errorString__;
	global $__rate__;
	$countFile = 0; //用于统计文件总数的变量
	$countDir = 0; //用于统计目录总数的变量
	
	if(is_file($dirTo))
	{ //判断目录是否与文件重名
		logto_file($_IMC_LOG_FILE["name"], $_IMC_LOG_TYPE["copyDir"], "创建失败！指定的目录名： $dirTo 与文件名： $dirTo 不能相同。");
		$__errorString__ = "创建失败！指定的目录名： $dirTo 与文件名： $dirTo 不能相同。";
		return false;
	}
	
	if(!is_dir($dirFrom))
	{ //判断被拷贝的目录是否存在
		logto_file($_IMC_LOG_FILE["name"], $_IMC_LOG_TYPE["copyDir"], "需要拷贝的目录： $dirFrom 不存在！");
		$__errorString__ = "需要拷贝的目录： $dirFrom 不存在！";
		return false;
	}
	
	//回滚时不删除目录
	if(is_dir($dirTo) && $noticeString != 'Rollback')
	{ //指定目录如果存在则删除了所有内容。
		logto_file($_IMC_LOG_FILE["name"], $_IMC_LOG_TYPE["copyDir"], "指定目录： $dirTo 已经存在！");
		if (removeDir($dirTo))
		{
			logto_file($_IMC_LOG_FILE["name"], $_IMC_LOG_TYPE["copyDir"], "目录： $dirTo 删除成功！");
		}
		else
		{
			logto_file($_IMC_LOG_FILE["name"], $_IMC_LOG_TYPE["copyDir"], "目录： $dirTo 删除失败！");
			$__errorString__ = "目录： $dirTo 删除失败！";
			return false;
		}
	}
	
	//目录不存在，建立
	if(!is_dir($dirTo))
	{
		//新建目录
		if(!mkdir($dirTo))
		{
			logto_file($_IMC_LOG_FILE["name"], $_IMC_LOG_TYPE["copyDir"], "发生未知错误，目录： $dirTo 建立失败！有可能是权限不足造成的！");
			$__errorString__ = "发生未知错误，目录： $dirTo 建立失败！有可能是权限不足造成的！";
			return false;
		}
	}
	
	//打开目录
	$handle = opendir($dirFrom);
	while(($file_name = readdir($handle))!==false )
	{
		//被复制的文件路径
		$file_path_from = $dirFrom.DIRECTORY_SEPARATOR.$file_name;
		//复制到的文件路径
		$file_path_to = $dirTo.DIRECTORY_SEPARATOR.$file_name;
		
		if($file_name != "." && $file_name != ".." && $file_name != 'temp_download' && $file_name != 'temp_backup')
		{//当目录不是本目录. 和上级目录 .. 以及更新文件夹、备份文件夹的时候才允许复制
			if(is_dir($file_path_from))
			{//如果当前路径是目录
				copyDir($file_path_from,$file_path_to,$noticeString);
				$__countDir__ ++;
			}
			else
			{//如果不是目录则复制文件
				if(!copy($file_path_from, $file_path_to))
				{
					logto_file($_IMC_LOG_FILE["name"], $_IMC_LOG_TYPE["copyDir"], "复制文件 $file_path_from 时发生未知错误，有可能是权限不足！");
					$__errorString__ = "复制文件 $file_path_from 时发生未知错误，有可能是权限不足！";
					return false;
				}
				//每复制一个文件countFile 加1
				$__countFile__ ++;
				$rate = ((int)$__countFile__) / 8;
				
				//增加统计间隔，减少file IO
				if ($rate - $__rate__ > 0.5)
				{
					if($noticeString === 'Backup')
					{
						$status = array('Backup' => array('Waiting' => Array('Backup' => $rate)));
						setState(json_encode($status));					
					}
					else if ($noticeString === 'Rollback')
					{
						$status = array('Rollback' => array('Waiting' => Array('Rollback' => $rate)));
						setState(json_encode($status));	
					}
					$__rate__ = $rate;
				}
			}
		}
	}
	closedir($handle); //关闭目录
	return array($__countDir__, $__countFile__);
}// func copyDir

/*
 * 删除文件夹（非空也可删除）
 * $dirName : 目标文件夹
 * @return : 成功true
 */
function removeDir($dirName)
{
    $result = false;
    if(! is_dir($dirName))
    {
        trigger_error("目录名称错误", E_USER_ERROR);
    }
    $handle = opendir($dirName);
    while(($file = readdir($handle)) !== false)
    {
		//不能删除update文件夹，否则程序无法执行
        if($file != '.' && $file != '..')
        {
            $dir = $dirName . DIRECTORY_SEPARATOR . $file;
            is_dir($dir) ? removeDir($dir) : unlink($dir);
        }
    }
    closedir($handle);
    $result = rmdir($dirName) ? true : false;
    return $result;
}// func removeDir

/*
 * 回滚工程
 * $project_path : 工程全路径（为空时，直接使用 $_IMC["install_path"] . DIRECTORY_SEPARATOR . 'webim'）
 * @return : 成功返回ture，否则返回false
 */
function roll_back($project_path = null)
{
	global $_IMC;
	if ($project_path === null)
	{
		$project_path = $_IMC["install_path"].'webim';
	}
	
	if ($project_path[strlen($project_path)-1] === DIRECTORY_SEPARATOR)
	{
		$project_path = substr($project_path, 0, strlen($project_path)-1);
	}
	
	$_backup_project_path = $project_path . DIRECTORY_SEPARATOR . 'update' . DIRECTORY_SEPARATOR . 'temp_backup' . DIRECTORY_SEPARATOR . 'webim';
	
	$status = array('Rollback' => array('Waiting' => Array('Rollback' => 0)));
	setState(json_encode($status));
	return copyDir($_backup_project_path, $project_path, 'Rollback');
}// func roll_back

?>
