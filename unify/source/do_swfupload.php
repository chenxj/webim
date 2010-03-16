<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: do_swfupload.php 12830 2009-07-22 06:42:32Z zhengqingpeng $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

include_once(S_ROOT.'./source/function_cp.php');

$op = empty($_GET['op'])?'':$_GET['op'];
$isupload = empty($_GET['cam']) && empty($_GET['doodle']) ? true : false;
$iscamera = isset($_GET['cam']) ? true : false;
$isdoodle = isset($_GET['doodle']) ? true : false;
$fileurl = '';
if(!empty($_POST['uid'])) {
	$_SGLOBAL['supe_uid'] = intval($_POST['uid']);
	if(empty($_SGLOBAL['supe_uid']) || $_POST['hash'] != md5($_SGLOBAL['supe_uid'].UC_KEY)) {
		exit();
	}
} elseif (empty($_SGLOBAL['supe_uid'])) {
	showmessage('to_login', 'do.php?ac='.$_SCONFIG['login_action']);
}

if($op == "finish") {

	$albumid = intval($_GET['albumid']);

	$space = getspace($_SGLOBAL['supe_uid']);
	if(ckprivacy('upload', 1)) {
		include_once(S_ROOT.'./source/function_feed.php');
		feed_publish($albumid, 'albumid');
	}
	exit();

} elseif($op == 'config') {
	
	$hash = md5($_SGLOBAL['supe_uid'].UC_KEY);
	
	if($isupload && !checkperm('allowupload')) {
		$hash = '';
	} else {
		$filearr = $dirstr = array();
		//大头贴背景图
		if($iscamera) {
			$directory = sreaddir(S_ROOT.'./image/foreground');
			foreach($directory as $key => $value) {
				$dirstr = S_ROOT.'./image/foreground/'.$value;
				if(is_dir($dirstr)) {
					$filearr = sreaddir($dirstr, array('jpg','jpeg','gif','png'));
					if(!empty($filearr)) {
						if(is_file($dirstr.'/categories.txt')) {
							$catfile = @file($dirstr.'/categories.txt');
							$dirarr[$key][0] = trim($catfile[0]);
						} else {
							$dirarr[$key][0] = trim($value);
						}
						$dirarr[$key][1] = trim('image/foreground/'.$value.'/');
						$dirarr[$key][2] = $filearr;
					}
				}
			}
		} elseif($isdoodle) {
			$filearr = sreaddir(S_ROOT.'./image/doodle/big', array('jpg','jpeg','gif','png'));
		}
	}
	$max = @ini_get(upload_max_filesize);
	$unit = strtolower(substr($max, -1, 1));
	if($unit == 'k') {
		$max = intval($max)*1024;
	} elseif($unit == 'm') {
		$max = intval($max)*1024*1024;
	} elseif($unit == 'g') {
		$max = intval($max)*1024*1024*1024;
	}
	$albums = getalbums($_SGLOBAL['supe_uid']);
	
} elseif($op == "screen" || $op == "doodle") {
	
	if(empty($GLOBALS['HTTP_RAW_POST_DATA'])) {
		$GLOBALS['HTTP_RAW_POST_DATA'] = file_get_contents("php://input");
	}
	$status = "failure";
	$dosave = true;
	
	if($op == "doodle") {
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('usermagic')." WHERE uid = '$_SGLOBAL[supe_uid]' AND mid = 'doodle'");
		$value = $_SGLOBAL['db']->fetch_array($query);
		if(empty($value) || $value['count'] < 1) {//没有涂鸦板
			$uploadfiles = -8;
			$dosave = false;
		}
	}
	
	//如果为空则代表发送过来的流有错误
	if($dosave && !empty($GLOBALS['HTTP_RAW_POST_DATA'])) {
		$_SERVER['HTTP_ALBUMID'] = addslashes(siconv(urldecode($_SERVER['HTTP_ALBUMID']), $_SC['charset'], "UTF-8"));
		$from = false;
		if($op == 'screen') {
			$from = 'camera';
		} elseif($_GET['from'] == 'album') {
			$from = 'uploadimage';
		}
		$_SCONFIG['allowwatermark'] = 0;	//禁止添加水印
		$uploadfiles = stream_save($GLOBALS['HTTP_RAW_POST_DATA'], $_SERVER['HTTP_ALBUMID'], 'jpg', '', '', 0, $from);
	}
	
	$uploadResponse = true;
	$picid = $proid = $albumid = 0;
	if($uploadfiles && is_array($uploadfiles)) {
		$status = "success";
		$albumid = $uploadfiles['albumid'];
		$picid =  $uploadfiles['picid'];			
		if($op == "doodle") {			
			$fileurl = pic_get($uploadfiles['filepath'], $uploadfiles['thumb'], $uploadfiles['remote'], 0);
			include_once(S_ROOT.'./source/function_magic.php');
			magic_use('doodle', array(), 1);
		}
	} else {
		switch ($uploadfiles) {
			case -1:
				$uploadfiles = cplang('inadequate_capacity_space');
				break;
			case -2:
				$uploadfiles = cplang('only_allows_upload_file_types');
				break;
			case -4:
				$uploadfiles = cplang('ftp_upload_file_size');
				break;
			case -8:
				$uploadfiles = cplang('has_not_more_doodle');
				break;
			default:
				$uploadfiles = cplang('mobile_picture_temporary_failure');
				break;
		}
	}

} elseif($_FILES && $_POST) {
	
	if($_FILES["Filedata"]['error']) {
		$uploadfiles = cplang('file_is_too_big');
	} else {
		$_FILES["Filedata"]['name'] = addslashes(siconv(urldecode($_FILES["Filedata"]['name']), $_SC['charset'], "UTF-8"));
		$_POST['albumid'] = addslashes(siconv(urldecode($_POST['albumid']), $_SC['charset'], "UTF-8"));
		$uploadfiles = pic_save($_FILES["Filedata"], $_POST['albumid'], addslashes(siconv(urldecode($_POST['title']), $_SC['charset'], "UTF-8")));
	}
	$proid = $_POST['proid'];
	$uploadResponse = true;
	$albumid = 0;
	if($uploadfiles && is_array($uploadfiles)) {
		$status = "success";
		$albumid = $uploadfiles['albumid'];
	} else {
		$status = "failure";
	}
}

$newalbumname = sgmdate('Ymd');

include template("do_swfupload");

$outxml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
$outxml .= siconv(ob_get_contents(), 'UTF-8');
obclean();
@header("Expires: -1");
@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
@header("Pragma: no-cache");
@header("Content-type: application/xml; charset=utf-8");
echo $outxml;

?>