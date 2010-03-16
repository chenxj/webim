<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_upload.php 13245 2009-08-25 02:01:40Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$albumid = empty($_GET['albumid'])?0:intval($_GET['albumid']);
$eventid = empty($_GET['eventid'])?0:intval($_GET['eventid']);

if($eventid){
	$query = $_SGLOBAL['db']->query("SELECT e.*, ef.* FROM ".tname("event")." e LEFT JOIN ".tname("eventfield")." ef ON e.eventid=ef.eventid WHERE e.eventid='$_GET[eventid]'");
	$event = $_SGLOBAL['db']->fetch_array($query);
	if(empty($event)){
		showmessage('event_does_not_exist');
	}
	if($event['grade'] == -2) {
		showmessage('event_is_closed');
	} elseif ($event['grade'] < 1) {
		showmessage('event_under_verify');
	}
	$query = $_SGLOBAL['db']->query("SELECT * FROM " . tname("userevent") . " WHERE uid = '$_SGLOBAL[supe_uid]' AND eventid = '$eventid'");
	$userevent = $_SGLOBAL['db']->fetch_array($query);
	if($event['allowpic'] == 0 && $userevent['status'] < 3){
		showmessage('event_only_allows_admins_to_upload');
	}
	if($event['allowpic'] && $userevent['status'] < 2) {
	    showmessage("event_only_allows_members_to_upload");
    }
}

if(submitcheck('albumsubmit')) {
	//创建相册
	if($_POST['albumop'] == 'creatalbum') {
		$_POST['albumname'] = empty($_POST['albumname'])?'':getstr($_POST['albumname'], 50, 1, 1);
		if(empty($_POST['albumname'])) $_POST['albumname'] = gmdate('Ymd');

		$_POST['friend'] = intval($_POST['friend']);

		//隐私
		$_POST['target_ids'] = '';
		if($_POST['friend'] == 2) {
			//特定好友
			$uids = array();
			$names = empty($_POST['target_names'])?array():explode(' ', str_replace(array(cplang('tab_space'), "\r\n", "\n", "\r"), ' ', $_POST['target_names']));
			if($names) {
				$query = $_SGLOBAL['db']->query("SELECT uid FROM ".tname('space')." WHERE username IN (".simplode($names).")");
				while ($value = $_SGLOBAL['db']->fetch_array($query)) {
					$uids[] = $value['uid'];
				}
			}
			if(empty($uids)) {
				$_POST['friend'] = 3;//仅自己可见
			} else {
				$_POST['target_ids'] = implode(',', $uids);
			}
		} elseif($_POST['friend'] == 4) {
			//加密
			$_POST['password'] = trim($_POST['password']);
			if($_POST['password'] == '') $_POST['friend'] = 0;//公开
		}
		if($_POST['friend'] !== 2) {
			$_POST['target_ids'] = '';
		}
		if($_POST['friend'] !== 4) {
			$_POST['password'] = '';
		}

		//创建相册
		$setarr = array();
		$setarr['albumname'] = $_POST['albumname'];
		$setarr['uid'] = $_SGLOBAL['supe_uid'];
		$setarr['username'] = $_SGLOBAL['supe_username'];
		$setarr['dateline'] = $setarr['updatetime'] = $_SGLOBAL['timestamp'];
		$setarr['friend'] = $_POST['friend'];
		$setarr['password'] = $_POST['password'];
		$setarr['target_ids'] = $_POST['target_ids'];

		$albumid = inserttable('album', $setarr, 1);
		
		//更新用户统计
		if(empty($space['albumnum'])) {
			$space['albumnum'] = getcount('album', array('uid'=>$space['uid']));
			$albumnumsql = "albumnum=".$space['albumnum'];
		} else {
			$albumnumsql = 'albumnum=albumnum+1';
		}
		$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET {$albumnumsql}, updatetime='$_SGLOBAL[timestamp]' WHERE uid='$_SGLOBAL[supe_uid]'");
	} else {
		$albumid = intval($_POST['albumid']);
	}
	
	$_POST['topicid'] = topic_check($_POST['topicid'], 'pic');
	
	if($_SGLOBAL['mobile']) {
		showmessage('do_success', 'cp.php?ac=upload');
	} else {
		echo "<script>";
		echo "parent.no_insert = 1;";
		echo "parent.albumid = $albumid;";
		echo "parent.topicid = $_POST[topicid];";
		echo "parent.start_upload();";
		echo "</script>";
	}
	exit();

} elseif(submitcheck('uploadsubmit')) {

	//上传图片
	$albumid = $picid = 0;

	if(!checkperm('allowupload')) {
		if($_SGLOBAL['mobile']) {
			showmessage(cplang('not_allow_upload'));
		} else {
			echo "<script>";
			echo "alert(\"".cplang('not_allow_upload')."\")";
			echo "</script>";
			exit();
		}
	}

	//上传
	$_POST['topicid'] = topic_check($_POST['topicid'], 'pic');
	
	$uploadfiles = pic_save($_FILES['attach'], $_POST['albumid'], $_POST['pic_title'], $_POST['topicid']);
	if($uploadfiles && is_array($uploadfiles)) {
		$albumid = $uploadfiles['albumid'];
		$picid = $uploadfiles['picid'];
		$uploadStat = 1;
		if($eventid){
            $arr = array("eventid"=>$eventid, "picid" =>$picid, "uid"=>$_SGLOBAL['supe_uid'], "username"=>$_SGLOBAL['supe_username'], "dateline"=>$_SGLOBAL['timestamp']);
            inserttable("eventpic", $arr);
		}
	} else {
		$uploadStat = $uploadfiles;
	}

	if($_SGLOBAL['mobile']) {
		if($picid) {
			showmessage('do_success', "space.php?do=album&picid=$picid");
		} else {
			showmessage($uploadStat, 'cp.php?ac=upload');
		}
	} else {
		echo "<script>";
		echo "parent.albumid = $albumid;";
		echo "parent.topicid = $_POST[topicid];";
		echo "parent.uploadStat = '$uploadStat';";
		echo "parent.picid = $picid;";
		echo "parent.upload();";
		echo "</script>";
	}
	exit();

} elseif(submitcheck('viewAlbumid')) {
	
	//上传完成发送feed
	if($eventid){//跳到活动页面
	
		$imgs = array();
		$imglinks = array();
		$dateline = $_SGLOBAL['timestamp'] - 600;
		$query = $_SGLOBAL['db']->query("SELECT pic.* FROM ".tname("eventpic")." ep LEFT JOIN ".tname("pic")." pic ON ep.picid=pic.picid WHERE ep.uid='$_SGLOBAL[supe_uid]' AND ep.eventid='$eventid' AND ep.dateline > $dateline ORDER BY ep.dateline DESC LIMIT 4");
		while($value=$_SGLOBAL['db']->fetch_array($query)){
			$imgs[] = pic_get($value['filepath'], $value['thumb'], $value['remote']);
			$imglinks[] = "space.php?do=event&id=$eventid&view=pic&picid=".$value['picid'];
		}
		$picnum = 0;
		if($imgs){
			$picnum = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname("eventpic")." WHERE eventid='$eventid'"), 0);
			feed_add('event', cplang('event_feed_share_pic_title'), '', cplang('event_feed_share_pic_info'),array("eventid"=>$eventid,"title"=>$event['title'],"picnum"=>$picnum)
			,'',$imgs,$imglinks);
		}
		$_SGLOBAL['db']->query("UPDATE ".tname("event")." SET picnum='$picnum', updatetime='$_SGLOBAL[timestamp]' WHERE eventid='$eventid'");
	    showmessage('do_success', 'space.php?do=event&view=pic&id='.$eventid, 0);
	    
	} else {	
		
		//相册feed
		if(ckprivacy('upload', 1)) {
			include_once(S_ROOT.'./source/function_feed.php');
			feed_publish($_POST['opalbumid'], 'albumid');
		}
		
		//单个图片feed
		if($_POST['topicid']) {
			topic_join($_POST['topicid'], $_SGLOBAL['supe_uid'], $_SGLOBAL['supe_username']);
			$url = "space.php?do=topic&topicid=$_POST[topicid]&view=pic";
		} else {
			$url = "space.php?uid=$_SGLOBAL[supe_uid]&do=album&id=".(empty($_POST['opalbumid'])?-1:$_POST['opalbumid']);
		}
		showmessage('upload_images_completed', $url, 0);
	}
} else {
	
	if(!checkperm('allowupload')) {
		ckspacelog();
		showmessage('no_privilege');
	}
	//实名认证
	ckrealname('album');
	
	//视频认证
	ckvideophoto('album');
	
	//新用户见习
	cknewuser();
	
	$siteurl = getsiteurl();
	
	//获取相册
	$albums = getalbums($_SGLOBAL['supe_uid']);
	
	//激活
	$actives = ($_GET['op'] == 'flash' || $_GET['op'] == 'cam')?array($_GET['op']=>' class="active"'):array('js'=>' class="active"');
	
	//空间大小
	$maxattachsize = checkperm('maxattachsize');
	if(!empty($maxattachsize)) {
		$maxattachsize = $maxattachsize + $space['addsize'];//额外空间
		$haveattachsize = formatsize($maxattachsize - $space['attachsize']);
	} else {
		$haveattachsize = 0;
	}
	
	//好友组
	$groups = getfriendgroup();
	
	//热闹
	$topic = array();
	$topicid = $_GET['topicid'] = intval($_GET['topicid']);
	if($topicid) {
		$topic = topic_get($topicid);
	}
	if($topic) $actives = array('upload' => ' class="active"');

}

//模版
include_once template("cp_upload");

?>
