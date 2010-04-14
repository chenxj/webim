<?php
include_once('common.php');
$version = gp('version');
if(update("2.2.2")){
	echo json_encode(array("state"=>"Update", "isok"=>true, "iswait"=>false, "errmsg"=>"", "percent"=>"100"));
}else{
	echo json_encode(array("state"=>"Update", "isok"=>false, "iswait"=>false, "errmsg"=>"Update failed", "percent"=>"0"));
}
?>