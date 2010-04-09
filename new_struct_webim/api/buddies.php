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
}
$ids = gp('ids');
if(empty($ids)){
        echo "[]";
        exit();
}
echo json_encode(find_buddy($ids));
?>
