<?php 
$platform = $_GET['platform'];
switch($platform){
	case 'discuz':
		include_once('discuz.php');
		break;
	case 'uchome':
		include_once('uchome.php');
		break;
}
$ids = gp('ids');
if(empty($ids)){
        echo "[]";
        exit();
}
echo json_encode(find_buddy($ids));
?>
