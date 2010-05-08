<?php
$platform = $_GET['platform'];
$configRoot = '..' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR ;
include_once( $configRoot . 'http_client.php');

switch($platform){
	case 'discuz':
		include_once($configRoot . 'discuz.php');
		break;
	case 'uchome':
		include_once($configRoot . 'uchome.php');
		break;
	case 'phpwind':
		include_once($configRoot . 'phpwind.php');
		break;
}
$platform = $_GET['platform'];
$ticket = gp('ticket');
$room_id = gp('id');
if(empty($ticket)) {
    exit;
}
$data = array('ticket'=>$ticket, 'domain'=>$_IMC['domain'], 'apikey'=>$_IMC['apikey'], 'rooms'=>$room_id, 'endpoint' => $space['uid']);
$client = new HttpClient($_IMC['imsvr'], $_IMC['impost']);
$client->post('/room/members', $data);
$pageContents = $client->getContent();
$result  = json_decode($pageContents,TRUE);
unset($client);
unset($pageContents);
foreach($result as $group =>$v )
{
    foreach($result[$group] as $k=>$v)
    {
        $uid = $result[$group][$k]['id'];
	if($platform !== 'phpwind'){
	        $pic = user_pic($uid);
        	$result[$group][$k]['pic'] = $pic;
	        $result[$group][$k]['default_pic_url'] = UC_API.'/images/noavatar_small.gif';
	}else if($platform === 'phpwind'){
		$pic = showfacedesign($uid, 1, 'm');
		$result[$group][$k]['pic'] = $pic[0];
		$result[$group][$k]['default_pic_url'] = R_P.'/images/face/none.gif';
	}
    }
}
echo  json_encode($result);
unset($result);
?>
