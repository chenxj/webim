<?php 
$platform = $_GET['platform'];
switch($platform){
	case 'discuz':
		include_once('common_discuz.php');
		break;
	case 'uchome':
		include_once('common_uchome.php');
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
#$time = time();
if(empty($to)||empty($from)){
	echo '{success:false}';exit();
}



$client = new HttpClient($_IMC['imsvr'], $_IMC['impost']);
$nick = to_unicode(to_utf8(nick($space)));
$client->post('/messages', array('domain'=>$_IMC['domain'],'apikey'=>$_IMC['apikey'],'ticket' => $ticket,'nick'=>$nick, 'type'=> $type, 'to'=>$to,'body'=>to_unicode($body),'timestamp'=>$time,'style'=>$style));
$pageContents = $client->getContent();

//TODO:send => true if forward message successfully.
//
$body = from_utf8($body);
$columns = "`uid`,`send`,`to`,`from`,`style`,`body`,`timestamp`,`type`";
$values_from = "'$from','1','$to','$from','$style','$body','$time','$type'";
$values_to = "'$to','$send','$to','$from','$style','$body','$time','$type'";
$_SGLOBAL['db']->query("INSERT INTO ".im_tname('histories')." ($columns) VALUES ($values_to)");

$output = array();
$output["success"] = $send;
$output["msg"] = $pageContents;
#$output['timestamp'] = time();
echo json_encode($output);
?>
