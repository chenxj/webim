<?php
include_once("../config.php");
$platform = $_IMC['platform'] ? $_IMC['platform'] : $_GET['platform'];
$configRoot = '..' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR ;
include_once($configRoot . 'http_client.php');
include_once($configRoot . "{$platform}.php");
include_once("../config.php");
$platfrom = $_IMC['platform'];

session_start();

if($platform === "discuz"){
	if(!isset($_SESSION['timestamp']) || (gp('timestamp') - $_SESSION['timestamp'] > $_IMC['timestamp']*60)){//第一次登陆，获得好友列表，保存第一次登陆的时间戳
		require_once($_IMC['install_path'].'/config.inc.php');
		require_once($_IMC['install_path'].'/uc_client/client.php');
		$buddynum = uc_friend_totalnum($space['uid']);
		$buddies = uc_friend_ls($space['uid'], 1, $buddynum, $buddynum);
		foreach($buddies as $value){
			$friend_ids[] = $value['friendid'];
		}
		$_SESSION['timestamp'] = gp('timestamp');
		if(!isset($_SESSION['friend_ids'])){
			$_SESSION['friend_ids'] = $friend_ids;
		}
	}else{//不是第一次登陆，比较与上次登录的时间差，大于10分钟重新获取好友列表
		$friend_ids = $_SESSION['friend_ids'];
	}
}else if($platform === "uchome"){
	$friend_ids = ids_array($space['friends']);
}else if($platform === "phpwind"){
	if(!isset($_SESSION['timestamp']) || (gp('timestamp') - $_SESSION['timestamp'] > $_IMC['timestamp']*60)){
		$buddies = getFriends($_SGLOBAL['supe_uid'], 0, 0, false, true);
		foreach($buddies as $var){
			$friend_ids[] = $var['uid'];
		}
		$_SESSION['timestamp'] = gp('timestamp');
                if(!isset($_SESSION['friend_ids'])){
                        $_SESSION['friend_ids'] = $friend_ids;
                }
	}else{
		$friend_ids = $_SESSION['friend_ids'];
	}
}

if($platform !== "phpwind"){
	if(empty($space))exit();
	$name = to_utf8(nick($space));
	$stranger_ids = ids_except($space["uid"], ids_array(gp("stranger_ids")));//陌生人
}else if($platform === "phpwind"){
	$space = User_info();
	if(empty($space))exit();
	$name = $space['username'];
	$stranger_ids = ids_except($space["uid"], ids_array(gp("stranger_ids")));
	//$tmp = showfacedesign($space['uid'], 1, 'm');
	//var_dump($tmp);
	//echo $tmp[0];
}

/* if $friend_ids or $stranger_ids = Null
 *
 * Change into Array().
 * */
if(!$friend_ids){
    $friend_ids = array();
}
if(!$stranger_ids){
    $stranger_ids = array();
}

//var_dump($stranger_ids);
//modify by jinyu
//var_dump($_SESSION['uid']);
if(!isset($_SESSION['uid'])){
	$_SESSION['uid'] = $space["uid"];
}
if(!isset($_SESSION['stranger_ids'])){
	foreach($friend_ids as $id){
		$stranger_ids = ids_except($id, $stranger_ids);
	}
	$_SESSION['stranger_ids'] = $stranger_ids;
}else{
	if(empty($stranger_ids)){
		foreach($friend_ids as $id){
			$_SESSION['stranger_ids'] = ids_except($id, $_SESSION['stranger_ids']);
		}
		$stranger_ids = $_SESSION['stranger_ids'];
	}
}

$buddy_ids = ids_array(gp("buddy_ids"));//正在聊天的联系人

 $new_messages = find_new_message();//查找离线消息
for($i=0;$i<count($new_messages);$i++){
        $msg_uid = $new_messages[$i]["from"];
        array_push($buddy_ids, $msg_uid);
        array_push($stranger_ids, $msg_uid);
}

//Login webim server.
# $setting = setting();
$block_list = is_array($setting->block_list) ? $setting->block_list : array();

//fix by jinyu
if($platform == 'uchome'){
	$rooms = find_room();
	$room_ids = array();
}


if($platform == 'discuz'){
	$rooms = find_room(gp('room_ids'));
	$room_ids = array();
}

if($platform == 'phpwind'){
	if(gp('room_ids') != ""){
		$rooms = find_room(gp('room_ids'));
	}else if(gp('tid') != ""){
		$rooms = find_fid(gp('tid'));
	}
	$room_ids = array();
}

foreach($rooms as $key => $value){
	if(in_array($key, $block_list)){
		$rooms[$key]['blocked'] = true;
	}else
        	$rooms[$key]['pic_url'] = "webim/static/images/group_chat_head.png";
	array_push($room_ids, $rooms[$key]['id']);
}

//fix by jinyu
if($platform == 'uchome'){
	$data = array ('rooms'=> join(',', $room_ids),'buddies'=>join(',', array_unique(array_merge($friend_ids, $buddy_ids, $stranger_ids))), 'domain' => $_IMC['domain'], 'apikey' => $_IMC['apikey'], 'endpoint'=> $space['uid'], 'nick'=>to_unicode($name));
}else if($platform == 'discuz' || $platform == 'phpwind'){
	if($platform == "phpwind"){
		require(WEBIM_ROOT . '/config.php');
	}
	$data = array ('rooms'=> join(',', $room_ids),'buddies'=>join(',', array_unique(array_merge($friend_ids, $stranger_ids))), 'domain' => $_IMC['domain'], 'apikey' => $_IMC['apikey'], 'endpoint'=> $space['uid'], 'nick'=>$name);
}
///
//var_dump($data);
///
$client = new HttpClient($_IMC['imsvr'], $_IMC['impost']);
$client->post('/presences/online', $data);
$pageContents = $client->getContent();
//TODO: handle errors!
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
if($platform !== "phpwind"){
	$output['user']=array('id'=>$space['uid'], 'name'=>$name, 'pic_url'=>user_pic($space['uid']), 'status'=>'', 'presence' => 'online', 'status_time'=>'', 'url'=>'space.php?uid='.$space['uid']);//用户信息
}else if($platform === "phpwind"){
	$pic = showfacedesign($space['icon'], 1, 'm');
	$output['user']=array('id'=>$space['uid'], 'name'=>$name, 'pic_url'=>$pic[0], 'status'=>'', 'presence' => 'online', 'status_time'=>'', 'url'=>"");//用户信息
}

$imserver = 'http://'.$_IMC['imsvr'].':'.$_IMC['impoll'];
$output['connection'] = array('domain' => $_IMC['domain'], 'ticket'=>$ticket, 'server'=>$imserver);//服务器连接

$output['new_messages'] = $new_messages;
///
if($platform === 'uchome'){
	$output['buddies'] = find_buddy($buddy_ids);
}else if($platform === 'discuz' || $platform === 'phpwind'){
	foreach($buddy_online_ids as $id){
		if(in_array($id, $friend_ids)){
			$friends[] = $id;
        } else {
            $stranger[] = $id; 
        }
	}
	$output['buddies'] = find_buddy($strangers, $friends);
}
$output['rooms'] = $rooms;
 $output['histories'] = find_history($buddy_ids);
# new_message_to_histroy(); //新消息转到历史记录

echo json_encode($output);
?>
