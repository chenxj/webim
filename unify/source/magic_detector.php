<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: function_image.php 7350 2008-05-12 09:36:04Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$magic['custom']['maxdetect'] = $magic['custom']['maxdetect'] ? intval($magic['custom']['maxdetect']) : 10;

//Ì½²âµÆ
if(submitcheck("usesubmit")) {

	magic_use($mid, array(), true);
	
	$op = 'show';
	$limit = $magic['custom']['maxdetect'] + 20;//¶àÈ¡20¸ö
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('magicuselog')." WHERE uid != '$_SGLOBAL[supe_uid]' AND mid = 'gift' LIMIT $limit");
	$list = array();
	$max = 1;
	while($value = $_SGLOBAL['db']->fetch_array($query)) {
		$value['data'] = unserialize($value['data']);
		if($value['data']['left'] && 
			(empty($value['data']['receiver']) || 
				!in_array($_SGLOBAL['supe_uid'], $value['data']['receiver']))) {
					
			realname_set($value['uid'], $value['username']);
			$list[] = $value;
			if(++$max > $magic['custom']['maxdetect'])	break;
		}
	}
	realname_get();
}

?>