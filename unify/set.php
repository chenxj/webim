<?php
$platform = $_GET['platform'];
switch($platform){
	case 'discuz':
		include_once('discuz.php');
		break;
	case 'uchome':
		include_once('uchome.php');
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
