<?php 
$platform = $_GET['platform'];
$configRoot = '..' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR ;
include_once($configRoot . 'http_client.php');
switch($platform){
	case 'discuz':
		include_once($configRoot . 'discuz.php');
		break;
	case 'uchome':
		include_once($configRoot . 'uchome.php');
		break;
	case 'phpwind':
		include_once($configRoot . 'phpwind.php');
		$space = User_info();
		break;
}
$platform = $_GET['platform'];

$ticket = gp('ticket');
$body = gp('body','');
$style = gp('style','');
$to = gp('to');
$send = gp('offline') == "1" ? false : true;
$type = gp('type');
$from = $space['uid'];
$time = microtime(true)*1000;
//change by chenxj
if($type != "broadcast" && (empty($to)||empty($from))){
	echo "{success:false}"."{".$to.":".$from."}";exit();
}
$client = new HttpClient($_IMC['imsvr'], $_IMC['impost']);
if($platform !== 'phpwind'){
	$nick = to_unicode(to_utf8(nick($space)));
}else if($platform === 'phpwind'){
	$nick = to_unicode($space['username']);
}
$client->post('/messages', array('domain'=>$_IMC['domain'],'apikey'=>$_IMC['apikey'],'ticket' => $ticket,'nick'=>$nick, 'type'=> $type, 'to'=>$to,'body'=>to_unicode($body),'timestamp'=>(string)$time,'style'=>$style));
$pageContents = $client->getContent();

//TODO:send => true if forward message successfully.
//
$columns = "`send`,`to`,`from`,`style`,`body`,`timestamp`,`type`";
if($type=="multicast"){//add by free.wang
    $to = $to + $_IMC['room_id_pre'];//add by free.wang
}//add by free.wang

if($platform !== "phpwind"){
	$_SGLOBAL['db']->query("SET NAMES " . UC_DBCHARSET);
}else if($platform === "phpwind"){
	if($db_charset === "utf-8"){
		$mycharset = "utf8";
	}
	$db->query("SET NAMES " . $mycharset);
}

$body=from_utf8($body);
//add by Harvey.
if ($type == "broadcast"){
	if(strpos($_IMC["admin_ids"], $from) !== false){
		$values_from = "'1','$to','$from','$style','$body','$time','$type'";
	        $_SGLOBAL['db']->query("INSERT INTO ".im_tname('histories')." ($columns) VALUES ($values_from)");
	}
	require_once('../update/notify_update.php');
}
else{
	$values_from = "'1','$to','$from','$style','$body','$time','$type'";
	$_SGLOBAL['db']->query("INSERT INTO ".im_tname('histories')." ($columns) VALUES ($values_from)");
}

$output = array();
$output["success"] = $send;
$output["msg"] = $pageContents;
echo json_encode($output);
?>
