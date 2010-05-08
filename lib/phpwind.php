<?php
error_reporting(E_ALL & ~E_NOTICE);
define('WEBIM_ROOT', substr(dirname(__FILE__), 0, -4));
include_once(WEBIM_ROOT . '/config.php');
include_once($_IMC['install_path'].'global.php');
define('IM_ROOT', dirname(__FILE__).DIRECTORY_SEPARATOR);
include_once(WEBIM_ROOT . "/lib/json.php");
$_SGLOBAL['supe_uid'] = $winduid;
$_SGLOBAL['db'] = $db;
$_SGLOBAL['timestamp'] = time();
$_SC['gzipcompress'] = true;
$_SC['dbcharset'] = $db_charset;

function _iconv($s,$t,$data){
	if( function_exists('iconv') ) {
        return iconv($s,$t,$data);
    }else{
		require_once 'chinese.class.php';
		$chs = new Chinese($s,$t);
		return $chs->convert($data);
	}
}

if( !function_exists('json_encode') ) {
    function json_encode($data) {
        $json = new Services_JSON();
        return( $json->encode($data) );
    }
}

if( !function_exists('json_decode') ) {
    function json_decode($data) {
        $json = new Services_JSON();
        return( $json->decode($data) );
    }
}

function g($key = '') {
	return $key === '' ? $_GET : (isset($_GET[$key]) ? $_GET[$key] : null);
}

function p($key = '') {
	return $key === '' ? $_POST : (isset($_POST[$key]) ? $_POST[$key] : null);
}

function gp($key = '',$def = null) {
	$v = g($key);
	if(is_null($v)){
		$v = p($key);
	}
	if(is_null($v)){
		$v = $def;
	}
	return $v;
}

function setting(){
	global $_SGLOBAL,$_IMC;
	if(!empty($_SGLOBAL['supe_uid'])) {
		$setting  = $_SGLOBAL['db']->fetch_array($_SGLOBAL['db']->query("SELECT * FROM ".im_tname('setting')." WHERE uid='$_SGLOBAL[supe_uid]'"));
		if(empty($setting)){
			$setting = array('uid'=>$_SGLOBAL['supe_uid'],'web'=>"");
			$_SGLOBAL['db']->query("INSERT INTO ".im_tname('setting')." (uid,web) VALUES ($_SGLOBAL[supe_uid],'')");
		}
		$setting = $setting["web"];
	}
	return json_decode(empty($setting) ? "{}" : $setting);
}

function to_utf8($s) {
	global $_SC;
	if($_SC['charset'] == 'utf-8') {
		return $s;
	} else {
		return  _iconv($_SC['charset'],'utf-8',$s);
	}
}

function from_utf8($s) {
	global $_SC;
	if($_SC['charset'] == 'utf-8') {
		return $s;
	} else {
		return  _iconv('utf-8',$_SC['charset'],$s);
	}
}

function to_unicode($s) { 
	return preg_replace("/^\"(.*)\"$/","$1",json_encode($s));
}

function ids_array($ids){
        return ($ids===NULL || $ids==="") ? array() : (is_array($ids) ? array_unique($ids) : array_unique(split(",", $ids)));
}

function ids_except($id, $ids){
        if(in_array($id, $ids)){
                array_splice($ids, array_search($id, $ids), 1);
        }
        return $ids;
}

function im_tname($name){
        return "`webim_".$name."`";
}

$is_login = false;
if(empty($_SGLOBAL['supe_uid'])) {
	$is_login = false;
} else {
	$is_login = true;
}

function find_buddy($strangers, $friends = array()){
        global $_SGLOBAL, $_IMC;
        $friends = ids_array($friends);
	$strangers = ids_array($strangers);
        
	if(empty($friends) && empty($strangers))return array();
        $buddies = array();
	//$buddies = getFriends($_SGLOBAL['supe_uid'], 0, 0, false, true);
	if(!empty($friend_ids)){
		$friend_ids = join(',', $friends);
		$sql = "SELECT main.uid, main.username, main.icon FROM pw_members main WHERE main.uid IN ($friend_ids)";
		$query_sid = $_SGLOBAL['db']->query($sql);
		while($value = $_SGLOBAL['db']->fetch_array($query_sid)){
			$id = $value['uid'];
			$nick = $value['username'];
			require_once R_P."require/showimg.php";
			$pic = showfacedesign($value['icon'], 1, 'm');
			$buddies[$id]=array('id'=>$id,'name'=>to_utf8($nick),'pic_url' =>$pic[0], 'status'=>'' ,'status_time'=>'','url'=>'','group'=> "friend", 'default_pic_url' => R_P."images/face/none.gif");
		}
	}

	if(!empty($stranger_ids)){
	        $stranger_ids = join(',', $strangers);
        	$sql = "SELECT main.uid, main.username, main.icon FROM pw_members main WHERE main.uid IN ($stranger_ids)";
		$query_sid = $_SGLOBAL['db']->query($sql);
		while($value = $_SGLOBAL['db']->fetch_array($query_sid)){
			$id = $value['uid'];
			$nick = $value['username'];
			require_once R_P."require/showimg.php";
			$pic = showfacedesign($value['icon'], 1, 'm');
			$buddies[$id]=array('id'=>$id,'name'=>to_utf8($nick),'pic_url' =>$pic[0], 'status'=>'' ,'status_time'=>'','url'=>'','group'=> "stranger", 'default_pic_url' => R_P."images/face/none.gif");
		}
	}
	return $buddies;
}

function find_new_message(){
        global $_SGLOBAL,$_IMC;
        $uid = $_SGLOBAL['supe_uid'];
        $messages = array();
        $ids = array();
        $query = $_SGLOBAL['db']->query("SELECT * FROM ".im_tname('histories')." WHERE `to`='$uid' and send = 0 ORDER BY timestamp DESC LIMIT 100");
        while ($value = $_SGLOBAL['db']->fetch_array($query)){
                array_unshift($messages,array('to'=>$value['to'],'from'=>$value['from'],'style'=>$value['style'],'body'=>to_utf8($value['body']),'timestamp'=>$value['timestamp'], 'type' =>$value['type'], 'new' => 1));
        }
        return $messages;
}

function find_room($fid){
	global $_SGLOBAL,$_IMC;
	$rooms = array();
	$query = $_SGLOBAL['db']->query("SELECT name FROM pw_forums WHERE fid = '$fid'");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$name = $value['name'];
		$id = (string)($_IMC['room_id_pre'] + $fid);
		$eid = 'channel:'.$id.'@'.$_IMC['domain'];
		# $pic = empty($value['pic']) ? 'image/nologo.jpg' : $value['pic'];
		$rooms[$id]=array('id'=>$id,'name'=> $name, 'pic_url'=>"", 'status'=>'','status_time'=>'');
	}
	return $rooms;
}

function new_message_to_histroy(){
        global $_SGLOBAL,$_IMC;
        $uid = $_SGLOBAL['supe_uid'];
        $_SGLOBAL['db']->query("UPDATE ".im_tname('histories')." SET send=1 WHERE `to`='$uid' AND send = 0");
}

function find_history($ids){
        global $_SGLOBAL,$_IMC;
        $uid = $_SGLOBAL['supe_uid'];
        $histories = array();
        $ids = ids_array($ids);
        if($ids===NULL)return array();
        for($i=0;$i<count($ids);$i++){
                $id = $ids[$i];
                $list = array();
		if(((int)$id) == 0){
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".im_tname('histories')." WHERE (`type`='broadcast') and send = 1 ORDER BY timestamp DESC LIMIT 30");
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				array_unshift($list,array('to'=>$value['to'],'from'=>$value['from'],'style'=>$value['style'],'body'=>to_utf8($value['body']),'timestamp'=>$value['timestamp'], 'type' =>$value['type'], 'new' => 0));
			}
		}
		else if(((int)$id) < $_IMC['room_id_pre']){
                        $query = $_SGLOBAL['db']->query("SELECT * FROM ".im_tname('histories')." WHERE (`to`='$id' and `from`='$uid'  and fromdel=0) or (`to`='$uid' and `from`='$id'  and todel=0 and send=1) ORDER BY timestamp DESC LIMIT 30");
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
                        	array_unshift($list,array('to'=>$value['to'],'from'=>$value['from'],'style'=>$value['style'],'body'=>to_utf8($value['body']),'timestamp'=>$value['timestamp'], 'type' =>$value['type'], 'new' => 0));
                        }
                }else{
			// unknown...
                }
                $histories[$id] = $list;
        }
        return $histories;
}

function saddslashes($string) {
        if(is_array($string)) {
                foreach($string as $key => $val) {
                        $string[$key] = saddslashes($val);
                }
        } else {
                $string = addslashes($string);
        }
        return $string;
}

function obclean() {
        global $_SC;

        ob_end_clean();
        if ($_SC['gzipcompress'] && function_exists('ob_gzhandler')) {
                ob_start('ob_gzhandler');
        } else {
                ob_start();
        }
}
?>
