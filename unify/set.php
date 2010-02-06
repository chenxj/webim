<?php
include_once('common.php');
$type=gp('type');
$value=gp('value');
if(!empty($type)){
	$type = str_replace('_','',$type);
	$value=$value=='true'?true:false;
	updatetable('im_config', array($type=>$value), array('uid'=>$space['uid']));
	
	die("{success:true}");
}
