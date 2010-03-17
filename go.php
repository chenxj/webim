<?php

require 'http_client.php';
$data= array('room'=>"102,101",'buddies'=>"1,2",'domain'=>'localhost','apikey'=>'public','endpoint'=>"4",'nick'=>"f" );
var_dump($data);
$client = new HttpClient("post.nextim.cn",80);
$client->post('/presences/online',$data);
echo $client->getContent();
?>



