 <?php
	//global $ret;
	if(gp['cmd'] === 'ClearState'){
<<<<<<< .mine<?php
	include_once('./common.php');
	global $version_info;
	if(gp('cmd') === 'ClearState'){
=======<?php
	include_once('./common.php');
	global $version_info;
	if(gp('cmd') === 'ClearState'){
>>>>>>> .theirs		if(!clearState()){
			echo "false";
		}
		echo "true";
	}else if(gp('cmd') === 'GetNewestVersionInfo'){
		$version_info = getNewestVersionInfo();
		if(!$version_info){
			echo getCurrentState();
		}else{
<<<<<<< .mine			echo $version_info ;
=======			if(substr($version_info, -1) === "\n"){
				$version_info = substr($version_info, 0, -2);
			}
			echo $version_info;
>>>>>>> .theirs		}
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
<<<<<<< .mine?>

<?php
/*
'ClearState' �� ���webim��������#current_state�ļ������ݡ�
'GetNewestVersionInfo' �� �Ӹ��·�������ȡ���°汾��Ϣ���������°汾�š��汾����˵�����������ص��ļ��б�
'Update' �� ִ�и��¡�
'Rollback' �� ִ�лع���
*/
?>
=======?>>>>>>>> .theirs