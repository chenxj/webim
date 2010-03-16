<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_credit.php 12210 2009-05-21 07:05:38Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//分页
$perpage = 20;
$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page=1;
$start = ($page-1)*$perpage;
if(empty($_SCONFIG['networkpage'])) $start = 0;

//检查开始数
ckstart($start, $perpage);

//普通浏览模式
$cache_file = '';
$cache_time = $_SCONFIG['topcachetime'];
if($cache_time<5) $cache_time = 5;
$fuids = array();
$count = 0;
$now_pos = 0;

if(!in_array($_GET['view'], array('online','mm','gg','credit','experience','friendnum','viewnum','updatetime'))) $_GET['view'] = 'show';

if ($_GET['view'] == 'show') {
	$c_sql = "SELECT COUNT(*) FROM ".tname('show');
	$sql = "SELECT space.*, field.*, main.* FROM ".tname('show')." main
		LEFT JOIN ".tname('space')." space ON space.uid=main.uid
		LEFT JOIN ".tname('spacefield')." field ON field.uid=main.uid
		ORDER BY main.credit DESC";

	//清理
	if(substr($_SGLOBAL['timestamp'], -1) == '0') {
		$_SGLOBAL['db']->query("DELETE FROM ".tname('show')." WHERE credit<1");//清理小于1的数据
	}

	//我的竞价积分
	$space['showcredit'] = getcount('show', array('uid'=>$space['uid']), 'credit');
	$space['showcredit'] = intval($space['showcredit']);

	//我的位置
	$cookie_name = 'space_top_'.$_GET['view'];
	if($_SCOOKIE[$cookie_name]) {
		$now_pos = $_SCOOKIE[$cookie_name];
	} else {
		$now_pos = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('show')." WHERE credit>'$space[showcredit]'"), 0);
		$now_pos++;
		ssetcookie($cookie_name, $now_pos);
	}

} elseif ($_GET['view'] == 'mm') {
	if($multi_mode) {
		$c_sql = "SELECT COUNT(*) FROM ".tname('spacefield')." WHERE sex='2'";
	} else {
		$count = 100;
		$cache_file = S_ROOT.'./data/cache_top_mm.txt';
	}
	$sql = "SELECT main.*, field.* FROM ".tname('space')." main, ".tname('spacefield')." field
		WHERE field.sex='2' AND field.uid=main.uid
		ORDER BY main.viewnum DESC";

	//我的位置
	$cookie_name = 'space_top_'.$_GET['view'];
	if($_SCOOKIE[$cookie_name]) {
		$now_pos = $_SCOOKIE[$cookie_name];
	} else {
		if($space['sex']==2) {
			$pos_sql = "SELECT COUNT(*) FROM ".tname('space')." s, ".tname('spacefield')." f WHERE s.viewnum>'$space[viewnum]' AND f.sex='2' AND f.uid=s.uid";
			$now_pos = $_SGLOBAL['db']->result($_SGLOBAL['db']->query($pos_sql), 0);
			$now_pos++;
		} else {
			$now_pos = -1;
		}
		ssetcookie($cookie_name, $now_pos);
	}

} elseif ($_GET['view'] == 'gg') {
	if($multi_mode) {
		$c_sql = "SELECT COUNT(*) FROM ".tname('spacefield')." WHERE sex='1'";
	} else {
		$count = 100;
		$cache_file = S_ROOT.'./data/cache_top_gg.txt';
	}
	$sql = "SELECT main.*, field.* FROM ".tname('space')." main, ".tname('spacefield')." field
		WHERE field.sex='1' AND field.uid=main.uid
		ORDER BY main.viewnum DESC";

	//我的位置
	$cookie_name = 'space_top_'.$_GET['view'];
	if($_SCOOKIE[$cookie_name]) {
		$now_pos = $_SCOOKIE[$cookie_name];
	} else {
		if($space['sex']==1) {
			$pos_sql = "SELECT COUNT(*) FROM ".tname('space')." s, ".tname('spacefield')." f WHERE s.viewnum>'$space[viewnum]' AND f.sex='1' AND f.uid=s.uid";
			$now_pos = $_SGLOBAL['db']->result($_SGLOBAL['db']->query($pos_sql), 0);
			$now_pos++;
		} else {
			$now_pos = -1;
		}
		ssetcookie($cookie_name, $now_pos);
	}

} elseif ($_GET['view'] == 'credit') {
	if($multi_mode) {
		$c_sql = "SELECT COUNT(*) FROM ".tname('space');
	} else {
		$count = 100;
		$cache_file = S_ROOT.'./data/cache_top_credit.txt';
	}
	$sql = "SELECT main.*, field.* FROM ".tname('space')." main
		LEFT JOIN ".tname('spacefield')." field ON field.uid=main.uid
		ORDER BY main.credit DESC";

	//我的位置
	$cookie_name = 'space_top_'.$_GET['view'];
	if($_SCOOKIE[$cookie_name]) {
		$now_pos = $_SCOOKIE[$cookie_name];
	} else {
		$pos_sql = "SELECT COUNT(*) FROM ".tname('space')." s WHERE s.credit>'$space[credit]'";
		$now_pos = $_SGLOBAL['db']->result($_SGLOBAL['db']->query($pos_sql), 0);
		$now_pos++;
		ssetcookie($cookie_name, $now_pos);
	}

} elseif ($_GET['view'] == 'experience') {
	if($multi_mode) {
		$c_sql = "SELECT COUNT(*) FROM ".tname('space');
	} else {
		$count = 100;
		$cache_file = S_ROOT.'./data/cache_top_experience.txt';
	}
	$sql = "SELECT main.*, field.* FROM ".tname('space')." main
		LEFT JOIN ".tname('spacefield')." field ON field.uid=main.uid
		ORDER BY main.experience DESC";

	//我的位置
	$cookie_name = 'space_top_'.$_GET['view'];
	if($_SCOOKIE[$cookie_name]) {
		$now_pos = $_SCOOKIE[$cookie_name];
	} else {
		$pos_sql = "SELECT COUNT(*) FROM ".tname('space')." s WHERE s.experience>'$space[experience]'";
		$now_pos = $_SGLOBAL['db']->result($_SGLOBAL['db']->query($pos_sql), 0);
		$now_pos++;
		ssetcookie($cookie_name, $now_pos);
	}

} elseif ($_GET['view'] == 'friendnum') {
	if($multi_mode) {
		$c_sql = "SELECT COUNT(*) FROM ".tname('space');
	} else {
		$count = 100;
		$cache_file = S_ROOT.'./data/cache_top_friendnum.txt';
	}
	$sql = "SELECT main.*, field.* FROM ".tname('space')." main
		LEFT JOIN ".tname('spacefield')." field ON field.uid=main.uid
		ORDER BY main.friendnum DESC";

	//我的位置
	$cookie_name = 'space_top_'.$_GET['view'];
	if($_SCOOKIE[$cookie_name]) {
		$now_pos = $_SCOOKIE[$cookie_name];
	} else {
		$pos_sql = "SELECT COUNT(*) FROM ".tname('space')." s WHERE s.friendnum>'$space[friendnum]'";
		$now_pos = $_SGLOBAL['db']->result($_SGLOBAL['db']->query($pos_sql), 0);
		$now_pos++;
		ssetcookie($cookie_name, $now_pos);
	}

} elseif ($_GET['view'] == 'viewnum') {
	if($multi_mode) {
		$c_sql = "SELECT COUNT(*) FROM ".tname('space');
	} else {
		$count = 100;
		$cache_file = S_ROOT.'./data/cache_top_viewnum.txt';
	}
	$sql = "SELECT main.*, field.* FROM ".tname('space')." main
		LEFT JOIN ".tname('spacefield')." field ON field.uid=main.uid
		ORDER BY main.viewnum DESC";

	//我的位置
	$cookie_name = 'space_top_'.$_GET['view'];
	if($_SCOOKIE[$cookie_name]) {
		$now_pos = $_SCOOKIE[$cookie_name];
	} else {
		$pos_sql = "SELECT COUNT(*) FROM ".tname('space')." s WHERE s.viewnum>'$space[viewnum]'";
		$now_pos = $_SGLOBAL['db']->result($_SGLOBAL['db']->query($pos_sql), 0);
		$now_pos++;
		ssetcookie($cookie_name, $now_pos);
	}

} elseif ($_GET['view'] == 'online') {
	$c_sql = "SELECT COUNT(*) FROM ".tname('session');
	$sql = "SELECT field.*, space.*, main.*
		FROM ".tname('session')." main USE INDEX (lastactivity)
		LEFT JOIN ".tname('space')." space ON space.uid=main.uid
		LEFT JOIN ".tname('spacefield')." field ON field.uid=main.uid
		ORDER BY main.lastactivity DESC";
	$now_pos = -1;
} elseif ($_GET['view'] == 'updatetime') {
	$c_sql = "SELECT COUNT(*) FROM ".tname('space');
	$sql = "SELECT main.*, field.* FROM ".tname('space')." main USE INDEX (updatetime)
		LEFT JOIN ".tname('spacefield')." field ON field.uid=main.uid
		ORDER BY main.updatetime DESC";
	$now_pos = -1;
}

$list = array();
if(empty($count)) {
	$cache_mode = false;
	$count = empty($_SCONFIG['networkpage'])?1:$_SGLOBAL['db']->result($_SGLOBAL['db']->query($c_sql),0);
	$multi = multi($count, $perpage, $page, "space.php?do=top&view=$_GET[view]");
} else {
	$cache_mode = true;
	$multi = '';
	$start = 0;
	$perpage = $count;

	if($cache_file && file_exists($cache_file) && $_SGLOBAL['timestamp'] - @filemtime($cache_file) < $cache_time*60) {
		$list_cache = sreadfile($cache_file);
		$list = unserialize($list_cache);
	}
}
if($count && empty($list)) {
	$query = $_SGLOBAL['db']->query("$sql LIMIT $start,$perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$list[$value['uid']] = $value;
	}
	if($cache_mode && $cache_file) {
		swritefile($cache_file, serialize($list));
	}
}

foreach($list as $key => $value) {
	$value['isfriend'] = ($value['uid']==$space['uid'] || ($space['friends'] && in_array($value['uid'], $space['friends'])))?1:0;
	realname_set($value['uid'], $value['username'], $value['name'], $value['namestatus']);
	$fuids[] = $value['uid'];
	$list[$key] = $value;
}

//在线状态
$ols = array();
if($fuids) {
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('session')." WHERE uid IN (".simplode($fuids).")");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if(!$value['magichidden']) {
			$ols[$value['uid']] = $value['lastactivity'];
		} elseif ($_GET['view'] == 'online' && $list[$value['uid']]) {
			unset($list[$value['uid']]);
		}
	}
}

$actives = array($_GET['view'] => ' class="active"');

include_once template("space_top");

?>