<?php

error_reporting(E_ALL & ~E_NOTICE);
define('WEBIM_ROOT', substr(dirname(__FILE__), 0, -4));
//API DEFINE
include_once(WEBIM_ROOT . '/config.php');
require_once($_IMC['install_path'].'/config.inc.php');
define('API_COMMFILE','/include/common.inc.php');
define('IM_ROOT', dirname(__FILE__).DIRECTORY_SEPARATOR);
include_once($_IMC['discuz_path'] . API_COMMFILE);
include_once(WEBIM_ROOT . "/lib/json.php");
include_once($_IMC['install_path'].'/uc_client/client.php');
$_SGLOBAL['supe_uid'] =  $discuz_uid;
$_SGLOBAL['db'] = $db;
$_SGLOBAL['timestamp'] = time();
$_SC['gzipcompress'] = true;
$_SC['tablepre']=$tablepre;
$_SC['dbcharset'] = UC_DBCHARSET;
$_SC['charset'] = UC_CHARSET;


//DISCUZ API FUN
if( !function_exists('getspace') ) {
function getspace($uid){
	global $db;
    $db->query("SET NAMES ". UC_DBCHARSET);
    $space = $db->fetch_first("SELECT username,gender,nickname FROM ".tname('members')." m left join ".tname('memberfields')." mf  on m.uid=mf.uid WHERE m.uid='$uid'");
	$space['uid']=$uid;
	$space['nickname']=$space['nickname']?$space['nickname']:$space['username'];
	return $space;
}
}
if( !function_exists('user_pic') ) {
function user_pic($uid, $size='small') {
		return UC_API.'/avatar.php?uid='.$uid.'&size='.$size;
}
}
if( !function_exists('tname') ) {

function tname($name) {
	global $tablepre;
	return $tablepre.$name;
}
}

if( !function_exists('inserttable') ) {
function inserttable($table, $data, $returnid=0, $replace = false, $silent=0) {
	global $db;
	reset($data);
   	$method = $replace?'REPLACE':'INSERT';
	$query  = $method.' into `';
    $query .= tname($table). '` set ';
	$col = array();
	while (list($columns, $value) = each($data)) 
	{
		$value=trim($value);
		$value = (strpos($value,'&|') === 0) ? substr($value, 2) : "'" . mysql_escape_string(str_replace(array('"',"'",'<','>'),array('&quot;' ,'&#039;','&lt;', '&gt;'),$value)) ."'";   
		$col[] = '`'.$columns.'`' . '= '.$value;
	}
    $query .= implode(',',$col);
	$db->query($query, $silent?'SILENT':'');
	if (mysql_errno())return false;
	if ($returnid&&$last_insert_id = mysql_insert_id())return $last_insert_id;
	return true;
}
}
if( !function_exists('updatetable') ) {
function updatetable($table, $data, $where, $silent=0){
	global $db;
	reset($data);
	$query  = 'update `';
    $query .= tname($table). '` set ';
	$col = array();
	while (list($columns, $value) = each($data)) 
	{
		$value=trim($value);
		$value = (strpos($value,'&|') === 0) ? substr($value, 2) : "'" . mysql_escape_string(str_replace(array('"',"'",'<','>'),array('&quot;' ,'&#039;','&lt;', '&gt;'),$value)) ."'";   
		$col[] = '`'.$columns.'`' . '= '.$value;
	}
    $query .= implode(',',$col);
	if(!empty($where)) 
	{
		if(is_array($where)) {
			foreach ($wheresqlarr as $key => $value) {
				$wheres[] = '`'.$key.'`'.'=\''.$value.'\'';
			}
			$where .= implode(' and ',$wheres);
		}
		$query .= ' where ' . $where;
	}
	
	$db->query($query, $silent?'SILENT':'');
	if (mysql_errno())return false;
	if ($affected_rows = mysql_affected_rows())return $affected_rows;
	return true;
}
}
if( !function_exists('getfriendgroup') ) {
function getfriendgroup(){return;}
}
//
//


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

//var_dump($_SGLOBAL['supe_uid']);
$is_login = false;
if(empty($_SGLOBAL['supe_uid'])) {
	$is_login = false;
} else {
	$is_login = true;
	$space = getspace($_SGLOBAL['supe_uid']);
}
$groups = getfriendgroup();

function find_buddy($strangers, $friends = array()){
        global $_SGLOBAL,$_IMC, $groups;
        $friends = ids_array($friends);
		$strangers = ids_array($strangers);
        
        if(empty($friends) && empty($strangers))return array();
        //$ids = join(',', $ids);
		$friend_ids = join(',', $friends);
		$stranger_ids = join(',', $strangers);
        $buddies = array();

		$_SGLOBAL['db']->query("SET NAMES " . UC_DBCHARSET);
		if(!empty($friend_ids)){
			$query_fid = $_SGLOBAL['db']->query("SELECT main.uid, main.username FROM ".tname("members")." main WHERE main.uid IN ({$friend_ids})");
			while($value = $_SGLOBAL['db']->fetch_array($query_fid)){
				$id = $value['uid'];
				$nick = nick($value);
				$buddies[$id]=array('id'=>$id,'name'=> to_utf8($nick),'pic_url' =>user_pic($id,'small'), 'status'=>'' ,'status_time'=>'','url'=>'','group'=> "friend", 'default_pic_url' => UC_API.'/images/noavatar_small.gif');
			}
		}
		$_SGLOBAL['db']->query("SET NAMES " . UC_DBCHARSET);
		if(!empty($stranger_ids)){
            $sql = "SELECT main.uid,main.username  FROM ".tname("members")." main WHERE main.uid IN ($stranger_ids)";
			$query_sid = $_SGLOBAL['db']->query($sql);
			while($value = $_SGLOBAL['db']->fetch_array($query_sid)){
				$id = $value['uid'];
				$nick = nick($value);
				$buddies[$id]=array('id'=>$id,'name'=>to_utf8($nick),'pic_url' =>user_pic($id,'small'), 'status'=>'' ,'status_time'=>'','url'=>'','group'=> "stranger", 'default_pic_url' => UC_API.'/images/noavatar_small.gif');
			}
		}
		return $buddies;
}

function find_new_message(){
        global $_SGLOBAL,$_IMC,$space;
        $uid = $space['uid'];
        $messages = array();
        $ids = array();
	    $_SGLOBAL['db']->query("SET NAMES " . UC_DBCHARSET);
        $query = $_SGLOBAL['db']->query("SELECT * FROM ".im_tname('histories')." WHERE `to`='$uid' and send = 0 ORDER BY timestamp DESC LIMIT 100");
        while ($value = $_SGLOBAL['db']->fetch_array($query)){
                array_unshift($messages,array('to'=>$value['to'],'from'=>$value['from'],'style'=>$value['style'],'body'=>to_utf8($value['body']),'timestamp'=>$value['timestamp'], 'type' =>$value['type'], 'new' => 1));
        }
        return $messages;
}


function find_room($tid){
    global $_SGLOBAL,$_IMC,$space;
	$rooms = array();
	//uchome_mtag table
//	$query = $_SGLOBAL['db']->query("SELECT t.tagid, t.membernum, t.tagname, t.pic
//		FROM ".tname('threads')." main
//		LEFT JOIN ".tname('mtag')." t ON t.tagid = main.tagid
//		WHERE main.uid = '$space[uid]'");
	$_SGLOBAL['db']->query("SET NAMES " . UC_DBCHARSET);
	$query = $_SGLOBAL['db']->query("SELECT subject
		FROM ".tname('threads')." 
		WHERE tid = '$tid'");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$subject = $value['subject'];
		$id = (string)($_IMC['room_id_pre'] + $tid);
		$eid = 'channel:'.$id.'@'.$_IMC['domain'];
		$pic = empty($value['pic']) ? 'image/nologo.jpg' : $value['pic'];
		$rooms[$id]=array('id'=>$id,'name'=> to_utf8($subject), 'pic_url'=>"", 'status'=>'','status_time'=>'');
	}
	return $rooms;
}



function new_message_to_histroy(){
        global $_SGLOBAL,$_IMC,$space;
        $uid = $space['uid'];
        $_SGLOBAL['db']->query("SET NAMES " . UC_DBCHARSET);
        $_SGLOBAL['db']->query("UPDATE ".im_tname('histories')." SET send=1 WHERE `to`='$uid' AND send = 0");
}

function find_history($ids){
        global $_SGLOBAL,$_IMC,$space;
        $_SGLOBAL['db']->query("SET NAMES " . UC_DBCHARSET);
        $uid = $space['uid'];
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
             	        $query = $_SGLOBAL['db']->query("SELECT main.*, s.username, s.name FROM ".im_tname('histories')." main
             	LEFT JOIN ".tname('space')." s ON s.uid=main.from
             	 WHERE `to`='$id' ORDER BY timestamp DESC LIMIT 30");
                        while ($value = $_SGLOBAL['db']->fetch_array($query)) {
                                $nick = nick($value); array_unshift($list,array('to'=>$value['to'],'nick'=>to_utf8($nick),'from'=>$value['from'],'style'=>$value['style'],'body'=>to_utf8($value['body']),'timestamp'=>$value['timestamp']));
             
                        }
                }
                $histories[$id] = $list;
        }
        return $histories;
}
//当设置UC_DIR为相对路径时，避免取不到头像
if(!empty($_SCONFIG['uc_dir'])&& (substr($_SCONFIG['uc_dir'],0,2)=='./'||substr($_SCONFIG['uc_dir'],0,3)=='../'))
$_SCONFIG['uc_dir']= '../'.$_SCONFIG['uc_dir'];

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

function tname($name) {
        global $tablepre;
        return $tablepre.$name;
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
