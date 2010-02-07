<?php
include_once('common.php');
require 'http_client.php';
$ticket = gp('ticket');
$room_id = gp('id');
if(!empty($ticket)) {
	$data = array('ticket'=>$ticket, 'domain'=>$_IMC['domain'], 'apikey'=>$_IMC['apikey'], 'rooms'=>$room_id, 'endpoint' => $space['uid']);
	$client = new HttpClient($_IMC['imsvr'], $_IMC['impost']);
	$client->post('/room/members', $data);
	$pageContents = $client->getContent();
	echo $pageContents;
}
?>
