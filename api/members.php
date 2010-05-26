<?php









$platform = $_GET['platform'];
$configRoot = '..' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR ;
include_once( $configRoot . 'http_client.php');
include_once( $configRoot . 'common.php');


$pic = user_pic(2);
$json = <<<ZZZ
{"9999999":[{"id":"2","nick":"admin","pic":"{$pic}"}]}
ZZZ;
echo $json;
exit;



$space = my_info();
$ticket = gp('ticket');
$room_id = gp('id');
if(empty($ticket)) {
    exit;
}






$data = array('ticket'=>$ticket, 'domain'=>$_IMC['domain'], 'apikey'=>$_IMC['apikey'], 'rooms'=>$room_id,'endpoint'=>$space['nick']) ;
$client = new HttpClient($_IMC['imsvr'], $_IMC['impost']);
$client->post('/room/members', $data);
$pageContents = $client->getContent();
$result  = json_decode($pageContents,TRUE);
foreach($result as $group =>$v )
{
    foreach($result[$group] as $k=>$v)
    {
        $uid = $result[$group][$k]['id'];
	$pic = user_pic($uid);
        $result[$group][$k]['pic'] = $pic;
	$result[$group][$k]['default_pic_url'] = UC_API.'/images/noavatar_small.gif';
    }
}
$json =   json_encode($result);

if(!$result) 
{
$pic = user_pic(2);
$json = <<<ZZZ
{"9999999":[{"id":"2","nick":"admin","pic":"{$pic}"}]}
ZZZ;
}
echo $json;
unset($result);
?>
