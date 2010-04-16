<?php
	include_once('./common.php');
	global $version_info;
	if(gp('cmd') === 'ClearState'){
		if(!clearState()){
			echo "false";
		}
		echo "true";
	}else if(gp('cmd') === 'GetNewestVersionInfo'){
		$version_info = getNewestVersionInfo();
		if(!$version_info){
			echo getCurrentState();
		}else{
			echo $version_info
		}
	}else if(gp('cmd') === 'Update'){
		if(!update($version)){
			echo getCurrentState();
		}else{
			echo getCurrentState();
		}
	}else if(gp('cmd') === 'Rollback'){
		roll_back();
	}else if(gp('cmd') === 'GetCurrentState'){
		echo getCurrentState();
	}
?>

<?php
/*
'ClearState' ： 清除webim服务器上#current_state文件的内容。
'GetNewestVersionInfo' ： 从更新服务器读取最新版本信息，包括最新版本号、版本更新说明、所需下载的文件列表。
'Update' ： 执行更新。
'Rollback' ： 执行回滚。
*/
?>
