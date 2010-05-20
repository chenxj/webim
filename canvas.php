<?php
include("common.php");

$domain = $_SERVER['HTTP_HOST'];
$param = gp('param');
$url = gp('url');

$client = new HttpClient($domain,$param);
$client->post($url,$param);
$pageContents = $client->getContent();
exit($pageContents);
?>
