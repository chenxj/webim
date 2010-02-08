<?php 
$platform = $_GET['platform'];
switch($platform){
	case 'discuz':
	include_once('common_discuz.php');
	break;
	case 'uchome':
		include_once('common_uchome.php');
		break;
}
$ids = gp('ids');
if(empty($ids)){
        echo "[]";
        exit();
}
echo json_encode(find_buddy($ids));
?>
