<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: do_seccode.php 8531 2008-08-20 07:27:23Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//配置
$seccodedata = array (
	'width' => 100,
	'height' => 40,
	'adulterate' => '1',//随机背景图形
	'angle' => '0',//随机倾斜度
	'shadow' => '1',//阴影
);

//验证码
$seccode = mkseccode();

//设定cookie
ssetcookie('seccode', authcode($seccode, 'ENCODE'));

if(function_exists('imagecreate') && function_exists('imagecolorset') && function_exists('imagecopyresized') &&
	function_exists('imagecolorallocate') && function_exists('imagechar') && function_exists('imagecolorsforindex') &&
	function_exists('imageline') && function_exists('imagecreatefromstring') && (function_exists('imagegif') || function_exists('imagepng') || function_exists('imagejpeg'))) {

	$bgcontent = seccode_background();

	$im = imagecreatefromstring($bgcontent);
	if($seccodedata['adulterate']) {
		seccode_adulterate();
	}
	seccode_giffont();

	if(function_exists('imagepng')) {
		header('Content-type: image/png');
		imagepng($im);
	} else {
		header('Content-type: image/jpeg');
		imagejpeg($im, '', 100);
	}
	imagedestroy($im);
	
} else {
	
	$numbers = array
		(
		'B' => array('00','fc','66','66','66','7c','66','66','fc','00'),
		'C' => array('00','38','64','c0','c0','c0','c4','64','3c','00'),
		'E' => array('00','fe','62','62','68','78','6a','62','fe','00'),
		'F' => array('00','f8','60','60','68','78','6a','62','fe','00'),
		'G' => array('00','78','cc','cc','de','c0','c4','c4','7c','00'),
		'H' => array('00','e7','66','66','66','7e','66','66','e7','00'),
		'J' => array('00','f8','cc','cc','cc','0c','0c','0c','7f','00'),
		'K' => array('00','f3','66','66','7c','78','6c','66','f7','00'),
		'M' => array('00','f7','63','6b','6b','77','77','77','e3','00'),
		'P' => array('00','f8','60','60','7c','66','66','66','fc','00'),
		'Q' => array('00','78','cc','cc','cc','cc','cc','cc','78','00'),
		'R' => array('00','f3','66','6c','7c','66','66','66','fc','00'),
		'T' => array('00','78','30','30','30','30','b4','b4','fc','00'),
		'V' => array('00','1c','1c','36','36','36','63','63','f7','00'),
		'W' => array('00','36','36','36','77','7f','6b','63','f7','00'),
		'X' => array('00','f7','66','3c','18','18','3c','66','ef','00'),
		'Y' => array('00','7e','18','18','18','3c','24','66','ef','00'),
		'2' => array('fc','c0','60','30','18','0c','cc','cc','78','00'),
		'3' => array('78','8c','0c','0c','38','0c','0c','8c','78','00'),
		'4' => array('00','3e','0c','fe','4c','6c','2c','3c','1c','1c'),
		'6' => array('78','cc','cc','cc','ec','d8','c0','60','3c','00'),
		'7' => array('30','30','38','18','18','18','1c','8c','fc','00'),
		'8' => array('78','cc','cc','cc','78','cc','cc','cc','78','00'),
		'9' => array('f0','18','0c','6c','dc','cc','cc','cc','78','00')
		);

	foreach($numbers as $i => $number) {
		for($j = 0; $j < 6; $j++) {
			$a1 = substr('012', mt_rand(0, 2), 1).substr('012345', mt_rand(0, 5), 1);
			$a2 = substr('012345', mt_rand(0, 5), 1).substr('0123', mt_rand(0, 3), 1);
			mt_rand(0, 1) == 1 ? array_push($numbers[$i], $a1) : array_unshift($numbers[$i], $a1);
			mt_rand(0, 1) == 0 ? array_push($numbers[$i], $a1) : array_unshift($numbers[$i], $a2);
		}
	}

	$bitmap = array();
	for($i = 0; $i < 20; $i++) {
		for($j = 0; $j < 4; $j++) {
			$n = substr($seccode, $j, 1);
			$bytes = $numbers[$n][$i];
			$a = mt_rand(0, 14);
			array_push($bitmap, $bytes);
		}
	}

	for($i = 0; $i < 8; $i++) {
		$a = substr('012345', mt_rand(0, 2), 1) . substr('012345', mt_rand(0, 5), 1);
		array_unshift($bitmap, $a);
		array_push($bitmap, $a);
	}

	$image = pack('H*', '424d9e000000000000003e000000280000002000000018000000010001000000'.
			'0000600000000000000000000000000000000000000000000000FFFFFF00'.implode('', $bitmap));

	header('Content-Type: image/bmp');
	echo $image;
}

//生成随机
function mkseccode() {
	$seccode = random(6, 1);
	$s = sprintf('%04s', base_convert($seccode, 10, 24));
	$seccode = '';
	$seccodeunits = 'BCEFGHJKMPQRTVWXY2346789';
	for($i = 0; $i < 4; $i++) {
		$unit = ord($s{$i});
		$seccode .= ($unit >= 0x30 && $unit <= 0x39) ? $seccodeunits[$unit - 0x30] : $seccodeunits[$unit - 0x57];
	}
	return $seccode;
}

//背景
function seccode_background() {
	global $seccodedata, $c;
	
	$im = imagecreatetruecolor($seccodedata['width'], $seccodedata['height']);
	$backgroundcolor = imagecolorallocate($im, 255, 255, 255);
	
	for($i = 0;$i < 3;$i++) {
		$start[$i] = mt_rand(200, 255);
		$end[$i] = mt_rand(100, 245);
		$step[$i] = ($end[$i] - $start[$i]) / $seccodedata['width'];
		$c[$i] = $start[$i];
	}
	//$color = imagecolorallocate($im, 235, 235, 235);
	for($i = 0;$i < $seccodedata['width'];$i++) {
		$color = imagecolorallocate($im, $c[0], $c[1], $c[2]);
		imageline($im, $i, 0, $i-$angle, $seccodedata['height'], $color);
		$c[0] += $step[0];
		$c[1] += $step[1];
		$c[2] += $step[2];
	}
	$c[0] -= 20;
	$c[1] -= 20;
	$c[2] -= 20;

	obclean();
	if(function_exists('imagepng')) {
		imagepng($im);
	} else {
		imagejpeg($im, '', 100);
	}
	imagedestroy($im);
	$bgcontent = ob_get_contents();
	obclean();
	
	return $bgcontent;
}

function seccode_adulterate() {
	global $seccodedata, $im, $c;
	$linenums = $seccodedata['height'] / 10;
	for($i=0; $i <= $linenums; $i++) {
		$color = imagecolorallocate($im, $c[0], $c[1], $c[2]);
		$x = mt_rand(0, $seccodedata['width']);
		$y = mt_rand(0, $seccodedata['height']);
		if(mt_rand(0, 1)) {
			imagearc($im, $x, $y, mt_rand(0, $seccodedata['width']), mt_rand(0, $seccodedata['height']), mt_rand(0, 360), mt_rand(0, 360), $color);
		} else {
			imageline($im, $x, $y, $linex + mt_rand(0, 20), $liney + mt_rand(0, mt_rand($seccodedata['height'], $seccodedata['width'])), $color);
		}
	}
}

function seccode_giffont() {
	global $seccode, $seccodedata, $im, $c;
	$seccodedir = array();
	if(function_exists('imagecreatefromgif')) {
		$seccoderoot = 'image/seccode/';
		$dirs = opendir($seccoderoot);
		while($dir = readdir($dirs)) {
			if($dir != '.' && $dir != '..' && file_exists($seccoderoot.$dir.'/9.gif')) {
				$seccodedir[] = $dir;
			}
		}
	}
	$widthtotal = 0;
	for($i = 0; $i <= 3; $i++) {
		$imcodefile = $seccodedir ? $seccoderoot.$seccodedir[array_rand($seccodedir)].'/'.strtolower($seccode[$i]).'.gif' : '';
		if(!empty($imcodefile) && file_exists($imcodefile)) {
			$font[$i]['file'] = $imcodefile;
			$font[$i]['data'] = getimagesize($imcodefile);
			$font[$i]['width'] = $font[$i]['data'][0] + mt_rand(0, 6) - 4;
			$font[$i]['height'] = $font[$i]['data'][1] + mt_rand(0, 6) - 4;
			$font[$i]['width'] += mt_rand(0, $seccodedata['width'] / 5 - $font[$i]['width']);
			$widthtotal += $font[$i]['width'];
		} else {
			$font[$i]['file'] = '';
			$font[$i]['width'] = 8 + mt_rand(0, $seccodedata['width'] / 5 - 5);
			$widthtotal += $font[$i]['width'];
		}
	}
	$x = mt_rand(1, $seccodedata['width'] - $widthtotal);
	for($i = 0; $i <= 3; $i++) {
		if($font[$i]['file']) {
			$imcode = imagecreatefromgif($font[$i]['file']);
			$y = mt_rand(0, $seccodedata['height'] - $font[$i]['height']);
			if($seccodedata['shadow']) {
				$imcodeshadow = $imcode;
				imagecolorset($imcodeshadow, 0 , 255 - $c[0], 255 - $c[1], 255 - $c[2]);
				imagecopyresized($im, $imcodeshadow, $x + 1, $y + 1, 0, 0, $font[$i]['width'], $font[$i]['height'], $font[$i]['data'][0], $font[$i]['data'][1]);
			}
			imagecolorset($imcode, 0 , $c[0], $c[1], $c[2]);
			imagecopyresized($im, $imcode, $x, $y, 0, 0, $font[$i]['width'], $font[$i]['height'], $font[$i]['data'][0], $font[$i]['data'][1]);
		} else {
			$y = mt_rand(0, $seccodedata['height'] - 20);
			if($seccodedata['shadow']) {
				$text_shadowcolor = imagecolorallocate($im, 255 - $c[0], 255 - $c[1], 255 - $c[2]);
				imagechar($im, 5, $x + 1, $y + 1, $seccode[$i], $text_shadowcolor);
			}
			$text_color = imagecolorallocate($im, $c[0], $c[1], $c[2]);
			imagechar($im, 5, $x, $y, $seccode[$i], $text_color);
		}
		$x += $font[$i]['width'];
	}
}

?>