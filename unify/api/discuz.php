<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: discuz.php 13204 2009-08-20 02:27:38Z zhengqingpeng $
*/

include_once('../common.php');

$ac = isset($_GET['ac']) ? trim($_GET['ac']) : '';

$uri = $_SERVER['REQUEST_URI']?$_SERVER['REQUEST_URI']:($_SERVER['PHP_SELF']?$_SERVER['PHP_SELF']:$_SERVER['SCRIPT_NAME']);
$siteurl = 'http://'.$_SERVER['HTTP_HOST'].substr($uri, 0, strrpos($uri, '/')-3);

if(!in_array($ac, array('doing', 'album', 'space', 'feed'))) {
	$ac = 'js';
}

if(!empty($_SCONFIG['uc_dir']) && !strexists($_SCONFIG['uc_dir'], ':/') && !strexists($_SCONFIG['uc_dir'], ':\\') && substr($_SCONFIG['uc_dir'], 0, 1) != '/') {
	$_SCONFIG['uc_dir'] = '../'.$_SCONFIG['uc_dir'];
}
include_once(S_ROOT.'./source/function_cp.php');
include_once(S_ROOT.'./api/discuz/'.$ac.'.php');

function makeurl($str) {
	global $siteurl;
	
	$str = stripslashes(preg_replace("/(\r\n|\n|\r)/", '', $str));
	$str = preg_replace("/src\=\"(?!http\:\/\/)(.+?)\"/i", ' src="'.$siteurl.'\\1"', $str);
	return addslashes(preg_replace("/href\=\"(?!http\:\/\/)(.+?)\"/i", ' target="_blank" href="'.$siteurl.'\\1"', $str));
}

function getdotstring ($string, $vartype, $allownull=false, $varscope=array(), $sqlmode=1, $unique=true) {

	if(is_array($string)) {
		$stringarr = $string;
	} else {
		if(substr($string, 0, 1) == '$') {
			return $string;
		}
		$string = str_replace('，', ',', $string);
		$string = str_replace(' ', ',', $string);
		$stringarr = explode(',', $string);
	}

	$newarr = array();
	foreach ($stringarr as $value) {
		$value = trim($value);
		if($vartype == 'int') {
			$value = intval($value);
		}
		if(!empty($varscope)) {
			if(in_array($value, $varscope)) {
				$newarr[] = $value;
			}
		} else {
			if($allownull) {
				$newarr[] = $value;
			} else {
				if(!empty($value)) $newarr[] = $value;
			}
		}
	}

	if($unique) $newarr = sarray_unique($newarr);
	if($vartype == 'int') {
		$string = implode(',', $newarr);
	} else {
		if($sqlmode) {
			$string = '\''.implode('\',\'', $newarr).'\'';
		} else {
			$string = implode(',', $newarr);
		}
	}
	return $string;
}

//将数组中相同的值去掉,同时将后面的键名也忽略掉
function sarray_unique($array) {
	$newarray = array();
	if(!empty($array) && is_array($array)) {
		$array = array_unique($array);
		foreach ($array as $value) {
			$newarray[] = $value;
		}
	}
	return $newarray;
}
?>
