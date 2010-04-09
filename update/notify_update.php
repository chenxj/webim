<<<<<<< HEAD
﻿<?php
=======
<?php
>>>>>>> 69616df03b1c6bafe22de571f7c44d7059c1e965
$platform = $_GET['platform'];
switch($platform){
	case 'discuz':
		include_once('discuz.php');
		break;
	case 'uchome':
		include_once('uchome.php');
		break;
}

$version_info = file_get_contents($_IMC['update_url']."publish/NewestVersionInfo");
if($version_info){
	$new_version = array();
	$temp_info = json_decode($version_info);
	foreach($temp_info as $key=>$value){
		$new_version[$key] = $value;
	}
}else{
	exit();
}

if($new_version['Version'] > $_IMC['version']){
	$time = microtime(true)*1000;
	$body = from_utf8("WebIM有新的更新！请访问{$IMC['install_url']}update/index.php了解详情");
	$columns = "`uid`,`send`,`to`,`from`,`style`,`body`,`timestamp`,`type`";
	$values_from = "'1','0','1','1','','$body','$time','unicast'";
	$_SGLOBAL['db']->query("INSERT INTO ".im_tname('histories')." ($columns) VALUES ($values_from)");
	@touch($_IMC['version']);
}
?>