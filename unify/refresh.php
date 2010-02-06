<?php
include_once('common.php');
require 'http_client.php';
$ticket = gp('ticket');
if(!empty($ticket)) {
        $data = array('ticket'=>$ticket,'domain'=>$_IMC['domain'],'apikey'=>$_IMC['apikey']);
	//Logout webim server.
	$client = new HttpClient($_IMC['imsvr'], $_IMC['impost']);
	$client->post('/presences/offline',$data);
}
?>
