<?php
include_once('common.php');
include_once('../config.php');
/*
$arrids = explode(',',$_IMC['admin_ids']);
if (!in_array($space['uid'],$arrids)){
	header("Location:{$_IMC['install_url']}");	
}
*/

function _get_version()
{
    global $_IMC;
    $url = $_IMC['install_url'] . "webim/update/check.php";
    $res = file_get_contents($url);
    $data = json_decode($res,TRUE);
    return  $data['version'];
}

function _get_download_index()
{
    global $_IMC;
    $url = $_IMC['install_url'] . "webim/update/compare.php";
    $res = file_get_contents($url);
    return json_decode($res,TRUE);
}


function run($version,$download_index){ # 执行更新, 参数是将更新到的版本(新版) global $_IMC, $_IMC_LOG_FILE;
    global $_IMC;
	global $_IMC_LOG_FILE;
	global $_IMC_LOG_TYPE;

    try{ set_time_limit(0);}catch(Exception $e){}

	if(!setState(setStatus("Download", "Waiting", array("Download"=>0)))){
		logto_file($_IMC_LOG_FILE["name"], "SetState", "下载更新文件:写入状态失败！\n");
		return false;
	}
/*	
	if(!$download_index){
        $data =array(
            "state"=>"Update", 
            "isok"=>false, 
            "iswait"=>false, 
            "errmsg"=>"Read download_index file error! Check your permission", 
            "percent"=>""
        );
		echo json_encode($data);
		return false;
	}
*/
	if(!$download_index){
		if(!setState(setStatus("Download", "Invalid"))){
			logto_file($_IMC_LOG_FILE["name"], "SetState", "载入更新列表失败:写入状态失败！\n");
			return false;
		}
	}
	
    $temp_download_path =  dirname(__FILE__).DIRECTORY_SEPARATOR.'temp_download';
    if(file_exists($temp_download_path))
    {
        removeDir($temp_download_path);// 删除临时目录下所有文件
    }
	mkdir($temp_download_path);// 存放下载的临时更新文件
	chmod($temp_download_path,0777);// 存放下载的临时更新文件
	
	$total = count($download_index);// 下载文件总数
	$update_list = array();// 更新路径列表
	$num = 0;
	$remain = 3;// 下载失败尝试次数
	$success = false;
	$complete = true;
    foreach($download_index as $i_path=>$d_path)
    {   // 下载更新文件 $d_path--download路径, $i_path  install路径
        while($remain > 0 && !$success)
        {
            $content = file_get_contents($d_path);
            if(!$content){// if download failed
                if(-- $remain > 0){
                    continue;// break while-loop
                }else{
                    echo json_encode(array("state"=>"Update", "isok"=>false, "iswait"=>false, "errmsg"=>"Download update file error!", "percent"=>""));
                    $complete = false;
                    break;
                }
            }
			$i_path = ($i_path[0] === '/')?substr($i_path, 1):$i_path;
			$prefix = explode("/", substr($i_path, 0, strrpos($i_path, '/')));
            $update_list[IM_ROOT.substr($i_path, 6)] = dirname(__FILE__).DIRECTORY_SEPARATOR.'temp_download'.DIRECTORY_SEPARATOR.join("_", $prefix)."____".substr(strrchr($i_path, '/'), 1);

            write_downlaod_file($i_path,$content);

            if(!setState(setStatus("Download", "Waiting", array("Download"=>$num*100/$total)))){
                logto_file($_IMC_LOG_FILE["name"], "SetState", "下载文件过程:写入状态失败！\n");
                return false;
            }
            $success = true;
		}// while-loop
        //echo "</br> {$i_path} has downloaded!</br>";
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
		chmod('./temp_backup',0777);
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
	//include_once("run_after_update.php");
	return true;
}// func update



$version = _get_version();
$download_index  = _get_download_index();


if(run($version,$download_index)){
	echo json_encode(array("state"=>"Update", "isok"=>true, "iswait"=>false, "errmsg"=>"", "percent"=>"100"));
}else{
	echo json_encode(array("state"=>"Update", "isok"=>false, "iswait"=>false, "errmsg"=>"Update failed", "percent"=>"0"));
}
?>
