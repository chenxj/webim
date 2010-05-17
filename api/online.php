<?php
error_reporting(E_ALL);
$configRoot = '..' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR ;
include_once($configRoot . 'http_client.php');
include_once($configRoot . 'common.php');



$space = my_info();


/* if $friend_ids or $stranger_ids = Null
 *
 * Change into Array().
 * */




/* if $friend_ids or $stranger_ids = Null
 *
 * Change into Array().
 * */
$friend_ids = array();

$buddy_ids = ids_array(gp("buddy_ids"));//正在聊天的联系人

// Login webim server.
// $setting = setting();
// $block_list = is_array($setting->block_list) ? $setting->block_list : array();
$block_list = array();
$room_ids = array();
$rooms = find_room();
foreach($rooms as $room_id => $value){
	if(in_array($room_id, $block_list)){
		$rooms[$room_id]['blocked'] = true;
	} else {
        $rooms[$room_id]['pic_url'] = "webim/static/images/group_chat_head.png";
        $rooms[$room_id]['name'] = "来吧,激情plu!";
    }
    $room_ids[] = $room_id;
}

$param = array(
    'rooms'=> join(',', $room_ids),
    'buddies'=>join(',', array_unique(array_merge($friend_ids, $stranger_ids))), 
    'domain' => $_IMC['domain'], 
    'apikey' => $_IMC['apikey'], 
    'endpoint'=> $space['uid'], 
    'nick'=>$space['nick']
);

///
//var_dump($data);
///
$client = new HttpClient($_IMC['imsvr'], $_IMC['impost']);
$client->post('/presences/online', $param);
$pageContents = $client->getContent();
$pageData = json_decode($pageContents);

//var_dump($pageData);
if($client->status !="200"||empty($pageData->ticket)){
        $ticket ="";
}else
        $ticket = $pageData->ticket;
if(empty($ticket)){
        //登录失败
        echo '{status: "'.$client->status.'", "errorMsg":"'.$pageContents.'"}';
        exit();
}
//var_dump($pageData);
$buddy_online_ids = ids_array($pageData->buddies);//online ids
//$_SESSION['online_ids'] = $buddy_online_ids;
$clientnum = $pageData->clientnum;
$rooms_num = $pageData->roominfo;
if(is_object($rooms_num)){
	foreach($rooms_num as $key => $value){
		$rooms[$key]['count'] = $value;
	}
}
$output = array();
$output['buddy_online_ids'] = join(",", $buddy_online_ids);
$output['clientnum'] = $clientnum;
$output['server_time'] = microtime(true)*1000;
$output['user']=array(
    'id'=>$space['uid'], 
    'name'=> $space['nick'], 
    'pic_url'=>user_pic($space['uid']), 
    'status'=>'', 
    'presence' => 'online', 
    'status_time'=>'', 
    'url'=>'space.php?uid='.$space['uid']
);
//用户信息
$imserver = 'http://'.$_IMC['imsvr'].':'.$_IMC['impoll'];
$output['connection'] = array('domain' => $_IMC['domain'], 'ticket'=>$ticket, 'server'=>$imserver);//服务器连接

$output['new_messages'] = $new_messages;
///
foreach($buddy_online_ids as $id){
    if(in_array($id, $friend_ids)){
        $friends[] = $id;
    } else {
        $stranger[] = $id; 
    }
}
$output['buddies'] = find_buddy($strangers, $friends);
$output['rooms'] = $rooms;
# $output['histories'] = find_history($buddy_ids);
# new_message_to_histroy(); //新消息转到历史记录

echo json_encode($output);
?>
