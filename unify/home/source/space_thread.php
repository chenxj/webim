<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: space_thread.php 13210 2009-08-20 07:09:06Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

@include_once(S_ROOT.'./data/data_profield.php');

$eventid = empty($_GET['eventid']) ? 0 : intval($_GET['eventid']);
if($eventid) {
	$query = $_SGLOBAL['db']->query("SELECT e.* FROM ".tname("event")." e WHERE e.eventid='$_GET[eventid]'");
	$event = $_SGLOBAL['db']->fetch_array($query);
	if(empty($event)){
		showmessage('event_does_not_exist');
	}
	$query = $_SGLOBAL['db']->query("SELECT * FROM " . tname("userevent") . " WHERE uid = '$_SGLOBAL[supe_uid]' AND eventid = '$eventid'");
	$userevent = $_SGLOBAL['db']->fetch_array($query);
}

//��̬����
@include_once(S_ROOT.'./data/data_click.php');
$clicks = empty($_SGLOBAL['click']['tid'])?array():$_SGLOBAL['click']['tid'];

//��ҳ
$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page=1;
$id = empty($_GET['id'])?0:intval($_GET['id']);

if($id) {
	//����
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('thread')." WHERE tid='$id' LIMIT 1");
	if(!$thread = $_SGLOBAL['db']->fetch_array($query)) {
		showmessage('topic_does_not_exist');
	}
	
	//��֤�ռ��Ƿ�����
	$space = getspace($thread['uid']);
	if($space['flag'] == -1) {
		showmessage('space_has_been_locked');
	}
	
	realname_set($thread['uid'], $thread['username']);
	
	//Ⱥ����Ϣ
	$tagid = $thread['tagid'];
	
	if($eventid && $event['tagid'] != $tagid) {
		showmessage('event_mtag_not_match');
	}
	if(!$eventid && $thread['eventid']) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname("event")." WHERE eventid='$thread[eventid]' LIMIT 1");
		$event = $_SGLOBAL['db']->fetch_array($query);
		if(empty($event)) {
			updatetable('thread',array("eventid"=>0),array("eventid"=>$thread['eventid']));
		}
	}

	$mtag = getmtag($tagid);
	if($mtag['close']) {
		showmessage('mtag_close');
	}

	if($eventid && $event['public']==0 && $userevent['status']<2) {
		showmessage('event_memberstatus_limit', "space.php?do=event");
	} elseif(empty($mtag['allowview'])) {
		showmessage('mtag_not_allow_to_do', "space.php?do=mtag&tagid=$tagid");
	}

	//�����б�
	$perpage = 30;
	$start = ($page-1)*$perpage;

	$count = $thread['replynum'];

	if($count % $perpage == 0) {
		$perpage = $perpage + 1;
	}
	//��鿪ʼ��
	ckstart($start, $perpage);

	$pid = empty($_GET['pid'])?0:intval($_GET['pid']);
	$psql = $pid?"(isthread='1' OR pid='$pid') AND":'';

	$list = array();
	$postnum = $start;
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('post')." WHERE $psql tid='$thread[tid]' ORDER BY dateline LIMIT $start,$perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		realname_set($value['uid'], $value['username']);
		$value['num'] = $postnum;
		$list[] = $value;
		$postnum++;
	}

	//ȡ������
	if($list[0]['isthread']) {
		$thread['content'] = $list[0];
		include_once(S_ROOT.'./source/function_blog.php');
		$thread['content']['message'] = blog_bbcode($thread['content']['message']);
		unset($list[0]);
	} else {
		$thread['content'] = array();
	}

	//��ҳ
	$multi = multi($count, $perpage, $page, "space.php?uid=$thread[uid]&do=$do&id=$id");

	//����ͳ��
	if(!$space['self'] && $_SCOOKIE['view_tid'] != $id) {
		$_SGLOBAL['db']->query("UPDATE ".tname('thread')." SET viewnum=viewnum+1 WHERE tid='$id'");
		inserttable('log', array('id'=>$space['uid'], 'idtype'=>'uid'));//�ӳٸ���
		ssetcookie('view_tid', $id);
	}
	
	//��̬
	$hash = md5($thread['uid']."\t".$thread['dateline']);
	$id = $thread['tid'];
	$idtype = 'tid';
	
	foreach ($clicks as $key => $value) {
		$value['clicknum'] = $thread["click_$key"];
		$value['classid'] = mt_rand(1, 4);
		if($value['clicknum'] > $maxclicknum) $maxclicknum = $value['clicknum'];
		$clicks[$key] = $value;
	}
	
	//����
	$clickuserlist = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('clickuser')."
		WHERE id='$id' AND idtype='$idtype'
		ORDER BY dateline DESC
		LIMIT 0,18");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		realname_set($value['uid'], $value['username']);//ʵ��
		$value['clickname'] = $clicks[$value['clickid']]['name'];
		$clickuserlist[] = $value;
	}
	
	//����
	$topic = topic_get($thread['topicid']);

	//ʵ��
	realname_get();
	
	$_TPL['css'] = 'thread';
	include_once template("space_thread_view");

} else {

	$perpage = 30;
	$start = ($page-1)*$perpage;
	
	//��鿪ʼ��
	ckstart($start, $perpage);
	
	if(!in_array($_GET['view'], array('hot','new','me', 'all'))) {
		$_GET['view'] = 'hot';
	}

	//�����б�
	$wheresql = $f_index = '';
	if($_GET['view'] == 'hot') {
		$minhot = $_SCONFIG['feedhotmin']<1?3:$_SCONFIG['feedhotmin'];
		$wheresql = "main.hot>='$minhot'";
		
		//����Ⱥ��
		if($page == 1) {
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('mtag')." mt ORDER BY mt.threadnum DESC LIMIT 0,6");
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				$value['title'] = $_SGLOBAL['profield'][$value['fieldid']]['title'];
				if(empty($value['pic'])) $value['pic'] = 'image/nologo.jpg';
				$rlist[] = $value;
			}
		}
		
	} elseif($_GET['view'] == 'me')  {
		//�Լ���
		$wheresql = "main.uid='$space[uid]'";
	} else {
		$wheresql = "1";
		$f_index = 'USE INDEX (lastpost)';
	}
	$theurl = "space.php?uid=$space[uid]&do=thread&view=$_GET[view]";
	$actives = array($_GET['view']=>' class="active"');

	$list = array();
	$count = 0;
	
		
	//����
	if($searchkey = stripsearchkey($_GET['searchkey'])) {
		$wheresql = "main.subject LIKE '%$searchkey%'";
		$theurl .= "&searchkey=$_GET[searchkey]";
		cksearch($theurl);
	}
		
	if($wheresql) {
		$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('thread')." main WHERE $wheresql"),0);
		
		//����ͳ��
		if($wheresql == "main.uid='$space[uid]'" && $space['threadnum'] != $count) {
			updatetable('space', array('threadnum' => $count), array('uid'=>$space['uid']));
		}
		
		if($count) {
			$query = $_SGLOBAL['db']->query("SELECT main.*,field.tagname,field.membernum,field.fieldid,field.pic FROM ".tname('thread')." main $f_index
				LEFT JOIN ".tname('mtag')." field ON field.tagid=main.tagid WHERE $wheresql
				ORDER BY main.lastpost DESC
				LIMIT $start,$perpage");
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				realname_set($value['uid'], $value['username']);
				realname_set($value['lastauthorid'], $value['lastauthor']);
				$value['tagname'] = getstr($value['tagname'], 20);
				$list[] = $value;
				if(empty($value['pic'])) {
					$value['pic'] = 'image/nologo.jpg';
				}
			}
		}
	}

	//��ҳ
	$multi = multi($count, $perpage, $page, $theurl);
	
	//ʵ��
	realname_get();

	$_TPL['css'] = 'thread';
	include_once template("space_thread_list");
}


?>