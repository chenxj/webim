<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_task.php 11506 2009-03-06 09:19:17Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$op = empty($_GET['op']) ? "view" : $_GET['op'];
$mid = empty($_GET['mid']) ? '' : trim($_GET['mid']);

if(!checkperm('allowmagic')) {
	ckspacelog();
	showmessage('magic_groupid_not_allowed');//�����ڵ��û��鱻��ֹʹ�õ���
}

//��õ���
$magic = $mid ? magic_get($mid) : array();

//�ύ����
if (submitcheck("buysubmit")) {//����
	
	if(!$mid) {
		showmessage('unknown_magic');
	}

	//��õ�����Ϣ
	$results = magic_buy_get($magic);
	extract($results);

	//�������
	$charge = magic_buy_post($magic, $magicstore, $coupon);

	if($magic['experience']) {
		showmessage('magicbuy_success_with_experence', $_POST['refer'], 0, array($charge, $magic['experience'] * intval($_POST['buynum'])));
	} else {
		showmessage('magicbuy_success', $_POST['refer'], 0, array($charge));
	}

} elseif (submitcheck("presentsubmit")) {//����
	
	if(!$mid) {
		showmessage('unknown_magic');
	}

	if($mid == 'license') {
		showmessage("magic_can_not_be_presented");//�˵��߲���ת��
	}

	//����
	$fuid = 0;
	$_POST['fusername'] = trim($_POST['fusername']);
	if(empty($_POST['fusername'])) {
		showmessage("bad_friend_username_given");//����������Ч
	}
	$_POST['fusername'] = getstr($_POST['fusername'], 15);
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname("friend")." WHERE uid = '$_SGLOBAL[supe_uid]' AND fusername='$_POST[fusername]'");
	$value = $_SGLOBAL['db']->fetch_array($query);
	if(!$value) {
		showmessage("bad_friend_username_given");//����������Ч
	}
	$fuid = $value['fuid'];
	$fusername = $value['fusername'];

	//�����ߵı���ӵ�иõ��߼�ת����
	$usermagics = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname("usermagic")." WHERE uid='$_SGLOBAL[supe_uid]' AND mid IN('license', '$mid')");
	while($value = $_SGLOBAL['db']->fetch_array($query)) {
		$usermagics[$value['mid']] = $value;
	}
	if(!$usermagics['license'] || !$usermagics['license']['count']) {
		showmessage('has_no_more_present_magic');
	}
	if(!$usermagics[$mid] || !$usermagics[$mid]['count']) {
		showmessage('has_no_more_magic', '', '',  array($magic['name'], 'a_buy_'.$mid, "cp.php?ac=magic&op=buy&mid=$mid"));
	}

	//������
	$_SGLOBAL['db']->query('UPDATE '.tname('usermagic')." SET count = count - 1 WHERE uid = '$_SGLOBAL[supe_uid]' AND mid IN ('license', '$mid')");

	//������
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('usermagic')." WHERE uid='$fuid' AND mid='$mid'");
	$value = $_SGLOBAL['db']->fetch_array($query);
	$count = $value ? $value['count'] + 1 : 1;
	inserttable('usermagic',
		array('uid'=>$fuid,
			'username'=>$fusername,
			'mid'=>$mid,
			'count'=>$count), 0, true);

	//���루���ͣ���־
	inserttable('magicinlog',
		array('uid'=>$fuid,
			'username'=>$fusername,
			'mid'=>$mid,
			'count'=>1,
			'type'=>2,
			'fromid'=>$_SGLOBAL['supe_uid'],
			'credit'=>0,
			'dateline'=>$_SGLOBAL['timestamp']));

	//֪ͨ��������
	notification_add($fuid, 'magic', cplang('magic_present_note', array($magic['name'], "cp.php?ac=magic&view=me&mid=$mid")));
	showmessage("magicpresent_success", $_POST['refer'], '', array($fusername));
}

if($op == 'buy') {//����

	$results = magic_buy_get($magic);
	extract($results);

} elseif ($op == "present") {//����

	if($mid == 'license') {
		showmessage("magic_can_not_be_presented");//�˵��߲���ת��
	}

	//�����ߵı���ӵ�иõ��߼�ת����
	$usermagics = array();
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('usermagic')." WHERE uid='$_SGLOBAL[supe_uid]' AND mid IN('license', '$mid')");
	while($value = $_SGLOBAL['db']->fetch_array($query)) {
		$usermagics[$value['mid']] = $value;
	}
	if(!$usermagics['license'] || !$usermagics['license']['count']) {
		showmessage('has_no_more_present_magic');
	}
	if(!$usermagics[$mid] || !$usermagics[$mid]['count']) {
		showmessage('has_no_more_magic', '', '',  array($magic['name'], 'a_buy_'.$mid, "cp.php?ac=magic&op=buy&mid=$mid"));
	}
} elseif($op == 'showusage') {
	//��ʾʹ�ð���ͼƬ

	if(!$mid) {
		showmessage('unknown_magic');
	}
	
} elseif($op == 'receive') {//��ȡ���

	$uid = intval($_GET['uid']);
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('magicuselog')." WHERE uid='$uid' AND mid='gift' LIMIT 1");
	$value = $_SGLOBAL['db']->fetch_array($query);
	if($value && $value['data']) {
		$data = unserialize($value['data']);
		if($data['left'] <= 0) {
			showmessage("magic_gift_already_given_out");//����Ѿ���������
		}
		$data['receiver'] = is_array($data['receiver']) ? $data['receiver'] : array();
		if(in_array($_SGLOBAL['supe_uid'], $data['receiver'])) {
			showmessage("magic_had_got_gift");//���Ѿ���ȡ���˴κ����
		}
		$credit = $data['left'] > $data['chunk'] ? $data['chunk'] : $data['left'];
		$data['receiver'][] = $_SGLOBAL['supe_uid'];
		$data['left'] = $data['left'] - $credit;
		if($data['left'] > 0) {
			updatetable('magicuselog', array('data'=>serialize($data)), array('logid'=>$value['logid']));
		} else {
			$_SGLOBAL['db']->query('DELETE FROM '.tname('magicuselog')." WHERE logid = '$value[logid]'");
		}
		$_SGLOBAL['db']->query('UPDATE '.tname('space')." SET credit = credit + '$credit' WHERE uid='$_SGLOBAL[supe_uid]'");
		showmessage('magic_got_gift', '', '', array($credit));//���Ѿ���ȡ������ˣ���� x ����
	} else {
		showmessage('magic_has_no_gift');//�ռ�����û�����ú��
	}

} elseif($op == 'appear') {
	//ȡ�������Ч��

	if(!$_SGLOBAL['session']['magichidden']) {
		showmessage('magic_not_hidden_yet');
	}

	if(submitcheck('appearsubmit')) {
		updatetable('session', array('magichidden'=>'0'), array('uid'=>$_SGLOBAL['supe_uid']));
		updatetable('magicuselog', array('expire'=>$_SGLOBAL['timestamp']), array('uid'=>$_SGLOBAL['supe_uid'], 'mid'=>'invisible'));
		showmessage('do_success', $_POST['refer'], 0);
	}

} elseif($op == 'retrieve') {
	//���պ����

	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('magicuselog')." WHERE uid = '$_SGLOBAL[supe_uid]' AND mid = 'gift'");
	$value = $_SGLOBAL['db']->fetch_array($query);
	
	$leftcredit = 0;
	if(!$value) {
		showmessage('not_set_gift');//����ǰû�����ú��
	} elseif($value['data']) {
		$data = unserialize($value['data']);
		$leftcredit = intval($data['left']);
	}

	if(submitcheck('retrievesubmit')) {
		$_SGLOBAL['db']->query('DELETE FROM '.tname('magicuselog')." WHERE uid = '$_SGLOBAL[supe_uid]' AND mid = 'gift'");
		$_SGLOBAL['db']->query('UPDATE '.tname('space')." SET credit = credit + $leftcredit WHERE uid = '$_SGLOBAL[supe_uid]'");
		showmessage('do_success', $_POST['refer'], 0);
	}
	
} elseif($op == 'cancelsuperstar') {//ȡ����������
	
	$mid = 'superstar';
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('spacefield')." WHERE uid = '$_SGLOBAL[supe_uid]'");
	$value = $_SGLOBAL['db']->fetch_array($query);
	if(!$value || !$value['magicstar']) {
		showmessage('not_superstar_yet');
	}
	
	if(submitcheck('cancelsubmit')) {
		updatetable('spacefield', array('magicstar'=>0), array('uid'=>$_SGLOBAL['supe_uid']));
		updatetable('magicuselog', array('expire'=>$_SGLOBAL['timestamp']), array('uid'=>$_SGLOBAL['supe_uid'], 'mid'=>'superstar'));
		showmessage('do_success', $_POST['refer'], 0);
	}
	
} elseif($op == 'cancelflicker') {//ȡ���ʺ���
	
	$mid = 'flicker';
	$_GET['idtype'] = 'cid';
	$_GET['id'] = intval($_GET['id']);
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('comment')." WHERE cid = '$_GET[id]' AND authorid = '$_SGLOBAL[supe_uid]'");
	$value = $_SGLOBAL['db']->fetch_array($query);
	if(!$value || !$value['magicflicker']) {
		showmessage('no_flicker_yet');
	}
	
	if(submitcheck('cancelsubmit')) {
		updatetable('comment', array('magicflicker'=>0), array('cid'=>$_GET['id'], 'authorid'=>$_SGLOBAL['supe_uid']));
		showmessage('do_success', $_POST['refer'], 0);
	}
	
} elseif($op == 'cancelcolor') {//ȡ����ɫ��
	
	$mid = 'color';
	$_GET['id'] = intval($_GET['id']);
	//idtype������magiccolor�ֶεı�ӳ��
	$mapping = array('blogid'=>'blogfield', 'tid'=>'thread');
	$tablename = $mapping[$_GET['idtype']];
	if(empty($tablename)) {
		showmessage('no_color_yet');
	}
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname($tablename)." WHERE $_GET[idtype] = '$_GET[id]' AND uid = '$_SGLOBAL[supe_uid]'");
	$value = $_SGLOBAL['db']->fetch_array($query);
	if(!$value || !$value['magiccolor']) {
		showmessage('no_color_yet');
	}
	
	if(submitcheck('cancelsubmit')) {
		updatetable($tablename, array('magiccolor'=>0), array($_GET['idtype']=>$_GET[id]));
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('feed')." WHERE id = '$_GET[id]' AND idtype = '$_GET[idtype]'");
		$feed = $_SGLOBAL['db']->fetch_array($query);
		if($feed) {
			$feed['body_data'] = unserialize($feed['body_data']);
			if($feed['body_data']['magic_color']) {
				unset($feed['body_data']['magic_color']);
			}
			$feed['body_data'] = serialize($feed['body_data']);
			updatetable('feed', array('body_data'=>$feed['body_data']), array('feedid'=>$feed['feedid']));
		}
		showmessage('do_success', $_POST['refer'], 0);
	}
	
} elseif($op == 'cancelframe') {//ȡ�����
	
	$mid = 'frame';
	$_GET['idtype'] = 'picid';
	$_GET['id'] = intval($_GET['id']);
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('pic')." WHERE picid = '$_GET[id]' AND uid = '$_SGLOBAL[supe_uid]'");
	$value = $_SGLOBAL['db']->fetch_array($query);
	if(!$value || !$value['magicframe']) {
		showmessage('no_frame_yet');
	}
	
	if(submitcheck('cancelsubmit')) {
		updatetable('pic', array('magicframe'=>0), array('picid'=>$_GET['id']));
		showmessage('do_success', $_POST['refer'], 0);
	}

} elseif($op == 'cancelbgimage') {//ȡ����ֽ

	$mid = 'bgimage';
	$_GET['idtype'] = 'blogid';
	$_GET['id'] = intval($_GET['id']);
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('blogfield')." WHERE blogid = '$_GET[id]' AND uid = '$_SGLOBAL[supe_uid]'");
	$value = $_SGLOBAL['db']->fetch_array($query);
	if(!$value || !$value['magicpaper']) {
		showmessage('no_bgimage_yet');
	}
	
	if(submitcheck('cancelsubmit')) {
		updatetable('blogfield', array('magicpaper'=>0), array('blogid'=>$_GET['id']));
		showmessage('do_success', $_POST['refer'], 0);
	}
	
} else {//���

	if($_GET['view'] == 'me') {//�ҵĵ���

		//ӵ�еĵ���
		$types['list'] = ' class="active"';
		$list = $ids = $magics = array();
		if($mid) {
			$magics[$mid] = $magic;
			$ids[] = $mid;
		} else {
			//��ʾȫ��
			$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('magic')." WHERE close = '0'");
			while($value = $_SGLOBAL['db']->fetch_array($query)) {
				$value['forbiddengid'] = explode(',', $value['forbiddengid']);
				$magics[$value['mid']] = $value;
				$ids[] = $value['mid'];
			}
		}
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('usermagic')." WHERE uid='$_SGLOBAL[supe_uid]' AND mid IN (".simplode($ids).") AND count > 0");
		while($value=$_SGLOBAL['db']->fetch_array($query)) {
			$list[$value['mid']] = $value;
		}

	} elseif($_GET['view'] == 'log') {//��¼

		$_GET['type'] = in_array($_GET['type'], array('in', 'out', 'present')) ? $_GET['type'] : 'in';
		$types = array($_GET['type']=>' class="active"');

		//��ҳ
		$perpage = 20;
		$page = empty($_GET['page'])?0:intval($_GET['page']);
		if($page<1) $page = 1;
		$start = ($page-1)*$perpage;
		//��鿪ʼ��
		ckstart($start, $perpage);

		$list = array();
		if($_GET['type'] == 'in') {
			//��ü�¼
			$uids = array();//��ʾ������
			$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('magicinlog')." WHERE uid = '$_SGLOBAL[supe_uid]'"), 0);
			if($count) {
				$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('magicinlog')." WHERE uid = '$_SGLOBAL[supe_uid]' ORDER BY dateline DESC LIMIT $start, $perpage");
				while ($value = $_SGLOBAL['db']->fetch_array($query)) {
					if($value['type'] == 2) {
						$uids[] = $value['fromid'];
					}
					$list[] = $value;
				}
			}
			if($uids) {
				$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('member').' WHERE uid IN ('.simplode($uids).')');
				while($value = $_SGLOBAL['db']->fetch_array($query)) {
					realname_set($value['uid'], $value['username']);
				}
				realname_get();
			}
		} elseif($_GET['type'] == 'present') {
			//���ͼ�¼
			$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('magicinlog')." WHERE type = 2 AND fromid = '$_SGLOBAL[supe_uid]'"), 0);
			if($count) {
				$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('magicinlog')." WHERE type = 2 AND fromid = '$_SGLOBAL[supe_uid]' ORDER BY dateline DESC LIMIT $start, $perpage");
				while ($value = $_SGLOBAL['db']->fetch_array($query)) {
					realname_set($value['uid'], $value['username']);
					$list[] = $value;
				}
			}
			realname_get();
		} else {
			//ʹ�ü�¼
			$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('magicuselog')." WHERE uid = '$_SGLOBAL[supe_uid]'"), 0);
			if($count) {
				$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('magicuselog')." WHERE uid = '$_SGLOBAL[supe_uid]' ORDER BY dateline DESC LIMIT $start, $perpage");
				while ($value = $_SGLOBAL['db']->fetch_array($query)) {
					$value['data'] = unserialize($value['data']);
					$list[] = $value;
				}
			}
		}

		//��ҳ
		$theurl = 'cp.php?ac=magic&view=log&type='.$_GET['type'];
		$multi = multi($count, $perpage, $page, $theurl);

	} else {//�����г�
		$_GET['view'] = 'store';

		//��ʾ˳��
		$_GET['order'] = $_GET['order'] == 'hot' ? 'hot' : 'default';
		$orders = array($_GET['order']=>' class="active"');

		$magics = $ids = $list = array();
		$blacklist = array('coupon');//�����̵�������ʾ�ĵ���
		if($mid) {
			//ֻ��ʾ����
			$magics[$mid] = $magic;
			$ids[] = $mid;
		} else {
			//��ʾȫ��
			$orderby = $_GET['order'] == 'hot' ? '' : 'ORDER BY displayorder';
			$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('magic')." $orderby");
			while($value = $_SGLOBAL['db']->fetch_array($query)) {
				if($value['close'] || in_array($value['mid'], $blacklist)) {
					continue;
				}
				$value['forbiddengid'] = explode(',', $value['forbiddengid']);
				$magics[$value['mid']] = $value;
				$ids[] = $value['mid'];
			}
		}

		if(empty($magics)) {
			showmessage('magic_store_is_closed');//�����̵��Ѿ��رգ�û���κε��߿��ţ�
		}

		$oldids = array();//�Ѿ�¼���̵�ĵ���
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('magicstore').' WHERE mid IN ('.simplode($ids).')');
		while($value = $_SGLOBAL['db']->fetch_array($query)) {
			$list[$value['mid']] = $value;
			$oldids[] = $value['mid'];
			//���¿����
			if($value['storage'] < $magics[$value['mid']]['providecount'] &&
				$value['lastprovide'] + $magics[$value['mid']]['provideperoid'] < $_SGLOBAL['timestamp']) {

				$_SGLOBAL['db']->query('UPDATE '.tname('magicstore')." SET storage = '{$magics[$value[mid]][providecount]}', lastprovide = '$_SGLOBAL[timestamp]' WHERE mid = '$value[mid]'");
				$list[$value['mid']]['storage'] = $magics[$value['mid']]['providecount'];
			}
		}

		$newids = array_diff($ids, $oldids);//��δ¼���̵�ĵ���
		if($newids) {
			$inserts = array();
			foreach ($newids as $id) {
				$inserts[] = "('$id', '{$magics[$id][providecount]}', '$_SGLOBAL[timestamp]')";
				$list[$id] = array("mid"=>$id,
								'storage'=>$magics[$id]['providecount'],
								'lastprovide'=>$_SGLOBAL['timestamp']);
			}
			$_SGLOBAL['db']->query('INSERT INTO '.tname('magicstore').'(mid, storage, lastprovide) VALUES '.implode(',',$inserts));
		}

		//����
		if($_GET['order'] == 'hot') {
			//���۳�������
			function hotsort($a, $b) {
				return ($a['sellcount'] > $b['sellcount']) ? -1 : ($a['sellcount'] < $b['sellcount']);
			}
			usort($list, 'hotsort');
			$order = array();
			foreach ($list as $value) {
				$order[$value['mid']] = $value;
			}
			$list = $order;
			unset($order);
		} else {
			//Ĭ��
			$order = array();
			foreach ($ids as $id) {
				$order[$id] = $list[$id];
			}
			$list = $order;
			unset($order);
		}
	}

	$actives = array($_GET['view']=>' class="active"');
}

include_once template('cp_magic');

?>