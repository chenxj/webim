<?php
include_once('common.php');
if(roll_back()){
	echo json_encode(array("state"=>"Rollback", "isok"=>true, "iswait"=>false, "errmsg"=>"", "percent"=>"100"));
}else{
	echo json_encode(array("state"=>"Rollback", "isok"=>false, "iswait"=>false, "errmsg"=>"Rollback failed", "percent"=>"0"));
}
?>