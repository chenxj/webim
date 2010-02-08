<?php
$platform = $_GET['platform'];
switch($platform){
	case 'discuz':
	include_once('common_discuz.php');
	break;
	case 'uchome':
		include_once('common_uchome.php');
		break;
}
$type=gp('type');
$value=gp('value');
if(!empty($type)){
	$type = str_replace('_','',$type);
	$value=$value=='true'?true:false;
	updatetable('im_config', array($type=>$value), array('uid'=>$space['uid']));
	
	die("{success:true}");
}
