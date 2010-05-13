<?php 
$platform = $_GET['platform'];
include_once("../lib/{$platform}.php");
$ids = gp('ids');
if($ids===NULL){
        echo "{empty}";
        exit();
}
//echo json_encode($ids);
echo json_encode(find_history($ids));
?>
