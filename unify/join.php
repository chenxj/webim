<?php
include_once('common.php');
require 'http_client.php';
$ticket = gp('ticket');
$room_id = gp('id');
$nick = gp('nick');
//TODO: should get nick from database
if(!empty($ticket)) {
    $data = array('ticket'=>$ticket, 'domain'=>$_IMC['domain'],'nick'=>$nick, 'apikey'=>$_IMC['apikey'], 'room'=>$room_id, 'endpoint' => $space['uid']);
	$client = new HttpClient($_IMC['imsvr'], $_IMC['impost']);
	$client->post('/room/join', $data);
	$pageContents = $client->getContent();
        if($client->status !="200"||empty($pageContents)){

          echo '{"errorMsg":"'.$pageContents.'"}';
	}else{
		echo '{"count":"'.$pageContents.'"}';
	}
}else{
	echo '{"errorMsg":"no ticket"}';
}
?>
