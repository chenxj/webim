<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_comment.php 13000 2009-08-05 05:58:30Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

include_once(S_ROOT.'./source/function_bbcode.php');

//共用变量
$tospace = $pic = $blog = $album = $share = $event = $poll = array();

if(submitcheck('commentsubmit')) {

	$idtype = $_POST['idtype'];
	
	if(!checkperm('allowcomment')) {
		ckspacelog();
		showmessage('no_privilege');
	}

	//实名认证
	ckrealname('comment');

	//新用户见习
	cknewuser();

	//判断是否发布太快
	$waittime = interval_check('post');
	if($waittime > 0) {
		showmessage('operating_too_fast','',1,array($waittime));
	}

	$message = getstr($_POST['message'], 0, 1, 1, 1, 2);
	if(strlen($message) < 2) {
		showmessage('content_is_too_short');
	}

	//摘要
	$summay = getstr($message, 150, 1, 1, 0, 0, -1);

	$id = intval($_POST['id']);

	//引用评论
	$cid = empty($_POST['cid'])?0:intval($_POST['cid']);
	$comment = array();
	if($cid) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('comment')." WHERE cid='$cid' AND id='$id' AND idtype='$_POST[idtype]'");
		$comment = $_SGLOBAL['db']->fetch_array($query);
		if($comment && $comment['authorid'] != $_SGLOBAL['supe_uid']) {
			//实名
			if($comment['author'] == '') {
				$_SN[$comment['authorid']] = lang('hidden_username');
			} else {
				realname_set($comment['authorid'], $comment['author']);
				realname_get();
			}
			$comment['message'] = preg_replace("/\<div class=\"quote\"\>\<span class=\"q\"\>.*?\<\/span\>\<\/div\>/is", '', $comment['message']);
			//bbcode转换
			$comment['message'] = html2bbcode($comment['message']);
			$message = addslashes("<div class=\"quote\"><span class=\"q\"><b>".$_SN[$comment['authorid']]."</b>: ".getstr($comment['message'], 150, 0, 0, 0, 2, 1).'</span></div>').$message;
			if($comment['idtype']=='uid') {
				$id = $comment['authorid'];
			}
		} else {
			$comment = array();
		}
	}

	$hotarr = array();
	$stattype = '';

	//检查权限
	switch ($idtype) {
		case 'uid':
			//检索空间
			$tospace = getspace($id);
			$stattype = 'wall';//统计
			break;
		case 'picid':
			//检索图片
			$query = $_SGLOBAL['db']->query("SELECT p.*, pf.hotuser
				FROM ".tname('pic')." p
				LEFT JOIN ".tname('picfield')." pf
				ON pf.picid=p.picid
				WHERE p.picid='$id'");
			$pic = $_SGLOBAL['db']->fetch_array($query);
			//图片不存在
			if(empty($pic)) {
				showmessage('view_images_do_not_exist');
			}

			//检索空间
			$tospace = getspace($pic['uid']);

			//获取相册
			$album = array();
			if($pic['albumid']) {
				$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('album')." WHERE albumid='$pic[albumid]'");
				if(!$album = $_SGLOBAL['db']->fetch_array($query)) {
					updatetable('pic', array('albumid'=>0), array('albumid'=>$pic['albumid']));//相册丢失
				}
			}
			//验证隐私
			if(!ckfriend($album['uid'], $album['friend'], $album['target_ids'])) {
				showmessage('no_privilege');
			} elseif(!$tospace['self'] && $album['friend'] == 4) {
				//密码输入问题
				$cookiename = "view_pwd_album_$album[albumid]";
				$cookievalue = empty($_SCOOKIE[$cookiename])?'':$_SCOOKIE[$cookiename];
				if($cookievalue != md5(md5($album['password']))) {
					showmessage('no_privilege');
				}
			}
			
			$hotarr = array('picid', $pic['picid'], $pic['hotuser']);
			$stattype = 'piccomment';//统计
			break;
		case 'blogid':
			//读取日志
			$query = $_SGLOBAL['db']->query("SELECT b.*, bf.target_ids, bf.hotuser
				FROM ".tname('blog')." b
				LEFT JOIN ".tname('blogfield')." bf ON bf.blogid=b.blogid
				WHERE b.blogid='$id'");
			$blog = $_SGLOBAL['db']->fetch_array($query);
			//日志不存在
			if(empty($blog)) {
				showmessage('view_to_info_did_not_exist');
			}
			
			//检索空间
			$tospace = getspace($blog['uid']);
			
			//验证隐私
			if(!ckfriend($blog['uid'], $blog['friend'], $blog['target_ids'])) {
				//没有权限
				showmessage('no_privilege');
			} elseif(!$tospace['self'] && $blog['friend'] == 4) {
				//密码输入问题
				$cookiename = "view_pwd_blog_$blog[blogid]";
				$cookievalue = empty($_SCOOKIE[$cookiename])?'':$_SCOOKIE[$cookiename];
				if($cookievalue != md5(md5($blog['password']))) {
					showmessage('no_privilege');
				}
			}

			//是否允许评论
			if(!empty($blog['noreply'])) {
				showmessage('do_not_accept_comments');
			}
			if($blog['target_ids']) {
				$blog['target_ids'] .= ",$blog[uid]";
			}
			
			$hotarr = array('blogid', $blog['blogid'], $blog['hotuser']);
			$stattype = 'blogcomment';//统计
			break;
		case 'sid':
			//读取日志
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('share')." WHERE sid='$id'");
			$share = $_SGLOBAL['db']->fetch_array($query);
			//日志不存在
			if(empty($share)) {
				showmessage('sharing_does_not_exist');
			}

			//检索空间
			$tospace = getspace($share['uid']);
			
			$hotarr = array('sid', $share['sid'], $share['hotuser']);
			$stattype = 'sharecomment';//统计
			break;
		case 'pid':
			$query = $_SGLOBAL['db']->query("SELECT p.*, pf.hotuser
				FROM ".tname('poll')." p
				LEFT JOIN ".tname('pollfield')." pf ON pf.pid=p.pid
				WHERE p.pid='$id'");
			$poll = $_SGLOBAL['db']->fetch_array($query);
			if(empty($poll)) {
				showmessage('voting_does_not_exist');
			}
			//是否允许评论
			$tospace = getspace($poll['uid']);
			if($poll['noreply']) {
				//是否好友
				if(!$tospace['self'] && !in_array($_SGLOBAL['supe_uid'], $tospace['friends'])) {
					showmessage('the_vote_only_allows_friends_to_comment');
				}
			}
			
			$hotarr = array('pid', $poll['pid'], $poll['hotuser']);
			$stattype = 'pollcomment';//统计
			break;
		case 'eventid':
		    // 读取活动
		    $query = $_SGLOBAL['db']->query("SELECT e.*, ef.* FROM ".tname('event')." e LEFT JOIN ".tname("eventfield")." ef ON e.eventid=ef.eventid WHERE e.eventid='$id'");
			$event = $_SGLOBAL['db']->fetch_array($query);

			if(empty($event)) {
				showmessage('event_does_not_exist');
			}
			
			if($event['grade'] < -1){
				showmessage('event_is_closed');//活动已经关闭
			} elseif($event['grade'] <= 0){
				showmessage('event_under_verify');//活动未通过审核
			}
			
			if(!$event['allowpost']){
				$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname("userevent")." WHERE eventid='$id' AND uid='$_SGLOBAL[supe_uid]' LIMIT 1");
				$value = $_SGLOBAL['db']->fetch_array($query);
				if(empty($value) || $value['status'] < 2){
					showmessage('event_only_allows_members_to_comment');//只有活动成员允许发表留言
				}
			}

			//检索空间
			$tospace = getspace($event['uid']);
			
			$hotarr = array('eventid', $event['eventid'], $event['hotuser']);
			$stattype = 'eventcomment';//统计
			break;
		default:
			showmessage('non_normal_operation');
			break;
	}
	
	if(empty($tospace)) {
		showmessage('space_does_not_exist');
	}
	
	//视频认证
	if($tospace['videostatus']) {
		if($idtype == 'uid') {
			ckvideophoto('wall', $tospace);
		} else {
			ckvideophoto('comment', $tospace);
		}
	}
	
	//黑名单
	if(isblacklist($tospace['uid'])) {
		showmessage('is_blacklist');
	}
	
	//热点
	if($hotarr && $tospace['uid'] != $_SGLOBAL['supe_uid']) {
		hot_update($hotarr[0], $hotarr[1], $hotarr[2]);
	}

	//事件
	$fs = array();
	$fs['icon'] = 'comment';
	$fs['target_ids'] = $fs['friend'] = '';

	switch ($_POST['idtype']) {
		case 'uid':
			//事件
			$fs['icon'] = 'wall';
			$fs['title_template'] = cplang('feed_comment_space');
			$fs['title_data'] = array('touser'=>"<a href=\"space.php?uid=$tospace[uid]\">".$_SN[$tospace['uid']]."</a>");
			$fs['body_template'] = '';
			$fs['body_data'] = array();
			$fs['body_general'] = '';
			$fs['images'] = array();
			$fs['image_links'] = array();
			break;
		case 'picid':
			//事件
			$fs['title_template'] = cplang('feed_comment_image');
			$fs['title_data'] = array('touser'=>"<a href=\"space.php?uid=$tospace[uid]\">".$_SN[$tospace['uid']]."</a>");
			$fs['body_template'] = '{pic_title}';
			$fs['body_data'] = array('pic_title'=>$pic['title']);
			$fs['body_general'] = $summay;
			$fs['images'] = array(pic_get($pic['filepath'], $pic['thumb'], $pic['remote']));
			$fs['image_links'] = array("space.php?uid=$tospace[uid]&do=album&picid=$pic[picid]");
			$fs['target_ids'] = $album['target_ids'];
			$fs['friend'] = $album['friend'];
			break;
		case 'blogid':
			//更新评论统计
			$_SGLOBAL['db']->query("UPDATE ".tname('blog')." SET replynum=replynum+1 WHERE blogid='$id'");
			//事件
			$fs['title_template'] = cplang('feed_comment_blog');
			$fs['title_data'] = array('touser'=>"<a href=\"space.php?uid=$tospace[uid]\">".$_SN[$tospace['uid']]."</a>", 'blog'=>"<a href=\"space.php?uid=$tospace[uid]&do=blog&id=$id\">$blog[subject]</a>");
			$fs['body_template'] = '';
			$fs['body_data'] = array();
			$fs['body_general'] = '';
			$fs['target_ids'] = $blog['target_ids'];
			$fs['friend'] = $blog['friend'];
			break;
		case 'sid':
			//事件
			$fs['title_template'] = cplang('feed_comment_share');
			$fs['title_data'] = array('touser'=>"<a href=\"space.php?uid=$tospace[uid]\">".$_SN[$tospace['uid']]."</a>", 'share'=>"<a href=\"space.php?uid=$tospace[uid]&do=share&id=$id\">".str_replace(cplang('share_action'), '', $share['title_template'])."</a>");
			$fs['body_template'] = '';
			$fs['body_data'] = array();
			$fs['body_general'] = '';
			break;
		case 'eventid':
		    // 活动
		    $fs['title_template'] = cplang('feed_comment_event');
			$fs['title_data'] = array('touser'=>"<a href=\"space.php?uid=$tospace[uid]\">".$_SN[$tospace['uid']]."</a>", 'event'=>'<a href="space.php?do=event&id='.$event['eventid'].'">'.$event['title'].'</a>');
			$fs['body_template'] = '';
			$fs['body_data'] = array();
			$fs['body_general'] = '';
			break;
		case 'pid':
			// 投票
			//更新评论统计
			$_SGLOBAL['db']->query("UPDATE ".tname('poll')." SET replynum=replynum+1 WHERE pid='$id'");
			$fs['title_template'] = cplang('feed_comment_poll');
			$fs['title_data'] = array('touser'=>"<a href=\"space.php?uid=$tospace[uid]\">".$_SN[$tospace['uid']]."</a>", 'poll'=>"<a href=\"space.php?uid=$tospace[uid]&do=poll&pid=$id\">$poll[subject]</a>");
			$fs['body_template'] = '';
			$fs['body_data'] = array();
			$fs['body_general'] = '';
			$fs['friend'] = '';
			break;
	}

	$setarr = array(
		'uid' => $tospace['uid'],
		'id' => $id,
		'idtype' => $_POST['idtype'],
		'authorid' => $_SGLOBAL['supe_uid'],
		'author' => $_SGLOBAL['supe_username'],
		'dateline' => $_SGLOBAL['timestamp'],
		'message' => $message,
		'ip' => getonlineip()
	);
	//入库
	$cid = inserttable('comment', $setarr, 1);
	$action = 'comment';
	$becomment = 'getcomment';
	switch ($_POST['idtype']) {
		case 'uid':
			$n_url = "space.php?uid=$tospace[uid]&do=wall&cid=$cid";
			$note_type = 'wall';
			$note = cplang('note_wall', array($n_url));
			$q_note = cplang('note_wall_reply', array($n_url));
			if($comment) {
				$msg = 'note_wall_reply_success';
				$magvalues = array($_SN[$tospace['uid']]);
				$becomment = '';
			} else {
				$msg = 'do_success';
				$magvalues = array();
				$becomment = 'getguestbook';
			}
			$msgtype = 'comment_friend';
			$q_msgtype = 'comment_friend_reply';
			$action = 'guestbook';
			break;
		case 'picid':
			$n_url = "space.php?uid=$tospace[uid]&do=album&picid=$id&cid=$cid";
			$note_type = 'piccomment';
			$note = cplang('note_pic_comment', array($n_url));
			$q_note = cplang('note_pic_comment_reply', array($n_url));
			$msg = 'do_success';
			$magvalues = array();
			$msgtype = 'photo_comment';
			$q_msgtype = 'photo_comment_reply';
			break;
		case 'blogid':
			//通知
			$n_url = "space.php?uid=$tospace[uid]&do=blog&id=$id&cid=$cid";
			$note_type = 'blogcomment';
			$note = cplang('note_blog_comment', array($n_url, $blog['subject']));
			$q_note = cplang('note_blog_comment_reply', array($n_url));
			$msg = 'do_success';
			$magvalues = array();
			$msgtype = 'blog_comment';
			$q_msgtype = 'blog_comment_reply';
			break;
		case 'sid':
			//分享
			$n_url = "space.php?uid=$tospace[uid]&do=share&id=$id&cid=$cid";
			$note_type = 'sharecomment';
			$note = cplang('note_share_comment', array($n_url));
			$q_note = cplang('note_share_comment_reply', array($n_url));
			$msg = 'do_success';
			$magvalues = array();
			$msgtype = 'share_comment';
			$q_msgtype = 'share_comment_reply';
			break;
		case 'pid':
			$n_url = "space.php?uid=$tospace[uid]&do=poll&pid=$id&cid=$cid";
			$note_type = 'pollcomment';
			$note = cplang('note_poll_comment', array($n_url, $poll['subject']));
			$q_note = cplang('note_poll_comment_reply', array($n_url));
			$msg = 'do_success';
			$magvalues = array();
			$msgtype = 'poll_comment';
			$q_msgtype = 'poll_comment_reply';
			break;
		case 'eventid':
		    // 活动
		    $n_url = "space.php?do=event&id=$id&view=comment&cid=$cid";
		    $note_type = 'eventcomment';
		    $note = cplang('note_event_comment', array($n_url));
		    $q_note = cplang('note_event_comment_reply', array($n_url));
		    $msg = 'do_success';
		    $magvalues = array();
		    $msgtype = 'event_comment';
		    $q_msgtype = 'event_comment_reply';
		    break;
	}

	if(empty($comment)) {
		
		//非引用评论
		if($tospace['uid'] != $_SGLOBAL['supe_uid']) {
			//事件发布
			if(ckprivacy('comment', 1)) {
				feed_add($fs['icon'], $fs['title_template'], $fs['title_data'], $fs['body_template'], $fs['body_data'], $fs['body_general'],$fs['images'], $fs['image_links'], $fs['target_ids'], $fs['friend']);
			}
			
			//发送通知
			notification_add($tospace['uid'], $note_type, $note);
			
			//留言发送短消息
			if($_POST['idtype'] == 'uid' && $tospace['updatetime'] == $tospace['dateline']) {
				include_once S_ROOT.'./uc_client/client.php';
				uc_pm_send($_SGLOBAL['supe_uid'], $tospace['uid'], cplang('wall_pm_subject'), cplang('wall_pm_message', array(addslashes(getsiteurl().$n_url))), 1, 0, 0);
			}
			
			//发送邮件通知
			smail($tospace['uid'], '', cplang($msgtype, array($_SN[$space['uid']], shtmlspecialchars(getsiteurl().$n_url))), '', $msgtype);
		}
		
	} elseif($comment['authorid'] != $_SGLOBAL['supe_uid']) {
		
		//发送邮件通知
		smail($comment['authorid'], '', cplang($q_msgtype, array($_SN[$space['uid']], shtmlspecialchars(getsiteurl().$n_url))), '', $q_msgtype);
		notification_add($comment['authorid'], $note_type, $q_note);
		
	}
	
	//统计
	if($stattype) {
		updatestat($stattype);
	}

	//积分
	if($tospace['uid'] != $_SGLOBAL['supe_uid']) {
		$needle = $id;
		if($_POST['idtype'] != 'uid') {
			$needle = $_POST['idtype'].$id;
		} else {
			$needle = $tospace['uid'];
		}
		//奖励评论发起者
		getreward($action, 1, 0, $needle);
		//奖励被评论者
		if($becomment) {
			if($_POST['idtype'] == 'uid') {
				$needle = $_SGLOBAL['supe_uid'];
			}
			getreward($becomment, 1, $tospace['uid'], $needle, 0);
		}
	}

	showmessage($msg, $_POST['refer'], 0, $magvalues);
}

$cid = empty($_GET['cid'])?0:intval($_GET['cid']);

//编辑
if($_GET['op'] == 'edit') {

	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('comment')." WHERE cid='$cid' AND authorid='$_SGLOBAL[supe_uid]'");
	if(!$comment = $_SGLOBAL['db']->fetch_array($query)) {
		showmessage('no_privilege');
	}

	//提交编辑
	if(submitcheck('editsubmit')) {

		$message = getstr($_POST['message'], 0, 1, 1, 1, 2);
		if(strlen($message) < 2) showmessage('content_is_too_short');

		updatetable('comment', array('message'=>$message), array('cid'=>$comment['cid']));

		showmessage('do_success', $_POST['refer'], 0);
	}

	//bbcode转换
	$comment['message'] = html2bbcode($comment['message']);//显示用

} elseif($_GET['op'] == 'delete') {

	if(submitcheck('deletesubmit')) {
		include_once(S_ROOT.'./source/function_delete.php');
		if(deletecomments(array($cid))) {
			showmessage('do_success', $_POST['refer'], 0);
		} else {
			showmessage('no_privilege');
		}
	}

} elseif($_GET['op'] == 'reply') {

	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('comment')." WHERE cid='$cid'");
	if(!$comment = $_SGLOBAL['db']->fetch_array($query)) {
		showmessage('comments_do_not_exist');
	}

} else {

	showmessage('no_privilege');
}

include template('cp_comment');

?>