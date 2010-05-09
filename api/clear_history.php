<?php
$platform = $_GET['platform'];
$configRoot = '..' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR ;
switch($platform){
	case 'discuz':
		include_once($configRoot . 'discuz.php');
		break;
	case 'uchome':
		include_once($configRoot . 'uchome.php');
		break;
	case 'phpwind':
		include_once($configRoot . 'phpwind.php');
		$space = User_info();
		break;

}

$uid = $space['uid'];
$ids = ids_array(gp("ids"));
if(!empty($ids)){
        for($i=0;$i<count($ids);$i++){
                $id = $ids[$i];
                $_SGLOBAL['db']->query("UPDATE ".im_tname('histories')." SET fromdel=1 WHERE `from`='$uid'");
                $_SGLOBAL['db']->query("UPDATE ".im_tname('histories')." SET todel=1 WHERE `to`='$uid'");
        }
}
echo '{success:true}';
?>
