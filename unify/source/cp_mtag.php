<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_mtag.php 13223 2009-08-24 01:53:27Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$fieldarr = $_SGLOBAL['profield'] = $textarr = $choicearr = array();
$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('profield')." ORDER BY displayorder");
while ($value = $_SGLOBAL['db']->fetch_array($query)) {
	if($value['formtype'] == 'text') {
		$textarr[$value['fieldid']] = $value;
	} else {
		$value['choice'] = explode("\n", $value['choice']);
		foreach ($value['choice'] as $subkey => $subvalue) {
			$value['choice'][$subkey] = trim($subvalue);
		}
		$fieldarr[$value['fieldid']] = $value['fieldid'];
		$choicearr[$value['fieldid']] = $value;
	}
	$_SGLOBAL['profield'][$value['fieldid']] = $value;
}

//显示
if($_GET['op'] == 'manage') {
	
	if(empty($_GET['subop'])) {
		$_GET['subop'] = 'base';
	}
	
	//检查当前用户权限
	$mtag = array();
	$managemtag = 0;
	$tagid = empty($_GET['tagid'])?0:intval($_GET['tagid']);
	
	$mtag = getmtag($tagid);
	
	if(submitcheck('invitesubmit') || $_GET['subop']=='invite') {
		if(empty($mtag['allowinvite'])) {
			showmessage('no_privilege');//不允许邀请
		}
	} else {
		if($mtag['grade'] < 8) {
			showmessage('no_privilege');//吧主/副吧主
		}
	}
	
	//栏目
	$field = $_SGLOBAL['profield'][$mtag['fieldid']];
	
	//提交处理
	if(submitcheck('basesubmit')) {
		$setarr = array();
		if($mtag['grade'] == 9) {
			//群主
			$setarr['joinperm'] = $field['manualmember']?intval($_POST['joinperm']):0;
			$setarr['viewperm'] = intval($_POST['viewperm']);
			$setarr['threadperm'] = intval($_POST['threadperm']);
			$setarr['postperm'] = intval($_POST['postperm']);
			$setarr['closeapply'] = intval($_POST['closeapply']);
		}
		$setarr['pic'] = picurl_get($_POST['pic'], 150);
		$setarr['announcement'] = getstr($_POST['announcement'], 5000, 1, 1, 1, 1);
		updatetable('mtag', $setarr, array('tagid'=>$tagid));

		showmessage('do_success', "cp.php?ac=mtag&op=manage&tagid=$tagid&subop=$_GET[subop]");
		
	} elseif (submitcheck('memberssubmit')) {

		//人员管理
		mtag_managemember($mtag, $_POST['ids'], $_POST['newgrade']);
		
		showmessage('do_success', "cp.php?ac=mtag&op=manage&tagid=$tagid&subop=$_GET[subop]&grade=$_GET[grade]");
	
	} elseif (submitcheck('invitesubmit')) {
		//邀请
		$ids = empty($_POST['ids'])?array():$_POST['ids'];
		$inserts = array();
		if($ids) {
			$haves = array();
			$query = $_SGLOBAL['db']->query("SELECT uid FROM ".tname('tagspace')." WHERE tagid='$mtag[tagid]' AND uid IN (".simplode($ids).")");
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				$haves[$value['uid']] = $value['uid'];
			}
			
			$touids = array();
			$nones = array_diff($ids, $haves);
			if($nones) {
				$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('friend')." WHERE uid='$_SGLOBAL[supe_uid]' AND fuid IN (".simplode($nones).") AND status='1'");
				while ($value = $_SGLOBAL['db']->fetch_array($query)) {
					$touids[] = $value['fuid'];
					$inserts[] = "('$value[fuid]', '$mtag[tagid]', '$_SGLOBAL[supe_uid]', '$_SGLOBAL[supe_username]', '$_SGLOBAL[timestamp]')";
				}
			}
		}
		if($inserts) {
			$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET mtaginvitenum=mtaginvitenum+1 WHERE uid IN (".simplode($touids).")");
			$_SGLOBAL['db']->query("REPLACE INTO ".tname('mtaginvite')." (uid,tagid,fromuid,fromusername,dateline) VALUES ".implode(',', $inserts));
		}
		showmessage('do_success', "cp.php?ac=mtag&op=manage&tagid=$tagid&subop=invite&page=$_GET[page]&group=$_GET[group]&start=$_GET[start]");
		
	} elseif (submitcheck('membersubmit')) {
		//人员管理
		mtag_managemember($mtag, array($_GET['uid']), $_POST['grade']);
		showmessage('do_success', $_POST['refer'], 0);
	}
	
	//编辑用户
	if($_GET['subop'] == 'member') {

		$grades = array();
        $tagid = $_GET['tagid'];
        $uid = $_GET['uid'];
        $query = $_SGLOBAL['db']->query("SELECT grade FROM ".tname('tagspace')." WHERE tagid='$tagid' AND uid='$uid' LIMIT 1");
        if($value = $_SGLOBAL['db']->fetch_array($query)) {
            $grades = array($value['grade'] => ' selected');
        }
		
	} elseif($_GET['subop'] == 'members') {
		
		//分页
		$perpage = 24;
		$start = empty($_GET['start'])?0:intval($_GET['start']);
		$list = array();
		$count = 0;
		
		//检索
		$wheresql = '';
		$_GET['key'] = stripsearchkey($_GET['key']);
		if($_GET['key']) {
			$wheresql = " AND username LIKE '%$_GET[key]%' ";
		}
		
		//检查开始数
		ckstart($start, $perpage);

		$_GET['grade'] = intval($_GET['grade']);
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('tagspace')." WHERE tagid='$tagid' AND grade='$_GET[grade]' $wheresql LIMIT $start,$perpage");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			realname_set($value['uid'], $value['username']);
			$list[] = $value;
			$count++;
		}
		
		realname_get();
		
		$multi = smulti($start, $perpage, $count, "cp.php?ac=mtag&op=manage&tagid=$mtag[tagid]&subop=members&grade=$_GET[grade]&key=$_GET[key]");
		
	} elseif($_GET['subop'] == 'invite') {
		//邀请
		
		//分页
		$perpage = 24;
		$page = empty($_GET['page'])?0:intval($_GET['page']);
		if($page<1) $page = 1;
		$start = ($page-1)*$perpage;
		
		
		
		//检查开始数
		ckstart($start, $perpage);
		
		$list = array();

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
		if($count) {
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('friend')." WHERE uid='$_SGLOBAL[supe_uid]' AND status='1' $sql ORDER BY num DESC, dateline DESC LIMIT $start,$perpage");
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				realname_set($value['fuid'], $value['fusername']);
				$list[] = $value;
				$fuids[] = $value['fuid'];
			}
		}
		
		//是否加入
		$joins = array();
		$query = $_SGLOBAL['db']->query("SELECT uid FROM ".tname('tagspace')." WHERE tagid='$tagid' AND uid IN (".simplode($fuids).")");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$joins[$value['uid']] = $value['uid'];
		}
		
		//是否邀请
		$query = $_SGLOBAL['db']->query("SELECT uid FROM ".tname('mtaginvite')." WHERE tagid='$tagid' AND uid IN (".simplode($fuids).")");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$joins[$value['uid']] = $value['uid'];
		}
		
		realname_get();
		
		//用户组
		$groups = getfriendgroup();
		$groupselect = array($_GET['group'] => ' selected');
		
		$multi = multi($count, $perpage, $page, "cp.php?ac=mtag&op=manage&tagid=$mtag[tagid]&subop=invite&group=$_GET[group]&key=$_GET[key]");
		
	} else {
		//显示
		include_once(S_ROOT.'./source/function_bbcode.php');
		$mtag['announcement'] = html2bbcode($mtag['announcement']);
	
		$joinperms = array($mtag['joinperm'] => ' selected');
		$viewperms = array($mtag['viewperm'] => ' selected');
		$threadperms = array($mtag['threadperm'] => ' selected');
		$postperms = array($mtag['postperm'] => ' selected');
		$closeapply = array($mtag['closeapply'] => ' checked');
	}
	
	$actives = array($_GET['subop'] => ' class="active"');
	
} elseif($_GET['op'] == 'join') {
	
	$tagid = empty($_GET['tagid'])?0:intval($_GET['tagid']);
	if(submitcheck('joinsubmit')) {
		$mtag = mtag_join('tagid', $tagid);
		if(empty($mtag)) {
			showmessage('mtag_join_error');
		} else {
			showmessage('join_success', "space.php?uid=$_SGLOBAL[supe_uid]&do=mtag&tagid=$mtag[tagid]", 0);
		}
	}

} elseif($_GET['op'] == 'out') {

	$tagid = empty($_GET['tagid'])?0:intval($_GET['tagid']);
	$mtag = $tagid?getmtag($tagid):array();

	if(submitcheck('outsubmit')) {
		//对私密群组进行验证
		if(($mtag['joinperm'] > 0 || $mtag['viewperm'] > 0) && $mtag['grade'] == 9) {
			//验证是否还有主群组
			$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('tagspace')." WHERE tagid='$tagid' AND grade='9'"), 0);
			if($count < 2) {
				showmessage('failure_to_withdraw_from_group');
			}
		}
		if($mtag['status'] != -9) {
			mtag_out($mtag, array($_SGLOBAL['supe_uid']));//退出
		}
		showmessage('do_success', "space.php?do=mtag");
	}
	
} elseif($_GET['op'] == 'mtaginvite') {
	
	//群组邀请
	$count = 0;
	$invites = array();
	$query = $_SGLOBAL['db']->query("SELECT mtag.*, i.* FROM ".tname('mtaginvite')." i
		LEFT JOIN ".tname('mtag')." mtag ON mtag.tagid=i.tagid
		WHERE i.uid='$_SGLOBAL[supe_uid]' ORDER BY i.dateline DESC");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		realname_set($value['fromuid'], $value['fromusername']);
		$value['title'] = $_SGLOBAL['profield'][$value['fieldid']]['title'];
		if(empty($value['pic'])) {
			$value['pic'] = 'image/nologo.jpg';
		}
		$invites[] = $value;
		$count++;
	}
	realname_get();
	
	//更新统计
	if($count != $space['mtaginvitenum']) {
		updatetable('space', array('mtaginvitenum'=>$count), array('uid'=>$space['uid']));
	}
	
} elseif($_GET['op'] == 'inviteconfirm') {
	
	$tagid = intval($_GET['tagid']);

	if($tagid && !empty($_GET['r'])) {
		//判断是否已经加入群组
		$ts_count = getcount('tagspace', array('tagid'=>$tagid, 'uid'=>$_SGLOBAL['supe_uid']));
		if(!$ts_count) {
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('mtaginvite')." WHERE tagid='$tagid' AND uid='$_SGLOBAL[supe_uid]'");
			if($invite = $_SGLOBAL['db']->fetch_array($query)) {
				//群组信息
				$mtag = getmtag($tagid);
				
				//检查数量
				$fieldid = $mtag['fieldid'];
				$field = $mtag['field'];
				//自己在当前栏目下面的群组
				$maxinputnum = 0;
				if($field['formtype'] == 'text' || $field['formtype'] == 'multi') {
					$maxinputnum = intval($field['inputnum']);
				} elseif($field['formtype'] == 'select') {
					$maxinputnum = 1;
				}
				if($maxinputnum) {
					$query = $_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('tagspace')." ts, ".tname('mtag')." mtag 
						WHERE ts.tagid=mtag.tagid AND ts.uid='$_SGLOBAL[supe_uid]' AND mtag.fieldid='$fieldid'");
					$count = $_SGLOBAL['db']->result($query, 0);
					if($count >= $maxinputnum) {
						showmessage('mtag_join_field_error', '', 1, array($field['title'], $maxinputnum));
					}
				}
				
				//加入群组
				$setarr = array(
					'tagid' => $tagid,
					'uid' => $_SGLOBAL['supe_uid'],
					'username' => $_SGLOBAL['supe_username']
				);
				$_SGLOBAL['db']->query("UPDATE ".tname('mtag')." SET membernum=membernum+1 WHERE tagid='$tagid'");
				inserttable('tagspace', $setarr, 0, true);
				
				//事件通知
				//实名
				realname_set($invite['fromuid'], $invite['fromusername']);
				realname_get();
				
				if(ckprivacy('mtag', 1)) {
					$fs = array();
					$fs['icon'] = 'mtag';
					
					$fs['title_template'] = cplang('feed_mtag_join_invite');
					$fs['title_data'] = array(
							'mtag' => "<a href=\"space.php?do=mtag&tagid=$tagid\">$mtag[tagname]</a>",
							'field' => "<a href=\"space.php?do=mtag&id=$mtag[fieldid]\">$mtag[title]</a>",
							'fromusername' => "<a href=\"space.php?uid=$invite[fromuid]\">{$_SN[$invite['fromuid']]}</a>"
						);
					$fs['body_template'] = '';
					$fs['body_data'] = array();
					$fs['body_general'] = '';
					
					feed_add($fs['icon'], $fs['title_template'], $fs['title_data'], $fs['body_template'], $fs['body_data'], $fs['body_general']);
				}

				$_SGLOBAL['db']->query("DELETE FROM ".tname('mtaginvite')." WHERE tagid='$tagid' AND uid='$_SGLOBAL[supe_uid]'");
				
				//更新统计
				if($space['mtaginvitenum']>0) {
					updatetable('space', array('mtaginvitenum'=>$space['mtaginvitenum']-1), array('uid'=>$space['uid']));
				}

				showmessage('invite_mtag_ok', '', 1, array($tagid));
			}
		}
	}

	//取消
	if($tagid) {
		
		$_SGLOBAL['db']->query("DELETE FROM ".tname('mtaginvite')." WHERE uid='$_SGLOBAL[supe_uid]' AND tagid='$tagid'");
		
		//更新统计
		if($space['mtaginvitenum']>0) {
			updatetable('space', array('mtaginvitenum'=>$space['mtaginvitenum']-1), array('uid'=>$space['uid']));
		}
				
		showmessage('invite_mtag_cancel');
		
	} elseif($tagid == 0) {
		
		$_SGLOBAL['db']->query("DELETE FROM ".tname('mtaginvite')." WHERE uid='$_SGLOBAL[supe_uid]'");
		
		//统计
		updatetable('space', array('mtaginvitenum'=>0), array('uid'=>$space['uid']));
		
		showmessage('do_success', "cp.php?ac=mtag&op=mtaginvite", 0);
	}

	showmessage('invite_mtag_cancel', "cp.php?ac=mtag&op=mtaginvite", 0);
	
}elseif($_GET['op'] == 'apply') {
	
	$tagid = intval($_GET['tagid']);
	if($tagid && submitcheck('pmsubmit')) {
		
		if(empty($_POST['message'])) {
			showmessage('fill_out_the_grounds_for_the_application');
		}
		$notearr = array();
		$mtag = getmtag($tagid);
		$mtagurl = 'cp.php?ac=mtag&tagid='.$tagid.'&op=manage&subop=members&key='.$_SGLOBAL['supe_username'];
		$_POST['message'] = getstr($_POST['message'], 0, 1, 1, 1);
		$message = cplang('apply_mtag_manager', array($mtagurl, $mtag['tagname'], $_POST['message']));
		
		$query = $_SGLOBAL['db']->query("SELECT uid FROM ".tname('tagspace')." WHERE tagid='$tagid' AND grade > 8 LIMIT 0 , 5");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$notearr[] = array(
					'uid' => $value['uid'],
					'type' => 'mtag',
					'new' => 1,
					'authorid' => $_SGLOBAL['supe_uid'],
					'author' => $_SGLOBAL['supe_username'],
					'note' => addslashes(sstripslashes($message)),
					'dateline' => $_SGLOBAL['timestamp']
				);

		}
		
		if(!$notearr) {
			$groups = array();
			$query = $_SGLOBAL['db']->query("SELECT gid FROM ".tname('usergroup')." WHERE managemtag='1'");
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				$groups[] = $value['gid'];
			}
			if($groups) {
				$query = $_SGLOBAL['db']->query("SELECT uid FROM ".tname('space')." WHERE groupid IN (".simplode($groups).") LIMIT 0 , 5");
				while ($value = $_SGLOBAL['db']->fetch_array($query)) {
					$notearr[] = array(
							'uid' => $value['uid'],
							'type' => 'mtag',
							'new' => 1,
							'authorid' => $_SGLOBAL['supe_uid'],
							'author' => $_SGLOBAL['supe_username'],
							'note' => addslashes(sstripslashes($message)),
							'dateline' => $_SGLOBAL['timestamp']
						);
				}
			}
		}
		note_apply($notearr);
		showmessage('do_success');
	}
} else {
	
	//创建新群组
	if(!checkperm('allowmtag')) {
		ckspacelog();
		showmessage('no_privilege');
	}
	
	//实名认证
	ckrealname('thread');
	
	//视频认证
	ckvideophoto('thread');

	//新用户见习
	cknewuser();
	
	//提交
	if(submitcheck('textsubmit')) {
		
		//自由输入
		$_POST['tagname'] = $tagname = getstr($_POST['tagname'], 40, 1, 1, 1);
		$_POST['fieldid'] = $fieldid = intval($_POST['fieldid']);
		
		$profield = $_SGLOBAL['profield'][$fieldid];
		if(empty($profield) || $profield['formtype'] != 'text') {
			showmessage('mtag_fieldid_does_not_exist');
		}
		if(strlen($tagname) < 2) {
			showmessage('mtag_tagname_error');
		}
		
		if(!empty($_POST['joinmode'])) {
			//二次确认
			$mtag = mtag_join('tagname', stripslashes($tagname), $fieldid);
			if(empty($mtag)) {
				showmessage('mtag_join_error');
			} else {
				$url = "space.php?uid=$_SGLOBAL[supe_uid]&do=mtag&tagid=$mtag[tagid]";
				showmessage('join_success', $url, 0);
			}
		} else {
			//寻找
			$newtagname = stripslashes($_POST['tagname']);
			$findmtag = $likemtags = array();
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('mtag')." WHERE tagname='$tagname' AND fieldid='$fieldid'");
			if(!$findmtag = $_SGLOBAL['db']->fetch_array($query)) {
				$key = stripsearchkey($_POST['tagname']);
				//找相似的
				$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('mtag')." WHERE tagname LIKE '%$key%' ORDER BY membernum DESC LIMIT 0,20");
				while ($value = $_SGLOBAL['db']->fetch_array($query)) {
					$likemtags[] = $value;
				}
			} else {
				if(empty($findmtag['pic'])) $findmtag['pic'] = 'image/nologo.jpg';
			}
			$_GET['op'] = 'confirm';
			include template("cp_mtag");
			exit();
		}
	} elseif(submitcheck('choicesubmit')) {
		
		$mtags = array();
		foreach ($_POST['tagname'] as $fieldid => $values) {
			$profield = $_SGLOBAL['profield'][$fieldid];
			if($profield['formtype'] == 'multi') {
				if($values && is_array($values)) {
					foreach ($values as $value) {
						$s = stripslashes($value);
						if(in_array($s, $profield['choice'])) {
							if($mtag = mtag_join('tagname', $s, $fieldid)) {
								$mtags[] = $mtag;
							}
						}
					}
				}
			} elseif($profield['formtype'] == 'select') {
				$s = stripslashes($values);
				if(in_array($s, $profield['choice'])) {
					if($mtag = mtag_join('tagname', $s, $fieldid)) {
						$mtags[] = $mtag;
					}
				}
			} else {
				continue;
			}
		}
		if(empty($mtags)) {
			showmessage('do_success', 'cp.php?ac=mtag');
		} else {
			$_GET['op'] = 'multiresult';
			include template("cp_mtag");
			exit();
		}
	}
	
	//已经加入的
	$existmtag = array();
	$query = $_SGLOBAL['db']->query("SELECT mtag.tagname, mtag.fieldid FROM ".tname('tagspace')." main
		LEFT JOIN ".tname('mtag')." mtag ON mtag.tagid=main.tagid
		WHERE main.uid='$_SGLOBAL[supe_uid]'");
	while($value = $_SGLOBAL['db']->fetch_array($query)) {
		$existmtag[$value['fieldid']][] = $value['tagname'];
	}
}

include template("cp_mtag");

//加入
function mtag_join($type, $key, $fieldid=0) {
	global $_SGLOBAL, $space;
	
	//判断用户是否已经加入
	$havejoin = 0;
	$key = addslashes($key);
	
	if($type == 'tagid') {
		$wheresql = "main.tagid='$key'";
	} else {
		if(strlen($key) < 2) {
			showmessage('mtag_tagname_error');
		}
		$wheresql = "main.tagname='$key' AND main.fieldid='$fieldid'";
	}
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('mtag')." main WHERE $wheresql LIMIT 1");
	if($mtag = $_SGLOBAL['db']->fetch_array($query)) {
		//判断是否加入
		$fieldid = $mtag['fieldid'];
		$havejoin = getcount('tagspace', array('tagid'=>$mtag['tagid'], 'uid'=>$_SGLOBAL['supe_uid']));
	} elseif($type == 'tagid') {
		return array();
	}
	if($havejoin) {
		return $mtag;
	}
	
	$field = $_SGLOBAL['profield'][$fieldid];
	if(!$mtag) {
		//创建
		$mtag = array(
			'tagname' => $key,
			'fieldid' => $fieldid
		);
		$tagid = inserttable('mtag', $mtag, 1);
		$mtag['tagid'] = $tagid;
	} else {
		$tagid = $mtag['tagid'];
	}
	
	//检查个数
	$mtag['title'] = $field['title'];
	
	//自己在当前栏目下面的群组
	$maxinputnum = 0;
	if($field['formtype'] == 'text' || $field['formtype'] == 'multi') {
		$maxinputnum = intval($field['inputnum']);
	} elseif($field['formtype'] == 'select') {
		$maxinputnum = 1;
	}
	if($maxinputnum) {
		$var = "myinputnum_$fieldid";
		$query = $_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('tagspace')." ts, ".tname('mtag')." mtag 
				WHERE ts.tagid=mtag.tagid AND ts.uid='$_SGLOBAL[supe_uid]' AND mtag.fieldid='$fieldid'");
		$_SGLOBAL[$var] = $_SGLOBAL['db']->result($query, 0);
		if($_SGLOBAL[$var] >= $maxinputnum) {
			showmessage('mtag_join_field_error', '', 1, array($field['title'], $maxinputnum));
		}
	}
	
	//加入
	$setarr = array(
		'tagid' => $tagid,
		'uid' => $_SGLOBAL['supe_uid'],
		'username' => $_SGLOBAL['supe_username']
	);
	if($mtag['joinperm'] == 2) {
		return array();
	} elseif($mtag['joinperm'] == 1) {
		$mtag['grade'] = $setarr['grade'] = -2;//需要审核
	} else {
		//检测是否有群主
		$query = $_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('tagspace')." WHERE tagid='$tagid' AND grade>=8");
		$modcount = $_SGLOBAL['db']->result($query, 0);
		if($modcount) {
			$mtag['grade'] = $setarr['grade'] = 0;//已经有群主
		} else {
			$mtag['grade'] = $setarr['grade'] = $field['manualmoderator']?0:9;//自动为群主
		}
		//事件通知
		if(ckprivacy('mtag', 1)) {
			$fs = array();
			$fs['icon'] = 'mtag';
			$fs['title_template'] = cplang('feed_mtag_join');
			$fs['title_data'] = array(
					'mtag' => "<a href=\"space.php?do=mtag&tagid=$tagid\">$mtag[tagname]</a>",
					'field' => "<a href=\"space.php?do=mtag&id=$mtag[fieldid]\">$mtag[title]</a>"
				);
			feed_add($fs['icon'], $fs['title_template'], $fs['title_data']);
		}
	}
	if($setarr) {
		$_SGLOBAL['db']->query("UPDATE ".tname('mtag')." SET membernum=membernum+1 WHERE tagid='$tagid'");
		inserttable('tagspace', $setarr, 0, true);
		$mtag['membernum'] = $mtag['membernum'] + 1;
	}
	return $mtag;
}

//踢人
function mtag_out($mtag, $uids) {
	global $_SGLOBAL;

	$_SGLOBAL['db']->query("DELETE FROM ".tname('tagspace')." WHERE tagid='$mtag[tagid]' AND uid IN (".simplode($uids).")");
	//更新成员数
	$count = getcount('tagspace', array('tagid'=>$mtag['tagid']));
	if($count > 0) {
		updatetable('mtag', array('membernum'=>$count), array('tagid'=>$mtag['tagid']));
	} else {
		$_SGLOBAL['db']->query("DELETE FROM ".tname('tagspace')." WHERE tagid='$mtag[tagid]'");
		$_SGLOBAL['db']->query("DELETE FROM ".tname('mtag')." WHERE tagid='$mtag[tagid]'");
		$_SGLOBAL['db']->query("DELETE FROM ".tname('thread')." WHERE tagid='$mtag[tagid]'");
		$_SGLOBAL['db']->query("DELETE FROM ".tname('post')." WHERE tagid='$mtag[tagid]'");
		$_SGLOBAL['db']->query("DELETE FROM ".tname('mtaginvite')." WHERE tagid='$mtag[tagid]'");
	
		//删除举报
		$_SGLOBAL['db']->query("DELETE FROM ".tname('report')." WHERE id='$mtag[tagid]' AND idtype='tagid'");
	}
}

//管理成员
function mtag_managemember($mtag, $uids, $newgrade) {
	global $_SGLOBAL;
	
	if(empty($uids)) return false;
	
	$managemtag = checkperm('managemtag');
	
	//副吧主
	if($mtag['grade'] < 9 && $newgrade >= 8 && !$managemtag) {
		showmessage('no_privilege');
	}
	
	$newuids = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('tagspace')." WHERE tagid='$mtag[tagid]' AND uid IN (".simplode($uids).")");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if($value['grade'] < 8 || ($mtag['grade'] == 9 && $value['uid'] != $_SGLOBAL['supe_uid']) || $managemtag) {
			$newuids[] = $value['uid'];
		}
	}
	if(empty($newuids)) {
		showmessage('mtag_managemember_no_privilege');
	}
	
	//通知
	$note_msg = cplang("note_members_grade_$newgrade", array($mtag['tagid'], $mtag['tagname']));
	$inserts = $n_uids = array();
	foreach ($newuids as $uid) {
		if($uid != $_SGLOBAL['supe_uid']) {
			$n_uids[] = $uid;
			$inserts[] = "('$uid', 'mtag', '1', '$_SGLOBAL[supe_uid]', '$_SGLOBAL[supe_username]', '".addslashes($note_msg)."', '$_SGLOBAL[timestamp]')";
		}
	}
	if($n_uids) {
		$_SGLOBAL['db']->query("INSERT INTO ".tname('notification')." (`uid`, `type`, `new`, `authorid`, `author`, `note`, `dateline`) VALUES ".implode(',', $inserts));
		$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET notenum=notenum+1 WHERE uid IN (".simplode($n_uids).")");
	}

	if($newgrade == -9) {
		mtag_out($mtag, $newuids);//踢人
	} else {
		$_SGLOBAL['db']->query("UPDATE ".tname('tagspace')." SET grade='$newgrade' WHERE tagid='$mtag[tagid]' AND uid IN (".simplode($newuids).")");
	}
}

function note_apply($sqlarr) {
	global $_SGLOBAL;
	
	$fieldsql = $comma = '';
	if(is_array($sqlarr)) {
		$uids = array();
		$valsql = '(';
		foreach($sqlarr as $key => $value) {
			$uids[] = $value['uid'];
			foreach($value as $vkey => $val) {
				if($key == 0) {
					$fieldsql .= $comma.$vkey;
				}
				$valsql .= $comma.'\''.$val.'\'';
				$comma = ', ';
			}
			if(count($sqlarr)-1 > $key) {
				$valsql .= '), (';
				$comma = '';
			}
		}
		$valsql .= ')';
		$_SGLOBAL['db']->query('insert into '.tname('notification').' ('.$fieldsql.') values '.$valsql);
		$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET notenum=notenum+1 WHERE uid IN (".simplode($uids).")");
	}
}

?>