<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: space_mtag.php 13083 2009-08-10 09:35:23Z xupeng $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

@include_once(S_ROOT.'./data/data_profield.php');

$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page=1;
$id = empty($_GET['id'])?0:intval($_GET['id']);
$tagid = empty($_GET['tagid'])?0:intval($_GET['tagid']);
$fieldid = empty($_GET['fieldid'])?0:intval($_GET['fieldid']);
$tagname = trim($_GET['tagname']);

//查询
if($tagname) {
	
	$fields = array();
	foreach ($_SGLOBAL['profield'] as $value) {
		if($value['formtype'] == 'text') {
			$fields[] = $value;//自由输入的分类
		}
	}
	
	$taglist = array();
	if($fieldid) {
		$plussql = " AND fieldid='$fieldid'";
		$field = $_SGLOBAL['profield'][$fieldid];
	} else {
		$plussql = '';
		$field = array();
	}
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('mtag')." WHERE tagname='$tagname' $plussql");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$taglist[] = $value;
	}
	
	if(empty($taglist)) {
		//群组创建
		$allowmk = 0;
		if($field && $field['formtype'] != 'text') {
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('profield')." WHERE fieldid='$fieldid'");
			if($field = $_SGLOBAL['db']->fetch_array($query)) {
				$field['choice'] = explode("\n", $field['choice']);
				$s = stripslashes($tagname);
				foreach ($field['choice'] as $subkey => $subvalue) {
					$subvalue = trim($subvalue);
					if($s == $subvalue) {
						//自动创建
						$mtag = array(
							'tagname' => addslashes($s),
							'fieldid' => $fieldid
						);
						$tagid = inserttable('mtag', $mtag, 1);
						showmessage('do_sucess', "space.php?do=mtag&tagid=".$tagid, 0);
					}
				}
			}
		} elseif ($fields) {
			$allowmk = 1;
		}
		if(!$allowmk) {
			showmessage('mtag_creat_error');
		}
	} elseif(count($taglist) == 1) {
		//直接跳转
		showmessage('do_sucess', "space.php?do=mtag&tagid=".$taglist[0]['tagid'], 0);
	}
	
	$_TPL['css'] = 'thread';
	include_once template("space_mtag_tagname");
	
} elseif($id) {
	$perpage = 20;
	$start = ($page-1)*$perpage;
	
	//检查开始数
	ckstart($start, $perpage);
	
	//栏目
	$list = array();
	$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('mtag')." WHERE fieldid='$id'"),0);
	if($count) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('mtag')." WHERE fieldid='$id' ORDER BY membernum DESC LIMIT $start,$perpage");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			if(empty($value['pic'])) {
				$value['pic'] = 'image/nologo.jpg';
			}
			$list[] = $value;
		}
	}
	
	//分页
	$multi = multi($count, $perpage, $page, "space.php?uid=$space[uid]&do=mtag&id=$id");

	$fieldtitle = $_SGLOBAL['profield'][$id]['title'];
	
	$sub_actives = array($id => ' class="active"');
	$fieldids = array($id => ' selected');

	$_TPL['css'] = 'thread';
	include_once template("space_mtag_field");

} elseif($tagid) {

	$actives = array($_GET['view'] => ' class="active"');
	
	//指定的群组
	$mtag = getmtag($tagid);
	if($mtag['close']) {
		showmessage('mtag_close');
	}
	
	//群组活动
	$eventnum = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname("event")." WHERE tagid='$tagid'"), 0);
	
	if($_GET['view'] == 'list' || $_GET['view'] == 'digest') {
		
		$perpage = 30;
		$start = ($page-1)*$perpage;
		
		//检查开始数
		ckstart($start, $perpage);
		$theurl = "space.php?uid=$space[uid]&do=mtag&tagid=$tagid&view=$_GET[view]";

		$wheresql = ($_GET['view'] == 'list')?'':" AND main.digest='1'";
		
		if($searchkey = stripsearchkey($_GET['searchkey'])) {
		   $wheresql .= "AND main.subject LIKE '%$searchkey%' ";
		   $theurl .= "&searchkey=$_GET[searchkey]";
		}
		  
		$list = array();
		$count = 0;
		
		if($mtag['allowview']) {
			$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('thread')." main WHERE main.tagid='$tagid' $wheresql"),0);
			if($count) {
				$query = $_SGLOBAL['db']->query("SELECT main.* FROM ".tname('thread')." main 
					WHERE main.tagid='$tagid' $wheresql
					ORDER BY main.displayorder DESC, main.lastpost DESC 
					LIMIT $start,$perpage");
				while ($value = $_SGLOBAL['db']->fetch_array($query)) {
					realname_set($value['uid'], $value['username']);
					realname_set($value['lastauthorid'], $value['lastauthor']);
					$list[] = $value;
				}
			}
			//分页
			$multi = multi($count, $perpage, $page, $theurl);
	
			realname_get();
		}
		
		$_TPL['css'] = 'thread';
		include_once template("space_mtag_list");
		
	} elseif($_GET['view'] == 'member') {
		
		$perpage = 50;
		$start = ($page-1)*$perpage;
		
		//检查开始数
		ckstart($start, $perpage);
		
		//检索
		$wheresql = '';
		$_GET['key'] = stripsearchkey($_GET['key']);
		if($_GET['key']) {
			$wheresql = " AND main.username LIKE '%$_GET[key]%' ";
		}

		
		$list = $fuids = array();
		$count = 0;
		
		if($mtag['allowview']) {
			$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('tagspace')." main WHERE main.tagid='$tagid' $wheresql"),0);
			if($count) {
				$query = $_SGLOBAL['db']->query("SELECT field.*, main.username, main.grade FROM ".tname('tagspace')." main 
					LEFT JOIN ".tname('spacefield')." field ON field.uid=main.uid 
					WHERE main.tagid='$tagid' $wheresql ORDER BY main.grade DESC LIMIT $start,$perpage");
				while ($value = $_SGLOBAL['db']->fetch_array($query)) {
					//实名
					realname_set($value['uid'], $value['username']);
					
					$value['p'] = rawurlencode($value['resideprovince']);
					$value['c'] = rawurlencode($value['residecity']);
					$fuids[] = $value['uid'];
					$list[] = $value;
				}
			}
			
			//在线状态
			$ols = array();
			if($fuids) {
				$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('session')." WHERE uid IN (".simplode($fuids).")");
				while ($value = $_SGLOBAL['db']->fetch_array($query)) {
					if(!$value['magichidden']) {
						$ols[$value['uid']] = $value['lastactivity'];
					}
				}
			}
	
			//分页
			$multi = multi($count, $perpage, $page, "space.php?uid=$space[uid]&do=mtag&tagid=$tagid&view=member");
			
			//实名
			realname_get();
		}
		
		$_TPL['css'] = 'thread';
		include_once template("space_mtag_member");
	
	} elseif ($_GET['view'] == 'event') {
		
		$perpage = 10;
		$start = ($page-1)*$perpage;
		
		//检查开始数
		ckstart($start, $perpage);
		$eventlist = array();
		if($eventnum) {
			// 活动分类
			@include_once(S_ROOT.'./data/data_eventclass.php');
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname("event")." WHERE tagid='$tagid' ORDER BY eventid DESC LIMIT $start, $perpage");
			while($value=$_SGLOBAL['db']->fetch_array($query)) {
				if($value['poster']){
					$value['pic'] = pic_get($value['poster'], $value['thumb'], $value['remote']);
				} else {
					$value['pic'] = $_SGLOBAL['eventclass'][$value['classid']]['poster'];
				}
				$eventlist[] = $value;
			}
		}
		
		//分页
		$multi = multi($eventnum, $perpage, $page, "space.php?uid=$space[uid]&do=mtag&tagid=$tagid&view=event");
	
		$_TPL['css'] = 'thread';
		include_once template("space_mtag_event");
		
	} else {

		//群组首页
		$list = $starlist = $modlist = $memberlist = $checklist = array();
		
		if($mtag['allowview']) {
			$query = $_SGLOBAL['db']->query("SELECT main.* FROM ".tname('thread')." main 
				WHERE main.tagid='$tagid' 
				ORDER BY main.displayorder DESC, main.lastpost DESC 
				LIMIT 0,50");
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				realname_set($value['uid'], $value['username']);
				realname_set($value['lastauthorid'], $value['lastauthor']);
				$list[] = $value;
			}
			
			//明星会员
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('tagspace')." WHERE tagid='$tagid' AND grade='1'");
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				realname_set($value['uid'], $value['username']);
				$starlist[] = $value;
			}
			$starlist = sarray_rand($starlist, 12);//随机选择
								
			//会员
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('tagspace')." WHERE tagid='$tagid' AND grade='0' LIMIT 0,12");
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				realname_set($value['uid'], $value['username']);
				$memberlist[] = $value;
			}
		}
		//群主
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('tagspace')." WHERE tagid='$tagid' AND grade>'7' ORDER BY grade DESC LIMIT 0,12");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			realname_set($value['uid'], $value['username']);
			$modlist[] = $value;
		}
		//是群主
		if($mtag['grade']>=8) {
			//待审
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('tagspace')." WHERE tagid='$tagid' AND grade='-2' LIMIT 0,12");
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				realname_set($value['uid'], $value['username']);
				$checklist[] = $value;
			}
		}
		
		realname_get();
		
		$_TPL['css'] = 'thread';
		include_once template("space_mtag_index");
	}

} else {

	$theurl = "space.php?uid=$space[uid]&do=mtag";
	
	if(empty($_GET['view'])) $_GET['view'] = 'me';
	if(!in_array($_GET['view'], array('me', 'hot', 'recommend', 'manage'))) $_GET['view'] = 'hot';
	
	$theurl .= "&view=$_GET[view]";
	$actives = array($_GET['view'] => ' class="active"');		

	$wherearr = array();
	
	//排序
	if (!in_array($_GET['orderby'], array('threadnum', 'postnum', 'membernum'))) {
		$_GET['orderby'] = 'threadnum';
	} else {
		$theurl .= "&orderby=$_GET[orderby]";
	}
	$orderbyarr = array($_GET['orderby'] => ' class="active"');
	
	//查询
	$_GET['fieldid'] = intval($_GET['fieldid']);
	if($_GET['fieldid']) {
		$wherearr[] = "mt.fieldid='$_GET[fieldid]'";
		$theurl .= "&fieldid=$_GET[fieldid]";
		$pro_actives = array($_GET['fieldid'] => ' class="current"');
	} else {
		$pro_actives = array('all' => ' class="current"');
	}
	
	$list = $rlist = array();
	$multi = '';
	$count = 0;
	
	$perpage = 20;
	$page = intval($_GET['page']);
	if($page < 1) $page = 1;
	$start = ($page-1)*$perpage;

	if($_GET['view'] == 'me' || $_GET['view'] == 'manage') {
		$sqlplus = $_GET['view'] == 'manage'?' AND main.grade=\'9\'':'';
		if($_GET['fieldid']) {
			$countsql = "SELECT COUNT(*) FROM ".tname('tagspace')." main, ".tname('mtag')." mt
				WHERE main.uid='$space[uid]' $sqlplus AND mt.tagid=main.tagid AND ".implode(' AND ', $wherearr);
			$sql = "SELECT main.*,mt.* FROM ".tname('tagspace')." main, ".tname('mtag')." mt
				WHERE main.uid='$space[uid]' $sqlplus AND mt.tagid=main.tagid AND ".implode(' AND ', $wherearr)." ORDER BY mt.{$_GET['orderby']} DESC LIMIT $start,$perpage";
		} else {
			$countsql = "SELECT COUNT(*) FROM ".tname('tagspace')." main
				WHERE main.uid='$space[uid]' $sqlplus";
			$sql = "SELECT main.*,mt.* FROM ".tname('tagspace')." main 
				LEFT JOIN ".tname('mtag')." mt ON mt.tagid=main.tagid
				WHERE main.uid='$space[uid]' $sqlplus ORDER BY mt.{$_GET['orderby']} DESC LIMIT $start,$perpage";
		}
	} else {
		if($_GET['view'] == 'recommend') {
			$wherearr[] = "mt.recommend='1'";
		}
		
		//搜索
		if($searchkey = stripsearchkey($_GET['searchkey'])) {
			$wherearr[] = "mt.tagname LIKE '%$searchkey%'";
			$theurl .= "&searchkey=$_GET[searchkey]";
		}
		
		$countsql = "SELECT COUNT(*) FROM ".tname('mtag')." mt WHERE ".(empty($wherearr)?'1':implode(' AND ', $wherearr));
		$sql = "SELECT mt.* FROM ".tname('mtag')." mt
			WHERE ".(empty($wherearr)?'1':implode(' AND ', $wherearr))." ORDER BY mt.{$_GET['orderby']} DESC LIMIT $start,$perpage";
	}
	
	$tagids = $tagnames = array();
	$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query($countsql), 0);
	if($count) {
		$query = $_SGLOBAL['db']->query($sql);
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$value['title'] = $_SGLOBAL['profield'][$value['fieldid']]['title'];
			if(empty($value['pic'])) $value['pic'] = 'image/nologo.jpg';
			$tagids[] = $value['tagid'];
			$tagnames[$value['tagid']] = $value['tagname'];
			$list[] = $value;
		}
		
		$multi = multi($count, $perpage, $page, $theurl);
	}
	
	$threadlist = array();
	if($tagids) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('thread')." WHERE tagid IN (".simplode($tagids).") ORDER BY dateline DESC LIMIT 0,10");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			realname_set($value['uid'], $value['username']);
			realname_set($value['lastauthorid'], $value['lastauthor']);
			$value['tagname'] = getstr($tagnames[$value['tagid']], 20);
			$threadlist[] = $value;
		}
	}

	$_TPL['css'] = 'thread';
	include_once template("space_mtag");
}

?>