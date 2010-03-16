<?php
include_once('discuz.php');
$channels = array();
//uchome_mtag table
$query = $_SGLOBAL['db']->query("SELECT t.tagid, t.tagname, t.pic
		FROM ".tname('tagspace')." main
		LEFT JOIN ".tname('mtag')." t ON t.tagid = main.tagid
		WHERE main.uid = '$space[uid]'");
while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$tagid = $value['tagid'];
		$id = (string)($_IMC['channel_pre'] + $tagid);
		$eid = 'channel:'.$id.'@'.$_IMC['domain'];
		$tagname = $value['tagname']; 
		$pic = empty($value['pic']) ? 'image/nologo.jpg' : $value['pic'];
		$channels[$id]=array('id'=>$id,'eid'=>$eid,'name'=> to_utf8($tagname), 'pic'=>$pic, 'statusText'=>'','statusTime'=>'','link'=>'space.php?do=mtag&tagid='.$tagid,'channel'=>true);
}
?>
