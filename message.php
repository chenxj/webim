<?php 
$platform = $_GET['platform'];
switch($platform){
	case 'discuz':
		include_once('discuz.php');
		break;
	case 'uchome':
		include_once('uchome.php');
		break;
}
require 'http_client.php';

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
$nick = to_unicode(to_utf8(nick($space)));
$client->post('/messages', array('domain'=>$_IMC['domain'],'apikey'=>$_IMC['apikey'],'ticket' => $ticket,'nick'=>$nick, 'type'=> $type, 'to'=>$to,'body'=>to_unicode($body),'timestamp'=>(string)$time,'style'=>$style));
$pageContents = $client->getContent();

//TODO:send => true if forward message successfully.
//
$body = from_utf8($body);
$columns = "`uid`,`send`,`to`,`from`,`style`,`body`,`timestamp`,`type`";
if($type=="multicast"){//add by free.wang
    $to = $to + $_IMC['room_id_pre'];//add by free.wang
}//add by free.wang

//add by Harvey.
if ($type == "broadcast"){
	if(strpos($_IMC["admin_ids"], $from) !== false){
		$values_from = "'$from','1','$to','$from','$style','$body','$time','$type'";
        	$values_to = "'$to','$send','$to','$from','$style','$body','$time','$type'";
        	$_SGLOBAL['db']->query("INSERT INTO ".im_tname('histories')." ($columns) VALUES ($values_from)");
	}
	//check updates
	if(!file_exists($_IMC['install_path'].'webim'.DIRECTORY_SEPARATOR.'update'.DIRECTORY_SEPARATOR.$_IMC['version'].'.lock'))
		require_once('./update/notify_update.php');
}
else{
	$values_from = "'$from','1','$to','$from','$style','$body','$time','$type'";
	$values_to = "'$to','$send','$to','$from','$style','$body','$time','$type'";
	$_SGLOBAL['db']->query("INSERT INTO ".im_tname('histories')." ($columns) VALUES ($values_from)");
}

$output = array();
$output["success"] = $send;
$output["msg"] = $pageContents;
echo json_encode($output);
?>
