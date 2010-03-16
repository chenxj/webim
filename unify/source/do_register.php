<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: do_register.php 13111 2009-08-12 02:39:58Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$op = $_GET['op'] ? trim($_GET['op']) : '';

if($_SGLOBAL['supe_uid']) {
	showmessage('do_success', 'space.php?do=home', 0);
}

//û�е�¼��
$_SGLOBAL['nologinform'] = 1;

//��������
$uid = empty($_GET['uid'])?0:intval($_GET['uid']);
$code = empty($_GET['code'])?'':$_GET['code'];
$app = empty($_GET['app'])?'':intval($_GET['app']);
$invite = empty($_GET['invite'])?'':$_GET['invite'];
$invitearr = array();

$invitepay = getreward('invitecode', 0);
$pay = $app ? 0 : $invitepay['credit'];

if($uid && $code && !$pay) {
	$m_space = getspace($uid);
	if($code == space_key($m_space, $app)) {//��֤ͨ��
		$invitearr['uid'] = $uid;
		$invitearr['username'] = $m_space['username'];
	}
	$url_plus = "uid=$uid&app=$app&code=$code";
} elseif($uid && $invite) {
	include_once(S_ROOT.'./source/function_cp.php');
	$invitearr = invite_get($uid, $invite);
	$url_plus = "uid=$uid&invite=$invite";
}

$jumpurl = $app?"userapp.php?id=$app&my_extra=invitedby_bi_{$uid}_{$code}&my_suffix=Lw%3D%3D":'space.php?do=home';

if(empty($op)) {

	if($_SCONFIG['closeregister']) {
		if($_SCONFIG['closeinvite']) {
			showmessage('not_open_registration');
		} elseif(empty($invitearr)) {
			showmessage('not_open_registration_invite');
		}
	}

	//�Ƿ�ر�վ��
	checkclose();

	if(submitcheck('registersubmit')) {

		//�Ѿ�ע���û�
		if($_SGLOBAL['supe_uid']) {
			showmessage('registered', 'space.php');
		}

		if($_SCONFIG['seccode_register']) {
			include_once(S_ROOT.'./source/function_cp.php');
			if(!ckseccode($_POST['seccode'])) {
				showmessage('incorrect_code');
			}
		}

		if(!@include_once S_ROOT.'./uc_client/client.php') {
			showmessage('system_error');
		}

		if($_POST['password'] != $_POST['password2']) {
			showmessage('password_inconsistency');
		}

		if(!$_POST['password'] || $_POST['password'] != addslashes($_POST['password'])) {
			showmessage('profile_passwd_illegal');
		}
		
		$username = trim($_POST['username']);
		$password = $_POST['password'];
		
		$email = isemail($_POST['email'])?$_POST['email']:'';
		if(empty($email)) {
			showmessage('email_format_is_wrong');
		}
		//����ʼ�
		if($_SCONFIG['checkemail']) {
			if($count = getcount('spacefield', array('email'=>$email))) {
				showmessage('email_has_been_registered');
			}
		}
		//���IP
		$onlineip = getonlineip();
		if($_SCONFIG['regipdate']) {
			$query = $_SGLOBAL['db']->query("SELECT dateline FROM ".tname('space')." WHERE regip='$onlineip' ORDER BY dateline DESC LIMIT 1");
			if($value = $_SGLOBAL['db']->fetch_array($query)) {
				if($_SGLOBAL['timestamp'] - $value['dateline'] < $_SCONFIG['regipdate']*3600) {
					showmessage('regip_has_been_registered', '', 1, array($_SCONFIG['regipdate']));
				}
			}
		}

		$newuid = uc_user_register($username, $password, $email);
		if($newuid <= 0) {
			if($newuid == -1) {
				showmessage('user_name_is_not_legitimate');
			} elseif($newuid == -2) {
				showmessage('include_not_registered_words');
			} elseif($newuid == -3) {
				showmessage('user_name_already_exists');
			} elseif($newuid == -4) {
				showmessage('email_format_is_wrong');
			} elseif($newuid == -5) {
				showmessage('email_not_registered');
			} elseif($newuid == -6) {
				showmessage('email_has_been_registered');
			} else {
				showmessage('register_error');
			}
		} else {
			$setarr = array(
				'uid' => $newuid,
				'username' => $username,
				'password' => md5("$newuid|$_SGLOBAL[timestamp]")//���������������
			);
			//���±����û���
			inserttable('member', $setarr, 0, true);

			//��ͨ�ռ�
			include_once(S_ROOT.'./source/function_space.php');
			$space = space_open($newuid, $username, 0, $email);

			//Ĭ�Ϻ���
			$flog = $inserts = $fuids = $pokes = array();
			if(!empty($_SCONFIG['defaultfusername'])) {
				$query = $_SGLOBAL['db']->query("SELECT uid,username FROM ".tname('space')." WHERE username IN (".simplode(explode(',', $_SCONFIG['defaultfusername'])).")");
				while ($value = $_SGLOBAL['db']->fetch_array($query)) {
					$value = saddslashes($value);
					$fuids[] = $value['uid'];
					$inserts[] = "('$newuid','$value[uid]','$value[username]','1','$_SGLOBAL[timestamp]')";
					$inserts[] = "('$value[uid]','$newuid','$username','1','$_SGLOBAL[timestamp]')";
					$pokes[] = "('$newuid','$value[uid]','$value[username]','".addslashes($_SCONFIG['defaultpoke'])."','$_SGLOBAL[timestamp]')";
					//��Ӻ��ѱ����¼
					$flog[] = "('$value[uid]','$newuid','add','$_SGLOBAL[timestamp]')";
				}
				if($inserts) {
					$_SGLOBAL['db']->query("REPLACE INTO ".tname('friend')." (uid,fuid,fusername,status,dateline) VALUES ".implode(',', $inserts));
					$_SGLOBAL['db']->query("REPLACE INTO ".tname('poke')." (uid,fromuid,fromusername,note,dateline) VALUES ".implode(',', $pokes));
					$_SGLOBAL['db']->query("REPLACE INTO ".tname('friendlog')." (uid,fuid,action,dateline) VALUES ".implode(',', $flog));

					//��ӵ����ӱ�
					$friendstr = empty($fuids)?'':implode(',', $fuids);
					updatetable('space', array('friendnum'=>count($fuids), 'pokenum'=>count($pokes)), array('uid'=>$newuid));
					updatetable('spacefield', array('friend'=>$friendstr, 'feedfriend'=>$friendstr), array('uid'=>$newuid));

					//����Ĭ���û����ѻ���
					include_once(S_ROOT.'./source/function_cp.php');
					foreach ($fuids as $fuid) {
						friend_cache($fuid);
					}
				}
			}

			//����session
			insertsession($setarr);

			//����cookie
			ssetcookie('auth', authcode("$setarr[password]\t$setarr[uid]", 'ENCODE'), 2592000);
			ssetcookie('loginuser', $username, 31536000);
			ssetcookie('_refer', '');

			//��������
			if($invitearr) {
				include_once(S_ROOT.'./source/function_cp.php');
				invite_update($invitearr['id'], $setarr['uid'], $setarr['username'], $invitearr['uid'], $invitearr['username'], $app);
				//����ύ�������ַ�������������ֱ��ͨ��������֤
				if($invitearr['email'] == $email) {
					updatetable('spacefield', array('emailcheck'=>1), array('uid'=>$newuid));
				}
				
				//ͳ�Ƹ���
				include_once(S_ROOT.'./source/function_cp.php');
				if($app) {
					updatestat('appinvite');
				} else {
					updatestat('invite');
				}
			}

			//�����¼
			if($_SCONFIG['my_status']) inserttable('userlog', array('uid'=>$newuid, 'action'=>'add', 'dateline'=>$_SGLOBAL['timestamp']), 0, true);

			showmessage('registered', $jumpurl);
		}

	}
	
	$register_rule = data_get('registerrule');

	include template('do_register');

} elseif($op == "checkusername") {

	$username = trim($_GET['username']);
	if(empty($username)) {
		showmessage('user_name_is_not_legitimate');
	}
	@include_once (S_ROOT.'./uc_client/client.php');
	$ucresult = uc_user_checkname($username);

	if($ucresult == -1) {
		showmessage('user_name_is_not_legitimate');
	} elseif($ucresult == -2) {
		showmessage('include_not_registered_words');
	} elseif($ucresult == -3) {
		showmessage('user_name_already_exists');
	} else {
		showmessage('succeed');
	}
} elseif($op == "checkseccode") {
	
	include_once(S_ROOT.'./source/function_cp.php');
	if(ckseccode(trim($_GET['seccode']))) {
		showmessage('succeed');
	} else {
		showmessage('incorrect_code');
	}
}

?>