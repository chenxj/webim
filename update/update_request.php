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
			# ����ʧ��
		}else{
			# ���³ɹ�
		}
	}else if(gp['cmd'] === 'Rollback'){
		# backup_project($project_path = null)
	}else if(gp['cmd'] === 'GetCurrentState'){
		return getCurrentState();
	}
?>

'ClearState' �� ���webim��������#current_state�ļ������ݡ�
'GetNewestVersionInfo' �� �Ӹ��·�������ȡ���°汾��Ϣ���������°汾�š��汾����˵�����������ص��ļ��б�
'Update' �� ִ�и��¡�
'Rollback' �� ִ�лع���
json_encode(array('status'=>5, 'info'=>'ClearStateFailed', 'iserror'=>'true', 'isrollbackable'=>'false'));