<?php 
$configRoot = '..' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR ;
include_once($configRoot . 'http_client.php');
include_once($configRoot . 'common.php');

$space = my_info();

$ticket = gp('ticket');
$body = gp('body','');
$style = gp('style','');
$to = gp('to');
$send = gp('offline') == "1" ? false : true;
$type = gp('type');
$from = $space['uid'];
$nick = $space['nick'];
$time = microtime(true)*1000;



//change by chenxj
if($type != "broadcast" && (empty($to)||empty($from))){
	echo "{success:false}"."{".$to.":".$from."}";exit();
}
$client = new HttpClient($_IMC['imsvr'], $_IMC['impost']);
$client->post('/messages', array('domain'=>$_IMC['domain'],'apikey'=>$_IMC['apikey'],'ticket' => $ticket,'nick'=>$nick, 'type'=> $type, 'to'=>$to,'body'=>to_unicode($body),'timestamp'=>(string)$time,'style'=>$style));
$pageContents = $client->getContent();

//TODO:send => true if forward message successfully.
//

$output = array();
$output["success"] = $send;
$output["msg"] = $pageContents;
echo json_encode($output);
?>
