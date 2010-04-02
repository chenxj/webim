<?php 
$platform = $_GET['platform'];
$configRoot = $_IMC["install_path"] . 'webim/api/';
switch($platform){
	case 'discuz':
		include_once($configRoot . 'discuz.php');
		break;
	case 'uchome':
		include_once($configRoot . 'uchome.php');
		break;
}
$ids = gp('ids');
if($ids===NULL){
        echo "{empty}";
        exit();
}
//echo json_encode($ids);
echo json_encode(find_history($ids));
?>
