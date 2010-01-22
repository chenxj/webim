<?php 
include_once('common.php');
$ids = gp('ids');
if(empty($ids)){
        echo "{}";
        exit();
}
echo json_encode(find_history($ids));
?>
