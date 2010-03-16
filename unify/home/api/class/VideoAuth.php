<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: VideoAuth.php 12398 2009-06-24 08:26:38Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

class VideoAuth extends MyBase {

	function setAuthStatus($uId, $status) {
		global $_SGLOBAL;

		if ($status == 'approved') {
			$status = 1;
			//奖励积分
			getreward('videophoto', 1, $uId, '', 0);
		} else if($status == 'refused') {
			$status = 0;
		} else {
			$errCode = '200';
			$errMessage = 'Error arguments';
			return new APIErrorResponse($errCode, $errMessage);
		}

		updatetable('space', array('videostatus' => $status), array('uid' => $uId));

		$result = $_SGLOBAL['db']->affected_rows();
		return new APIResponse($result);
	}

	function auth($uId, $picData, $picExt = 'jpg', $isReward = false) {
		global $_SGLOBAL;

		$pic = base64_decode($picData);
		if (!$pic || strlen($pic) == strlen($picData)) {
			$errCode = '200';
			$errMessage = 'Error argument';
			return new APIErrorResponse($errCode, $errMessage);
		}

		$secret = md5($_SGLOBAL['timestamp']."\t".$_SGLOBAL['supe_uid']);
		$picDir = S_ROOT . './data/avatar/' . substr($secret, 0, 1);
		if (!is_dir($picDir)) {
			if (!mkdir($picDir, 0777)) {
				$errCode = '300';
				$errMessage = 'Cannot create directory';
				return new APIErrorResponse($errCode, $errMessage);
			}
		}

		$picDir .= '/' . substr($secret, 1, 1);
		if (!is_dir($picDir)) {
			if (!@mkdir($picDir, 0777)) {
				$errCode = '300';
				$errMessage = 'Cannot create directory';
				return new APIErrorResponse($errCode, $errMessage);
			}
		}

		$picPath = $picDir . '/' . $secret . '.' . $picExt;
		$fp = @fopen($picPath, 'wb');
		if ($fp) {
			if (fwrite($fp, $pic) !== FALSE) {
				fclose($fp);
				
				//主表
				updatetable('space', array('videostatus'=>1), array('uid' => $uId));
				//附表
				$fields = array('videopic' => $secret);
				updatetable('spacefield', $fields, array('uid' => $uId));
				$result = $_SGLOBAL['db']->affected_rows();

				if ($isReward) {
					//奖励积分
					getreward('videophoto', 1, $uId, '', 0);
				}
				return new APIResponse($result);
			}
		}

		$errCode = '300';
		$errMessage = 'Video Auth Error';
		return new APIErrorResponse($errCode, $errMessage);
	}
}
?>
