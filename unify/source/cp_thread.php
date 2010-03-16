<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_thread.php 13245 2009-08-25 02:01:40Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$eventid = empty($_GET['eventid']) ? 0 : intval($_GET['eventid']);
$event = $userevent = array();
if($eventid) {
	$query = $_SGLOBAL['db']->query("SELECT e.* FROM ".tname("event")." e WHERE e.eventid='$_GET[eventid]'");
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
	if($userevent['status'] < 2) {
		showmessage('event_only_allows_member_thread');
	}
}

include_once(S_ROOT.'./source/function_bbcode.php');
include_once(S_ROOT.'./source/function_blog.php');
	
if(submitcheck('threadsubmit')) {

	$tid = $_POST['tid'] = intval($_POST['tid']);
	$tagid = empty($_POST['tagid'])?0:intval($_POST['tagid']);
	
	if($eventid && $event['tagid']!=$tagid) {
		showmessage('event_mtag_not_match');
	}
	
	//添加
	if(!checkperm('allowthread')) {
		ckspacelog();
		showmessage('no_privilege');
	}
		
	if(empty($_POST['tid'])) {
		
		//验证码
		if(checkperm('seccode') && !ckseccode($_POST['seccode'])) {
			showmessage('incorrect_code');
		}
		
		//实名认证
		ckrealname('thread');
		
		//视频认证
		ckvideophoto('thread');
		
		//新用户见习
		cknewuser();
		
		//发新话题
		$mtag = ckmtagspace($tagid);
		
		//是否允许发
		if(empty($mtag['allowthread'])) {
			showmessage('no_privilege');
		}
	
		//判断是否操作太快
		$waittime = interval_check('post');
		if($waittime > 0) {
			showmessage('operating_too_fast','',1,array($waittime));
		}
	} else {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('thread')." WHERE tid='$tid'");
		if(!$thread = $_SGLOBAL['db']->fetch_array($query)) {
			showmessage('no_privilege');
		}
	
		//检查权限
		$tagid = $thread['tagid'];
		$mtag = ckmtagspace($thread['tagid']);
		if($mtag['grade']<8 && $thread['uid']!=$_SGLOBAL['supe_uid'] && $userevent['status']<3) {
			showmessage('no_privilege');
		}
	}

	$subject = getstr($_POST['subject'], 80, 1, 1, 1);
	if(strlen($subject) < 2) showmessage('title_not_too_little');
	
	$_POST['message'] = checkhtml($_POST['message']);
	$_POST['message'] = getstr($_POST['message'], 0, 1, 0, 1, 0, 1);
	$_POST['message'] = preg_replace("/\<div\>\<\/div\>/i", '', $_POST['message']);	
	$message = $_POST['message'];
	
	//标题图片
	$titlepic = '';
	
	//获取上传的图片
	$uploads = array();
	if(!empty($_POST['picids'])) {
		$picids = array_keys($_POST['picids']);
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('pic')." WHERE picid IN (".simplode($picids).") AND uid='$_SGLOBAL[supe_uid]'");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			if(empty($titlepic) && $value['thumb']) {
				$titlepic = pic_get($value['filepath'], $value['thumb'], $value['remote']);
			}
			$uploads[$_POST['picids'][$value['picid']]] = $value;
		}
		if(empty($titlepic) && $value) {
			$titlepic = pic_get($value['filepath'], $value['thumb'], $value['remote']);
		}
	}
	
	//插入文章
	if($uploads) {
		preg_match_all("/\<img\s.*?\_uchome\_localimg\_([0-9]+).+?src\=\"(.+?)\"/i", $message, $mathes);
		if(!empty($mathes[1])) {
			$searchs = $idsearchs = array();
			$replaces = array();
			foreach ($mathes[1] as $key => $value) {
				if(!empty($mathes[2][$key]) && !empty($uploads[$value])) {
					$searchs[] = $mathes[2][$key];
					$idsearchs[] = "_uchome_localimg_$value";
					$replaces[] = pic_get($uploads[$value]['filepath'], $uploads[$value]['thumb'], $uploads[$value]['remote'], 0);
					unset($uploads[$value]);
				}
			}
			if($searchs) {
				$message = str_replace($searchs, $replaces, $message);
				$message = str_replace($idsearchs, 'uchomelocalimg[]', $message);
			}
		}
		//未插入文章
		foreach ($uploads as $value) {
			$picurl = pic_get($value['filepath'], $value['thumb'], $value['remote'], 0);
			$message .= "<div class=\"uchome-message-pic\"><img src=\"$picurl\"><p>$value[title]</p></div>";
		}
	}
	
	//没有填写任何东西
	$ckmessage = preg_replace("/(\<div\>|\<\/div\>|\s)+/is", '', $message);
	if(strlen($message) < 2) {
		showmessage('content_is_not_less_than_four_characters');
	}
	
	//添加slashes
	$message = addslashes($message);
	
	if(empty($_POST['tid'])) {
		
		$_POST['topicid'] = topic_check($_POST['topicid'], 'thread');
		
		//从内容中读取图片
		if(empty($titlepic)) {
			$titlepic = getmessagepic($message);
		}
		$setarr = array(
			'tagid' => $tagid,
			'uid' => $_SGLOBAL['supe_uid'],
			'username' => $_SGLOBAL['supe_username'],
			'dateline' => $_SGLOBAL['timestamp'],
			'subject' => $subject,
			'lastpost' => $_SGLOBAL['timestamp'],
			'lastauthor' => $_SGLOBAL['supe_username'],
			'lastauthorid' => $_SGLOBAL['supe_uid'],
			'topicid' => $_POST['topicid']
		);
		if($eventid) {
			$setarr['eventid'] = $eventid;
		}
		$tid = inserttable('thread', $setarr, 1);
		if($eventid) {//更新话题数目和时间
			$_SGLOBAL['db']->query("UPDATE ".tname("event")." SET threadnum=threadnum+1, updatetime='$_SGLOBAL[timestamp]' WHERE eventid='$eventid'");
		}
		$psetarr = array(
			'tagid' => $tagid,
			'tid' => $tid,
			'uid' => $_SGLOBAL['supe_uid'],
			'username' => $_SGLOBAL['supe_username'],
			'ip' => getonlineip(),
			'dateline' => $_SGLOBAL['timestamp'],
			'message' => $message,
			'isthread' => 1
		);
		//添加
		inserttable('post', $psetarr);
		
		//更新群组统计
		$_SGLOBAL['db']->query("UPDATE ".tname("mtag")." SET threadnum=threadnum+1 WHERE tagid='$tagid'");
		
		//统计
		updatestat('thread');
		
		//更新用户统计
		if(empty($space['threadnum'])) {
			$space['threadnum'] = getcount('thread', array('uid'=>$space['uid']));
			$threadnumsql = "threadnum=".$space['threadnum'];
		} else {
			$threadnumsql = 'threadnum=threadnum+1';
		}
		//积分
		$reward = getreward('publishthread', 0);
		$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET {$threadnumsql}, lastpost='$_SGLOBAL[timestamp]', updatetime='$_SGLOBAL[timestamp]', credit=credit+$reward[credit], experience=experience+$reward[experience] WHERE uid='$_SGLOBAL[supe_uid]'");

	} else {
		$setarr = array(
			'tagid' => $tagid,
			'subject' => $subject
		);
		updatetable('thread', $setarr, array('tid'=>$_POST['tid']));

		$psetarr = array(
			'tagid' => $tagid,
			'ip' => getonlineip(),
			'message' => $message,
			'pic' => ''
		);
		if(checkperm('edittrail')) {
			$message = $message.saddslashes(cplang('thread_edit_trail', array($_SGLOBAL['supe_username'], sgmdate('Y-m-d H:i:s'))));
			$psetarr['message'] = $message;
		}
		updatetable('post', $psetarr, array('tid'=>$_POST['tid'], 'isthread'=>1));
	}
	
	//事件
	if($_POST['makefeed']) {
		include_once(S_ROOT.'./source/function_feed.php');
		feed_publish($tid, 'tid', empty($_POST['tid'])?1:0);
	}
		
	if($_POST['topicid']) {
		topic_join($_POST['topicid'], $_SGLOBAL['supe_uid'], $_SGLOBAL['supe_username']);
		$tourl = 'space.php?do=topic&topicid='.$_POST['topicid'].'&view=thread';
	} else {
		$tourl = "space.php?uid=$_SGLOBAL[supe_uid]&do=thread&id=$tid";
		if($eventid) {
			$tourl .= "&eventid=$eventid";
		}
	}

	showmessage('do_success', $tourl, 0);

} elseif(submitcheck('postsubmit')) {

	if(!checkperm('allowpost')) {
		ckspacelog();
		showmessage('no_privilege');
	}

	//实名认证
	ckrealname('post');
	
	//视频认证
	ckvideophoto('post');
	
	//新用户见习
	cknewuser();

	//判断是否操作太快
	$waittime = interval_check('post');
	if($waittime > 0) {
		showmessage('operating_too_fast','',1,array($waittime));
	}

	//获得话题
	$tid = empty($_POST['tid'])?0:intval($_POST['tid']);
	$thread = array();
	if($tid) {
		$query = $_SGLOBAL['db']->query("SELECT t.*, p.*
			FROM ".tname('thread')." t
			LEFT JOIN ".tname('post')." p ON p.tid=t.tid AND p.isthread=1
			WHERE t.tid='$tid'");
		$thread = $_SGLOBAL['db']->fetch_array($query);
	}
	if(empty($thread)) showmessage('the_discussion_topic_does_not_exist');

	//黑名单
	if(isblacklist($thread['uid'])) {
		showmessage('is_blacklist');
	}
			
	//权限
	$mtag = ckmtagspace($thread['tagid']);
	
	//是否允许发
	if(empty($mtag['allowpost'])) {
		showmessage('no_privilege');
	}
		
	$message = $_POST['message'];
	//处理网络图片
	if(!empty($_POST['pics'])) {
		foreach($_POST['pics'] as $key => $pic) {
			$picurl = picurl_get($pic);
			if(!empty($picurl)) {
				$message .= "\n[img]".$picurl."[/img]";
			}
		}
	}

	$message = getstr($message, 0, 1, 1, 1, 2);
	if(strlen($message) < 2) {
		showmessage('content_is_not_less_than_four_characters');
	}

	//摘要
	$summay = getstr($message, 150, 1, 1);

	//引用回复
	$pid = empty($_POST['pid'])?0:intval($_POST['pid']);
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('post')." WHERE pid='$pid' AND tid='$tid' AND isthread='0'");
	
	$post = $_SGLOBAL['db']->fetch_array($query);
	if($post) {
		//黑名单
		if(isblacklist($post['uid'])) {
			showmessage('is_blacklist');
		}
		
		//实名
		realname_set($post['uid'], $post['username']);
		realname_get();
		
		$post['message'] = preg_replace("/\<div class=\"quote\"\>\<span class=\"q\"\>.*?\<\/span\>\<\/div\>/is", '', $post['message']);
		//移除编辑记录
		$post['message'] = preg_replace("/<ins class=\"modify\".+?<\/ins>/is", '',$post['message']);
		$post['message'] = html2bbcode($post['message']);//显示用
		$message = addslashes("<div class=\"quote\"><span class=\"q\"><b>".$_SN[$post['uid']]."</b>: ".getstr($post['message'], 150, 0, 0, 0, 2, 1).'</span></div>').$message;
	}

	$setarr = array(
		'tagid' => intval($thread['tagid']),
		'tid' => $tid,
		'uid' => $_SGLOBAL['supe_uid'],
		'username' => $_SGLOBAL['supe_username'],
		'ip' => getonlineip(),
		'dateline' => $_SGLOBAL['timestamp'],
		'message' => $message
	);
	$pid = inserttable('post', $setarr, 1);

	//邮件通知
	smail($thread['uid'], '', cplang('mtag_reply',array($_SN[$space['uid']], shtmlspecialchars(getsiteurl()."space.php?uid=$thread[uid]&do=thread&id=$thread[tid]"))), '', 'mtag_reply');

	//更新统计数据
	$_SGLOBAL['db']->query("UPDATE ".tname('thread')."
		SET replynum=replynum+1, lastpost='$_SGLOBAL[timestamp]', lastauthor='$_SGLOBAL[supe_username]', lastauthorid='$_SGLOBAL[supe_uid]'
		WHERE tid='$tid'");
	
	//更新群组统计
	$_SGLOBAL['db']->query("UPDATE ".tname("mtag")." SET postnum=postnum+1 WHERE tagid='$thread[tagid]'");

	//普通回复
	if(empty($post) && $thread['uid'] != $_SGLOBAL['supe_uid']) {
		
		//积分
		getreward('replythread', 1, 0, $thread['tid']);
	
		realname_set($thread['uid'], $thread['username']);
		realname_get();
		
		if(empty($mtag['viewperm'])) {
			$fs = array();
			$fs['icon'] = 'post';
			$fs['body_template'] = '';
			$fs['body_data'] = array();
			$fs['body_general'] = '';
	
			$fs['title_template'] = cplang('feed_thread_reply');
	
			$fs['title_data'] = array('touser'=>"<a href=\"space.php?uid=$thread[uid]\">".$_SN[$thread['uid']]."</a>", 'thread'=>"<a href=\"space.php?uid=$thread[uid]&do=thread&id=$thread[tid]\">$thread[subject]</a>");
	
			if(ckprivacy('post', 1)) {
				feed_add($fs['icon'], $fs['title_template'], $fs['title_data'], $fs['body_template'], $fs['body_data'], $fs['body_general']);
			}
		}

		//通知
		$note = cplang('note_thread_reply')." <a href=\"space.php?uid=$thread[uid]&do=thread&id=$thread[tid]&pid=$pid\" target=\"_blank\">$thread[subject]</a>";
		notification_add($thread['uid'], 'post', $note);

	} elseif ($post) {
		
		$note = cplang('note_post_reply', array("space.php?uid=$thread[uid]&do=thread&id=$thread[tid]", $thread['subject'], "space.php?uid=$thread[uid]&do=thread&id=$thread[tid]&pid=$pid"));
		notification_add($post['uid'], 'post', $note);
	}
	
	//热点
	if($thread['uid'] != $_SGLOBAL['supe_uid']) {
		hot_update('tid', $thread['tid'], $thread['hotuser']);
	}
	
	//统计
	updatestat('post');

	//跳转
	showmessage('do_success', "space.php?uid=$_SGLOBAL[supe_uid]&do=thread&id=$tid&pid=$pid", 0);

} elseif(submitcheck('posteditsubmit')) {
	
	$pid = empty($_POST['pid'])?0:intval($_POST['pid']);
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('post')." WHERE pid='$pid'");
	if(!$post = $_SGLOBAL['db']->fetch_array($query)) {
		showmessage('no_privilege');
	}

	//检查权限
	$tagid = $post['tagid'];
	$mtag = ckmtagspace($post['tagid']);
	if($mtag['grade']<8 && $post['uid']!=$_SGLOBAL['supe_uid'] && $userevent['status']<3) {
		showmessage('no_privilege');
	}
	
	$message = $_POST['message'];
	//处理网络图片
	if(!empty($_POST['pics'])) {
		foreach($_POST['pics'] as $key => $pic) {
			$picurl = picurl_get($pic);
			if(!empty($picurl)) {
				$message .= "\n[img]".$picurl."[/img]";
			}
		}
	}
	$message = getstr($message, 0, 1, 1, 1, 2);
	if(strlen($message) < 2) showmessage('content_is_too_short');
	
	//开启编辑记录
	if(checkperm('edittrail') || ($post['uid'] && $post['uid'] != $space['uid'])) {
		$message = $message.saddslashes(cplang('thread_edit_trail', array($_SN[$_SGLOBAL['supe_uid']], sgmdate('Y-m-d H:i:s'))));
	}
	
	//内容
	updatetable('post', array('message'=>$message), array('pid'=>$pid));

	showmessage('do_success', $_POST['refer'], 0);
}

$pid = empty($_GET['pid'])?0:intval($_GET['pid']);
$tid = empty($_GET['tid'])?0:intval($_GET['tid']);
$tagid = empty($_GET['tagid'])?0:intval($_GET['tagid']);
$thread = $post = array();

//回帖编辑
if($_GET['op'] == 'edit') {

	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('post')." WHERE pid='$pid'");
	if(!$post = $_SGLOBAL['db']->fetch_array($query)) {
		showmessage('no_privilege');
	}
	//移除编辑记录
	$post['message'] = preg_replace("/<ins class=\"modify\".+?<\/ins>/is", '',$post['message']);
	
	//检查权限
	$tagid = $post['tagid'];
	$mtag = ckmtagspace($post['tagid']);
	if($mtag['grade']<8 && $post['uid']!=$_SGLOBAL['supe_uid'] && $userevent['status']<3) {
		showmessage('no_privilege');
	}

	//主题帖
	if($post['isthread']) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('thread')." WHERE tid='$post[tid]'");
		$thread = $_SGLOBAL['db']->fetch_array($query);
	}
	
	if($thread) {
		$post['message'] = str_replace('&amp;', '&amp;amp;', $post['message']);
		$post['message'] = shtmlspecialchars($post['message']);
		
		$_GET['op'] = '';
		$albums = getalbums($_SGLOBAL['supe_uid']);
		if($post['pic']) {
			$post['message'] .= "<div><img src=\"$post[pic]\"></div>";
		}
	} else {
		$post['message'] = html2bbcode($post['message']);//显示用
	}

} elseif($_GET['op'] == 'delete') {

	include_once(S_ROOT.'./source/function_delete.php');

	if(submitcheck('postdeletesubmit')) {
		if($delposts = deleteposts($tagid, array($pid))) {
			$post = $delposts[0];
			if($post['isthread']) {
				$url = "space.php?uid=$post[uid]&do=mtag&tagid=$post[tagid]&view=list";
			} else {
				$url = $_POST['refer'];
			}
			showmessage('do_success', $url, 0);
		} else {
			showmessage('no_privilege');
		}
	}

} elseif($_GET['op'] == 'reply') {
	
	if($eventid) {
		if($userevent['status']<2) {
			showmessage('event_only_allows_member_thread');
		}
	}

	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('post')." WHERE pid='$pid'");
	if(!$post = $_SGLOBAL['db']->fetch_array($query)) {
		showmessage('posting_does_not_exist');
	}

} elseif($_GET['op'] == 'digest') {

	include_once(S_ROOT.'./source/function_op.php');
	digestthreads($tagid, array($tid), isset($_GET['cancel'])?0:1);
	
	showmessage('do_success');

} elseif($_GET['op'] == 'top') {

	include_once(S_ROOT.'./source/function_op.php');
	topthreads($tagid, array($tid), isset($_GET['cancel'])?0:1);
	
	showmessage('do_success');

} elseif($_GET['op'] == 'edithot') {
	//权限
	if(!checkperm('managethread')) {
		showmessage('no_privilege');
	}
	
	$tid = intval($_GET['tid']);
	
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('thread')." WHERE tid='$tid'");
	if(!$thread = $_SGLOBAL['db']->fetch_array($query)) {
		showmessage('no_privilege');
	}
		
	if(submitcheck('hotsubmit')) {
		$_POST['hot'] = intval($_POST['hot']);
		updatetable('thread', array('hot'=>$_POST['hot']), array('tid'=>$tid));
		if($_POST['hot']>0) {
			include_once(S_ROOT.'./source/function_feed.php');
			feed_publish($tid, 'tid');
		} else {
			updatetable('feed', array('hot'=>$_POST['hot']), array('id'=>$tid, 'idtype'=>'tid'));
		}
		
		showmessage('do_success', "space.php?uid=$thread[uid]&do=thread&id=$tid", 0);
	}
	
} else {

	if(!checkperm('allowthread')) {
		ckspacelog();
		showmessage('no_privilege');
	}
	//实名认证
	ckrealname('thread');
	
	//视频认证
	ckvideophoto('thread');

	//新用户见习
	cknewuser();
	
	//发起话题
	$tagid = empty($_GET['tagid'])?0:intval($_GET['tagid']);
	
	if($tagid) {
		$mtag = ckmtagspace($tagid);
		
		//是否允许发
		if(empty($mtag['allowthread'])) {
			showmessage('no_privilege');
		}
	}
	
	//获取相册
	$albums = getalbums($_SGLOBAL['supe_uid']);

	if(!$mtag) {
		include_once(S_ROOT.'./data/data_profield.php');

		$tagid = 0;
		
		//我的群组列表
		$mtaglist = array();
		$query = $_SGLOBAL['db']->query("SELECT main.*,field.tagname,field.membernum,field.fieldid,field.close FROM ".tname('tagspace')." main
			LEFT JOIN ".tname('mtag')." field ON field.tagid=main.tagid
			WHERE main.uid='$_SGLOBAL[supe_uid]' AND main.grade>=0");
		$havemtag = false;
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$havemtag = true;
			if(empty($value['close']) && $value['membernum']>=$_SGLOBAL['profield'][$value['tagid']]['mtagminnum']) {
				$mtaglist[$value['fieldid']][$value['tagid']] = $value;
			}
		}

		if(empty($mtaglist)) {
			if($havemtag) {
				showmessage('no_mtag_allow_thread');
			} else {
				showmessage('settings_of_your_mtag');
			}
		}
	}
	
	//热点
	$topic = array();
	$topicid = $_GET['topicid'] = intval($_GET['topicid']);
	if($topicid) {
		$topic = topic_get($topicid);
	}
	if($topic) $actives = array('thread' => ' class="active"');
	
}

//模板
include template('cp_thread');

//判读是否是组员
function ckmtagspace($tagid) {
	global $_SGLOBAL, $_SCONFIG, $event, $userevent;

	if($event) {//活动话题单独处理
		if(empty($userevent) || $userevent['status'] < 2) {
			showmessage('event_only_allows_member_thread');
		}
		if($event['tagid']!=$tagid) {
			showmessage('event_mtag_not_match');
		}
		$mtag = getmtag($tagid);
		if($mtag['close']) {
			showmessage('mtag_close');
		}
		return $mtag;		
	}
	
	$count = 0;
	$mtag = array();
	if($tagid) {
		$mtag = getmtag($tagid);
		if($mtag) {
			//判断是否关闭
			if($mtag['close']) {
				showmessage('mtag_close');
			}
			//是否允许浏览
			if(empty($mtag['allowview'])) {
				showmessage('mtag_not_allow_to_do');
			}
			//判断是否满足人数要求
			if($mtag['field']['mtagminnum'] && $mtag['membernum'] < $mtag['field']['mtagminnum']) {
				showmessage('mtag_minnum_erro', '', 1, array($mtag['field']['mtagminnum']));
			}
		}
	}
	if(empty($mtag)) {
		showmessage('first_select_a_mtag');
	}
	return $mtag;
}

?>