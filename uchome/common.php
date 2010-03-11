<?php
error_reporting(E_ALL & ~E_NOTICE);
define('IM_ROOT', dirname(__FILE__).DIRECTORY_SEPARATOR);
include_once(IM_ROOT . '../common.php');
include_once(IM_ROOT . "json.php");

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

// Future-friendly json_decode
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

function nick($sp) {
	global $_IMC;
	//return $sp{$_IMC['buddy_name']};
	return (!$_IMC['show_realname']||empty($sp['name'])) ? $sp['username'] : $sp['name'];
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
        return empty($ids) ? array() : (is_array($ids) ? array_unique($ids) : array_unique(split(",", $ids)));
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
	$space = getspace($_SGLOBAL['supe_uid']);
}
$groups = getfriendgroup();
function find_buddy($ids){ 
        global $_SGLOBAL,$_IMC,$space, $groups;
        $ids = ids_array($ids);
        //删除自己
        $ids = ids_except($space['uid'], $ids);
        if(empty($ids))return array();
        $ids = join(',', $ids);
        $buddies = array();
        $query = $_SGLOBAL['db']-> query("SELECT main.uid, main.username, main.name, f.gid, f.fuid
                FROM ".tname('space')." main
                LEFT OUTER JOIN ".tname('friend')." f ON f.uid = '$space[uid]' AND main.uid = f.fuid
                WHERE main.uid IN ($ids)");
        while ($value = $_SGLOBAL['db']->fetch_array($query)) {
                realname_set($value['uid'], to_utf8($value['username']));
                $id = $value['uid'];
                $nick = nick($value); 
                $group = empty($value['fuid']) ? "stranger" : null; 
                if(empty($value['fuid'])){
                        $group = "stranger";
                }else{
                        $gid = $value['gid'];
                        $group = (empty($gid) || empty($groups[$gid])) ? "friend" : $groups[$gid];
                }
                //$jid = $id.'@'.$_IMC['domain'];
                //$status_time = empty($value['dateline'])?'':sgmdate('n月j日',$value['dateline'],1);
                $buddies[$id]=array('id'=>$id,'name'=> to_utf8($nick),'pic_url' =>avatar($id,'small',true), 'status'=>'' ,'status_time'=>'','url'=>'space.php?uid='.$id,'group'=> $group, 'default_pic_url' => UC_API.'/images/noavatar_small.gif');
        }
        return $buddies;
}

function find_room(){
        global $_SGLOBAL,$_IMC,$space;
	$rooms = array();
	//uchome_mtag table
	$query = $_SGLOBAL['db']->query("SELECT t.tagid, t.membernum, t.tagname, t.pic
		FROM ".tname('tagspace')." main
		LEFT JOIN ".tname('mtag')." t ON t.tagid = main.tagid
		WHERE main.uid = '$space[uid]'");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$tagid = $value['tagid'];
		$id = (string)($_IMC['room_id_pre'] + $tagid);
		$eid = 'channel:'.$id.'@'.$_IMC['domain'];
		$tagname = $value['tagname']; 
		$pic = empty($value['pic']) ? 'image/nologo.jpg' : $value['pic'];
		$rooms[$id]=array('id'=>$id,'name'=> to_utf8($tagname), 'pic_url'=>$pic, 'status'=>'','status_time'=>'', 'all_count' => $value['membernum'], 'url'=>'space.php?do=mtag&tagid='.$tagid);
	}
	return $rooms;
}

function find_new_message(){
        global $_SGLOBAL,$_IMC,$space;
        $uid = $space['uid'];
        $messages = array();
        $ids = array();
        $query = $_SGLOBAL['db']->query("SELECT * FROM ".im_tname('histories')." WHERE `uid`='$uid' and send = 0 ORDER BY timestamp DESC LIMIT 100");
        while ($value = $_SGLOBAL['db']->fetch_array($query)){
                array_unshift($messages,array('to'=>$value['to'],'from'=>$value['from'],'style'=>$value['style'],'body'=>to_utf8($value['body']),'timestamp'=>$value['timestamp'], 'type' =>$value['type'], 'new' => 1));
        }
        return $messages;
}
function new_message_to_histroy(){
        global $_SGLOBAL,$_IMC,$space;
        $uid = $space['uid'];
        $_SGLOBAL['db']->query("UPDATE ".im_tname('histories')." SET send=1 WHERE `uid`='$uid' AND send = 0");
}

//$uid = $space['uid'] : current user
//$id : user communacated with current user 
function find_history($ids){
        global $_SGLOBAL,$_IMC,$space;
        $uid = $space['uid'];
        $histories = array();
        $ids = ids_array($ids);
        if(empty($ids))return array();
        for($i=0;$i<count($ids);$i++){
                $id = $ids[$i];
                $list = array();
		if(((int)$id) = 0){
                        //$query = $_SGLOBAL['db']->query("SELECT * FROM ".im_tname('histories')." WHERE `uid`='$uid' and (`to`='$id' or `to`='$uid' ) and (`from`='$uid' or `from`='$id') and send = 1 ORDER BY timestamp DESC LIMIT 30");
                          $query = $_SGLOBAL['db']->query("SELECT * FROM ".im_tname('histories')." WHERE (`type`='broadcast') and send = 1 ORDER BY timestamp DESC LIMIT 30");
		}
		else if(((int)$id) < $_IMC['room_id_pre']){
                        //$query = $_SGLOBAL['db']->query("SELECT * FROM ".im_tname('histories')." WHERE `uid`='$uid' and (`to`='$id' or `to`='$uid' ) and (`from`='$uid' or `from`='$id') and send = 1 ORDER BY timestamp DESC LIMIT 30");
                          $query = $_SGLOBAL['db']->query("SELECT * FROM ".im_tname('histories')." WHERE (`from`='$id' and `to`='$uid' and `todel`!=1) or (`from`='$uid' and `to`='$id' and `fromdel`!=1) and send = 1 ORDER BY timestamp DESC LIMIT 30");
             		//$query = $_SGLOBAL['db']->query("SELECT main.*, s.username, s.name FROM ".im_tname('histories')." main LEFT JOIN ".tname('space')." s ON s.uid=main.from WHERE (`to`='$id' and `todel`!=1) or (`from`='$id' and `fromdel`!=1) ORDER BY timestamp DESC LIMIT 30");
                        while ($value = $_SGLOBAL['db']->fetch_array($query)) {
                                array_unshift($list,array('to'=>$value['to'],'from'=>$value['from'],'style'=>$value['style'],'body'=>to_utf8($value['body']),'timestamp'=>$value['timestamp'], 'type' =>$value['type'], 'new' => 0));
                        }
        }else{
            
            $id = $id+$_IMC['channel_pre']; //add by free.wang
			$query = $_SGLOBAL['db']->query("SELECT main.*, s.username, s.name FROM ".im_tname('histories')." main LEFT JOIN ".tname('space')." s ON s.uid=main.from WHERE `to`='$id' ORDER BY timestamp DESC LIMIT 30");
		//
		//(`to`='$id' and `todel` != 1) sended to him
		//(`from`='$id' and `fromdel` != 1) he sended 
             	//$query = $_SGLOBAL['db']->query("SELECT main.*, s.username, s.name FROM ".im_tname('histories')." main LEFT JOIN ".tname('space')." s ON s.uid=main.from WHERE (`to`='$id' and `todel`!=1) or (`from`='$id' and `fromdel`!=1) ORDER BY timestamp DESC LIMIT 30");
                        while ($value = $_SGLOBAL['db']->fetch_array($query)) {
                                $nick = nick($value); array_unshift($list,array('to'=>$value['to'],'nick'=>to_utf8($nick),'from'=>$value['from'],'style'=>$value['style'],'body'=>to_utf8($value['body']), 'type' => $value['type'], 'timestamp'=>$value['timestamp']));
             
                        }
                }
                $histories[$id] = $list;
        }
        return $histories;
}

function setting(){
        global $_SGLOBAL,$_IMC,$space;
	if(!empty($_SGLOBAL['supe_uid'])) {
		$setting  = $_SGLOBAL['db']->fetch_array($_SGLOBAL['db']->query("SELECT * FROM ".im_tname('setting')." WHERE uid='$_SGLOBAL[supe_uid]'"));
		if(empty($setting)){
			$setting = array('uid'=>$space['uid'],'web'=>"");
			$_SGLOBAL['db']->query("INSERT INTO ".im_tname('setting')." (uid,web) VALUES ($_SGLOBAL[supe_uid],'')");
		}
		$setting = $setting["web"];
	}
	return json_decode(empty($setting) ? "{}" : $setting);
}
/*
 * Search $content in array $arr.
 * @$arr : array.
 * @$content : item.
 * @return : return true if found or false.
 */
function ArraySearch($arr, $content){
	foreach($arr as $item){
		if($item === $content){
			return true;
		}
	}
	return false;
}
//当设置UC_DIR为相对路径时，避免取不到头像
if(!empty($_SCONFIG['uc_dir'])&& (substr($_SCONFIG['uc_dir'],0,2)=='./'||substr($_SCONFIG['uc_dir'],0,3)=='../'))
$_SCONFIG['uc_dir']= '../'.$_SCONFIG['uc_dir'];
?>
