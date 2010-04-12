<?php
include_once("../json.php"); # json 类

if( !function_exists('json_decode') ) { 
    function json_encode($data) { 
        $json = new Services_JSON(); 
        return( $json->encode($data) ); 
    } 
} 

if( !function_exists('json_decode') ) 
{ 
    function json_decode($data, $bool) { 
        if ($bool) { 
            $json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE); 
        } 
        else { 
            $json = new Services_JSON(); 
        } 
        return( $json->decode($data) ); 
    } 
}


function get_user_file_hash($path)
{
    $user_file_index = file_get_contents($path);
    return  json_decode($user_file_index,TRUE);
}

function get_latest_file_hash() 
{
//    $url = "http://update.nextim.cn/latest_file_index";
    $url = "latest_file_index";
    $latest_file_index = file_get_contents($url);
    return  json_decode($latest_file_index,TRUE);
}

function get_download_list($latest_file_hash,$user_file_hash)
{
    $download_list = array();
    foreach($latest_file_hash as $rel_path => $data) {
        if( array_key_exists("md5",$data) ){
            if($latest_file_hash[$rel_path]['md5'] != $user_file_hash[$rel_path]['md5'] ){
                array_push($download_list,$data['abs_path']);
            }
        }
        else {
                array_push($download_list,$data['abs_path']);
        }
    }
    return $download_list;
}


#==========
#
function run()
{
    $path = "../file_index";
    $user_file_hash = get_user_file_hash($path);
#    var_dump($user_file_hash);
    $latest_file_hash = get_latest_file_hash();
#    var_dump($latest_file_hash);

    $s = get_download_list($latest_file_hash,$user_file_hash);
    var_dump($s);


}

run();





?>
