<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: function_image.php 7350 2008-05-12 09:36:04Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//АЫидОЕ
if(submitcheck("usesubmit")) {
	
	$idtype = 'uid';
	magic_use($mid, array('id'=>$id, 'idtype'=>$idtype), true);
	
	$op = 'show';
	$list = array();
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('magicuselog')." WHERE uid='$id' ORDER BY dateline DESC LIMIT 10");
	while($value = $_SGLOBAL['db']->fetch_array($query)) {
		$list[] = $value;
	}
}

?>