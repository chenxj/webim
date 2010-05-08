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
		break;
}
if($platform === "phpwind")
{
	$ids = gp('ids');
	if(empty($ids)){
        	echo "[]";
        	exit();
	}
	echo json_encode(find_buddy($ids));
        //$output['buddies'] = find_buddy($buddy_ids);
}




if($platform === 'uchome'){
	$ids = gp('ids');
	if(empty($ids)){
        	echo "[]";
        	exit();
	}
	echo json_encode(find_buddy($ids));
        //$output['buddies'] = find_buddy($buddy_ids);
}
if($platform === 'discuz'){
/*
	session_start();
	$friends = $_SESSION['friend_ids'];
	$strangers = $_SESSION['stranger_ids'];
	$buddy_online_ids = $_SESSION['online_ids'];
        foreach($friend_ids as $id){
                if(in_array($id, $buddy_online_ids)){
                        $friends[] = $id;
                }
                $strangers = ids_except($id, $buddy_online_ids);
        }
        echo json_encode(find_buddy($strangers, $friends));
*/
$ids = gp('ids');
        if(empty($ids)){
                echo "[]";
                exit();
        }
        echo json_encode(find_buddy($ids));

}
?>
