<?php

include_once("common.php");

$url = "http://update.nextim.cn/webim/update/version";
$latest_version = explode("\n",file_get_contents($url));
$cur_version = $_IMC['version'];
$data = array("version"=>$latest_version[0]);
if ($latest_version == $cur_version){
    $data['updata_now'] = 0; 
}
else
    $data['updata_now'] = 1;

echo json_encode($data);
?>
