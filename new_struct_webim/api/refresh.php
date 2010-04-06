<?php
$platform = $_GET['platform'];
$configRoot = '..' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR ;
switch($platform){
	case 'discuz':
		include_once($configRoot . 'discuz.php');
		break;
	case 'uchome':
		include_once($configRoot . 'uchome.php');
		break;
}

require $configRoot . 'http_client.php';
$ticket = gp('ticket');
if(!empty($ticket)) {
        $data = array('ticket'=>$ticket,'domain'=>$_IMC['domain'],'apikey'=>$_IMC['apikey']);
	//Logout webim server.
	$client = new HttpClient($_IMC['imsvr'], $_IMC['impost']);
	$client->post('/presences/offline',$data);
}
?>
