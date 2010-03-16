<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: space_share.php 13206 2009-08-20 02:31:30Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//��ҳ

$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page=1;
$id = empty($_GET['id'])?0:intval($_GET['id']);

if($id) {

	//��ȡ
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('share')." WHERE sid='$id' AND uid='$space[uid]'");
	$share = $_SGLOBAL['db']->fetch_array($query);
	//������
	if(empty($share)) {
		showmessage('share_does_not_exist');
	}
	$share = mkshare($share);
	
	//����
	$perpage = 50;
	$start = ($page-1)*$perpage;

	//��鿪ʼ��
	ckstart($start, $perpage);
	
	$list = array();
	$cid = empty($_GET['cid'])?0:intval($_GET['cid']);
	$csql = $cid?"cid='$cid' AND":'';
	
	$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('comment')." WHERE $csql id='$id' AND idtype='sid'"),0);
	if($count) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('comment')." WHERE $csql id='$id' AND idtype='sid' ORDER BY dateline LIMIT $start,$perpage");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			realname_set($value['authorid'], $value['author']);
			$list[] = $value;
		}
	}
	
	//��ҳ
	$multi = multi($count, $perpage, $page, "space.php?uid=$share[uid]&do=share&id=$id", '', 'comment_ul');
	
	//����ȵ�
	$topic = topic_get($share['topicid']);
	
	realname_get();
	
	$tpl_title = getstr($share['title_template'], 0, 0, 0, 0, 0, -1);

	include_once template("space_share_view");
	
} else {
	
	if(empty($_GET['view']) && ($space['friendnum']<$_SCONFIG['showallfriendnum'])) {
		$_GET['view'] = 'all';//Ĭ����ʾ
	}
	
	$perpage = 20;
	
	//��鿪ʼ��
	$start = ($page-1)*$perpage;
	ckstart($start, $perpage);
	
	//�����ѯ
	$f_index = '';
	if($_GET['view']=='all') {
		//��ҵ�
		$wheresql = "1";
		$theurl = "space.php?uid=$space[uid]&do=$do&view=all";
		$actives = array('all'=>' class="active"');		
	} elseif(empty($space['feedfriend'])) {
		$wheresql = "uid='$space[uid]'";
		$theurl = "space.php?uid=$space[uid]&do=$do&view=me";
		$actives = array('me'=>' class="active"');
	} else {
		$wheresql = "uid IN ($space[feedfriend])";
		$theurl = "space.php?uid=$space[uid]&do=$do&view=we";
		$f_index = 'USE INDEX(dateline)';
		$actives = array('we'=>' class="active"');
	}
	
	//����
	if($_GET['type']) {
		$sub_actives = array('type_'.$_GET['type'] => ' class="active"');
		$wheresql .= " AND type='$_GET[type]'";
	} else {
		$sub_actives = array('type_all' => ' class="active"');
	}
	
	$list = array();
	
	$sid = empty($_GET['sid'])?0:intval($_GET['sid']);
	$sharesql = $sid?"sid='$sid' AND":'';
	
	$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('share')." WHERE $sharesql $wheresql"),0);
	
	//����ͳ��
	if(empty($sharesql) && $wheresql == "uid='$space[uid]'" && $space['sharenum'] != $count) {
		updatetable('space', array('sharenum' => $count), array('uid'=>$space['uid']));
	}
	
	if($count) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('share')." $f_index
			WHERE $sharesql $wheresql
			ORDER BY dateline DESC
			LIMIT $start,$perpage");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			realname_set($value['uid'], $value['username']);
			$value = mkshare($value);
			$list[] = $value;
		}
	}
	
	//��ҳ
	$multi = multi($count, $perpage, $page, $theurl."&type=$_GET[type]");
	
	realname_get();
	
	include_once template("space_share_list");
}

?>