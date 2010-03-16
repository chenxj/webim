<?php

if(!defined('IN_UCHOME') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!$allowmanage = checkperm('managemagiclog')) {
	$_GET['uid'] = $_SGLOBAL['supe_uid'];//只能操作本人的
	$_GET['username'] = $_SGLOBAL['supe_username'];
}

include_once(S_ROOT.'./data/data_magic.php');

$_GET['view'] = $_GET['view'] ? $_GET['view'] : 'holdlog';
$actives[$_GET['view']] = ' class="active"';

if ($_GET['view'] == 'inlog') {
	//获取记录
	$mpurl = 'admincp.php?ac=magiclog&view=inlog';

	//处理搜索
	$intkeys = array('type');
	$strkeys = array('mid');
	$randkeys = array();
	$likekeys = array('username');
	$results = getwheres($intkeys, $strkeys, $randkeys, $likekeys);
	$wherearr = $results['wherearr'];
	if($_GET['starttime']){
		$wherearr[] = 'dateline >= ' . sstrtotime($_GET['starttime']);
		$mpurl .= '&starttime=' . $_GET['starttime'];
	}
	if($_GET['endtime']){
		$wherearr[] = 'dateline <= ' . sstrtotime($_GET['endtime']);
		$mpurl .= '&endtime=' . $_GET['endtime'];
	}
	if($_GET['count']) {
		list($min, $max) = explode('-', $_GET['count']);
		$wherearr[] = "count >= '$min' AND count <= '$max'";
	}

	$wheresql = empty($wherearr)?'1':implode(' AND ', $wherearr);
	$ordersql = 'ORDER BY dateline DESC';
	$mpurl .= '&'.implode('&', $results['urls']);

	$perpage = 50;
	$mpurl .= '&perpage='.$perpage;

	$page = empty($_GET['page'])?1:intval($_GET['page']);
	if($page<1) $page = 1;
	$start = ($page-1)*$perpage;
	//检查开始数
	ckstart($start, $perpage);

	$list = array();
	$multi = '';

	$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('magicinlog')." WHERE $wheresql"), 0);
	if($count) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('magicinlog')." WHERE $wheresql $ordersql LIMIT $start,$perpage");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$list[] = $value;
		}
		$multi = multi($count, $perpage, $page, $mpurl);
	}

} elseif ($_GET['view'] == 'uselog') {
	//使用记录
	$mpurl = 'admincp.php?ac=magiclog&view=uselog';

	//处理搜索
	$intkeys = array('id');
	$strkeys = array('mid','idtype');
	$randkeys = array();
	$likekeys = array('username');
	$results = getwheres($intkeys, $strkeys, $randkeys, $likekeys);
	$wherearr = $results['wherearr'];
	if($_GET['starttime']){
		$wherearr[] = 'dateline >= ' . sstrtotime($_GET['starttime']);
		$mpurl .= '&starttime=' . $_GET['starttime'];
	}
	if($_GET['endtime']){
		$wherearr[] = 'dateline <= ' . sstrtotime($_GET['endtime']);
		$mpurl .= '&endtime=' . $_GET['endtime'];
	}

	$wheresql = empty($wherearr)?'1':implode(' AND ', $wherearr);
	$mpurl .= '&'.implode('&', $results['urls']);

	//排序
	$ordersql = 'ORDER BY dateline DESC';

	//显示分页
	$perpage = 50;
	$mpurl .= '&perpage='.$perpage;

	$page = empty($_GET['page'])?1:intval($_GET['page']);
	if($page<1) $page = 1;
	$start = ($page-1)*$perpage;
	//检查开始数
	ckstart($start, $perpage);

	$list = array();
	$multi = '';

	$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('magicuselog')." WHERE $wheresql"), 0);
	if($count) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('magicuselog')." WHERE $wheresql $ordersql LIMIT $start,$perpage");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$list[] = $value;
		}
		$multi = multi($count, $perpage, $page, $mpurl);
	}

} elseif($_GET['view'] == 'storelog') {
	//道具商店售出统计
	if(!$allowmanage) {
		cpmessage('no_authority_management_operation');
	}
	
	$list = array();
	$totalcount = $totalcredit = 0;
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('magicstore'));
	while($value = $_SGLOBAL['db']->fetch_array($query)) {
		$list[$value['mid']] = $value;
		$totalcount += $value['sellcount'];
		$totalcredit += $value['sellcredit'];
	}
	
	function hotsort($a, $b) {
		return ($a['sellcount'] > $b['sellcount']) ? -1 : ($a['sellcount'] < $b['sellcount']);
	}
	usort($list, 'hotsort');
	
} else {
	//持有道具
	$mpurl = 'admincp.php?ac=magiclog&view=holdlog';

	//处理搜索
	$intkeys = array('uid');
	$strkeys = array('mid');
	$randkeys = array();
	$likekeys = array('username');
	$results = getwheres($intkeys, $strkeys, $randkeys, $likekeys);
	$wherearr = $results['wherearr'];

	//除去为0的
	$wherearr[] = 'count > 0';

	$wheresql = empty($wherearr)?'1':implode(' AND ', $wherearr);
	$mpurl .= '&'.implode('&', $results['urls']);

	//排序
	$ordersql = '';

	//显示分页
	$perpage = 50;
	$mpurl .= '&perpage='.$perpage;

	$page = empty($_GET['page'])?1:intval($_GET['page']);
	if($page<1) $page = 1;
	$start = ($page-1)*$perpage;
	//检查开始数
	ckstart($start, $perpage);

	$list = array();
	$multi = '';

	$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('usermagic')." WHERE $wheresql"), 0);
	if($count) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('usermagic')." WHERE $wheresql $ordersql LIMIT $start,$perpage");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$list[] = $value;
		}
		$multi = multi($count, $perpage, $page, $mpurl);
	}
}


?>