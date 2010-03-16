<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: admincp_ad.php 11954 2009-04-17 09:29:53Z liguode $
*/

if(!defined('IN_UCHOME') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('managead')) {
	cpmessage('no_authority_management_operation');
}

if(submitcheck('adsubmit')) {

	$adid = intval($_POST['adid']);
	$_POST['title'] = getstr($_POST['title'], 50, 1, 1);
	if(empty($_POST['title'])) {
		$_POST['title'] = 'AD'.sgmdate('ndHis');
	}
	$_POST['system'] = intval($_POST['system']);

	//模板
	$html = '';
	$adcodes = array();
	switch($_POST['adcode']['type']) {
		case 'html':
			$adcodes['html'] = $_POST['adcode']['html'];
			$html = stripslashes($_POST['adcode']['html']);
			break;
		case 'flash':
			$adcodes['flashheight'] = floatval($_POST['adcode']['flashheight']);
			$adcodes['flashwidth'] = floatval($_POST['adcode']['flashwidth']);
			$adcodes['flashurl'] = $_POST['adcode']['flashurl'];

			$width = empty($adcodes['flashwidth'])?'':'width="'.$adcodes['flashwidth'].'"';
			$height = empty($adcodes['flashheight'])?'':'height="'.$adcodes['flashheight'].'"';
			$html  = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" adcodebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,45,0" '.$width.' '.$height.'>'."\n";
			$html .= '<param name="movie" value="'.stripslashes($_POST['adcode']['flashurl']).'" />'."\n";
			$html .= '<param name="quality" value="high" />'."\n";
			$html .= '<embed src="'.stripslashes($_POST['adcode']['flashurl']).'" quality="high" pluginspage="http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash" type="application/x-shockwave-flash" '.$width.' '.$height.'></embed>'."\n";
			$html .= '</object>'."\n";
			break;
		case 'image':
			$width = empty($adcodes['imagewidth'])?'':'width="'.$adcodes['imagewidth'].'"';
			$height = empty($adcodes['imageheight'])?'':'height="'.$adcodes['imageheight'].'"';
			$adcodes['imageheight'] = floatval($_POST['adcode']['imageheight']);
			$adcodes['imagewidth'] = floatval($_POST['adcode']['imagewidth']);
			$adcodes['imagesrc'] = $_POST['adcode']['imagesrc'];
			$adcodes['imageurl'] = $_POST['adcode']['imageurl'];
			$adcodes['imagealt'] = getstr($_POST['adcode']['imagealt'], 200, 1, 1);
			$width = empty($adcodes['imagewidth'])?'':'width="'.$adcodes['imagewidth'].'"';
			$height = empty($adcodes['imageheight'])?'':'height="'.$adcodes['imageheight'].'"';
			$html  = '<a href="'.$adcodes['imageurl'].'" target="_blank"><img src="'.stripslashes($_POST['adcode']['imagesrc']).'" '.$width.' '.$height.' border="0" alt="'.$adcodes['imagealt'].'"></a>';
			break;
		case 'text':
			$adcodes['textcontent'] = getstr($_POST['adcode']['textcontent'], 0, 1, 1);
			$adcodes['texturl'] = $_POST['adcode']['texturl'];
			$adcodes['textsize'] = floatval($_POST['adcode']['textsize']);
			$size = empty($adcodes['textsize'])?'':'style="font-size:'.$adcodes['textsize'].'px;"';
			$html  = '<span style="padding:0.8em"><a href="'.stripslashes($_POST['adcode']['texturl']).'" target="_blank" '.$size.'>'.$adcodes['textcontent'].'</a></span>';
			break;
		default:
			break;
	}

	if(empty($adcodes)) {
		cpmessage('please_check_whether_the_option_complete_required');
	} else {
		$adcodes['type'] = $_POST['adcode']['type'];
	}

	$setarr = array(
		'title' => $_POST['title'],
		'pagetype' => $_POST['pagetype'],
		'adcode' => addslashes(serialize(sstripslashes($adcodes))),
		'system' => $_POST['system'],
		'available' => empty($_POST['system'])?1:intval($_POST['available'])
	);

	if(empty($adid)) {
		$adid = inserttable('ad', $setarr, 1);
	} else {
		updatetable('ad', $setarr, array('adid' => $adid));
	}

	//写入模板
	$tpl = S_ROOT.'./data/adtpl/'.$adid.'.htm';
	swritefile($tpl, $html);

	//缓存更新
	include_once(S_ROOT.'./source/function_cache.php');
	ad_cache();

	cpmessage('do_success', 'admincp.php?ac=ad');

} elseif(submitcheck('delsubmit')) {

	include_once(S_ROOT.'./source/function_delete.php');
	if(!empty($_POST['adids']) && deleteads($_POST['adids'])) {

		//缓存更新
		include_once(S_ROOT.'./source/function_cache.php');
		ad_cache();

		cpmessage('do_success', 'admincp.php?ac=ad');
	} else {
		cpmessage('please_choose_to_remove_advertisements', 'admincp.php?ac=ad');
	}

}

if(empty($_GET['op'])) {

	$sql = '';
	if($_GET['pagetype']) {
		$sql = " WHERE pagetype='$_GET[pagetype]'";
	}
	$listvalue = array();
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('ad').$sql." ORDER BY adid DESC");
	while($ad = $_SGLOBAL['db']->fetch_array($query)) {
		$ad['adcode'] = unserialize($ad['adcode']);
		$listvalue[$ad['system']][] = $ad;
	}
	
	$actives = array('view' => ' class="active"');

} elseif ($_GET['op'] == 'add' || $_GET['op'] == 'edit') {

	$_GET['adid'] = empty($_GET['adid'])?0:intval($_GET['adid']);

	$advalue = array();
	if($_GET['adid']) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('ad')." WHERE adid='$_GET[adid]'");
		$advalue = $_SGLOBAL['db']->fetch_array($query);
	}
	if(empty($advalue)) {
		//默认数据
		$advalue = array('adid'=>0, 'system'=>1, 'pagetype'=>'leftside', 'available'=>1, 'adcode'=>array('type'=>'html'));
	} else {
		$advalue['adcode'] = unserialize($advalue['adcode']);
	}

	//显示处理
	$systems = array($advalue['system'] => ' checked');
	$pagetypes = array($advalue['pagetype'] => ' selected');
	$availables = array($advalue['available'] => ' checked');
	$adcodes = array($advalue['adcode']['type'] => ' selected');

} elseif ($_GET['op'] == 'tpl') {

	$adcode = shtmlspecialchars("<!--{template data/adtpl/$_GET[adid]}-->");

} elseif ($_GET['op'] == 'js') {

	$adcode = shtmlspecialchars("<script type=\"text/javascript\" src=\"".getsiteurl()."js.php?adid=$_GET[adid]\"></script>");

}

?>