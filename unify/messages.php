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
$style = gp('style','msg');
$to = gp('to');
$from = $space['uid'];
$time = microtime(true)*1000;
if(empty($to)||empty($from)){
	echo '{error:true}';exit();
}

$client = new HttpClient($_IMC['imsvr'], $_IMC['impost']);
$nick = to_unicode(to_utf8(nick($space)));
$client->post('/messages', array('domain'=>$_IMC['domain'],'apikey'=>$_IMC['apikey'],'ticket' => $ticket,'nick'=>$nick,'to'=>$to,'body'=>to_unicode($body),'timestamp'=>(string)$time,'style'=>$style));

//TODO: if forward message successfully.
$message = array('to'=>$to,'from'=>$from,'style'=>$style,'body'=>from_utf8($body),'timestamp'=>$time);
inserttable('im_histories', $message);

echo "ok";
?>
