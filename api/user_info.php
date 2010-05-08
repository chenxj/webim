<?php 
$platform = $_GET['platform'];
include_once("../lib/{$platform}.php");

$ids = gp('ids');
if(empty($ids)){
        echo "[]";
        exit();
}
echo json_encode(find_buddy($ids));
        //$output['buddies'] = find_buddy($buddy_ids);
exit;




if($platform === 'uchome'){
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
		break;
}
?>
