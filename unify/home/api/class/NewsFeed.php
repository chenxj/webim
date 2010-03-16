<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: NewsFeed.php 7952 2008-07-04 07:14:25Z zhouguoqiang $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

class NewsFeed extends MyBase {
	
	function get($uId, $num) {
		global $_SGLOBAL;
		$result = array();
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('feed')." WHERE uid='$uId' ORDER BY dateline DESC LIMIT 0,$num");
		while($value = $_SGLOBAL['db']->fetch_array($query)) {
			$result[] = array(
				'appId' => $value['appid'],
				'created' => $value['dateline'],
				'type' => $value['icon'],
				'titleTemplate' => $value['title_template'],
				'titleData' => $value['title_data'],
				'bodyTemplate' => $value['body_template'],
				'bodyData' => $value['body_data'],
				'bodyGeneral' => $value['body_general'],
				'image1' => $value['image_1'],
				'image1Link' => $value['image_1_link'],
				'image2' => $value['image_2'],
				'image2Link' => $value['image_2_link'],
				'image3' => $value['image_3'],
				'image3Link' => $value['image_3_link'],
				'image4' => $value['image_4'],
				'image4Link' => $value['image_4_link'],
				'targetIds' => $value['target_ids'],
				'privacy' => $value['friend']==0?'public':($value['friend']==1?'friends':'someFriends')
			);
		}
		return new APIResponse($result);
	}

}

?>
