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

function insertconfig($s, $find, $replace) { # 添加配置文件
	if(preg_match($find, $s)) {
		$s = preg_replace($find, $replace, $s);
	} else {
		$s .= "\r\n".$replace;
	}
	return $s;
}

function update_config($version){ # 修改配置文件版本号
	$fp = fopen('./config.php', 'r');
	$configfile = fread($fp, filesize('./config.php'));
	$configfile = trim($configfile);
	$configfile = substr($configfile, -2) == '?>' ? substr($configfile, 0, -2) : $configfile;
	$configfile = insertconfig($configfile, '/\$_IMC\["version"\] =\s*.*?;/i', '$_IMC["version"] = "'.$version.'";');
	$fp = fopen('./config.php', 'w');
	@fwrite($fp, trim($configfile));
	@fclose($fp);
}

/*
 * 记录日志
 * @$file_name : 日志文件
 * $type_string : 日志类型字符串
 * $content_string : 日志内容
 */
function logto_file($file_name, $type_string, $content_string){
	global $_IMC_BACKUP;
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
}// func logt0_file

/*
 * 备份旧工程
 * @$project_path : 需要备份的工程路径。
 * $version_string : 版本名称。
 * @return : 成功返回ture，否则返回false
 */
function backup_project($project_path, $version_string){

}// func backup_project

/*
 * 更新文件
 * @$file_list : 需要更新的文件列表以及内容，格式：Array('file_name'=>'file_content', 'file_name_2'=>'file_content_2')
 * @return : 成功返回ture，否则返回false
 */
function update_file($file_list){
	global $_IMC_BACKUP;
	global $_IMC_LOG_FILE;
	global $_IMC_LOG_TYPE;
	global $new_version;
	$count = 0;
	$files = "";
	foreach($file_list as $filename => $content){
		
		$pathpart = pathinfo($filename);
		if (!is_dir($pathpart["dirname"]))
		{
			if(!mkdir($pathpart["dirname"]))
			{
				logto_file($_IMC_LOG_FILE["name"], $_IMC_LOG_TYPE["update_file"], "创建文件夹：$pathpart 失败！");
				return false;
			}
		}
	
		if(!$handle = fopen($filename, 'wb')){
			logto_file($_IMC_LOG_FILE["name"], $_IMC_LOG_TYPE["update_file"], "不能打开文件 $filename");
			return false;
		}
		if($content != "M"){// Non-Multi-media file type
			if(fwrite($handle, $content) === FALSE){
				logto_file($_IMC_LOG_FILE["name"], $_IMC_LOG_TYPE["update_file"], "不能写入到文件 $filename");
				return false;
			}
		}else{// Multi-media file type
			if(fwrite($handle, file_get_contents($_IMC['update_url'].$new_version.substr($filename, 1))) === FALSE){
				logto_file($_IMC_LOG_FILE["name"], $_IMC_LOG_TYPE["update_file"], "不能写入到文件 $filename");
				return false;
			}
		}
		fclose($handle);
		$count ++;
		$files = $files . "\n" . $filename;
	}
	logto_file($_IMC_LOG_FILE["name"], $_IMC_LOG_TYPE["update_file"], "更新文件成功：$files\n总计：$count");
	return true;
}// func update_file

/*
 * 复制文件夹
 * $dirFrom : 源文件夹
 * $dirTo :目标文件夹（如果已经存在则删除）
 * @return : array("文件夹数"，"文件数目")
 */
function copyDir($dirFrom,$dirTo){
	global $_IMC_BACKUP;
	global $_IMC_LOG_FILE;
	global $_IMC_LOG_TYPE;
	$countFile = 0; //用于统计文件总数的变量
	$countDir = 0; //用于统计目录总数的变量
	
	if(is_file($dirTo))
	{ //判断目录是否与文件重名
		logto_file($_IMC_LOG_FILE["name"], $_IMC_LOG_TYPE["copyDir"], "创建失败！指定的目录名： $dirTo 与文件名： $dirTo 不能相同。");
		return false;
	}
	
	if(!is_dir($dirFrom))
	{ //判断被拷贝的目录是否存在
		logto_file($_IMC_LOG_FILE["name"], $_IMC_LOG_TYPE["copyDir"], "需要拷贝的目录： $dirFrom 不存在！");
		return false;
	}
	
	if(is_dir($dirTo))
	{ //指定目录如果存在则删除了所有内容。
		logto_file($_IMC_LOG_FILE["name"], $_IMC_LOG_TYPE["copyDir"], "指定目录： $dirTo 已经存在！");
		if (removeDir($dirTo))
		{
			logto_file($_IMC_LOG_FILE["name"], $_IMC_LOG_TYPE["copyDir"], "目录： $dirTo 删除成功！");
		}
		else
		{
			logto_file($_IMC_LOG_FILE["name"], $_IMC_LOG_TYPE["copyDir"], "目录： $dirTo 删除失败！");
			return false;
		}
	}
	
	//新建目录
	if(!mkdir($dirTo))
	{
		logto_file($_IMC_LOG_FILE["name"], $_IMC_LOG_TYPE["copyDir"], "发生未知错误，目录： $dirTo 建立失败！有可能是权限不足造成的！");
		return false;
	}
	$countDir++;
	
	//打开目录
	$handle = opendir($dirFrom);
	while(($file_name = readdir($handle))!==false )
	{
		//被复制的文件路径
		$file_path_from = $dirFrom.DIRECTORY_SEPARATOR.$file_name;
		//复制到的文件路径
		$file_path_to = $dirTo.DIRECTORY_SEPARATOR.$file_name;
		

		
		if($file_name!="." && $file_name!="..")
		{//当目录不是本目录. 和上级目录 .. 的时候才允许复制
			if(is_dir($file_path_from))
			{//如果当前路径是目录
				if(($res = copyDir($file_path_from,$file_path_to)) !== false)
				{//递归调用函数本身
					$countDir += $res[0];
					$countFile += $res[1];
				}
			}
			else
			{//如果不是目录则复制文件
				if(!copy($file_path_from, $file_path_to))
				{
					logto_file($_IMC_LOG_FILE["name"], $_IMC_LOG_TYPE["copyDir"], "复制文件 $file_path_from 时发生未知错误，有可能是权限不足！");
					return false;
				}
				$countFile++;//每复制一个文件countFile 加1
			}
		}
	}
	closedir($handle); //关闭目录
	return array($countDir, $countFile);
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

?>
