<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: admincp_block.php 12900 2009-07-27 07:26:58Z zhengqingpeng $
*/

if(!defined('IN_UCHOME') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('manageblock')) {
	cpmessage('no_authority_management_operation');
}

if(false === function_exists('mksqltime')) {
	function mksqltime($time) {
		global $_SGLOBAL;
		return $_SGLOBAL['timestamp']-$time;
	}
}

$turl = 'admincp.php?ac=block';

if(submitcheck('valuesubmit')) {
	$_POST['blockname'] = shtmlspecialchars(trim($_POST['blockname']));
	if(empty($_POST['blockname'])) cpmessage('correctly_completed_module_name');
	
	$setarr = array(
		'blockname' => $_POST['blockname'],
		'blocksql' => sub_getblocksql($_POST['blocksql'])
	);
	
	if($setarr['blocksql'] && !$_SGLOBAL['db']->query(stripslashes(preg_replace("/\[(\d+)\]/e", "mksqltime('\\1')", $setarr['blocksql'])).' LIMIT 1', 'SILENT')) {
		cpmessage('sql_statements_can_not_be_completed_for_normal', '', 1, array($_SGLOBAL['db']->error(), $_SGLOBAL['db']->errno()));
	}
	
	$bid = intval($_POST['bid']);
	if($bid) {
		updatetable('block', $setarr, array('bid'=>$bid));
	} else {
		$bid = inserttable('block', $setarr, 1);
	}
	
	//下一步
	cpmessage('enter_the_next_step', $turl.'&op=code&id='.$bid, 0);
	
} elseif (submitcheck('codesubmit')) {

	$bid = intval($_POST['bid']);
	$block = sub_getblock($bid);
	$setarr = array(
		'cachename' => $_POST['cachename'],
		'cachetime' => intval($_POST['cachetime']),
		'startnum' => intval($_POST['startnum']),
		'num' => intval($_POST['num']),
		'perpage' => intval($_POST['perpage']),
		'htmlcode' => trim($_POST['htmlcode'])
	);
	if($setarr['perpage']) $setarr['num'] = 0;
	$setarr['htmlcode'] = addslashes(preg_replace("/href\=\"(?!http\:\/\/)(.+?)\"/i", 'href="'.getsiteurl().'\\1"', stripslashes($setarr['htmlcode'])));
	updatetable('block', $setarr, array('bid'=>$bid));
	
	//更新缓存
	include_once(S_ROOT.'./source/function_cache.php');
	block_cache();
	
	//写入模板
	if($block['blocksql']) {
		if(empty($setarr['perpage'])) {
			$perstr = '';
			if(empty($setarr['num'])) $setarr['num'] = 1;
			$block['blocksql'] .= " LIMIT $setarr[startnum],$setarr[num]";
		} else {
			$perstr = 'perpage/'.$setarr['perpage'].'/';
		}
		$setarr['htmlcode'] = "<!--{block/{$perstr}sql/".rawurlencode($block['blocksql'])."/cachename/$setarr[cachename]/cachetime/$setarr[cachetime]}-->\r\n".stripslashes($setarr['htmlcode']);
	}
	$tpl = S_ROOT.'./data/blocktpl/'.$bid.'.htm';
	swritefile($tpl, $setarr['htmlcode']);
	
	cpmessage('do_success', $turl);
}

if(empty($_GET['op'])) {
	//显示列表
	$perpage = 20;
	$page = empty($_GET['page'])?1:intval($_GET['page']);
	if($page<1) $page = 1;
	$start = ($page-1)*$perpage;
	//检查开始数
	ckstart($start, $perpage);
	
	$list = array();
	$multi = '';
	$count = getcount('block', array());
	if($count) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('block')." ORDER BY bid DESC LIMIT $start,$perpage");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$list[] = $value;
		}
		$multi = multi($count, $perpage, $page, 'admincp.php?ac=block');
	}
	
	$actives = array('view' => ' class="active"');
	
} elseif($_GET['op'] == 'code') {
	
	//获取数据
	$block = sub_getblock($_GET['id']);

	//显示结果
	$colnames = $keys = array();
	if(!empty($block['blocksql'])) {
		if($query = $_SGLOBAL['db']->query(preg_replace("/\[(\d+)\]/e", "mksqltime('\\1')", $block['blocksql'])." LIMIT 1", 'SILENT')) {
			$value = $_SGLOBAL['db']->fetch_array($query);
			foreach ($value as $keyname => $keyvalue) {
				if(count($keys) < 2) $keys[] = $keyname;
				$colnames[$keyname] = getstr($keyvalue, 40);
			}
		}
	}
	
	$phptag = '$';
	
	//默认显示
	if(empty($block['cachename'])) {
		$block['cachename'] = 'block'.$block['bid'];
	}
	if(empty($block['htmlcode']) && !empty($colnames)) {
		$block['htmlcode'] = '<ul>'."\r\n";
		$block['htmlcode'] .= '<!--{loop $_SBLOCK[\''.$block['cachename'].'\'] $value}-->'."\r\n";
		$block['htmlcode'] .= '<li>$value['.$keys[0].'] $value['.$keys[1].']'."</li>\r\n";
		$block['htmlcode'] .= '<!--{/loop}-->'."\r\n";
		$block['htmlcode'] .= '</ul>'."\r\n";
	}
	$block['htmlcode'] = shtmlspecialchars($block['htmlcode']);

} elseif($_GET['op'] == 'add') {
	
	//获取数据
	$block = array();
	//获取现有表
	$tables = sub_gettables();
	
	$sqlTables = array(
		'blog' => tname('blog'),
		'album' => tname('album'),
		'thread' => tname('thread'),
		'feed' => tname('feed'),
		'space' => tname('space'),
		'pic' => tname('pic'),
		'mtag' => tname('mtag')
	);
	$sqls = array(
		'blog' => 'SELECT * FROM `'.tname('blog').'` AS `blog`WHEREORDER',
		'album' => 'SELECT * FROM `'.tname('album').'` AS `album`WHEREORDER',
		'thread' => 'SELECT * FROM `'.tname('thread').'` AS `thread`WHEREORDER',
		'feed' => 'SELECT * FROM `'.tname('feed').'` AS `feed`WHEREORDER',
		'space' => 'SELECT * FROM `'.tname('space').'` AS `space` LEFT JOIN `'.tname('spacefield').'` AS `spacefield` on `space`.`uid`=`spacefield`.`uid`WHEREORDER',
		'pic' => 'SELECT * FROM `'.tname('pic').'` AS `pic`WHEREORDER',
		'mtag' => 'SELECT * FROM `'.tname('mtag').'` AS `mtag`WHEREORDER'
	);
	$usergrouparr = $list = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('profield')." ORDER BY displayorder");
	while($value = $_SGLOBAL['db']->fetch_array($query)) {
		$list[] = $value;
	}
	$query = $_SGLOBAL['db']->query("SELECT gid, grouptitle FROM ".tname('usergroup'));
	while($value = $_SGLOBAL['db']->fetch_array($query)) {
		$usergrouparr[$value['gid']] = $value;
	}

} elseif($_GET['op'] == 'blocksql') {
	
	//获取数据
	$block = sub_getblock($_GET['id']);
	//获取现有表
	$tables = sub_gettables();

} elseif($_GET['op'] == 'tpl') {
	
	$bid = intval($_GET['id']);
	$code = shtmlspecialchars("<!--{template data/blocktpl/$bid}-->");

} elseif($_GET['op'] == 'js') {
	
	$bid = intval($_GET['id']);
	$code = shtmlspecialchars("<script language=\"javascript\" type=\"text/javascript\" src=\"".getsiteurl()."js.php?id=$bid\"></script>");

} elseif($_GET['op'] == 'delete') {
	
	$_POST['bids'] = array(intval($_GET['id']));
	include_once(S_ROOT.'./source/function_delete.php');
	if(!empty($_POST['bids']) && deleteblocks($_POST['bids'])) {
		cpmessage('a_call_to_delete_the_specified_modules_success', $turl);
	} else {
		cpmessage('choose_to_delete_the_data_transfer_module', $turl);
	}
}

function sub_getblock($bid) {
	global $_SGLOBAL;

	$bid = intval($bid);
	$block = array();
	if($bid) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('block')." WHERE bid='$bid'");
		$block = $_SGLOBAL['db']->fetch_array($query);
	}
	if(empty($block)) {
		cpmessage('designated_data_transfer_module_does_not_exist');
	}
	
	return $block;
}

function sub_gettables() {
	global $_SGLOBAL, $_SC;
	
	$file = S_ROOT.'./data/data_table_'.X_RELEASE.'.txt';
	
	$tables = array();
	$content = trim(sreadfile($file));
	if($content) {
		$tables = unserialize($content);
	} else {
		$query = $_SGLOBAL['db']->query("SHOW TABLES LIKE '$_SC[tablepre]%'");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$values = array_values($value);
			if(!strexists($values[0], 'cache')) {
				$subquery = $_SGLOBAL['db']->query("SHOW CREATE TABLE $values[0]");
				$result = $_SGLOBAL['db']->fetch_array($subquery);
				$tables[$values[0]] = sub_getcolumn($result['Create Table']);
			}
		}
		swritefile($file, serialize($tables));
	}
	
	return $tables;
	
}

function sub_getblocksql($sql) {
	if(strlen($sql)> 15) {
		$searchs = array("/^(select)/i", "/(\s+limit.+)/i");
		$replaces = array('', '');
		$sql = 'SELECT '.trim(str_replace(';', '', preg_replace($searchs, $replaces, $sql)));
	} else {
		$sql = '';
	}
	return $sql;
}

function sub_getcolumn($creatsql) {
	
	$cols = array();
	$arr = explode("\n", $creatsql);
	foreach ($arr as $value) {
		$value = trim($value);
		$value = str_replace('`', '', substr($value, 0, strpos($value, ' ')));
		if(!preg_match("/(CREATE|PRIMARY|KEY|\))/i", $value)) {
			$cols[] = $value;
		}
	}
	return $cols;
}

?>