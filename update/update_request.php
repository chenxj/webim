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
			if(substr($version_info, -1) === "\n"){
				$version_info = substr($version_info, 0, -2);
			}
			echo $version_info;
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