<?php
$platform = $_GET['platform'];
$configRoot = $_IMC["install_path"] . 'webim/api/';
$configRoot = $_IMC["install_path"] . 'webim/api/';
$configRoot = '..' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR ;
include_once($configRoot . 'http_client.php');

switch($platform){
	case 'discuz':
		include_once($configRoot . 'discuz.php');
		break;
	case 'uchome':
		include_once($configRoot . 'uchome.php');
		break;
}
$ticket = gp('ticket');
$room_id = gp('id');
$nick = gp('nick');
if(!empty($ticket)) {
  $data = array('ticket'=>$ticket,'nick'=>$nick, 'domain'=>$_IMC['domain'], 'apikey'=>$_IMC['apikey'], 'room'=>$room_id, 'endpoint' => $space['uid']);
	$client = new HttpClient($_IMC['imsvr'], $_IMC['impost']);
	$client->post('/room/leave', $data);
	$pageContents = $client->getContent();
	echo $pageContents;
}
?>
