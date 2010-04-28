<?php
include_once('common.php');
if(getDownloadList()){
	echo json_encode(array("state"=>"DownloadList", "isok"=>true, "iswait"=>false, "errmsg"=>"", "percent"=>""));
}else{
	echo json_encode(array("state"=>"DownloadList", "isok"=>false, "iswait"=>false, "errmsg"=>"Get download_list failed", "percent"=>""));
}
?>