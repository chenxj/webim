<?php

include_once("common.php");
include_once('../config.php');
/*
$arrids = explode(',',$_IMC['admin_ids']);
if (!in_array($space['uid'],$arrids)){
	header("Location:{$_IMC['install_url']}");	
}
*/
$url = "http://update.nextim.cn/webim/update/version";
$latest_version = explode("\n",file_get_contents($url));
$latest_version = $latest_version[0];
$cur_version = $_IMC['version'];
$data = array("version"=>$latest_version);
if ($latest_version != $cur_version){
    $data['update_now'] = true; 
}
else
    $data['update_now'] = false;

echo json_encode($data);
?>
