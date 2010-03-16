<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: space.php 13203 2009-08-20 02:26:58Z zhengqingpeng $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$credit = $friendnum = $viewnum = $wherearr = $spacelist = array();
$sql = '';
$uid = !empty($_GET['uid']) ? trim($_GET['uid']) : '';

$dateline = !empty($_GET['dateline']) ? intval($_GET['dateline']) : 0;
$updatetime = !empty($_GET['updatetime']) ? intval($_GET['updatetime']) : 0;
$start = !empty($_GET['start']) ? intval($_GET['start']) : 0;
$limit = !empty($_GET['limit']) ? intval($_GET['limit']) : 10;

$friendnum[] = empty($_GET['startfriendnum']) ? 0 : intval($_GET['startfriendnum']);
$friendnum[] = empty($_GET['endfriendnum']) ? 0 : intval($_GET['endfriendnum']);

$viewnum[] = empty($_GET['startviewnum']) ? 0 : intval($_GET['startviewnum']);
$viewnum[] = empty($_GET['endviewnum']) ? 0 : intval($_GET['endviewnum']);

$credit[] = empty($_GET['startcredit']) ? 0 : intval($_GET['startcredit']);
$credit[] = empty($_GET['endcredit']) ? 0 : intval($_GET['endcredit']);


$uids = getdotstring($uid, 'int');
if($uids) $wherearr[] = 'uid IN ('.$uids.')';
if($dateline) $wherearr[] = "dateline>'".($_SGLOBAL['timestamp']-$dateline)."'";
if($updatetime) $wherearr[] = "updatetime>'".($_SGLOBAL['timestamp']-$updatetime)."'";

$friendnumstr = getscopequery('friendnum', $friendnum);
if($friendnumstr) $wherearr[] = $friendnumstr;

$viewnumstr = getscopequery('viewnum', $viewnum);
if($viewnumstr) $wherearr[] = $viewnumstr;

$creditstr = getscopequery('credit', $credit);
if($creditstr) $wherearr[] = $creditstr;

if(isset($_GET['avatar'])) {
	$wherearr[] = "avatar='".(empty($_GET['avatar']) ? 0 : intval($_GET['avatar']))."'";
}

if(isset($_GET['namestatus'])) {
	$wherearr[] = "namestatus='".(empty($_GET['namestatus']) ? 0 : intval($_GET['namestatus']))."'";
}

$order = !empty($_GET['order']) ? strtolower(trim($_GET['order'])) : 'dateline';
$sc = !empty($_GET['sc']) ? strtoupper(trim($_GET['sc'])) : 'DESC';

if(!in_array($order, array('dateline', 'updatetime', 'viewnum', 'friendnum', 'credit')))	$order = 'dateline';
if(!in_array($sc, array('DESC', 'ASC')))	$sc = 'DESC';

if($wherearr)	$sql = 'WHERE '.implode(' AND ', $wherearr);
$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('space')." $sql  ORDER BY $order $sc LIMIT $start,$limit");
while($value = $_SGLOBAL['db']->fetch_array($query)) {
	$value['dateline'] = sgmdate('m-d H:i', $value['dateline']);
	$value['updatetime'] = sgmdate('m-d H:i', $value['updatetime']);
	$value['userlink'] = $siteurl.'space.php?uid='.$value['uid'];	
	$value['photo'] = ckavatar($value['uid']) ? avatar($value['uid'], 'small',true) : UC_API.'/images/noavatar_small.gif';
	$value = sstripslashes($value);
	
	$spacelist[] = $value;
}

echo serialize($spacelist);

function getscopequery($var, $tarr, $isdate=0, $pre='') {
	global $_SGLOBAL;

	$wheresql = '';
	if(!empty($pre)) $pre = $pre.'.';
	if($tarr) {
		if($isdate) {
			$tarr = intval($tarr);
			if($tarr) $wheresql = $pre.$var.">='".($_SGLOBAL['timestamp']-$tarr)."'";
		} else {
			$tarr[0] = intval($tarr[0]);
			$tarr[1] = intval($tarr[1]);
			if($tarr[0] && $tarr[1] && $tarr[1] > $tarr[0]) {
				$wheresql = '('.$pre.$var.'>='.$tarr[0].' AND '.$pre.$var.'<='.$tarr[1].')';
			} else if($tarr[0] && empty($tarr[1])) {
				$wheresql = $pre.$var.'>='.$tarr[0];
			} else if(empty($tarr[0]) && $tarr[1]) {
				$wheresql = $pre.$var.'<='.$tarr[1];
			}
		}
	}
	return $wheresql;
}
?>