<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_share.php 13245 2009-08-25 02:01:40Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$sid = intval($_GET['sid']);

if($_GET['op'] == 'delete') {
	if(submitcheck('deletesubmit')) {
		include_once(S_ROOT.'./source/function_delete.php');
		deleteshares(array($sid));
		showmessage('do_success', $_GET['type']=='view'?'space.php?do=share':$_POST['refer'], 0);
	}
} elseif($_GET['op'] == 'edithot') {
	//权限
	if(!checkperm('manageshare')) {
		showmessage('no_privilege');
	}
	
	if($sid) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('share')." WHERE sid='$sid'");
		if(!$share = $_SGLOBAL['db']->fetch_array($query)) {
			showmessage('no_privilege');
		}
	}
		
	if(submitcheck('hotsubmit')) {
		$_POST['hot'] = intval($_POST['hot']);
		updatetable('share', array('hot'=>$_POST['hot']), array('sid'=>$sid));
		if($_POST['hot']>0) {
			include_once(S_ROOT.'./source/function_feed.php');
			feed_publish($sid, 'sid');
		} else {
			updatetable('feed', array('hot'=>$_POST['hot']), array('id'=>$sid, 'idtype'=>'sid'));
		}
		
		showmessage('do_success', $_POST['refer'], 0);
	}
	
} else {

	if(!checkperm('allowshare')) {
		ckspacelog();
		showmessage('no_privilege');
	}
	//实名认证
	ckrealname('share');
	
	//视频认证
	ckvideophoto('share');

	//新用户见习
	cknewuser();

	$type = empty($_GET['type'])?'':$_GET['type'];
	$id = empty($_GET['id'])?0:intval($_GET['id']);
	$note_uid = 0;
	$note_message = '';
	
	$hotarr = array();

	$arr = array();

	switch ($type) {
		case 'space':
		
			if($id == $space['uid']) {
				showmessage('share_space_not_self');
			}
			
			$tospace = getspace($id);
			if(empty($tospace)) {
				showmessage('space_does_not_exist');
			}
			//黑名单
			if(isblacklist($tospace['uid'])) {
				showmessage('is_blacklist');
			}

			$arr['title_template'] = cplang('share_space');
			$arr['body_template'] = '<b>{username}</b><br>{reside}<br>{spacenote}';
			$arr['body_data'] = array(
				'username' => "<a href=\"space.php?uid=$id\">".$_SN[$tospace['uid']]."</a>",
				'reside' => $tospace['resideprovince'].$tospace['residecity'],
				'spacenote' => $tospace['spacenote']
			);
			
			$arr['image'] = ckavatar($id)?avatar($id, 'middle', true):UC_API.'/images/noavatar_middle.gif';
			$arr['image_link'] = "space.php?uid=$id";
			
			//通知
			$note_uid = $id;
			$note_message = cplang('note_share_space');

			break;
		case 'blog':
			$query = $_SGLOBAL['db']->query("SELECT b.*,bf.message,bf.hotuser FROM ".tname('blog')." b
				LEFT JOIN ".tname('blogfield')." bf ON bf.blogid=b.blogid
				WHERE b.blogid='$id'");
			if(!$blog = $_SGLOBAL['db']->fetch_array($query)) {
				showmessage('blog_does_not_exist');
			}
			if($blog['uid'] == $space['uid']) {
				showmessage('share_not_self');
			}
			if($blog['friend']) {
				showmessage('logs_can_not_share');
			}
			//黑名单
			if(isblacklist($blog['uid'])) {
				showmessage('is_blacklist');
			}

			//实名
			realname_set($blog['uid'], $blog['username']);
			realname_get();

			$arr['title_template'] = cplang('share_blog');
			$arr['body_template'] = '<b>{subject}</b><br>{username}<br>{message}';
			$arr['body_data'] = array(
				'subject' => "<a href=\"space.php?uid=$blog[uid]&do=blog&id=$blog[blogid]\">$blog[subject]</a>",
				'username' => "<a href=\"space.php?uid=$blog[uid]\">".$_SN[$blog['uid']]."</a>",
				'message' => getstr($blog['message'], 150, 0, 1, 0, 0, -1)
			);
			if($blog['pic']) {
				$arr['image'] = pic_cover_get($blog['pic'], $blog['picflag']);
				$arr['image_link'] = "space.php?uid=$blog[uid]&do=blog&id=$blog[blogid]";
			}
			//通知
			$note_uid = $blog['uid'];
			$note_message = cplang('note_share_blog', array("space.php?uid=$blog[uid]&do=blog&id=$blog[blogid]", $blog['subject']));
			
			$hotarr = array('blogid', $blog['blogid'], $blog['hotuser']);
			
			break;
		case 'album':
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('album')." WHERE albumid='$id'");
			if(!$album = $_SGLOBAL['db']->fetch_array($query)) {
				showmessage('album_does_not_exist');
			}
			if($album['uid'] == $space['uid']) {
				showmessage('share_not_self');
			}
			if($album['friend']) {
				showmessage('album_can_not_share');
			}
			//黑名单
			if(isblacklist($album['uid'])) {
				showmessage('is_blacklist');
			}

			//实名
			realname_set($album['uid'], $album['username']);
			realname_get();

			$arr['title_template'] =  cplang('share_album');
			$arr['body_template'] = '<b>{albumname}</b><br>{username}';
			$arr['body_data'] = array(
				'albumname' => "<a href=\"space.php?uid=$album[uid]&do=album&id=$album[albumid]\">$album[albumname]</a>",
				'username' => "<a href=\"space.php?uid=$album[uid]\">".$_SN[$album['uid']]."</a>"
			);
			$arr['image'] = pic_cover_get($album['pic'], $album['picflag']);
			$arr['image_link'] = "space.php?uid=$album[uid]&do=album&id=$album[albumid]";
			//通知
			$note_uid = $album['uid'];
			$note_message = cplang('note_share_album', array("space.php?uid=$album[uid]&do=album&id=$album[albumid]", $album['albumname']));
			
			break;
		case 'pic':
			$query = $_SGLOBAL['db']->query("SELECT album.username, album.albumid, album.albumname, album.friend, pic.*, pf.*
				FROM ".tname('pic')." pic
				LEFT JOIN ".tname('picfield')." pf ON pf.picid=pic.picid
				LEFT JOIN ".tname('album')." album ON album.albumid=pic.albumid
				WHERE pic.picid='$id'");
			if(!$pic = $_SGLOBAL['db']->fetch_array($query)) {
				showmessage('image_does_not_exist');
			}
			if($pic['uid'] == $space['uid']) {
				showmessage('share_not_self');
			}
			if($pic['friend']) {
				showmessage('image_can_not_share');
			}
			//黑名单
			if(isblacklist($pic['uid'])) {
				showmessage('is_blacklist');
			}
			if(empty($pic['albumid'])) $pic['albumid'] = 0;
			if(empty($pic['albumname'])) $pic['albumname'] = cplang('default_albumname');

			//实名
			realname_set($pic['uid'], $pic['username']);
			realname_get();

			$arr['title_template'] = cplang('share_image');
			$arr['body_template'] = cplang('album').': <b>{albumname}</b><br>{username}<br>{title}';
			$arr['body_data'] = array(
				'albumname' => "<a href=\"space.php?uid=$pic[uid]&do=album&id=$pic[albumid]\">$pic[albumname]</a>",
				'username' => "<a href=\"space.php?uid=$pic[uid]\">".$_SN[$pic['uid']]."</a>",
				'title' => getstr($pic['title'], 100, 0, 1, 0, 0, -1)
			);
			$arr['image'] = pic_get($pic['filepath'], $pic['thumb'], $pic['remote']);
			$arr['image_link'] = "space.php?uid=$pic[uid]&do=album&picid=$pic[picid]";
			//通知
			$note_uid = $pic['uid'];
			$note_message = cplang('note_share_pic', array("space.php?uid=$pic[uid]&do=album&picid=$pic[picid]", $pic['albumname']));
			
			$hotarr = array('picid', $pic['picid'], $pic['hotuser']);
			
			break;
		case 'thread':
			$query = $_SGLOBAL['db']->query("SELECT t.*, p.message, p.hotuser FROM ".tname('thread')." t
				LEFT JOIN ".tname('post')." p ON p.tid=t.tid AND p.isthread='1'
				WHERE t.tid='$id'");
			if(!$thread = $_SGLOBAL['db']->fetch_array($query)) {
				showmessage('topics_does_not_exist');
			}
			if($thread['uid'] == $space['uid']) {
				showmessage('share_not_self');
			}
			//黑名单
			if(isblacklist($thread['uid'])) {
				showmessage('is_blacklist');
			}
			include_once(S_ROOT.'./data/data_profield.php');

			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('mtag')." WHERE tagid='$thread[tagid]'");
			$mtag = $_SGLOBAL['db']->fetch_array($query);
			$mtag['title'] = $_SGLOBAL['profield'][$mtag['fieldid']]['title'];

			//实名
			realname_set($thread['uid'], $thread['username']);
			realname_get();

			$arr['title_template'] = cplang('share_thread');
			$arr['body_template'] = '<b>{subject}</b><br>{username}<br>'.cplang('mtag').': {mtag} ({field})<br>{message}';
			$arr['body_data'] = array(
				'subject' => "<a href=\"space.php?uid=$thread[uid]&do=thread&id=$thread[tid]\">$thread[subject]</a>",
				'username' => "<a href=\"space.php?uid=$thread[uid]\">".$_SN[$thread['uid']]."</a>",
				'mtag' => "<a href=\"space.php?do=mtag&tagid=$mtag[tagid]\">$mtag[tagname]</a>",
				'field' => "<a href=\"space.php?do=mtag&id=$mtag[fieldid]\">$mtag[title]</a>",
				'message' => getstr($thread['message'], 150, 0, 1, 0, 0, -1)
			);
			$arr['image'] = '';
			$arr['image_link'] = '';
			//通知
			$note_uid = $thread['uid'];
			$note_message = cplang('note_share_thread', array("space.php?uid=$thread[uid]&do=thread&id=$thread[tid]", $thread['subject']));
			
			$hotarr = array('tid', $thread['tid'], $thread['hotuser']);
			
			break;
		case 'mtag':
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('mtag')." WHERE tagid='$id'");
			if(!$mtag = $_SGLOBAL['db']->fetch_array($query)) {
				showmessage('designated_election_it_does_not_exist');
			}

			include_once(S_ROOT.'./data/data_profield.php');

			$mtag['title'] = $_SGLOBAL['profield'][$mtag['fieldid']]['title'];

			$arr['title_template'] = cplang('share_mtag');
			$arr['body_template'] = '<b>{mtag}</b><br>{field}<br>'.cplang('share_mtag_membernum');
			$arr['body_data'] = array(
				'mtag' => "<a href=\"space.php?do=mtag&tagid=$mtag[tagid]\">$mtag[tagname]</a>",
				'field' => "<a href=\"space.php?do=mtag&id=$mtag[fieldid]\">$mtag[title]</a>",
				'membernum' => $mtag['membernum']
			);
			$arr['image'] = $mtag['pic'];
			$arr['image_link'] = "space.php?do=mtag&tagid=$mtag[tagid]";
			break;
		case 'tag':
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('tag')." WHERE tagid='$id'");
			if(!$tag = $_SGLOBAL['db']->fetch_array($query)) {
				showmessage('tag_does_not_exist');
			}

			$arr['title_template'] = cplang('share_tag');
			$arr['body_template'] = '<b>{tagname}</b><br>'.cplang('share_tag_blognum');
			$arr['body_data'] = array(
				'tagname' => "<a href=\"space.php?do=tag&id=$tag[tagid]\">$tag[tagname]</a>",
				'blognum' => $tag['blognum']
			);
			$arr['image'] = '';
			$arr['image_link'] = '';
			break;
		case 'event':
		    $query = $_SGLOBAL['db']->query("SELECT e.*, ef.hotuser
		    	FROM ".tname("event")." e
		    	LEFT JOIN ".tname('eventfield')." ef
		    	ON ef.eventid=e.eventid
		    	WHERE e.eventid='$id'");
		    if(!$event = $_SGLOBAL['db']->fetch_array($query)){
		        showmessage('event_does_not_exist');
		    }		    
			if($event['uid'] == $space['uid']) {
				showmessage('share_not_self');
			}
			//黑名单
			if(isblacklist($event['uid'])) {
				showmessage('is_blacklist');
			}

		    $arr['title_template'] = cplang('share_event');
			$arr['body_template'] = '<b>{eventname}</b><br>'.cplang("event_time").": {eventtime}<br>".cplang("event_location").": {eventlocation}<br>".cplang("event_creator").": {eventcreator}";
			$arr['body_data'] = array(
				'eventname' => "<a href=\"space.php?do=event&id=$event[eventid]\">$event[title]</a>",
				'eventtime' => sgmdate('m-d H:i', $event['starttime']) . " - " . sgmdate("m-d H:i", $event['endtime']),
				'eventlocation' => "$event[province] $event[city] $event[location]",
				'eventcreator' => $event['username']
			);
			$arr['image'] = $_SC['attachurl'] . $event['poster'];
			if(empty($event['poster'])){
			    include_once(S_ROOT.'./data/data_eventclass.php');
			    $arr['image'] = $_SGLOBAL['eventclass'][$event['classid']]['poster'];
			}
			$arr['image_link'] = "space.php?do=event&id=$event[eventid]";
			
			$hotarr = array('eventid', $event['eventid'], $event['hotuser']);
			
			break;
		case 'poll':
			$query = $_SGLOBAL['db']->query("SELECT p.*,pf.* FROM ".tname('poll')." p
				LEFT JOIN ".tname('pollfield')." pf ON pf.pid=p.pid
				WHERE p.pid='$id'");
			if(!$poll = $_SGLOBAL['db']->fetch_array($query)) {
				showmessage('poll_does_not_exist');
			}
			if($poll['uid'] == $space['uid']) {
				showmessage('share_not_self');
			}
			//黑名单
			if(isblacklist($poll['uid'])) {
				showmessage('is_blacklist');
			}

			//实名
			realname_set($poll['uid'], $poll['username']);
			realname_get();

			$arr['title_template'] = cplang('share_poll', array($poll['percredit'] ? cplang('reward') : ''));
			$arr['body_template'] = '<b>{subject}</b><br>{user}<br>{option}';
			$optionstr = '';
			$poll['option'] = unserialize($poll['option']);
			foreach($poll['option'] as $key => $val) {
				$optionstr .= '<input type="'.($poll['multiple'] ? 'checkbox' : 'radio').'" disabled name="poll_'.$key.'"/>'.$val.'<br/>';
			}
		
			$arr['body_data'] = array(
				'user' => "<a href=\"space.php?uid=$poll[uid]\">".$_SN[$poll['uid']]."</a>",
				'subject' => "<a href=\"space.php?uid=$poll[uid]&do=poll&pid=$poll[pid]\">$poll[subject]</a>",
				'option' => $optionstr
			);
			//通知
			$note_uid = $poll['uid'];
			$note_message = cplang('note_share_poll', array("space.php?uid=$poll[uid]&do=poll&pid=$poll[pid]", $poll['subject']));
			
			$hotarr = array('pid', $poll['pid'], $poll['hotuser']);
			break;
		default:
			//获得feed
			$topic = array();
			$topicid = $_GET['topicid'] = intval($_GET['topicid']);
			if($topicid) {
				$topic = topic_get($topicid);
			}
			if($topic) $actives = array('share' => ' class="active"');
	
			$_SGLOBAL['refer'] = 'space.php?do=share&view=me';
			$type = 'link';
			$_GET['op'] = 'link';
			break;
	}

	//添加分享
	if(submitcheck('sharesubmit')) {

		$_POST['topicid'] = topic_check($_POST['topicid'], 'share');
		
		//验证码
		if($type == 'link' && checkperm('seccode') && !ckseccode($_POST['seccode'])) {
			showmessage('incorrect_code');
		}

		if(empty($_POST['refer'])) $_POST['refer'] = "space.php?do=share&view=me";

		if($type == 'link') {
			$link = shtmlspecialchars(trim($_POST['link']));
			if($link) {
				if(!preg_match("/^(http|ftp|https|mms)\:\/\/.{4,300}$/i", $link)) $link = '';
			}
			if(empty($link)) {
				showmessage('url_incorrect_format');
			}
			$arr['title_template'] = cplang('share_link');
			$arr['body_template'] = '{link}';

			$link_text = sub_url($link, 45);

			$arr['body_data'] = array('link'=>"<a href=\"$link\" target=\"_blank\">$link_text</a>", 'data'=>$link);
			$parseLink = parse_url($link);
			if(preg_match("/(youku.com|youtube.com|5show.com|ku6.com|sohu.com|mofile.com|sina.com.cn)$/i", $parseLink['host'], $hosts)) {
				$flashvar = getflash($link, $hosts[1]);
				if(!empty($flashvar)) {
					$arr['title_template'] = cplang('share_video');
					$type = 'video';
					$arr['body_data']['flashvar'] = $flashvar;
					$arr['body_data']['host'] = $hosts[1];
				}
			}
			// 判断是否音乐 mp3、wma
			if(preg_match("/\.(mp3|wma)$/i", $link)) {
				$arr['title_template'] = cplang('share_music');
				$arr['body_data']['musicvar'] = $link;
				$type = 'music';
			}
			// 判断是否 Flash
			if(preg_match("/\.swf$/i", $link)) {
				$arr['title_template'] = cplang('share_flash');
				$arr['body_data']['flashaddr'] = $link;
				$type = 'flash';
			}
		}
		
		$arr['body_general'] = getstr($_POST['general'], 150, 1, 1, 1, 1);

		$arr['type'] = $type;
		$arr['uid'] = $_SGLOBAL['supe_uid'];
		$arr['username'] = $_SGLOBAL['supe_username'];
		$arr['dateline'] = $_SGLOBAL['timestamp'];
		$arr['topicid'] = $_POST['topicid'];
		$arr['body_data'] = serialize($arr['body_data']);//数组转化
		
		//入库
		$setarr = saddslashes($arr);//增加转义
		$sid = inserttable('share', $setarr, 1);

		//统计
		updatestat('share');
	
		//被分享通知当事人
		if($note_uid && $note_uid != $_SGLOBAL['supe_uid']) {
			notification_add($note_uid, 'sharenotice', $note_message);
		}
	
		//更新用户统计
		if(empty($space['sharenum'])) {
			$space['sharenum'] = getcount('share', array('uid'=>$space['uid']));
			$sharenumsql = "sharenum=".$space['sharenum'];
		} else {
			$sharenumsql = 'sharenum=sharenum+1';
		}
		
		//积分
		$needle = $id ? $type.$id : '';
		$reward = getreward('createshare', 0, 0, $needle);
		$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET {$sharenumsql}, lastpost='$_SGLOBAL[timestamp]', updatetime='$_SGLOBAL[timestamp]', credit=credit+$reward[credit], experience=experience+$reward[experience] WHERE uid='$_SGLOBAL[supe_uid]'");

		//动态
		if(ckprivacy('share', 1)) {
			include_once(S_ROOT.'./source/function_feed.php');
			feed_publish($sid, 'sid', 1);
		}
		
		if($_POST['topicid']) {
			topic_join($_POST['topicid'], $_SGLOBAL['supe_uid'], $_SGLOBAL['supe_username']);
			$url = 'space.php?do=topic&topicid='.$_POST['topicid'].'&view=share';
		} else {
			$url = $_POST['refer'];
		}

		showmessage('do_success', $url, 0);
	}

	//显示
	$arr['body_data'] = serialize($arr['body_data']);//数组转化
	$arr = mkshare($arr);

	realname_get();
}

include template('cp_share');

function getflash($link, $host) {
	$return = '';
	if('youku.com' == $host) {
		// http://v.youku.com/v_show/id_XNDg1MjA0ODg=.html
		preg_match_all("/id\_(\w+)[=.]/", $link, $matches);
		if(!empty($matches[1][0])) {
			$return = $matches[1][0];
		}
	} elseif('ku6.com' == $host) {
		// http://v.ku6.com/show/bjbJKPEex097wVtC.html
		preg_match_all("/\/([\w\-]+)\.html/", $link, $matches);
		if(1 > preg_match("/\/index_([\w\-]+)\.html/", $link) && !empty($matches[1][0])) {
			$return = $matches[1][0];
		}
	} elseif('youtube.com' == $host) {
		// http://tw.youtube.com/watch?v=hwHhRcRDAN0
		preg_match_all("/v\=([\w\-]+)/", $link, $matches);
		if(!empty($matches[1][0])) {
			$return = $matches[1][0];
		}
	} elseif('5show.com' == $host) {
		// http://www.5show.com/show/show/160944.shtml
		preg_match_all("/\/(\d+)\.shtml/", $link, $matches);
		if(!empty($matches[1][0])) {
			$return = $matches[1][0];
		}
	} elseif('mofile.com' == $host) {
		// http://tv.mofile.com/PPU3NTYW/
		preg_match_all("/\/(\w+)\/*$/", $link, $matches);
		if(!empty($matches[1][0])) {
			$return = $matches[1][0];
		}
	} elseif('sina.com.cn' == $host) {
		// http://you.video.sina.com.cn/b/16776316-1338697621.html
		preg_match_all("/\/(\d+)-(\d+)\.html/", $link, $matches);
		if(!empty($matches[1][0])) {
			$return = $matches[1][0];
		}
	} elseif('sohu.com' == $host) {
		// http://v.blog.sohu.com/u/vw/1785928
		preg_match_all("/\/(\d+)\/*$/", $link, $matches);
		if(!empty($matches[1][0])) {
			$return = $matches[1][0];
		}
	}
	return $return;
}

?>