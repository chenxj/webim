<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: function_cp.php 13245 2009-08-25 02:01:40Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//����ͼƬ
function pic_save($FILE, $albumid, $title, $topicid=0) {
	global $_SGLOBAL, $_SCONFIG, $space, $_SC;

	if($albumid<0) $albumid = 0;
	
	//�����ϴ�����
	$allowpictype = array('jpg','jpeg','gif','png');

	//���
	$FILE['size'] = intval($FILE['size']);
	if(empty($FILE['size']) || empty($FILE['tmp_name']) || !empty($FILE['error'])) {
		return cplang('lack_of_access_to_upload_file_size');
	}

	//�жϺ�׺
	$fileext = fileext($FILE['name']);
	if(!in_array($fileext, $allowpictype)) {
		return cplang('only_allows_upload_file_types');
	}

	//��ȡĿ¼
	if(!$filepath = getfilepath($fileext, true)) {
		return cplang('unable_to_create_upload_directory_server');
	}

	//���ռ��С
	if(empty($space)) {
		$space = getspace($_SGLOBAL['supe_uid']);
	}
	
	//�û���
	if(!checkperm('allowupload')) {
		ckspacelog();
		return cplang('inadequate_capacity_space');
	}
	
	//ʵ����֤
	if(!ckrealname('album', 1)) {
		return cplang('inadequate_capacity_space');
	}
	
	//��Ƶ��֤
	if(!ckvideophoto('album', array(), 1)) {
		return cplang('inadequate_capacity_space');
	}
	
	//���û���ϰ
	if(!cknewuser(1)) {
		return cplang('inadequate_capacity_space');
	}

	$maxattachsize = checkperm('maxattachsize');//��λMB
	if($maxattachsize) {//0Ϊ������
		if($space['attachsize'] + $FILE['size'] > $maxattachsize + $space['addsize']) {
			return cplang('inadequate_capacity_space');
		}
	}

	//���ѡ��
	$showtip = true;
	$albumfriend = 0;
	if($albumid) {
		preg_match("/^new\:(.+)$/i", $albumid, $matchs);
		if(!empty($matchs[1])) {
			$albumname = shtmlspecialchars(trim($matchs[1]));
			if(empty($albumname)) $albumname = sgmdate('Ymd');
			$albumid = album_creat(array('albumname' => $albumname));
		} else {
			$albumid = intval($albumid);
			if($albumid) {
				$query = $_SGLOBAL['db']->query("SELECT albumname,friend FROM ".tname('album')." WHERE albumid='$albumid' AND uid='$_SGLOBAL[supe_uid]'");
				if($value = $_SGLOBAL['db']->fetch_array($query)) {
					$albumname = addslashes($value['albumname']);
					$albumfriend = $value['friend'];
				} else {
					$albumname = sgmdate('Ymd');
					$albumid = album_creat(array('albumname' => $albumname));
				}
			}
		}
	} else {
		$albumid = 0;
		$showtip = false;
	}

	//�����ϴ�
	$new_name = $_SC['attachdir'].'./'.$filepath;
	$tmp_name = $FILE['tmp_name'];
	if(@copy($tmp_name, $new_name)) {
		@unlink($tmp_name);
	} elseif((function_exists('move_uploaded_file') && @move_uploaded_file($tmp_name, $new_name))) {
	} elseif(@rename($tmp_name, $new_name)) {
	} else {
		return cplang('mobile_picture_temporary_failure');
	}
	
	//����Ƿ�ͼƬ
	if(function_exists('getimagesize')) {
		$tmp_imagesize = @getimagesize($new_name);
		list($tmp_width, $tmp_height, $tmp_type) = (array)$tmp_imagesize;
		$tmp_size = $tmp_width * $tmp_height;
		if($tmp_size > 16777216 || $tmp_size < 4 || empty($tmp_type) || strpos($tmp_imagesize['mime'], 'flash') > 0) {
			@unlink($new_name);
			return cplang('only_allows_upload_file_types');
		}
	}

	//����ͼ
	include_once(S_ROOT.'./source/function_image.php');
	$thumbpath = makethumb($new_name);
	$thumb = empty($thumbpath)?0:1;

	//�Ƿ�ѹ��
	//��ȡ�ϴ���ͼƬ��С
	if(@$newfilesize = filesize($new_name)) {
		$FILE['size'] = $newfilesize;
	}

	//ˮӡ
	if($_SCONFIG['allowwatermark']) {
		makewatermark($new_name);
	}

	//����ftp�ϴ�
	if($_SCONFIG['allowftp']) {
		include_once(S_ROOT.'./source/function_ftp.php');
		if(ftpupload($new_name, $filepath)) {
			$pic_remote = 1;
			$album_picflag = 2;
		} else {
			@unlink($new_name);
			@unlink($new_name.'.thumb.jpg');
			runlog('ftp', 'Ftp Upload '.$new_name.' failed.');
			return cplang('ftp_upload_file_size');
		}
	} else {
		$pic_remote = 0;
		$album_picflag = 1;
	}
	
	//���
	$title = getstr($title, 200, 1, 1, 1);

	//���
	$setarr = array(
		'albumid' => $albumid,
		'uid' => $_SGLOBAL['supe_uid'],
		'username' => $_SGLOBAL['supe_username'],
		'dateline' => $_SGLOBAL['timestamp'],
		'filename' => addslashes($FILE['name']),
		'postip' => getonlineip(),
		'title' => $title,
		'type' => addslashes($FILE['type']),
		'size' => $FILE['size'],
		'filepath' => $filepath,
		'thumb' => $thumb,
		'remote' => $pic_remote,
		'topicid' => $topicid
	);
	$setarr['picid'] = inserttable('pic', $setarr, 1);

	//���¸�����С
	//����
	$setsql = '';
	if($showtip) {
		$reward = getreward('uploadimage', 0);
		if($reward['credit']) {
			$setsql = ",credit=credit+$reward[credit]";
		}
		if($reward['experience']) {
			$setsql .= ",experience=experience+$reward[experience]";
		}
	}
	$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET attachsize=attachsize+'$FILE[size]', updatetime='$_SGLOBAL[timestamp]' $setsql WHERE uid='$_SGLOBAL[supe_uid]'");

	//������
	if($albumid) {
		$file = $filepath.($thumb?'.thumb.jpg':'');
		$_SGLOBAL['db']->query("UPDATE ".tname('album')."
			SET picnum=picnum+1, updatetime='$_SGLOBAL[timestamp]', pic='$file', picflag='$album_picflag'
			WHERE albumid='$albumid'");
	}
	
	//ͳ��
	updatestat('pic');

	return $setarr;
}

//���������棬�������ݾ�Ϊ�����������д�������һ��ֻ����ͼƬ
function stream_save($strdata, $albumid = 0, $fileext = 'jpg', $name='', $title='', $delsize=0, $from = false) {
	global $_SGLOBAL, $space, $_SCONFIG, $_SC;

	if($albumid<0) $albumid = 0;
	
	$setarr = array();
	$filepath = getfilepath($fileext, true);
	$newfilename = $_SC['attachdir'].'./'.$filepath;

	if($handle = fopen($newfilename, 'wb')) {
		if(fwrite($handle, $strdata) !== FALSE) {
			fclose($handle);
			$size = filesize($newfilename);
			//���ռ��С

			if(empty($space)) {
				$space = getspace($_SGLOBAL['supe_uid']);
				$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('space')." WHERE uid='$_SGLOBAL[supe_uid]'");
				$space = $_SGLOBAL['db']->fetch_array($query);
				$_SGLOBAL['supe_username'] = addslashes($space['username']);
			}
			$_SGLOBAL['member'] = $space;

			$maxattachsize = checkperm('maxattachsize');//��λMB
			if($maxattachsize) {//0Ϊ������
				if($space['attachsize'] + $size - $delsize > $maxattachsize + $space['addsize']) {
					@unlink($newfilename);
					return -1;
				}
			}
			
			//����Ƿ�ͼƬ
			if(function_exists('getimagesize')) {	
				$tmp_imagesize = @getimagesize($newfilename);
				list($tmp_width, $tmp_height, $tmp_type) = (array)$tmp_imagesize;
				$tmp_size = $tmp_width * $tmp_height;
				if($tmp_size > 16777216 || $tmp_size < 4 || empty($tmp_type) || strpos($tmp_imagesize['mime'], 'flash') > 0) {
					@unlink($newfilename);
					return -2;
				}
			}

			//����ͼ
			include_once(S_ROOT.'./source/function_image.php');
			$thumbpath = makethumb($newfilename);
			$thumb = empty($thumbpath)?0:1;

			//��ͷ�������ˮӡ
			if($_SCONFIG['allowwatermark']) {
				makewatermark($newfilename);
			}

			//���
			$filename = addslashes(($name ? $name : substr(strrchr($filepath, '/'), 1)));
			$title = getstr($title, 200, 1, 1, 1);
			
			if($albumid) {
				preg_match("/^new\:(.+)$/i", $albumid, $matchs);
				if(!empty($matchs[1])) {
					$albumname = shtmlspecialchars(trim($matchs[1]));
					if(empty($albumname)) $albumname = sgmdate('Ymd');
					$albumid = album_creat(array('albumname' => $albumname));
				} else {
					$albumid = intval($albumid);
					if($albumid) {
						$query = $_SGLOBAL['db']->query("SELECT albumname,friend FROM ".tname('album')." WHERE albumid='$albumid' AND uid='$_SGLOBAL[supe_uid]'");
						if($value = $_SGLOBAL['db']->fetch_array($query)) {
							$albumname = addslashes($value['albumname']);
							$albumfriend = $value['friend'];
						} else {
							$albumname = sgmdate('Ymd');
							$albumid = album_creat(array('albumname' => $albumname));
						}
					}
				}
			} else {
				$albumid = 0;
			}

			$setarr = array(
				'albumid' => $albumid,
				'uid' => $_SGLOBAL['supe_uid'],
				'username' => $_SGLOBAL['supe_username'],
				'dateline' => $_SGLOBAL['timestamp'],
				'filename' => $filename,
				'postip' => getonlineip(),
				'title' => $title,
				'type' => $fileext,
				'size' => $size,
				'filepath' => $filepath,
				'thumb' => $thumb
			);
			$setarr['picid'] = inserttable('pic', $setarr, 1);

			//���¸�����С
			//����
			$setsql = '';
			if($from) {
				$reward = getreward($from, 0);
				if($reward['credit']) {
					$setsql = ",credit=credit+$reward[credit]";
				}
				if($reward['experience']) {
					$setsql .= ",experience=experience+$reward[experience]";
				}
			}
			$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET attachsize=attachsize+'$size', updatetime='$_SGLOBAL[timestamp]' $setsql WHERE uid='$_SGLOBAL[supe_uid]'");

			//������
			if($albumid) {
				$file = $filepath.($thumb?'.thumb.jpg':'');
				$_SGLOBAL['db']->query("UPDATE ".tname('album')."
					SET picnum=picnum+1, updatetime='$_SGLOBAL[timestamp]', pic='$file', picflag='1'
					WHERE albumid='$albumid'");
			}

			//������ftp�ϴ�,��ֹ��������
			if($_SCONFIG['allowftp']) {
				include_once(S_ROOT.'./source/function_ftp.php');
				if(ftpupload($newfilename, $filepath)) {
					$setarr['remote'] = 1;
					updatetable('pic', array('remote'=>$setarr['remote']), array('picid'=>$setarr['picid']));
					if($albumid) updatetable('album', array('picflag'=>2), array('albumid'=>$albumid));
				} else {
					return -4;
				}
			}
			
			//ͳ��
			updatestat('pic');

			return $setarr;
    	} else {
    		fclose($handle);
    	}
	}
	return -3;
}

//�������
function album_creat($arr) {
	global $_SGLOBAL, $space;
	//�������Ƿ����
	$albumid = getcount('album', array('albumname'=>$arr['albumname'], 'uid'=>$_SGLOBAL['supe_uid']), 'albumid');
	if($albumid) {
		return $albumid;
	} else {
		$arr['uid'] = $_SGLOBAL['supe_uid'];
		$arr['username'] = $_SGLOBAL['supe_username'];
		$arr['dateline'] = $arr['updatetime'] = $_SGLOBAL['timestamp'];
		$albumid = inserttable('album', $arr, 1);
		
		//�������
		$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET albumnum=albumnum+1 WHERE uid='$_SGLOBAL[supe_uid]'");

		return $albumid;
	}
}

//��ȡ�ϴ�·��
function getfilepath($fileext, $mkdir=false) {
	global $_SGLOBAL, $_SC;

	$filepath = "{$_SGLOBAL['supe_uid']}_{$_SGLOBAL['timestamp']}".random(4).".$fileext";
	$name1 = gmdate('Ym');
	$name2 = gmdate('j');

	if($mkdir) {
		$newfilename = $_SC['attachdir'].'./'.$name1;
		if(!is_dir($newfilename)) {
			if(!@mkdir($newfilename)) {
				runlog('error', "DIR: $newfilename can not make");
				return $filepath;
			}
		}
		$newfilename .= '/'.$name2;
		if(!is_dir($newfilename)) {
			if(!@mkdir($newfilename)) {
				runlog('error', "DIR: $newfilename can not make");
				return $name1.'/'.$filepath;
			}
		}
	}
	return $name1.'/'.$name2.'/'.$filepath;
}

//��ȡ������ͼƬ
function getalbumpic($uid, $id) {
	global $_SGLOBAL;
	$query = $_SGLOBAL['db']->query("SELECT filepath, thumb FROM ".tname('pic')." WHERE albumid='$id' AND uid='$uid' ORDER BY thumb DESC, dateline DESC LIMIT 0,1");
	if($pic = $_SGLOBAL['db']->fetch_array($query)) {
		return $pic['filepath'].($pic['thumb']?'.thumb.jpg':'');
	} else {
		return '';
	}
}

//��ȡ���˷���
function getclassarr($uid) {
	global $_SGLOBAL;

	$classarr = array();
	$query = $_SGLOBAL['db']->query("SELECT classid, classname FROM ".tname('class')." WHERE uid='$uid'");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$classarr[$value['classid']] = $value;
	}
	return $classarr;
}

//��ȡ���
function getalbums($uid) {
	global $_SGLOBAL;

	$albums = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('album')." WHERE uid='$uid' ORDER BY albumid DESC");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$albums[$value['albumid']] = $value;
	}
	return $albums;
}

//�¼�����
function feed_add($icon, $title_template='', $title_data=array(), $body_template='', $body_data=array(), $body_general='', $images=array(), $image_links=array(), $target_ids='', $friend='', $appid='', $returnid=0) {
	global $_SGLOBAL;

	if(empty($appid)) {
		if(is_numeric($icon)) {
			$appid = 0;
		} else {
			$appid = UC_APPID;//����
		}
	}
	
	$feedarr = array(
		'appid' => $appid,
		'icon' => $icon,
		'uid' => $_SGLOBAL['supe_uid'],
		'username' => $_SGLOBAL['supe_username'],
		'dateline' => $_SGLOBAL['timestamp'],
		'title_template' => $title_template,
		'body_template' => $body_template,
		'body_general' => $body_general,
		'image_1' => empty($images[0])?'':$images[0],
		'image_1_link' => empty($image_links[0])?'':$image_links[0],
		'image_2' => empty($images[1])?'':$images[1],
		'image_2_link' => empty($image_links[1])?'':$image_links[1],
		'image_3' => empty($images[2])?'':$images[2],
		'image_3_link' => empty($image_links[2])?'':$image_links[2],
		'image_4' => empty($images[3])?'':$images[3],
		'image_4_link' => empty($image_links[3])?'':$image_links[3],
		'target_ids' => $target_ids,
		'friend' => $friend,
		'id' => $id,
		'idtype' => $idtype
	);
	
	$feedarr = sstripslashes($feedarr);//ȥ��ת��
	$feedarr['title_data'] = serialize(sstripslashes($title_data));//����ת��
	$feedarr['body_data'] = serialize(sstripslashes($body_data));//����ת��
	$feedarr['hash_template'] = md5($feedarr['title_template']."\t".$feedarr['body_template']);//ϲ��hash
	$feedarr['hash_data'] = md5($feedarr['title_template']."\t".$feedarr['title_data']."\t".$feedarr['body_template']."\t".$feedarr['body_data']);//�ϲ�hash
	$feedarr = saddslashes($feedarr);//����ת��
	
	//ȥ��
	$query = $_SGLOBAL['db']->query("SELECT feedid FROM ".tname('feed')." WHERE uid='$feedarr[uid]' AND hash_data='$feedarr[hash_data]' LIMIT 0,1");
	if($oldfeed = $_SGLOBAL['db']->fetch_array($query)) {
		updatetable('feed', $feedarr, array('feedid'=>$oldfeed['feedid']));
		return 0;
	}
	
	//����
	if($returnid) {
		return inserttable('feed', $feedarr, $returnid);
	} else {
		inserttable('feed', $feedarr);
		return 1;
	}
}

//�ȵ�
function hot_update($idtype, $id, $hotuser) {
	global $_SGLOBAL, $_SCONFIG;
	
	$hotusers = empty($hotuser)?array():explode(',', $hotuser);
	if($hotusers && in_array($_SGLOBAL['supe_uid'], $hotusers)) {
		return false;//�Ѿ�����
	} else {
		$hotusers[] = $_SGLOBAL['supe_uid'];
		$hotuser = implode(',', $hotusers);
	}
	
	$newhot = count($hotusers)+1;
	if($newhot == $_SCONFIG['feedhotmin']) {
		//����
		$tablename = gettablebyidtype($idtype);
		$query = $_SGLOBAL['db']->query("SELECT uid FROM ".tname($tablename)." WHERE $idtype='$id'");
		$item = $_SGLOBAL['db']->fetch_array($query);
		getreward('hotinfo', 1, $item['uid'], '', 0);
	}

	switch ($idtype) {
		case 'blogid':
			$_SGLOBAL['db']->query("UPDATE ".tname('blogfield')." SET hotuser='$hotuser' WHERE blogid='$id'");
			$_SGLOBAL['db']->query("UPDATE ".tname('blog')." SET hot=hot+1 WHERE blogid='$id'");
			break;
		case 'tid':
			$_SGLOBAL['db']->query("UPDATE ".tname('post')." SET hotuser='$hotuser' WHERE tid='$id' AND isthread='1'");
			$_SGLOBAL['db']->query("UPDATE ".tname('thread')." SET hot=hot+1 WHERE tid='$id'");
			break;
		case 'picid':
			$_SGLOBAL['db']->query("REPLACE INTO ".tname('picfield')." (picid, hotuser) VALUES ('$id', '$hotuser')");
			$_SGLOBAL['db']->query("UPDATE ".tname('pic')." SET hot=hot+1 WHERE picid='$id'");
			break;
		case 'eventid':
			$_SGLOBAL['db']->query("UPDATE ".tname('eventfield')." SET hotuser='$hotuser' WHERE eventid='$id'");
			$_SGLOBAL['db']->query("UPDATE ".tname('event')." SET hot=hot+1 WHERE eventid='$id'");
			break;
		case 'sid':
			$_SGLOBAL['db']->query("UPDATE ".tname('share')." SET hot=hot+1, hotuser='$hotuser' WHERE sid='$id'");
			break;
		case 'pid':
			$_SGLOBAL['db']->query("UPDATE ".tname('pollfield')." SET hotuser='$hotuser' WHERE pid='$id'");
			$_SGLOBAL['db']->query("UPDATE ".tname('poll')." SET hot=hot+1 WHERE pid='$id'");
			break;
		default:
			return false;//�������Ͳ�֧��
	}
	//feed�ȶ�
	$query = $_SGLOBAL['db']->query("SELECT feedid, friend FROM ".tname('feed')." WHERE id='$id' AND idtype='$idtype'");
	if($feed = $_SGLOBAL['db']->fetch_array($query)) {
		if(empty($feed['friend'])) {//��˽
			$_SGLOBAL['db']->query("UPDATE ".tname('feed')." SET hot=hot+1 WHERE feedid='$feed[feedid]'");
		}
	} elseif($idtype == 'picid') {
		//ͼƬ
		include_once(S_ROOT.'./source/function_feed.php');
		feed_publish($id, $idtype);
	}

	return true;
}

//����idtype��ñ�
function gettablebyidtype($idtype) {
	$tablename = '';
	if($idtype == 'blogid') {
		$tablename = 'blog';
	} elseif($idtype == 'tid') {
		$tablename = 'thread';
	} elseif($idtype == 'picid') {
		$tablename = 'pic';
	} elseif($idtype == 'eventid') {
		$tablename = 'event';
	} elseif($idtype == 'sid') {
		$tablename = 'share';
	} elseif($idtype == 'pid') {
		$tablename = 'poll';
	}
	return $tablename;
}

//֪ͨ
function notification_add($uid, $type, $note, $returnid=0) {
	global $_SGLOBAL;

	//��ȡ�Է���ɸѡ����
	$tospace = getspace($uid);
	
	//�����ҵĺ��ѹ�ϵ�ȶ�
	if($_SGLOBAL['supe_uid']) {
		addfriendnum($tospace['uid'], $tospace['username']);
	}
	
	$setarr = array(
		'uid' => $uid,
		'type' => $type,
		'new' => 1,
		'authorid' => $_SGLOBAL['supe_uid'],
		'author' => $_SGLOBAL['supe_username'],
		'note' => addslashes(sstripslashes($note)),
		'dateline' => $_SGLOBAL['timestamp']
	);

	$filter = empty($tospace['privacy']['filter_note'])?array():array_keys($tospace['privacy']['filter_note']);
	if(cknote_uid($setarr, $filter)) {
		//�����û�֪ͨ
		$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET notenum=notenum+1 WHERE uid='$uid'");
	
		if($returnid) {
			return inserttable('notification', $setarr, $returnid);
		} else {
			inserttable('notification', $setarr);
		}
	}
}

//�������֪ͨ
function cknote_uid($note, $filter) {
	
	if($filter) {
		$key = $note['type'].'|0';
		if(in_array($key, $filter)) {
			return false;
		} else {
			$key = $note['type'].'|'.$note['authorid'];
			if(in_array($key, $filter)) {
				return false;
			}
		}
	}
	return true;
}

//����˫�����״̬
function friend_update($uid, $username, $fuid, $fusername, $op='add', $gid=0) {
	global $_SGLOBAL, $_SCONFIG;

	if(empty($uid) || empty($fuid) || $uid == $fuid) return false;

	$flog = array(
		'uid' => $uid > $fuid ? $uid : $fuid,
		'fuid' => $uid > $fuid ? $fuid : $uid,
		'dateline' => $_SGLOBAL['timestamp']
	);
	
	//����״̬
	if($op == 'add' || $op == 'invite') {
		//�Լ�
		inserttable('friend', array('uid'=>$uid, 'fuid'=>$fuid, 'fusername'=>$fusername, 'status'=>1, 'gid'=>$gid, 'dateline'=>$_SGLOBAL['timestamp']), 0, true);
		//�Է�����
		if($op == 'invite') {
			//����ģʽ
			inserttable('friend', array('uid'=>$fuid, 'fuid'=>$uid, 'fusername'=>$username, 'status'=>1, 'dateline'=>$_SGLOBAL['timestamp']), 0, true);
		} else {
			updatetable('friend', array('status'=>1, 'dateline'=>$_SGLOBAL['timestamp']), array('uid'=>$fuid, 'fuid'=>$uid));
		}
		
		//�û��������
		if($_SCONFIG['uc_status']) {
			include_once S_ROOT.'./uc_client/client.php';
			uc_friend_add($uid, $fuid);
			uc_friend_add($fuid, $uid);
		}

		$flog['action'] = 'add';
	} else {
		//ɾ��
		$_SGLOBAL['db']->query("DELETE FROM ".tname('friend')." WHERE (uid='$uid' AND fuid='$fuid') OR (uid='$fuid' AND fuid='$uid')");
		
		//���û�����ɾ��
		if($_SCONFIG['uc_status']) {
			include_once S_ROOT.'./uc_client/client.php';
			uc_friend_delete($uid, array($fuid));
			uc_friend_delete($fuid, array($uid));
		}

		$flog['action'] = 'delete';
	}

	if($_SCONFIG['my_status']) inserttable('friendlog', $flog, 0, true);
	
	//����
	friend_cache($uid);
	friend_cache($fuid);
}

//���º��ѻ���
function friend_cache($uid) {
	global $_SGLOBAL, $space, $_SCONFIG;

	if(!empty($space) && $space['uid'] == $uid) {
		$thespace = $space;
	} else {
		$thespace = getspace($uid);
	}
	if(empty($thespace)) {
		return false;
	}
	$groupids = empty($thespace['privacy']['filter_gid'])?array():$thespace['privacy']['filter_gid'];

	//���ѻ���
	$max_friendnum = 200;//�����ʾfeed������
	$friendlist = $fmod = $feedfriendlist = $ffmod = '';
	$i = $count = 0;
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('friend')." WHERE uid='$uid' AND status='1' ORDER BY num DESC, dateline DESC");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if($value['fuid']) {
			$friendlist .= $fmod.$value['fuid'];
			$fmod = ',';
			if($i < $max_friendnum && (empty($groupids) || !in_array($value['gid'], $groupids))) {
				$feedfriendlist .= $ffmod.$value['fuid'];
				$ffmod = ',';
				$i++;
			}
			$count++;
		}
	}
	if($count > 50000) {
		$friendlist = '';//�������ٻ���
	}
	updatetable('spacefield', array('friend'=>$friendlist, 'feedfriend'=>$feedfriendlist), array('uid'=>$uid));
	
	//����
	if($thespace['friendnum'] != $count) {
		updatetable('space', array('friendnum' => $count), array('uid'=>$uid));
	}
	
	//�����¼
	if($_SCONFIG['my_status']) {
		inserttable('userlog', array('uid'=>$uid, 'action'=>'update', 'dateline'=>$_SGLOBAL['timestamp']), 0, true);
	}
}

//���Ե����������
function request_ignore($uid) {
	global $_SGLOBAL, $space, $_SCONFIG;
	
	//����������ѣ��Ҳ�ͨ��
	$_SGLOBAL['db']->query("DELETE FROM ".tname('friend')." WHERE uid='$uid' AND fuid='$space[uid]'");
	//�ҵĺ�������������
	$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET addfriendnum=addfriendnum-1 WHERE uid='$space[uid]' AND addfriendnum>0");
	//���û�����ɾ��
	if($_SCONFIG['uc_status']) {
		include_once S_ROOT.'./uc_client/client.php';
		uc_friend_delete($space['uid'], array($uid));
		uc_friend_delete($uid, array($space['uid']));
	}
}

//�����֤��
function ckseccode($seccode) {
	global $_SGLOBAL, $_SCOOKIE, $_SCONFIG;

	$check = true;
	if(empty($_SGLOBAL['mobile'])) {
		if($_SCONFIG['questionmode']) {
			include_once(S_ROOT.'./data/data_spam.php');
			$cookie_seccode = intval($_SCOOKIE['seccode']);
			$seccode = trim($seccode);
			if($seccode != $_SGLOBAL['spam']['answer'][$cookie_seccode]) {
				$check = false;
			}
		} else {
			$cookie_seccode = empty($_SCOOKIE['seccode'])?'':authcode($_SCOOKIE['seccode'], 'DECODE');
			if(empty($cookie_seccode) || strtolower($cookie_seccode) != strtolower($seccode)) {
				$check = false;
			}
		}
	}
	return $check;
}

//������˽����
function privacy_update() {
	global $_SGLOBAL, $space;
	updatetable('spacefield', array('privacy'=>addslashes(serialize($space['privacy']))), array('uid'=>$_SGLOBAL['supe_uid']));
}

//�������
function invite_update($inviteid, $uid, $username, $m_uid, $m_username, $appid=0) {
	global $_SGLOBAL, $_SN;

	if($uid && $uid != $m_uid) {
		$friendstatus = getfriendstatus($uid, $m_uid);
		if($friendstatus < 1) {
			
			friend_update($uid, $username, $m_uid, $m_username, 'invite');
			
			//���������¼
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('invite')." WHERE uid='$m_uid' AND fuid='$uid'");
			if($oldinvite = $_SGLOBAL['db']->fetch_array($query)) {
				//�Ѿ������
				return false;
			}
			
			//��������
			getreward('invitefriend', 1, $m_uid, '', 0);

			//feed
			$_SGLOBAL['supe_uid'] = $m_uid;
			$_SGLOBAL['supe_username'] = $m_username;

			//ʵ��
			realname_set($uid, $username);
			realname_get();

			if(ckprivacy('invite', 1)) {
				$title_template = cplang('feed_invite');
				$tite_data = array('username'=>'<a href="space.php?uid='.$uid.'">'.stripslashes($_SN[$uid]).'</a>');
				feed_add('friend', $title_template, $tite_data);
			}

			//֪ͨ
			$_SGLOBAL['supe_uid'] = $uid;
			$_SGLOBAL['supe_username'] = $username;
			notification_add($m_uid, 'friend', cplang('note_invite'));

			//��������
			$setarr = array('fuid'=>$uid, 'fusername'=>$username, 'appid'=>$appid);
			if($inviteid) {
				updatetable('invite', $setarr, array('id'=>$inviteid));
			} else {
				$setarr['uid'] = $m_uid;
				inserttable('invite', $setarr, 0, true);//���������¼
			}
		}
	}
}

//�������
function invite_get($uid, $code) {
	global $_SGLOBAL, $_SN;

	$invitearr = array();
	if($uid && $code) {
		$query = $_SGLOBAL['db']->query("SELECT i.*, s.username, s.name, s.namestatus
			FROM ".tname('invite')." i
			LEFT JOIN ".tname('space')." s ON s.uid=i.uid
			WHERE i.uid='$uid' AND i.code='$code' AND i.fuid='0'");
		if($invitearr = $_SGLOBAL['db']->fetch_array($query)) {
			realname_set($invitearr['uid'], $invitearr['username'], $invitearr['name'], $invitearr['namestatus']);
			$invitearr = saddslashes($invitearr);
		}
	}
	return $invitearr;
}

//ʵ����֤
function ckrealname($type, $return=0) {
	global $_SCONFIG, $_SGLOBAL;
	$result = true;
	if($_SCONFIG['realname'] && empty($_SGLOBAL['member']['namestatus']) && empty($_SCONFIG['name_allow'.$type])) {
		if(empty($return)) showmessage('no_privilege_realname');
		$result = false;
	}
	return $result;
}

//��Ƶ��֤
function ckvideophoto($type, $tospace=array(), $return=0) {
	global $_SCONFIG, $_SGLOBAL;
	
	if(empty($_SCONFIG['videophoto']) || $_SGLOBAL['member']['videostatus']) {
		return true;
	}
	
	$result = true;
	if(empty($tospace) || empty($tospace['privacy']['view']['video'.$type])) {//վ��Ĭ��
		if(!checkperm('videophotoignore') && empty($_SCONFIG['video_allow'.$type])) {
			if($type != 'viewphoto' || $type == 'viewphoto' && !checkperm('allowviewvideopic')) {
				$result = false;
			}
		}
	} elseif ($tospace['privacy']['view']['video'.$type] == 2) {//�û���ֹ
		$result = false;
	}
	if($return) {
		return $result;
	} elseif(!$result) {
		showmessage('no_privilege_videophoto');
	}
}

//������Ƶ��֤��ַ
function getvideopic($filename) {
	$dir1 = substr($filename, 0, 1);
	$dir2 = substr($filename, 1, 1);
	return 'data/avatar/'.$dir1.'/'.$dir2.'/'.$filename.".jpg";
}

//������Ƶ��֤��Ƭ
function videopic_upload($FILE, $uid) {
	if($FILE['size']) {
		//�����ϴ�
		$newfilename = md5(substr($_SGLOBAL['timestamp'], 0, 7).$uid);
		//����Ŀ¼
		$dir1 = substr($newfilename, 0, 1);
		$dir2 = substr($newfilename, 1, 1);
		if(!is_dir(S_ROOT.'./data/avatar/'.$dir1)) {
			if(!mkdir(S_ROOT.'./data/avatar/'.$dir1)) return '';
		}
		if(!is_dir(S_ROOT.'./data/avatar/'.$dir1.'/'.$dir2)) {
			if(!mkdir(S_ROOT.'./data/avatar/'.$dir1.'/'.$dir2)) return '';
		}
		$new_name = S_ROOT.'./'.getvideopic($newfilename);
		$tmp_name = $FILE['tmp_name'];
		if(@copy($tmp_name, $new_name)) {
			@unlink($tmp_name);
		} elseif((function_exists('move_uploaded_file') && @move_uploaded_file($tmp_name, $new_name))) {
		} elseif(@rename($tmp_name, $new_name)) {
		} else {
			return '';
		}
		return $newfilename;
	} else {
		return '';
	}
}


//���û�����
function cknewuser($return=0) {
	global $_SGLOBAL, $_SCONFIG, $space;
	$result = true;
	
	//���ܷ���ˮ����
	if(checkperm('spamignore')) {
		return $result;
	}
	//��ϰʱ��
	if($_SCONFIG['newusertime'] && $_SGLOBAL['timestamp']-$space['dateline']<$_SCONFIG['newusertime']*3600) {
		if(empty($return)) showmessage('no_privilege_newusertime', '', 1, array($_SCONFIG['newusertime']));
		$result = false;
	}
	//��Ҫ�ϴ�ͷ��
	if($_SCONFIG['need_avatar'] && empty($space['avatar'])) {
		if(empty($return)) showmessage('no_privilege_avatar');
		$result = false;
	}
	//ǿ�����û����Ѹ���
	if($_SCONFIG['need_friendnum'] && $space['friendnum']<$_SCONFIG['need_friendnum']) {
		if(empty($return)) showmessage('no_privilege_friendnum', '', 1, array($_SCONFIG['need_friendnum']));
		$result = false;
	}
	//ǿ�����û����Ѹ���
	if($_SCONFIG['need_email'] && empty($space['emailcheck'])) {
		if(empty($return)) showmessage('no_privilege_email');
		$result = false;
	}
	return $result;
}

//�����ʼ�������
function smail($touid, $email, $subject, $message='', $mailtype='') {
	global $_SGLOBAL, $_SCONFIG;
	
	$cid = 0;
	if($touid && $_SCONFIG['sendmailday']) {
		//��ÿռ�
		$tospace = getspace($touid);
		if(empty($tospace)) return false;
		
		$sendmail = empty($tospace['sendmail'])?array():unserialize($tospace['sendmail']);
		if($tospace['emailcheck'] && $tospace['email'] && $_SGLOBAL['timestamp'] - $tospace['lastlogin'] > $_SCONFIG['sendmailday']*86400 && (empty($sendmail) || !empty($sendmail[$mailtype]))) {
			//����´η���ʱ��
			if(empty($tospace['lastsend'])) {
				$tospace['lastsend'] = $_SGLOBAL['timestamp'];
			}
			if(!isset($sendmail['frequency'])) $sendmail['frequency'] = 604800;//1��
			$sendtime = $tospace['lastsend'] + $sendmail['frequency'];
			
			//����Ƿ���ڵ�ǰ�û�����
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('mailcron')." WHERE touid='$touid' LIMIT 1");
			if($value = $_SGLOBAL['db']->fetch_array($query)) {
				$cid = $value['cid'];
				if($value['sendtime'] < $sendtime) $sendtime = $value['sendtime'];
				updatetable('mailcron', array('email'=>addslashes($tospace['email']), 'sendtime'=>$sendtime), array('cid'=>$cid));
			} else {
				$cid = inserttable('mailcron', array('touid'=>$touid, 'email'=>addslashes($tospace['email']), 'sendtime'=>$sendtime), 1);
			}
		}
	} elseif($email) {
		//ֱ�Ӳ����ʼ�
		$email = getstr($email, 80, 1, 1);
		
		//����Ƿ���ڵ�ǰ����
		$cid = 0;
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('mailcron')." WHERE email='$email' LIMIT 1");
		if($value = $_SGLOBAL['db']->fetch_array($query)) {
			$cid = $value['cid'];
		} else {
			$cid = inserttable('mailcron', array('email'=>$email), 1);
		}
	}
	
	if($cid) {
		//�����ʼ����ݶ���
		$setarr = array(
			'cid' => $cid,
			'subject' => addslashes(stripslashes($subject)),
			'message' => addslashes(stripslashes($message)),
			'dateline' => $_SGLOBAL['timestamp']
		);
		inserttable('mailqueue', $setarr);
	}
}

//��������
function isblacklist($to_uid) {
	global $_SGLOBAL;
	return getcount('blacklist', array('uid'=>$to_uid, 'buid'=>$_SGLOBAL['supe_uid']));
}

//������֤����
function emailcheck_send($uid, $email) {
	global $_SGLOBAL, $_SCONFIG;
	
	if($uid && $email) {
		$hash = authcode("$uid\t$email", 'ENCODE');
		$url = getsiteurl().'do.php?ac=emailcheck&amp;hash='.urlencode($hash);
		
		$mailsubject = cplang('active_email_subject');
		$mailmessage = cplang('active_email_msg', array($url));
		smail(0, $email, $mailsubject, $mailmessage);
	}
}

//�����ȶ�
function addfriendnum($touid, $tousername) {
	global $_SGLOBAL;
	
	//�Լ�
	if($touid == $_SGLOBAL['supe_uid'] || empty($_SGLOBAL['supe_uid'])) return false;
	
	//����Ƿ����
	$isfriend = in_array($touid, $_SGLOBAL['member']['friends'])?1:0;
	if($isfriend) {
		$_SGLOBAL['db']->query("UPDATE ".tname('friend')." SET num=num+1 WHERE uid='$_SGLOBAL[supe_uid]' AND fuid='$touid'");
	}
}

//����ͳ��
function updatestat($type, $primary=0) {
	global $_SGLOBAL, $_SCONFIG;

	if(empty($_SGLOBAL['supe_uid']) || empty($_SCONFIG['updatestat'])) return false;
	
	$nowdaytime = sgmdate('Ymd', $_SGLOBAL['timestamp']);
	if($primary) {
		//ȥ��
		$setarr = array(
			'uid' => $_SGLOBAL['supe_uid'],
			'daytime' => '$nowdaytime',
			'type' => $type
		);
		if(getcount('statuser', $setarr)) {
			return false;
		} else {
			inserttable('statuser', $setarr);//���뵱������
		}
	}
	if(getcount('stat', array('daytime'=>$nowdaytime))) {
		$_SGLOBAL['db']->query("UPDATE ".tname('stat')." SET `$type`=`$type`+1 WHERE daytime='$nowdaytime'");
	} else {
		//ɾ������ķ�������
		$_SGLOBAL['db']->query("DELETE FROM ".tname('statuser')." WHERE daytime != '$nowdaytime'");
		//����ͳ��
		inserttable('stat', array('daytime'=>$nowdaytime, $type=>'1'));
	}
}

//��������ͼƬ����
function picurl_get($picurl, $maxlenth='200') {
	$picurl = shtmlspecialchars(trim($picurl));
	if($picurl) {
		if(preg_match("/^http\:\/\/.{5,$maxlenth}\.(jpg|gif|png)$/i", $picurl)) return $picurl;
	}
	return '';
}

//������ֲ���
function topic_check($topicid, $type) {
	global $_SGLOBAL, $space;
	
	$topicid = intval($topicid);
	$newtopcid = $topicid;
	if($topic = topic_get($topicid)) {
		if($topic['joingid']) {
			if(!in_array($space['groupid'], $topic['joingid'])) $newtopcid = 0;
		}
		if($topic['jointype']) {
			if(!in_array($type, $topic['jointype'])) $newtopcid = 0;
		}
	} else {
		$newtopcid = 0;
	}
	return $newtopcid;
}

//��������
function topic_join($topicid, $uid, $username) {
	global $_SGLOBAL;
	
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('topicuser')." WHERE uid='$uid' AND topicid='$topicid'");
	if($value = $_SGLOBAL['db']->fetch_array($query)) {
		updatetable('topicuser', array('dateline'=>$_SGLOBAL['timestamp']), array('id'=>$value['id']));
	} else {
		$_SGLOBAL['db']->query("UPDATE ".tname('topic')." SET joinnum=joinnum+1, lastpost='$_SGLOBAL[timestamp]' WHERE topicid='$topicid'");
		$setarr = array(
			'uid' => $uid,
			'topicid' => $topicid,
			'username' => $username,
			'dateline' => $_SGLOBAL['timestamp']
		);
		inserttable('topicuser', $setarr);
	}
}

//���ͷ���Ƿ��ϴ�
function ckavatar($uid) {
	global $_SC, $_SCONFIG;

	$type = empty($_SCONFIG['avatarreal'])?'virtual':'real';
	if(empty($_SCONFIG['uc_dir'])) {
		include_once(S_ROOT.'./uc_client/client.php');
		$file_exists = uc_check_avatar($uid, 'middle', $type);
		return $file_exists;
	} else {
		$file = $_SCONFIG['uc_dir'].'./data/avatar/'.avatar_file($uid, 'middle');
		return file_exists($file)?1:0;
	}
}


?>