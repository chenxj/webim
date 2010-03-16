<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: function_cp.php 12354 2009-06-11 08:14:06Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//产生动态
function feed_publish($id, $idtype, $add=0) {
	global $_SGLOBAL;
	
	$setarr = array();
	switch ($idtype) {
		case 'blogid':
			$query = $_SGLOBAL['db']->query("SELECT b.*, bf.* FROM ".tname('blog')." b
				LEFT JOIN ".tname('blogfield')." bf ON bf.blogid=b.blogid
				WHERE b.blogid='$id'");
			if($value = $_SGLOBAL['db']->fetch_array($query)) {
				if($value['friend'] != 3) {
					//基本
					$setarr['icon'] = 'blog';
					$setarr['id'] = $value['blogid'];
					$setarr['idtype'] = $idtype;
					$setarr['uid'] = $value['uid'];
					$setarr['username'] = $value['username'];
					$setarr['dateline'] = $value['dateline'];
					$setarr['target_ids'] = $value['target_ids'];
					$setarr['friend'] = $value['friend'];
					$setarr['hot'] = $value['hot'];
					
					//详细
					$url = "space.php?uid=$value[uid]&do=blog&id=$value[blogid]";
					if($value['friend'] == 4) {//加密
						$setarr['title_template'] = cplang('feed_blog_password');
						$setarr['title_data'] = array('subject' => "<a href=\"$url\">$value[subject]</a>");
					} else {//非私人
						if($value['pic']) {
							$setarr['image_1'] = pic_cover_get($value['pic'], $value['picflag']);
							$setarr['image_1_link'] = $url;
						}
						$setarr['title_template'] = cplang('feed_blog');
						$setarr['body_template'] = '<b>{subject}</b><br>{summary}';
						$setarr['body_data'] = array(
							'subject' => "<a href=\"$url\">$value[subject]</a>",
							'summary' => getstr($value['message'], 150, 1, 1, 0, 0, -1)
						);
					}
				}
			}
			break;
		case 'albumid':
			$key = 1;
			if($id > 0) {
				$query = $_SGLOBAL['db']->query("SELECT p.*, a.username, a.albumname, a.picnum, a.friend, a.target_ids FROM ".tname('pic')." p
					LEFT JOIN ".tname('album')." a ON a.albumid=p.albumid
					WHERE p.albumid='$id' ORDER BY dateline DESC LIMIT 0,4");
				while ($value = $_SGLOBAL['db']->fetch_array($query)) {
					if($value['friend'] <= 2) {
						if(empty($setarr['icon'])) {
							//基本
							$setarr['icon'] = 'album';
							$setarr['id'] = $value['albumid'];
							$setarr['idtype'] = $idtype;
							$setarr['uid'] = $value['uid'];
							$setarr['username'] = $value['username'];
							$setarr['dateline'] = $value['dateline'];
							$setarr['target_ids'] = $value['target_ids'];
							$setarr['friend'] = $value['friend'];
							//详细
							$setarr['title_template'] = '{actor} '.cplang('upload_album');
							$setarr['body_template'] = '<b>{album}</b><br>'.cplang('the_total_picture', array('{picnum}'));
							$setarr['body_data'] = array(
								'album' => "<a href=\"space.php?uid=$value[uid]&do=album&id=$value[albumid]\">$value[albumname]</a>",
								'picnum' => $value['picnum']
							);
						}
						$setarr['image_'.$key] = pic_get($value['filepath'], $value['thumb'], $value['remote']);
						$setarr['image_'.$key.'_link'] = "space.php?uid=$value[uid]&do=album&picid=$value[picid]";
						$key++;
					} else {
						break;
					}
				}
			} else {
				//默认相册
				$picnum = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('pic')." WHERE uid='$_SGLOBAL[supe_uid]' AND albumid='0'"), 0);
				if($picnum>=1) {
					$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('pic')." WHERE uid='$_SGLOBAL[supe_uid]' AND albumid='0' ORDER BY dateline DESC LIMIT 0,4");
					while ($value = $_SGLOBAL['db']->fetch_array($query)) {
						if(empty($setarr['icon'])) {
							//基本
							$setarr['icon'] = 'album';
							$setarr['uid'] = $value['uid'];
							$setarr['username'] = $_SGLOBAL['supe_username'];
							$setarr['dateline'] = $value['dateline'];
							//详细
							$setarr['title_template'] = '{actor} '.cplang('upload_album');
							$setarr['body_template'] = '<b>{album}</b><br>'.cplang('the_total_picture', array('{picnum}'));
							$setarr['body_data'] = array(
								'album' => "<a href=\"space.php?uid=$value[uid]&do=album&id=-1\">".cplang('default_albumname')."</a>",
								'picnum' => $picnum
							);
						}
						$setarr['image_'.$key] = pic_get($value['filepath'], $value['thumb'], $value['remote']);
						$setarr['image_'.$key.'_link'] = "space.php?uid=$value[uid]&do=album&picid=$value[picid]";
						$key++;
					}
				}
			}
			break;
		case 'picid':
			$plussql = $id>0?"p.picid='$id'":"p.uid='$_SGLOBAL[supe_uid]' ORDER BY dateline DESC LIMIT 1";
			$query = $_SGLOBAL['db']->query("SELECT p.*, a.friend, a.target_ids, s.username FROM ".tname('pic')." p
				LEFT JOIN ".tname('space')." s ON s.uid=p.uid
				LEFT JOIN ".tname('album')." a ON a.albumid=p.albumid WHERE $plussql");
			if($value = $_SGLOBAL['db']->fetch_array($query)) {
				if(empty($value['friend'])) {//隐私
					//基本
					$setarr['icon'] = 'album';
					$setarr['id'] = $value['picid'];
					$setarr['idtype'] = $idtype;
					$setarr['uid'] = $value['uid'];
					$setarr['username'] = $value['username'];
					$setarr['dateline'] = $value['dateline'];
					$setarr['target_ids'] = $value['target_ids'];
					$setarr['friend'] = $value['friend'];
					$setarr['hot'] = $value['hot'];
					//详细
					$url = "space.php?uid=$value[uid]&do=album&picid=$value[picid]";
					$setarr['image_1'] = pic_get($value['filepath'], $value['thumb'], $value['remote']);
					$setarr['image_1_link'] = $url;
					$setarr['title_template'] = '{actor} '.cplang('upload_a_new_picture');
					$setarr['body_template'] = '{title}';
					$setarr['body_data'] = array('title' => $value['title']);
				}
			}
			break;
		case 'tid':
			$query = $_SGLOBAL['db']->query("SELECT t.*, p.* FROM ".tname('thread')." t
				LEFT JOIN ".tname('post')." p ON p.tid=t.tid AND p.isthread='1'
				WHERE t.tid='$id'");
			if($value = $_SGLOBAL['db']->fetch_array($query)) {
				//基本
				$setarr['icon'] = 'thread';
				$setarr['id'] = $value['tid'];
				$setarr['idtype'] = $idtype;
				$setarr['uid'] = $value['uid'];
				$setarr['username'] = $value['username'];
				$setarr['dateline'] = $value['dateline'];
				$setarr['hot'] = $value['hot'];
				
				//详细
				$url = "space.php?uid=$value[uid]&do=thread&id=$value[tid]";
				
				if($value['eventid']) {
					//活动
					$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname("event")." WHERE eventid='$value[eventid]'");
					$event = $_SGLOBAL['db']->fetch_array($query);
					
					$setarr['title_template'] = cplang('feed_eventthread');
					$setarr['body_template'] = '<b>{subject}</b><br>'.cplang('event').': {event}<br>{summary}';
					$setarr['body_data'] = array(
						'subject' => "<a href=\"$url&eventid=$value[eventid]\">$value[subject]</a>",
						'event' => "<a href=\"space.php?do=event&id=$value[eventid]\">$event[title]</a>",
						'summary' => getstr($value['message'], 150, 1, 1, 0, 0, -1)
					);
				} else {
					$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname("mtag")." WHERE tagid='$value[tagid]'");
					$mtag = $_SGLOBAL['db']->fetch_array($query);
					
					$setarr['title_template'] = cplang('feed_thread');	
					$setarr['body_template'] = '<b>{subject}</b><br>'.cplang('mtag').': {mtag}<br>{summary}';
					$setarr['body_data'] = array(
						'subject' => "<a href=\"$url\">$value[subject]</a>",
						'mtag' => "<a href=\"space.php?do=mtag&tagid=$value[tagid]\">$mtag[tagname]</a>",
						'summary' => getstr($value['message'], 150, 1, 1, 0, 0, -1)
					);
				}
			}
			break;
		case 'pid':
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('poll')." WHERE pid='$id'");
			if($value = $_SGLOBAL['db']->fetch_array($query)) {

				//基本
				$setarr['icon'] = 'poll';
				$setarr['id'] = $value['pid'];
				$setarr['idtype'] = $idtype;
				$setarr['uid'] = $value['uid'];
				$setarr['username'] = $value['username'];
				$setarr['dateline'] = $value['dateline'];
				$setarr['hot'] = $value['hot'];
				
				//详细
				$url = "space.php?uid=$value[uid]&do=poll&pid=$value[pid]";
				
				$setarr['title_template'] = cplang('feed_poll');
				$setarr['body_template'] = '<a href="{url}"><strong>{subject}</strong></a>{option}';
				
				$optionstr = '';
				$opquery = $_SGLOBAL['db']->query("SELECT * FROM ".tname("polloption")." WHERE pid='$value[pid]' LIMIT 0,2");
				while ($opt = $_SGLOBAL['db']->fetch_array($opquery)) {
					$optionstr .= '<br><input type="'.($value['maxchoice'] > 1 ? 'checkbox' : 'radio').'" disabled name="poll_'.$opt['oid'].'"/>'.$opt['option'];
				}
				$setarr['body_data'] = array(
					'url' => $url,
					'subject' => $value['subject'],
					'option' => $optionstr
				);
				$setarr['body_general'] = $value['percredit'] ? cplang('reward_info', array($value['percredit'])) : '';
			}
			break;
		case 'eventid':
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('event')." WHERE eventid='$id'");
			if($value = $_SGLOBAL['db']->fetch_array($query)) {

				//基本
				$setarr['icon'] = 'event';
				$setarr['id'] = $value['eventid'];
				$setarr['idtype'] = $idtype;
				$setarr['uid'] = $value['uid'];
				$setarr['username'] = $value['username'];
				$setarr['dateline'] = $value['dateline'];
				$setarr['hot'] = $value['hot'];
				
				//详细
				$url = "space.php?do=event&id=$value[eventid]";
				
				$setarr['title_template'] = cplang('event_add');
				$setarr['body_template'] =  cplang('event_feed_info');
				$setarr['body_data'] = array(
					'title' => "<a href=\"$url\">$value[title]</a>",
					'province' => $value['province'],
					'city' => $value['city'],
					'location' => $value['location'],
					'starttime' => sgmdate('m-d H:i', $value['starttime']),
					'endtime' => sgmdate('m-d H:i', $value['endtime'])
				);
				//封面
				if($value['poster']) {
					$setarr['image_1'] = pic_get($value['poster'], $value['thumb'], $value['remote']);
					$setarr['image_1_link'] = $url;
				}
			}
			break;
		case 'sid':
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('share')." WHERE sid='$id'");
			if($value = $_SGLOBAL['db']->fetch_array($query)) {

				//基本
				$setarr['icon'] = 'share';
				$setarr['id'] = $value['sid'];
				$setarr['idtype'] = $idtype;
				$setarr['uid'] = $value['uid'];
				$setarr['username'] = $value['username'];
				$setarr['dateline'] = $value['dateline'];
				$setarr['hot'] = $value['hot'];
				
				//详细
				$url = "space.php?uid=$value[uid]&do=share&id=$value[sid]";
				
				$setarr['title_template'] = '{actor} '.$value['title_template'];
				$setarr['body_template'] =  $value['body_template'];
				$setarr['body_data'] = $value['body_data'];
				$setarr['body_general'] = $value['body_general'];
				$setarr['image_1'] = $value['image'];
				$setarr['image_1_link'] = $value['image_link'];
			}
			break;
	}
	
	if($setarr['icon']) {
		
		$setarr['appid'] = UC_APPID;
			
		//数据处理
		$setarr['title_data'] = serialize($setarr['title_data']);//数组转化
		if($idtype != 'sid') {
			$setarr['body_data'] = serialize($setarr['body_data']);//数组转化
		}
		$setarr['hash_template'] = md5($setarr['title_template']."\t".$setarr['body_template']);//喜好hash
		$setarr['hash_data'] = md5($setarr['title_template']."\t".$setarr['title_data']."\t".$setarr['body_template']."\t".$setarr['body_data']);//合并hash
		
		$setarr = saddslashes($setarr);
		
		$feedid = 0;
		if(!$add && $setarr['id']) {
			$query = $_SGLOBAL['db']->query("SELECT feedid FROM ".tname('feed')." WHERE id='$id' AND idtype='$idtype'");
			$feedid = $_SGLOBAL['db']->result($query, 0);
		}
		if($feedid) {
			updatetable('feed', $setarr, array('feedid'=>$feedid));
		} else {
			inserttable('feed', $setarr);
		}
	}
	
}

?>