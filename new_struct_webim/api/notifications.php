<?php 
$platform = $_GET['platform'];
$configRoot = $_IMC["install_path"] . 'webim/api/';
switch($platform){
	case 'discuz':
		include_once($configRoot . 'discuz.php');
		break;
	case 'uchome':
		include_once($configRoot . 'uchome.php');
		break;
}

include_once S_ROOT.'./uc_client/client.php';
$page = max($page, 1);
$action = !empty($action) ? $action : (isset($uid) || !empty($pmid) ? 'view' : '');
$ppp=10;
$pmstatus = uc_pm_checknew($_SGLOBAL['supe_uid'], 4);
	$systemnewpm = $pmstatus['newpm'] - $pmstatus['newprivatepm'];
	$filter =  'newpm';
	
	$ucdata = uc_pm_list($_SGLOBAL['supe_uid'], $page, $ppp, !isset($search) ? 'inbox' : 'searchbox', !isset($search) ? $filter : $srchtxt, 200);
	if(!empty($search) && $srchtxt !== '') {
		$filter = '';
		$srchtxtinput = htmlspecialchars(stripslashes($srchtxt));
		$srchtxtenc = rawurlencode($srchtxt);
	} else {
		$multipage = multi($ucdata['count'], $ppp, $page, 'pm.php?filter='.$filter);
	}
	$_COOKIE['checkpm'] && setcookie('checkpm', '', -86400 * 365);

	$pmlist = array();
	$today = $timestamp - ($timestamp + $timeoffset * 3600) % 86400;
	foreach($ucdata['data'] as $pm) {
		$pm['msgfromurl'] = $pm['fromappid'] && $ucapp[$pm['fromappid']]['viewprourl'] ? sprintf($ucapp[$pm['fromappid']]['viewprourl'], $pm['msgfromid']) : 'space.php?uid='.$pm['msgfromid'];
		$pm['daterange'] = 5;
		if($pm['dateline'] >= $today) {
			$pm['daterange'] = 1;
		} elseif($pm['dateline'] >= $today - 86400) {
			$pm['daterange'] = 2;
		} elseif($pm['dateline'] >= $today - 172800) {
			$pm['daterange'] = 3;
		}
		$pm['date'] = gmdate($dateformat, $pm['dateline'] + $timeoffset * 3600);
		$pm['time'] = gmdate($timeformat, $pm['dateline'] + $timeoffset * 3600);
		
		//////
		if ($pm['msgfromid'] > 0){
				$from=to_utf8($pm['msgfrom']);$text=to_utf8($pm['subject']);$link= 'space.php?do=pm&filter=newpm&uid='.$pm['touid'].'&filter=newpm&daterange='.$pm['daterange'];
			}else{
				$from='';$text=to_utf8($pm['subject']);$link= 'space.php?do=pm&filter=newpm?pmid='.$pm['pmid'].'&filter=systempm';
			}
			if($text)
			$pmlist[]= array('from'=>$from,'text'=>$text,'link'=>$link,'time'=>$pm['time']);
		/////
	}

//get notice
$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('myinvite')." WHERE touid='$_SGLOBAL[supe_uid]' $typesql ORDER BY dateline DESC");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$key = md5($value['typename'].$value['type']);
		
		$list[$key][] = $value;
		$count++;
		$appidarr[] = $value['appid'];
	}
	if($list){
	foreach($list as $invite){
		$from='';$text='您有'.count($invite).'个 '.$invite[0]['typename'].($invite[0]['type']?'请求':'邀请');
		$link= 'space.php?do=notice&view=userapp';
		if($text)
		$pmlist[]= array('from'=>$from,'text'=>$text,'link'=>$link,'time'=>'');
	}
	}
	
	
	die(json_encode($pmlist));
?>
