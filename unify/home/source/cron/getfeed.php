<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: getfeed.php 12304 2009-06-03 07:29:34Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//从uc获取feed
if($_SCONFIG['uc_status']) {

	include_once(S_ROOT.'./uc_client/client.php');
	if($results = uc_feed_get(50)) {//每次取10个
	
		$cols = array('uid','username','appid','icon','dateline','hash_template','hash_data','title_template','title_data','body_template','body_data','body_general','image_1','image_1_link','image_2','image_2_link','image_3','image_3_link','image_4','image_4_link','target_ids');
		
		$inserts = array();
		foreach ($results as $value) {
			if(empty($value['uid']) || empty($value['username'])) continue;
			
			$vs = array();
			foreach ($cols as $key) {
				if(is_array($value[$key])) {
					//数组处理
					$value[$key] = addslashes(serialize(sstripslashes($value[$key])));
				} else {
					$value[$key] = addslashes(sstripslashes($value[$key]));
				}
				$vs[] = '\''.$value[$key].'\'';
			}
			$inserts[] = '('.implode(',', $vs).')';
		}
		
		//入库
		if($inserts) {
			$_SGLOBAL['db']->query("INSERT INTO ".tname('feed')." (`".implode('`,`', $cols)."`) VALUES ".implode(',', $inserts));
		}
	}
}

?>