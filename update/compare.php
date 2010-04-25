<?php
include_once("common.php");
include_once('../config.php');
/*$arrids = explode(',',$_IMC['admin_ids']);
if (!in_array($space['uid'],$arrids)){
	header("Location:{$_IMC['install_url']}");	
}
 */
error_reporting(0);

function get_user_file_hash($path)
{
    $user_file_index = file_get_contents($path);
    return  json_decode($user_file_index,TRUE);
}


function get_latest_file_hash() 
{
	$url = "http://update.nextim.cn/webim/update/file_index";
    $latest_file_index = file_get_contents($url);
    return  json_decode($latest_file_index,TRUE);
}

function compare_file_hash($latest_file_hash,$user_file_hash)
{
    $update_list = array();
    foreach($latest_file_hash as $rel_path => $data) {
        if( array_key_exists($rel_path,$user_file_hash) ){
            if($latest_file_hash[$rel_path]['md5'] == $user_file_hash[$rel_path]['md5'] )
                continue;
        }
        $temp = explode("/webim/",$data['abs_path']);
        $install_path = "/webim/" . $temp[1];
        $download_path = "http://update.nextim.cn" . $install_path;

        $update_list[$install_path] = $download_path;
    }
    return $update_list;
}


function get_downlaod_list()
{
    $path =   dirname(__file__) . "/file_index";
    $user_file_hash = get_user_file_hash($path);
    $latest_file_hash = get_latest_file_hash();

    return compare_file_hash($latest_file_hash,$user_file_hash);
}
echo json_encode(get_downlaod_list());

?>
