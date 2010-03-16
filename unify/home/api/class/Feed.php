<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: Feed.php 12545 2009-07-07 07:43:29Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

class Feed extends MyBase {

	function publishTemplatizedAction($uId, $appId, $titleTemplate, $titleData, $bodyTemplate, $bodyData, $bodyGeneral = '', $image1 = '', $image1Link = '', $image2 = '', $image2Link = '', $image3 = '', $image3Link = '', $image4 = '', $image4Link = '', $targetIds = '', $privacy = '', $hashTemplate = '', $hashData = '', $specialAppid=0) {
		global $_SGLOBAL;

		$friend = ($privacy == 'public') ? 0 : ($privacy == 'friends' ? 1 : 2);
		
		$images = array($image1, $image2, $image3, $image4);
		$image_links = array($image1Link, $image2Link, $image3Link, $image4Link);
		include_once(S_ROOT.'./source/function_cp.php');
		$result = feed_add($appId, $titleTemplate, $titleData, $bodyTemplate, $bodyData, $bodyGeneral, $images, $image_links, $targetIds, $friend, $specialAppid, 1);
		
		return new APIResponse($result);
	}
}

?>
