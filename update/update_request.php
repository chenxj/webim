<?php
	//global $ret;
	if(gp['cmd'] === 'ClearState'){
		if(!clearState()){
			echo "false";
		}
		echo "true";
	}else if(gp['cmd'] === 'GetNewestVersionInfo'){
		$ret = getNewestVersionInfo();
		if(!$ret){
			echo json_encode(array('status'=>0, 'info'=>'NoUpdatesAvaliable', 'iserror'=>'false', 'isrollbackable'=>'false'));
		}else{
			echo json_encode(array('status'=>1, 'info'=>$ret, 'iserror'=>'false', 'isrollbackable'=>'false'));
		}
	}else if(gp['cmd'] === 'Update'){
		if(!update($version)){
			# 更新失败
		}else{
			# 更新成功
		}
	}else if(gp['cmd'] === 'Rollback'){
		# backup_project($project_path = null)
	}else if(gp['cmd'] === 'GetCurrentState'){
		return getCurrentState();
	}
?>

'ClearState' ： 清除webim服务器上#current_state文件的内容。
'GetNewestVersionInfo' ： 从更新服务器读取最新版本信息，包括最新版本号、版本更新说明、所需下载的文件列表。
'Update' ： 执行更新。
'Rollback' ： 执行回滚。
json_encode(array('status'=>5, 'info'=>'ClearStateFailed', 'iserror'=>'true', 'isrollbackable'=>'false'));