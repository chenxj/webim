<?php
include_once("common.php");

$url = "http://update.nextim.cn/webim/update/version_info";
$_data = explode("\n",file_get_contents($url));
$data = array();
foreach($_data as $item){
    if($item)
        array_push($data,$item);
}
echo json_encode($data);
?>
