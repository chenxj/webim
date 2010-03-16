<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: class_tree.php 8006 2008-07-09 05:59:42Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$eventid = isset($_GET['id']) ? intval($_GET['id']) : 0;
$op = empty($_GET['op']) ? "edit" : $_GET['op'];

$menus = array();
$menus[$op] = " class='active'";

// 验证活动是否存在及当前用户与活动的关系
$allowmanage=  false; // 活动管理权限
if($eventid){
	$query = $_SGLOBAL['db']->query("SELECT e.*, ef.* FROM ".tname("event")." e LEFT JOIN ".tname("eventfield")." ef ON e.eventid=ef.eventid WHERE e.eventid='$eventid'");
	$event = $_SGLOBAL['db']->fetch_array($query);
	if(! $event){
		showmessage("event_does_not_exist"); // 活动不存在或者已被删除
	}
	if(($event['grade']==-1 || $event['grade'] == 0) && $event['uid'] != $_SGLOBAL['supe_uid'] && !checkperm('manageevent')){
		showmessage('event_under_verify');// 活动正在审核中
	}
	$query = $_SGLOBAL['db']->query("SELECT * FROM " . tname("userevent") . " WHERE eventid='$eventid' AND uid='$_SGLOBAL[supe_uid]'");
	$value = $_SGLOBAL['db']->fetch_array($query);
	$_SGLOBAL['supe_userevent'] = $value ? $value : array();	
	if($value['status'] >= 3 || checkperm('manageevent')){
		$allowmanage = true; // 活动管理权限
	}
}

// 获取活动分类信息
if(!@include_once(S_ROOT.'./data/data_eventclass.php')) {
	include_once(S_ROOT.'./source/function_cache.php');
	eventclass_cache();
}

// 发布/编辑活动
if(submitcheck('eventsubmit')) {
	
	//验证码
	if(checkperm('seccode') && !ckseccode($_POST['seccode'])) {
		showmessage('incorrect_code');
	}
	
	// 基本信息
	$arr1 = array(
		"title" => getstr($_POST['title'], 80, 1, 1, 1),
		"classid" => intval($_POST['classid']),
		"province" => getstr($_POST['province'], 20, 1, 1),
		"city" => getstr($_POST['city'], 20, 1, 1),
		"location" => getstr($_POST['location'], 80, 1, 1, 1),
		"starttime" => sstrtotime($_POST['starttime']),
		"endtime" => sstrtotime($_POST['endtime']),
		"deadline" => sstrtotime($_POST['deadline']),
		"public" => intval($_POST['public'])
	);
	// 扩展信息
	$arr2 = array(
		"detail" => getstr($_POST['detail'], '', 1, 1, 1, 0, 1),
		"limitnum" => intval($_POST['limitnum']),
		"verify" => intval($_POST['verify']),
		"allowpost" => intval($_POST['allowpost']),
		"allowpic" => intval($_POST['allowpic']),
		"allowfellow" => intval($_POST['allowfellow']),
		"allowinvite" => intval($_POST['allowinvite']),
		"template" => getstr($_POST['template'], 255, 1, 1, 1)
	);
	
	//检查输入
	if(empty($arr1['title'])){
		showmessage('event_title_empty');
	} elseif(empty($arr1['classid'])){
		showmessage('event_classid_empty');
	} elseif(empty($arr1['city'])) {
		showmessage('event_city_empty');
	} elseif(empty($arr2['detail'])) {
		showmessage('event_detail_empty');
	} elseif($arr1['endtime']-$arr1['starttime']>60 * 24 * 3600) {
		showmessage('event_bad_time_range');		
	} elseif($arr1['endtime']<$arr1['starttime']) {
		showmessage('event_bad_endtime');
	} elseif($arr1['deadline']>$arr1['endtime']) {
		showmessage('event_bad_deadline');
	} elseif(!$eventid && $arr1['starttime']<$_SGLOBAL['timestamp']) {
		showmessage('event_bad_starttime');
	}
	
	// 处理海报
	$pic = array();
	if($_FILES['poster']['tmp_name']){
		// 存到默认相册
		$pic = pic_save($_FILES['poster'], -1, $arr1['title']);
		if(is_array($pic) && $pic['filepath']){// 上传成功
			$arr1['poster'] = $pic['filepath'];
			$arr1['thumb'] = $pic['thumb'];
			$arr1['remote'] = $pic['remote'];
		}
	}
	
	//关联群组
	if($_POST['tagid'] && (!$eventid || $event['uid']==$_SGLOBAL['supe_uid']) && $_POST['tagid'] != $event['tagid']) {
		$_POST['tagid'] = intval($_POST['tagid']);
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname("tagspace")." WHERE tagid='$_POST[tagid]' AND uid='$_SGLOBAL[supe_uid]' LIMIT 1");
		if($value=$_SGLOBAL['db']->fetch_array($query)) {
			if($value['grade'] == 9) {
				$arr1['tagid'] = $value['tagid'];
			}
		}
	}

	if($eventid){// 修改已有活动
		if($allowmanage){
			//如果是未通过审核活动修改了，重置为待审核
			if($event['grade']==-1 && $event['uid'] == $_SGLOBAL['supe_uid']) {
				$arr1['grade'] = 0;
			}
			updatetable("event", $arr1, array("eventid"=>$eventid));
			updatetable("eventfield", $arr2, array("eventid"=>$eventid));
			// 共享海报
			if($_POST['sharepic'] && !empty($pic['picid'])){
				$arr = array(
					"eventid"=>$eventid,
					"picid"=>$pic['picid'],
					"uid"=>$_SGLOBAL['supe_uid'],
					"username"=>$_SGLOBAL['supe_username'],
					"dateline"=>$_SGLOBAL['timestamp']
				);
				inserttable("eventpic", $arr);
			}
			showmessage('do_success', 'space.php?do=event&id='.$eventid, 0);			
		} else {
			showmessage('no_privilege_edit_event');
		}

	} else {// 生成新的活动
	
		//实名认证
		ckrealname('event');
		
		//视频认证
		ckvideophoto('event');
		
		//新用户见习
		cknewuser();
	
		$_POST['topicid'] = topic_check($_POST['topicid'], 'event');
		$arr1['topicid'] = $_POST['topicid'];
		
		// 创建者
		$arr1['uid'] = $_SGLOBAL['supe_uid'];
		$arr1['username'] = $_SGLOBAL['supe_username'];
		// 创建时间
		$arr1['dateline'] = $_SGLOBAL['timestamp'];
		$arr1['updatetime'] = $_SGLOBAL['timestamp'];
		
		//人数
		$arr1['membernum'] = 1;
		
		// 是否需要审核
		$arr1['grade'] = checkperm("verifyevent") ? 0 : 1;

		// 插入 活动（event） 表
		$eventid = inserttable("event", $arr1, 1);
		if (! $eventid){
			showmessage("event_create_failed"); // 创建活动失败，请检查你输入的内容
		}
		// 活动信息
		$arr2['eventid'] = $eventid;
		inserttable("eventfield", $arr2);
		// 共享海报
		if($_POST['sharepic'] && !empty($pic['picid'])){
			$arr = array(
				"eventid"=>$eventid,
				"picid"=>$pic['picid'],
				"uid"=>$_SGLOBAL['supe_uid'],
				"username"=>$_SGLOBAL['supe_username'],
				"dateline"=>$_SGLOBAL['timestamp']
				);
			inserttable("eventpic", $arr);
		}
		$arr3 = array(
			"eventid" => $eventid,
			"uid" => $_SGLOBAL['supe_uid'],
			"username" => $_SGLOBAL['supe_username'],
			"status" => 4,  // 发起者
			"fellow" => 0,
			"template" => $arr1['template'],
			"dateline" => $_SGLOBAL['timestamp']
		   );
		// 插入 用户活动（userevent） 表
		inserttable("userevent", $arr3);
		if($arr1['grade'] > 0){
			//事件
			if($_POST['makefeed']) {
				include_once(S_ROOT.'./source/function_feed.php');
				feed_publish($eventid, 'eventid', 1);
			}
		}
		
		//统计
		updatestat('event');
		
		//更新用户统计
		if(empty($space['eventnum'])) {
			$space['eventnum'] = getcount('event', array('uid'=>$space['uid']));
			$eventnumsql = "eventnum=".$space['eventnum'];
		} else {
			$eventnumsql = 'eventnum=eventnum+1';
		}
		
		//积分
		$reward = getreward('createevent', 0);
		$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET {$eventnumsql}, lastpost='$_SGLOBAL[timestamp]', updatetime='$_SGLOBAL[timestamp]', credit=credit+$reward[credit], experience=experience+$reward[experience] WHERE uid='$_SGLOBAL[supe_uid]'");
			
		if($_POST['topicid']) {
			topic_join($_POST['topicid'], $_SGLOBAL['supe_uid'], $_SGLOBAL['supe_username']);
			$url = 'space.php?do=topic&topicid='.$_POST['topicid'].'&view=event';
		} else {
			$url = 'space.php?do=event&id='.$eventid;
		}
		
		showmessage('do_success', $url, 0); // 查看活动
	}
}

if($op == 'invite') {
	
	// 非活动成员或者不允许邀请的情况下非组织者没有邀请权限
	if((!$event['allowinvite'] && $_SGLOBAL['supe_userevent']['status'] < 3) || ($_SGLOBAL['supe_userevent']['status'] < 2)){
		showmessage("no_privilege_do_eventinvite");
	}
	
	if(submitcheck('invitesubmit')){
		$arr = array("uid"=>$_SGLOBAL['supe_uid'], "username"=>$_SGLOBAL['supe_username'], "eventid"=>$eventid, "dateline"=>$_SGLOBAL['timestamp']);
		$inserts = array();
		$touids = array();
		for($i=0, $L=sizeof($_POST['ids']); $i<$L; $i++){
			$arr['touid'] = intval($_POST['ids'][$i]);
			$arr['tousername'] = getstr($_POST['names'][$i], 15, 1, 1);
			$inserts[] = "(".simplode($arr).")";
			$touids[] = $arr['touid'];
		}
		if($inserts) {
			$_SGLOBAL['db']->query("INSERT INTO ".tname("eventinvite")."(uid, username, eventid, dateline, touid, tousername) VALUES ".implode(",", $inserts));
			$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET eventinvitenum=eventinvitenum+1 WHERE uid IN (".simplode($touids).")");
		}
		$_GET['group'] = isset($_GET['group']) ? intval($_GET['group']) : -1;
		$_GET['page'] = empty($_GET['page'])?0:intval($_GET['page']);
		showmessage("do_success", "cp.php?ac=event&op=invite&id=$eventid&group=$_GET[group]&page=$_GET[page]", 2);
	}

	//分页
	$perpage = 21;
	$page = empty($_GET['page'])?0:intval($_GET['page']);
	if($page<1) $page = 1;
	$start = ($page-1)*$perpage;
	//检查开始数
	ckstart($start, $perpage);

	$wherearr = array();
	$_GET['key'] = stripsearchkey($_GET['key']);
	if($_GET['key']) {
		$wherearr[] = " fusername LIKE '%$_GET[key]%' ";
	}

	$_GET['group'] = isset($_GET['group'])?intval($_GET['group']):-1;
	if($_GET['group'] >= 0) {
		$wherearr[] = " gid='$_GET[group]'";
	}

	$sql = $wherearr ? 'AND'.implode(' AND ', $wherearr) : '';

	$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('friend')." WHERE uid='$_SGLOBAL[supe_uid]' AND status='1' $sql"), 0);

	$fuids = array();
	$list = array();
	if($count) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('friend')." WHERE uid='$_SGLOBAL[supe_uid]' AND status='1' $sql ORDER BY num DESC, dateline DESC LIMIT $start,$perpage");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			realname_set($value['fuid'], $value['fusername']);
			$list[] = $value;
			$fuids[] = $value['fuid'];
		}
	}

	//是否已加入
	$joins = array();
	$query = $_SGLOBAL['db']->query("SELECT uid FROM ".tname('userevent')." WHERE eventid='$eventid' AND uid IN (".simplode($fuids).") AND status > 1");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$joins[$value['uid']] = $value['uid'];
	}

	//是否邀请
	$query = $_SGLOBAL['db']->query("SELECT touid FROM ".tname('eventinvite')." WHERE eventid='$eventid' AND touid IN (".simplode($fuids).")");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$joins[$value['touid']] = $value['touid'];
	}

	//用户组
	$groups = getfriendgroup();
	$groupselect = array($_GET['group'] => ' selected');

	$multi = multi($count, $perpage, $page, "cp.php?ac=event&op=invite&id=$eventid&group=$_GET[group]&key=$_GET[key]");

} elseif($op == 'members') {
	// 成员管理

	if($_SGLOBAL['supe_userevent']['status'] < 3){
		showmessage('no_privilege_manage_event_members');//您没有权限管理活动成员
	}

	if(submitcheck("memberssubmit")){
		
		$_POST['status'] = intval($_POST['status']);
		
		if($_POST['ids'] && verify_eventmembers($_POST['ids'], $_POST['status'])){
			showmessage("do_success", "cp.php?ac=event&op=members&id=$eventid&status=$_GET[status]", 2);
		} else {
			showmessage("choose_right_eventmember", "cp.php?ac=event&op=members&id=$eventid&status=$_GET[status]", 5);
		}
	}
	
	//分页
	$perpage = 24;
	$start = empty($_GET['start'])?0:intval($_GET['start']);
	$list = array();
	$count = 0;

	//检索
	$wheresql = '';	
	if($_GET['key']) {
		$_GET['key'] = stripsearchkey($_GET['key']);
		$wheresql = " AND username LIKE '%$_GET[key]%' ";
	} else {
		$_GET['status'] = intval($_GET['status']);
		$wheresql = " AND status='$_GET[status]'";		
	}

	//检查开始数
	ckstart($start, $perpage);
	
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('userevent')." WHERE eventid='$eventid' $wheresql LIMIT $start,$perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		realname_set($value['uid'], $value['username']);
		$list[] = $value;
		$count++;
	}
	
	if($_GET['key']){
		$_GET['status'] = $list[0]['status'];
	}

	$multi = smulti($start, $perpage, $count, "cp.php?ac=event&op=members&id=$eventid&status=$_GET[status]&key=$_GET[key]");

} elseif($op == 'member'){
	// 设置单个成员

	if($_SGLOBAL['supe_userevent']['status'] < 3){
		showmessage('no_privilege_manage_event_members');//您没有权限管理活动成员
	}

	if(submitcheck("membersubmit")){
		$_POST['status'] = intval($_POST['status']);
		if($_POST['uid'] && verify_eventmembers(array($_POST['uid']), $_POST['status'])){
			$refer = empty($_POST['refer']) ? "space.php?do=event&id=$eventid&view=member&status=$_POST[status]" : $_POST['refer'];
			showmessage("do_success", $refer , 0);	
		} else {
			showmessage("choose_right_eventmember");
		}
	}
	
	$_GET['uid'] = intval($_GET['uid']);
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname("userevent")." WHERE uid='$_GET[uid]' AND eventid='$eventid'");
	$userevent = $_SGLOBAL['db']->fetch_array($query);
	if(empty($userevent)){
		showmessage("choose_right_eventmember");
	}
	$userevent['template'] = nl2br(getstr($userevent['template'], 255, 1, 0, 1));

} elseif($op == 'comment') {// 活动留言
	
	if(!$allowmanage){
		showmessage("no_privilege_manage_event_comment");
	}

	showmessage("redirect", "admincp.php?ac=comment&idtype=eventid&id=$eventid", 0);

} elseif($op == 'pic') {// 活动照片

	if(!$allowmanage){
		showmessage("no_privilege_manage_event_pic");
	}

	if(submitcheck("deletepicsubmit")){
		if(! empty($_POST['ids'])) {
			$query = $_SGLOBAL['db']->query("DELETE FROM " . tname("eventpic") . " WHERE eventid='$eventid' AND picid IN (".simplode($_POST['ids']).")");
			$_SGLOBAL['db']->query("UPDATE ".tname("event")." SET picnum = (SELECT COUNT(*) FROM ".tname("eventpic")." WHERE eventid='$eventid') WHERE eventid = '$eventid'");
			showmessage("do_success", "cp.php?ac=event&op=pic&id=$eventid", 0);
		} else {
			showmessage("choose_event_pic");
		}
	}

	//分页
	$perpage = 16;
	$page = empty($_GET['page'])?1:intval($_GET['page']);
	if($page<1) $page=1;
	$start = ($page-1)*$perpage;

	//检查开始数
	ckstart($start, $perpage);

	//处理查询
	$theurl = "cp.php?ac=event&id=$eventid&op=pic";

	$photolist = array();
	$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname("eventpic")." WHERE eventid = '$eventid'"), 0);
	if($count) {
		$query = $_SGLOBAL['db']->query("SELECT pic.* FROM ".tname("eventpic")." ep LEFT JOIN ".tname("pic")." pic ON ep.picid=pic.picid WHERE ep.eventid='$eventid' ORDER BY ep.picid DESC LIMIT $start, $perpage");
		while($value = $_SGLOBAL['db']->fetch_array($query)){
			$value['pic'] = pic_get($value['filepath'], $value['thumb'], $value['remote']);
			$photolist[] = $value;
		}
	}

	//分页
	$multi = multi($count, $perpage, $page, $theurl);

} elseif($op == 'thread') {//活动话题
	
	if(!$allowmanage){
		showmessage("no_privilege_manage_event_thread");
	}
	if(!$event['tagid']) {
		showmessage('event_has_not_mtag');//活动没有关联群组
	}
	
	if(submitcheck('delthreadsubmit')) {
		if(!empty($_POST['ids'])) {
			$_SGLOBAL['db']->query("DELETE FROM ".tname("thread")." WHERE eventid='$eventid' AND tid IN (".simplode($_POST['ids']).")");
			$_SGLOBAL['db']->query("UPDATE ".tname("event")." SET threadnum = (SELECT COUNT(*) FROM ".tname("thread")." WHERE eventid='$eventid') WHERE eventid = '$eventid'");
			showmessage('do_success',"cp.php?ac=event&id=$eventid&op=thread",0);
		} else {
			showmessage('choose_event_thread');
		}
	}
	
	//分页
	$perpage = 20;
	$page = empty($_GET['page'])?1:intval($_GET['page']);
	if($page<1) $page=1;
	$start = ($page-1)*$perpage;

	//检查开始数
	ckstart($start, $perpage);
	$threadlist = array();
	$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname("thread")." WHERE eventid = '$eventid'"), 0);
	if($count) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname("thread")." WHERE eventid='$eventid' ORDER BY lastpost DESC LIMIT $start, $perpage");
		while($value = $_SGLOBAL['db']->fetch_array($query)){
			realname_set($value['uid'], $value['username']);
			realname_set($value['lastauthorid'], $value['lastauthor']);
			$threadlist[] = $value;
		}
	}

	//分页
	$multi = multi($count, $perpage, $page, $theurl);	
	
} elseif($op == 'join') {// 加入一个活动或修改报名信息
	
	if(isblacklist($event['uid'])) {
		$_GET['popupmenu_box'] = true;//开启关闭
		showmessage('is_blacklist');//黑名单
	}
	//新成员加入，检查加入条件
	if(empty($_SGLOBAL['supe_userevent'])){
		$_GET['popupmenu_box'] = true;//开启关闭
		if($_SGLOBAL['timestamp'] > $event['endtime']){	
			showmessage('event_is_over');// 活动已经结束
		}
		
		if($_SGLOBAL['timestamp'] > $event['deadline']){
			showmessage("event_meet_deadline"); // 活动已经截止报名
		}
		
		if($event['limitnum']>0 && $event['membernum']>=$event['limitnum']){
			showmessage('event_already_full');//活动人数已满
		}
		
		// 非公开活动，需要邀请才能加入
		if($event['public'] < 2){
			$query = $_SGLOBAL['db']->query("SELECT * FROM " . tname("eventinvite") . " WHERE eventid = '$event[eventid]' AND touid = '$_SGLOBAL[supe_uid]' LIMIT 1");
			$value = $_SGLOBAL['db']->fetch_array($query);
			if(empty($value)){				
				showmessage("event_join_limit"); // 此活动只有通过邀请才能加入
			}
		}
	}

	if(submitcheck("joinsubmit")){
		// 审核状态的人修改报名信息
		if(!empty($_SGLOBAL['supe_userevent']) && $_SGLOBAL['supe_userevent']['status'] == 0){
			$arr = array();

			if(isset($_POST['fellow'])){
				$arr['fellow'] = intval($_POST['fellow']);// 修改携带人数
			}
			if($_POST['template']){// 报名信息
				$arr['template'] = getstr($_POST['template'], 255, 1, 1, 1);
			}
			if($arr){
				updatetable("userevent", $arr, array("eventid"=>$eventid, "uid"=>$_SGLOBAL['supe_uid']));
			}
			showmessage("do_success", "space.php?do=event&id=$eventid", 2);
		}

		// 已经参加活动的人，修改报名信息
		if(!empty($_SGLOBAL['supe_userevent']) && $_SGLOBAL['supe_userevent']['status'] > 1){
			$arr = array();
			$num = 0; // 活动参与人数变化

			if(isset($_POST['fellow'])){// 修改携带人数
				$_POST['fellow'] = intval($_POST['fellow']);
				$arr['fellow'] = $_POST['fellow'];// 修改参加人数
				$num = $_POST['fellow'] - $_SGLOBAL['supe_userevent']['fellow'];
				// 检查人数
				if ($event['limitnum'] > 0 && $num + $event['membernum'] > $event['limitnum']){
					showmessage("event_already_full");
				}
			}
			if($_POST['template']){// 报名信息
				$arr['template'] = $_POST['template'];
			}
			if($arr){
				updatetable("userevent", $arr, array("eventid"=>$eventid, "uid"=>$_SGLOBAL['supe_uid']));
			}
			if($num){
				$_SGLOBAL['db']->query("UPDATE " . tname("event") . " SET membernum = membernum + ($num) WHERE eventid=$eventid");
			}
			showmessage("do_success", "space.php?do=event&id=$eventid", 0);
		}
		
		// 用户活动信息
		$arr = array(
			"eventid" => $eventid,
			"uid" => $_SGLOBAL['supe_uid'],
			"username" => $_SGLOBAL['supe_username'],
			"status" => 2,
			"template" => $event['template'],
			"fellow" => 0,
			"dateline" => $_SGLOBAL['timestamp']
		   );
		// 活动人数变化
		$num = 1;
		$numsql = "";

		if($_POST['fellow']){
			$arr['fellow'] = intval($_POST['fellow']);
			$num += $arr['fellow'];
		}
		if($_POST['template']){// 报名信息
			$arr['template'] = getstr($_POST['template'], 255, 1, 1, 1);
		}
		
		if ($event['limitnum'] > 0 && $num + $event['membernum'] > $event['limitnum']){
			showmessage("event_will_full");
		}
		$numsql = " membernum = membernum + ($num) ";
		
		// 检查是否有活动邀请
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname("eventinvite")." WHERE eventid='$eventid' AND touid='$_SGLOBAL[supe_uid]'");
		$eventinvite = $_SGLOBAL['db']->fetch_array($query);
		// 需要审核
		if($event['verify'] && !$eventinvite){			
			$arr['status'] = 0; // 待审核
		}

		// 插入 用户活动（userevent） 表		
		if($_SGLOBAL['supe_userevent']['status'] == 1){
			// 关注者参加，关注人数减1
			updatetable("userevent", $arr, array("uid"=>$_SGLOBAL['supe_uid'], "eventid"=>$eventid));
			$numsql .= ",follownum = follownum - 1 ";
		} else {
			// 直接参加
			inserttable("userevent", $arr, 0);
		}

		// 活动人数（参加/关注）修改
		if($arr['status'] == 2){
			$_SGLOBAL['db']->query("UPDATE " . tname("event") . " SET $numsql WHERE eventid = '$eventid'");
			if(ckprivacy('join')){
				realname_set($event['uid'], $event['username']);
				realname_get();				
				feed_add('event', cplang('event_join'), array('title'=>$event['title'], "eventid"=>$event['eventid'], "uid"=>$event['uid'], "username"=>$_SN[$event['uid']]));
			}
		} elseif($arr['status'] == 0){
			if($_SGLOBAL['supe_userevent']['status'] == 1){
				//关注人数减1
				$_SGLOBAL['db']->query("UPDATE " . tname("event") . " SET follownum = follownum - 1 WHERE eventid = '$eventid'");
			}
			//给活动组织者发送审核通知
			$note_inserts = array();
			$note_ids = array();
			$note_msg = cplang('event_join_verify', array("space.php?do=event&id=$event[eventid]", $event['title'], "cp.php?ac=event&id=$event[eventid]&op=members&status=0&key=$arr[username]"));
			$query = $_SGLOBAL['db']->query("SELECT ue.*, sf.* FROM ".tname("userevent")." ue LEFT JOIN ".tname("spacefield")." sf ON ue.uid=sf.uid WHERE ue.eventid='$eventid' AND ue.status >= 3");
			while($value=$_SGLOBAL['db']->fetch_array($query)){
				$value['privacy'] = empty($value['privacy']) ? array() : unserialize($value['privacy']);
				$filter = empty($value['privacy']['filter_note'])?array():array_keys($value['privacy']['filter_note']);
				if(cknote_uid(array("type"=>"eventmember","authorid"=>$_SGLOBAL['supe_uid']),$filter)){
					$note_ids[] = $value['uid'];
					$note_inserts[] = "('$value[uid]', 'eventmember', '1', '$_SGLOBAL[supe_uid]', '$_SGLOBAL[supe_username]', '".addslashes($note_msg)."', '$_SGLOBAL[timestamp]')";
				}
			}
			if($note_inserts){
				$_SGLOBAL['db']->query("INSERT INTO ".tname('notification')." (`uid`, `type`, `new`, `authorid`, `author`, `note`, `dateline`) VALUES ".implode(',', $note_inserts));
				$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET notenum=notenum+1 WHERE uid IN (".simplode($note_ids).")");
			}
			
			//邮件提醒
			smail($event['uid'], '', $note_msg, 'event');
		}
		
		//奖励积分
		getreward('joinevent', 1, 0, $eventid);
		
		//统计
		updatestat('eventjoin');
		
		//处理活动邀请
		if($eventinvite){
			$_SGLOBAL['db']->query("DELETE FROM ".tname("eventinvite")." WHERE eventid='$eventid' AND touid='$_SGLOBAL[supe_uid]'");
			$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET eventinvitenum=eventinvitenum-1 WHERE uid = '$_SGLOBAL[supe_uid]' AND eventinvitenum>0");
		}
		
		showmessage("do_success", "space.php?do=event&id=$eventid", 0); // 加入活动成功
	}

} elseif($op == "quit") {
	// 退出
	if(! $eventid){
		showmessage("event_does_not_exist"); // 活动不存在或者已被删除
	}

	if(submitcheck("quitsubmit")){

		$tourl = "space.php?do=event&id=$eventid";
		$uid = $_SGLOBAL['supe_uid'];
		$userevent = $_SGLOBAL['supe_userevent'];

		// 已经加入活动的非创建者
		if(! empty($userevent) && $event['uid'] != $uid){
			$_SGLOBAL['db']->query("DELETE FROM " . tname("userevent") . " WHERE eventid='$eventid' AND uid='$uid'");
			if($userevent['status']>=2){
				// 修改活动人数
				$num = 1 + $userevent['fellow'];
				$_SGLOBAL['db']->query("UPDATE " . tname("event") . " SET membernum = membernum - $num WHERE eventid='$eventid'");
			}
			showmessage("do_success", $tourl, 0);
		} else {
			showmessage("cannot_quit_event", $tourl, 2); // 你不能退出活动，原因是你还没有加入活动或者你是这个活动的发起人。
		}
	}

} elseif($op == "follow") {
	// 关注
	if(! $eventid){
		showmessage("event_does_not_exist"); // 活动不存在或者已被删除
	}
	
	if(!empty($_SGLOBAL['supe_userevent'])){
		$_GET['popupmenu_box'] = true;//开启关闭
		if($_SGLOBAL['supe_userevent']['status']<=1) {
			showmessage("event_has_followed");//您已经关注了此活动
		} else {
			showmessage("event_has_joint");//您已经加入了此活动
		}
	}
	
	//[to do:检查已经参加活动的人，优先级：低]
	if(submitcheck("followsubmit")){

		$arr = array(
			"eventid" => $eventid,
			"uid" => $_SGLOBAL['supe_uid'],
			"username" => $_SGLOBAL['supe_username'],
			"status" => 1,
			"fellow" => 0,
			"template" => $event['template']
		   );
		inserttable("userevent", $arr);

		$_SGLOBAL['db']->query("UPDATE " . tname("event") . " SET follownum = follownum + 1 WHERE eventid='$eventid'");
		showmessage("do_success", "space.php?do=event&id=$eventid", 0);
	}

} elseif($op == "cancelfollow") {
	// 取消关注
	if(! $eventid){
		showmessage("event_does_not_exist"); // 活动不存在或者已被删除
	}

	if(submitcheck("cancelfollowsubmit")){

		if($_SGLOBAL['supe_userevent']['status'] == 1){
			$_SGLOBAL['db']->query("DELETE FROM " . tname("userevent") . " WHERE uid='$_SGLOBAL[supe_uid]' AND eventid='$eventid'");
			$_SGLOBAL['db']->query("UPDATE " . tname("event") . " SET follownum = follownum - 1 WHERE eventid='$eventid'");
		}
		showmessage("do_success", "space.php?do=event&id=$eventid", 0);
	}

} elseif($op == 'eventinvite') {
	
	if($_GET['r']) {// 拒绝
		$tourl = "cp.php?ac=event&op=eventinvite" . (isset($_GET['page']) ? "&page=" . intval($_GET['page']) : "");	
		if($eventid) {// 传入了活动id
			$_SGLOBAL['db']->query("DELETE FROM ". tname("eventinvite") . " WHERE eventid = '$eventid' AND touid = '$_SGLOBAL[supe_uid]'");
			$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET eventinvitenum=eventinvitenum-1 WHERE uid = '$_SGLOBAL[supe_uid]' AND eventinvitenum>0");
		} else {// 所有
			$_SGLOBAL['db']->query("DELETE FROM ". tname("eventinvite") . " WHERE touid = '$_SGLOBAL[supe_uid]'");
			$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET eventinvitenum=0 WHERE uid = '$_SGLOBAL[supe_uid]'");
		}
	
		showmessage("do_success", $tourl, 0);
	}

	//分页
	$perpage = 20;
	$page = empty($_GET['page'])?1:intval($_GET['page']);
	if($page<1) $page=1;
	$start = ($page-1)*$perpage;

	//检查开始数
	ckstart($start, $perpage);

	//处理查询
	$theurl = "cp.php?ac=event&op=eventinvite";
	$count = getcount("eventinvite", array("touid"=>$_SGLOBAL['supe_uid']));
	
	//更新统计
	if($count != $space['eventinvitenum']) {
		updatetable('space', array('eventinvitenum'=>$count), array('uid'=>$space['uid']));
	}
		
	$eventinvites = array();
	if($count > 0) {
		// 未处理活动邀请
		$query = $_SGLOBAL['db']->query("SELECT ei.*, e.*, ei.dateline as invitetime FROM ".tname("eventinvite")." ei LEFT JOIN ".tname("event")." e ON ei.eventid=e.eventid WHERE ei.touid='$_SGLOBAL[supe_uid]' limit $start, $perpage");
		while($value = $_SGLOBAL['db']->fetch_array($query)){
			realname_set($value['uid'], $value['username']);
			if($value['poster']){
				$value['pic'] = pic_get($value['poster'], $value['thumb'], $value['remote']);
			} else {
				$value['pic'] = $_SGLOBAL['eventclass'][$value['classid']]['poster'];
			}
			$eventinvites[] = $value;
		}
	}

	//分页
	$multi = multi($count, $perpage, $page, $theurl);

} elseif($op == 'acceptinvite') {
	//接受邀请	
	if(! $eventid){
		showmessage("event_does_not_exist"); // 活动不存在或者已被删除
	}
	
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname("eventinvite")." WHERE eventid='$eventid' AND touid='$_SGLOBAL[supe_uid]' LIMIT 1");
	$eventinvite = $_SGLOBAL['db']->fetch_array($query);
	
	if(!$eventinvite) {
		showmessage('eventinvite_does_not_exist');//你没有该活动的活动邀请
	}
	
	$_SGLOBAL['db']->query("DELETE FROM ".tname("eventinvite")." WHERE eventid='$eventid' AND touid='$_SGLOBAL[supe_uid]'");
	$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET eventinvitenum=eventinvitenum-1 WHERE uid = '$_SGLOBAL[supe_uid]' AND eventinvitenum>0");
		
	if(isblacklist($event['uid'])) {
		showmessage('is_blacklist');//黑名单
	}	
	if($_SGLOBAL['timestamp'] > $event['endtime']){	
		showmessage('event_is_over');// 活动已经结束
	}
	if($_SGLOBAL['timestamp'] > $event['deadline']){
		showmessage("event_meet_deadline"); // 活动已经截止报名
	}	
	if($event['limitnum']>0 && $event['membernum']>=$event['limitnum']){
		showmessage('event_already_full');//活动人数已满
	}
	
	$numsql = "membernum = membernum + 1";	
	if(empty($_SGLOBAL['supe_userevent'])){		
		$arr = array(
			"eventid" => $eventid,
			"uid" => $_SGLOBAL['supe_uid'],
			"username" => $_SGLOBAL['supe_username'],
			"status" => 2,
			"template" => $event['template'],
			"fellow" => 0,
			"dateline" => $_SGLOBAL['timestamp']
		   );
		inserttable("userevent", $arr, 0);
		$_SGLOBAL['db']->query("UPDATE " . tname("event") . " SET $numsql WHERE eventid = '$eventid'");
		if(ckprivacy('join')){
			realname_set($event['uid'], $event['username']);
			realname_get();
			feed_add('event', cplang('event_join'), array('title'=>$event['title'], "eventid"=>$event['eventid'], "uid"=>$event['uid'], "username"=>$_SN[$event['uid']]));
		}
	} elseif($_SGLOBAL['supe_userevent'] && $_SGLOBAL['supe_userevent'] < 2) {
		$arr = array("status"=>2);
		if($_SGLOBAL['supe_userevent']['status'] == 1) {
			$numsql .= ",follownum = follownum - 1 ";
		}
		if($event['limitnum'] > 0 && $event['membernum'] + $_SGLOBAL['supe_userevent']['fellow'] > $event['limitnum']) {
			$arr['fellow'] = 0;
		}
		updatetable("userevent", $arr, array("uid"=>$_SGLOBAL['supe_uid'], "eventid"=>$eventid));
		$_SGLOBAL['db']->query("UPDATE " . tname("event") . " SET $numsql WHERE eventid = '$eventid'");
		if(ckprivacy('join')){
			feed_add('event', cplang('event_join'), array('title'=>$event['title'], "eventid"=>$event['eventid'], "uid"=>$event['uid'], "username"=>$event['username']));
		}
	}
	
	showmessage(cplang('event_accept_success', array("space.php?do=event&id=$event[eventid]")));

} elseif('delete'==$op) {
	// 删除/取消 活动

	if(! $eventid){
		showmessage("event_does_not_exist"); // 活动不存在或者已被删除
	}
	
	if(!$allowmanage){
		showmessage('no_privilege');
	}
	
	if(submitcheck("deletesubmit")){
		include_once(S_ROOT.'./source/function_delete.php');
		deleteevents(array($eventid));
		showmessage("do_success", "space.php?do=event", 2);
	}	

} elseif("print"==$op) {
	// 打印签到表

	if(! $eventid){
		showmessage("event_does_not_exist"); // 活动不存在或者已被删除
	}

	if(submitcheck("printsubmit")){

		$members=array();
		$uids=array();
		if($_POST['admin']){
			$query = $_SGLOBAL['db']->query("SELECT * FROM " . tname("userevent") . " WHERE eventid='$eventid' AND status > 1 ORDER BY status DESC, dateline ASC");
		} else {
			$query = $_SGLOBAL['db']->query("SELECT * FROM " . tname("userevent") . " WHERE eventid='$eventid' AND status = 2 ORDER BY dateline ASC");
		}
		while($value=$_SGLOBAL['db']->fetch_array($query)){
			$members[] = $value;
			realname_set($value['uid'], $value['username']);
		}
		realname_get();

		include template('cp_event_sheet');
		exit();
	}
	
} elseif($op == 'close') {//关闭活动
	
	if(!$eventid) {
		showmessage("event_does_not_exist"); // 活动不存在或者已被删除
	}
	
	if(!$allowmanage){
		showmessage('no_privilege');
	}
	
	if($event['grade'] < 1 || $event['endtime'] > $_SGLOBAL['timestamp']) {
		showmessage('event_can_not_be_closed');
	}
	
	if(submitcheck('closesubmit')){
		updatetable('event', array('grade'=>-2), array('eventid'=>$eventid));
		showmessage('do_success', 'space.php?do=event&id='.$eventid, 0);		
	}

} elseif($op == 'open') {//开启关闭的活动

	if(!$eventid) {
		showmessage("event_does_not_exist"); // 活动不存在或者已被删除
	}
	
	if(!$allowmanage){
		showmessage('no_privilege');
	}
	
	if($event['grade'] != -2 || $event['endtime'] > $_SGLOBAL['timestamp']) {
		showmessage('event_can_not_be_opened');
	}
	
	if(submitcheck('opensubmit')){
		updatetable('event', array('grade'=>1), array('eventid'=>$eventid));
		showmessage('do_success', 'space.php?do=event&id='.$eventid, 0);		
	}
	
} elseif($op == 'calendar') {//活动列表日历
	$match = array();
	if(!$_GET['month'] && preg_match("/^(\d{4}-\d{1,2})/", $_GET['date'], $match)) {
		$_GET['month'] = $match[1];
	}
	if(preg_match("/^(\d{4})-(\d{1,2})$/", $_GET['month'], $match)){
		$year = intval($match[1]);
		$month = intval($match[2]);
	} else {
		$year = intval(sgmdate("Y"));
		$month = intval(sgmdate("m"));
	}
	if($month==12) {
		$nextmonth = ($year + 1)."-"."1";
		$premonth = $year."-11";
	} elseif ($month==1) {
		$nextmonth = $year."-2";
		$premonth = ($year-1)."-12";
	} else {
		$nextmonth = $year."-".($month+1);
		$premonth = $year."-".($month-1);
	}
	
	$daystart = mktime(0,0,0,$month,1,$year);	
	$week = sgmdate("w",$daystart);//本月第一天是周几: 0-6	
	$dayscount = sgmdate("t",$daystart);//本月天数
	$dayend = mktime(0,0,0,$month,$dayscount,$year) + 86400;
	$days = array();
	for($i=1; $i<=$dayscount; $i++) {
		$days[$i] = array("count"=>0, "events"=>array(), "class"=>'');
	}
	
	//本月活动
	$events = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname("event")." WHERE starttime < $dayend AND endtime > $daystart ORDER BY eventid DESC LIMIT 100");//最多只取100
	while($value=$_SGLOBAL['db']->fetch_array($query)) {
		if($value['public']<1 || $value['grade'] == 0 || $value['grade'] == -1){
			continue;
		}
		$start = $value['starttime'] < $daystart ? 1 : intval(date("j", $value['starttime']));
		$end = $value['endtime'] > $dayend ? $dayscount : intval(date("j", $value['endtime']));
		for($i=$start; $i<=$end; $i++) {
			if($days[$i]['count'] < 10) {//最多只显示10个活动/每天
				$days[$i]['events'][] = $value;
				$days[$i]['count'] += 1;
				$days[$i]['class'] = " on_link";
			}
		}
	}
	unset($events);
	
	if($month == intval(sgmdate("m")) && $year == intval(sgmdate("Y"))) {
		$d = intval(sgmdate("j"));
		$days[$d]['class'] = "on_today";
	}
	
	if($_GET['date']) {
		$t = sstrtotime($_GET['date']);
		if($month == intval(sgmdate("m",$t)) && $year == intval(sgmdate("Y",$t))) {
			$d = intval(sgmdate("j",$t));
			$days[$d]['class'] = "on_select";
		}
	}
	
	//链接
	$url = $_GET['url'] ? preg_replace("/date=[\d\-]+/", '', $_GET['url']) : "space.php?do=event";
	
} elseif($_GET['op'] == 'edithot') {
	//权限
	if(!checkperm('manageevent')) {
		showmessage('no_privilege');
	}
	
	if(submitcheck('hotsubmit')) {
		$_POST['hot'] = intval($_POST['hot']);
		updatetable('event', array('hot'=>$_POST['hot']), array('eventid'=>$eventid));
		
		if($_POST['hot']>0) {
			include_once(S_ROOT.'./source/function_feed.php');
			feed_publish($eventid, 'eventid');
		} else {
			updatetable('feed', array('hot'=>$_POST['hot']), array('id'=>$eventid, 'idtype'=>'eventid'));
		}
		showmessage('do_success', "space.php?uid=$event[uid]&do=event&id=$eventid", 0);
	}
	
} elseif($op == 'edit'){// 创建、编辑一个新活动
	
	if($eventid) {
		// 检查权限			
		if(!$allowmanage){
			showmessage("no_privilege_edit_event");
		}
	} else {
		//检查用户所在组发活动权限
		if(! checkperm("allowevent")){
		   showmessage('no_privilege_add_event');
		}
		
		//实名认证
		ckrealname('event');
		
		//视频认证
		ckvideophoto('event');
		
		//新用户见习
		cknewuser();
		
		// 新活动默认项 [to do: 站长可以设置活动默认项，优先级：低]
		$event = array();
		$event['eventid'] = '';
		$event['starttime'] = ceil($_SGLOBAL['timestamp'] / 3600) * 3600 + 7200; // 活动开始时间：二小时后
		$event['endtime'] = $event['starttime'] + 14400; // 活动结束时间：开始时间后四小时
		$event['deadline'] = $event['starttime']; // 报名截止：开始时间
		$event['allowinvite'] = 1; // 是否允许邀请好友
		$event['allowpost'] = 1; // 是否允许发布帖子
		$event['allowpic'] = 1; // 是否允许共享活动照片
		$event['allowfellow'] = 0; // 是否允许携带朋友
		$event['verify'] = 0;  // 是否需要审核
		$event['public'] = 2;  // 是否公开活动：完全公开
		$event['limitnum'] = 0;  // 限制参加人数：不限制
		$event['province'] = $space['resideprovince'];  // 活动城市：发布者所在城市
		$event['city'] = $space['residecity'];
		
		//参与热点
		$topic = array();
		$topicid = $_GET['topicid'] = intval($_GET['topicid']);
		if($topicid) {
			$topic = topic_get($topicid);
		}
		if($topic) {
			$actives = array('event' => ' class="active"');
		}
	}
	
	//关联群组
	$mtags = array();
	if(!$eventid || $event['uid']==$_SGLOBAL['supe_uid']) {
		$query = $_SGLOBAL['db']->query("SELECT mtag.* FROM ".tname("tagspace")." st LEFT JOIN ".tname("mtag")." mtag ON st.tagid=mtag.tagid WHERE st.uid='$_SGLOBAL[supe_uid]' AND st.grade=9");
		while($value=$_SGLOBAL['db']->fetch_array($query)) {
			$mtags[] = $value;
		}
	}
	
	if($_GET['tagid'] && !$event['tagid']) {
		$event['tagid'] = intval($_GET['tagid']);		
	}
	
}

realname_get();

include template("cp_event");


// 审核活动成员、设置、取消活动组织者
// [to do: 可加入黑名单功能，只需改status为-2，在进入活动时检查即可。优先级：低]
function verify_eventmembers($uids, $status){
	global $_SGLOBAL, $event;	

	if($_SGLOBAL['supe_userevent']['status'] < 3){
		showmessage('no_privilege_manage_event_members');
	}
	$eventid = $_SGLOBAL['supe_userevent']['eventid'];
	if($eventid != $event['eventid']){
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname("event")." WHERE eventid='$eventid'");
		$event = $_SGLOBAL['db']->fetch_array($query);
	}
	
	$status = intval($status);
	if($status < -1 || $status > 3){
		showmessage("bad_userevent_status"); // 请选择正确的活动成员状态
	}
	if($event['verify'] == 0 && $status == 0){
		showmessage("event_not_set_verify");
	}
	if($status == 3 && $_SGLOBAL['supe_uid'] != $event['uid']){
		showmessage("only_creator_can_set_admin"); // 只有创建者可以设管理员
	}
	
	$newids = $actions = $userevents = array();
	$num = 0; // 活动人数变化
	$query = $_SGLOBAL['db']->query("SELECT ue.*, sf.* FROM " . tname("userevent") . " ue LEFT JOIN ".tname("spacefield")." sf ON ue.uid=sf.uid WHERE ue.uid IN (".simplode($uids).") AND ue.eventid='$eventid'");
	while($value = $_SGLOBAL['db']->fetch_array($query)){
		if($value['status'] == $status || $event['uid'] == $value['uid'] || $value['status'] == 1){
			// 相同 status 者，创建者，关注者 不处理
			continue;
		}
		if($status == 2) {//设为普通成员
			$newids[] = $value['uid'];
			$userevents[$value['uid']] = $value;
			if($value['status'] == 0){// 加入
				$actions[$value['uid']] = "set_verify";
				$num += ($value['fellow'] + 1);
			} elseif ($value['status'] == 3) { // 取消组织者身份
				$actions[$value['uid']] = "unset_admin";
			}
		} elseif($status == 3) {//设为组织者
			$newids[] = $value['uid'];
			$userevents[$value['uid']] = $value;
			$actions[$value['uid']] = "set_admin";
			if($value['status'] == 0){
				$num += ($value['fellow'] + 1);
			}
		} elseif($status == 0) {//设为待审核
			$newids[] = $value['uid'];
			$userevents[$value['uid']] = $value;
			$actions[$value['uid']] = "unset_verify";
			if($value['status'] >= 2){
				$num -= ($value['fellow'] + 1);
			}
		} elseif($status == -1) {//删除成员
			$newids[] = $value['uid'];
			$userevents[$value['uid']] = $value;
			$actions[$value['uid']] = "set_delete";
			if($value['status'] >= 2){
				$num -= ($value['fellow'] + 1);
			}
		}
	}
	
	if(empty($newids)) return array();
	if($event['limitnum'] > 0 && $event['membernum'] + $num > $event['limitnum']){
		// 活动人数超了
		showmessage("event_will_full");
	}
	
	$note_inserts = $note_ids = $feed_inserts = array();
	$feedarr = array(
		'appid' => UC_APPID,
		'icon' => 'event',
		'uid' => '',
		'username' => '',
		'dateline' => $_SGLOBAL['timestamp'],
		'title_template' => cplang('event_join'), 
		'title_data' => array('title'=>$event['title'], "eventid"=>$event['eventid'], "uid"=>$event['uid'], "username"=>$event['username']),
		'body_template' => '',
		'body_data' => array(),
		'body_general' => '',
		'image_1' => '',
		'image_1_link' => '',
		'image_2' => '',
		'image_2_link' => '',
		'image_3' => '',
		'image_3_link' => '',
		'image_4' => '',
		'image_4_link' => '',
		'target_ids' => '',
		'friend' => ''
	);
	$feedarr = sstripslashes($feedarr);//去掉转义
	$feedarr['title_data'] = serialize(sstripslashes($feedarr['title_data']));//数组转化
	$feedarr['body_data'] = serialize(sstripslashes($feedarr['body_data']));//数组转化
	$feedarr['hash_template'] = md5($feedarr['title_template']."\t".$feedarr['body_template']);//喜好hash
	$feedarr['hash_data'] = md5($feedarr['title_template']."\t".$feedarr['title_data']."\t".$feedarr['body_template']."\t".$feedarr['body_data']);//合并hash
	$feedarr = saddslashes($feedarr);//增加转义

	foreach ($newids as $id){
		if($status > 1 && $userevents[$id]['status'] ==0){
			// 通过审核参加了活动，发布参加活动feed
			$feedarr['uid'] = $userevents[$id]['uid'];
			$feedarr['username'] = $userevents[$id]['username'];
			$feed_inserts[] = "('$feedarr[appid]', 'event', '$feedarr[uid]', '$feedarr[username]', '$feedarr[dateline]', '0', '$feedarr[hash_template]', '$feedarr[hash_data]', '$feedarr[title_template]', '$feedarr[title_data]', '$feedarr[body_template]', '$feedarr[body_data]', '$feedarr[body_general]', '$feedarr[image_1]', '$feedarr[image_1_link]', '$feedarr[image_2]', '$feedarr[image_2_link]', '$feedarr[image_3]', '$feedarr[image_3_link]', '$feedarr[image_4]', '$feedarr[image_4_link]')";
		}
		$userevents[$id]['privacy'] = empty($userevents[$id]['privacy']) ? array() : unserialize($userevents[$id]['privacy']);
		$filter = empty($userevents[$id]['privacy']['filter_note'])?array():array_keys($userevents[$id]['privacy']['filter_note']);
		if(cknote_uid(array("type"=>"eventmemberstatus","authorid"=>$_SGLOBAL['supe_uid']),$filter)){
			$note_ids[] = $id;
			$note_msg = cplang('eventmember_'.$actions[$id], array("space.php?do=event&id=".$event['eventid'], $event['title']));
			$note_inserts[] = "('$id', 'eventmemberstatus', '1', '$_SGLOBAL[supe_uid]', '$_SGLOBAL[supe_username]', '".addslashes($note_msg)."', '$_SGLOBAL[timestamp]')";
		}
	}
	
	if($note_ids) {
		$_SGLOBAL['db']->query("INSERT INTO ".tname('notification')." (`uid`, `type`, `new`, `authorid`, `author`, `note`, `dateline`) VALUES ".implode(',', $note_inserts));
		$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET notenum=notenum+1 WHERE uid IN (".simplode($note_ids).")");
	}
	if($feed_inserts){
		$_SGLOBAL['db']->query("INSERT INTO ".tname('feed')." (`appid` ,`icon` ,`uid` ,`username` ,`dateline` ,`friend` ,`hash_template` ,`hash_data` ,`title_template` ,`title_data` ,`body_template` ,`body_data` ,`body_general` ,`image_1` ,`image_1_link` ,`image_2` ,`image_2_link` ,`image_3` ,`image_3_link` ,`image_4` ,`image_4_link`) VALUES ".implode(',', $feed_inserts));
	}

	if($status == -1){// 删除		
		$_SGLOBAL['db']->query("DELETE FROM ".tname("userevent")." WHERE uid IN (".simplode($newids).") AND eventid='$eventid'");
	} else {// 设置状态		
		$_SGLOBAL['db']->query("UPDATE ".tname("userevent")." SET status='$status' WHERE uid IN (".simplode($newids).") AND eventid='$eventid'");
	}
	// 修改活动人数
	if($num != 0){
		$_SGLOBAL['db']->query("UPDATE ".tname("event")." SET membernum = membernum + ($num) WHERE eventid='$eventid'");
	}
	return $newids;
}


?>
