<?php
include_once("../config.php");
$url = "http://update.nextim.cn/webim/update/version";
$latest_version = explode("\n",file_get_contents($url));
$latest_version = $latest_version[0];
$cur_version = $_IMC['version'];
$data = array("version"=>$latest_version);
if ($latest_version == $cur_version){
    //do nothing
}
else{
    $time = microtime(true)*1000;
	$body = from_utf8("WebIM有新的更新！请访问".$_IMC['install_url']."update/index.php了解详情!");
	$columns = "`uid`,`send`,`to`,`from`,`style`,`body`,`timestamp`,`type`";
	$values_from = "'1','0','1','webim','','$body','$time','unicast'";
	$_SGLOBAL['db']->query("INSERT INTO ".im_tname('histories')." ($columns) VALUES ($values_from)");
}
?>