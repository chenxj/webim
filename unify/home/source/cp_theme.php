<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_theme.php 12880 2009-07-24 07:20:24Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$op = empty($_GET['op'])?'':$_GET['op'];
$dir = empty($_GET['dir'])?'':preg_replace("/[^0-9a-z]/i", '', $_GET['dir']);
$allowcss = checkperm('allowcss');

if(submitcheck('csssubmit')) {
	
	checksecurity($_POST['css']);
	
	$css = $allowcss?getstr($_POST['css'], 5000, 1, 1):'';
	$nocss = empty($_POST['nocss'])?0:1;
	updatetable('spacefield', array('theme'=>'', 'css'=>$css, 'nocss'=>$nocss), array('uid'=>$_SGLOBAL['supe_uid']));
	
	showmessage('do_success', 'cp.php?ac=theme&op=diy&view=ok', 0);

} elseif (submitcheck('timeoffsetsubmit')) {
	
	updatetable('spacefield', array('timeoffset'=>$_POST['timeoffset']), array('uid'=>$_SGLOBAL['supe_uid']));
	showmessage('do_success', 'cp.php?ac=theme');
}

//确定文件是否存在
if($dir && $dir != 'uchomedefault') {
	$cssfile = S_ROOT.'./theme/'.$dir.'/style.css';
	if(!file_exists($cssfile)) {
		showmessage('theme_does_not_exist');
	}
}

if ($op == 'use') {
	//启用
	if($dir == 'uchomedefault') {
		$setarr = array('theme'=>'', 'css'=>'');
	} else {
		$setarr = array('theme'=>$dir, 'css'=>'');
	}
	updatetable('spacefield', $setarr, array('uid'=>$_SGLOBAL['supe_uid']));
	showmessage('do_success', 'space.php', 0);
	
} elseif ($op == 'diy') {
	//自定义
} else {
	
	//模板列表
	$themes = array(
		array('dir'=>'uchomedefault', 'name'=>cplang('the_default_style'), 'pic'=>'image/theme_default.jpg')
	);
	$themes[] = array('dir'=>'uchomediy', 'name'=>cplang('the_diy_style'), 'pic'=>'image/theme_diy.jpg');

	//获取本地风格目录
	$themedirs = sreaddir(S_ROOT.'./theme');
	foreach ($themedirs as $key => $dirname) {
		//样式文件和图片需存在
		$now_dir = S_ROOT.'./theme/'.$dirname;
		if(file_exists($now_dir.'/style.css') && file_exists($now_dir.'/preview.jpg')) {
			$themes[] = array(
				'dir' => $dirname,
				'name' => getcssname($dirname)
			);
		}
	}
	
	//时区
	$toselect = array($space['timeoffset'] => ' selected');
}

$actives = array('theme'=>' class="active"');

include_once template("cp_theme");

//获取系统风格名
function getcssname($dirname) {
	$css = sreadfile(S_ROOT.'./theme/'.$dirname.'/style.css');
	if($css) {
		preg_match("/\[name\](.+?)\[\/name\]/i", $css, $mathes);
		if(!empty($mathes[1])) $name = shtmlspecialchars($mathes[1]);
	} else {
		$name = 'No name';
	}
	return $name;
}

function checksecurity($str) {
	
	//执行一系列的过滤验证是否合法的CSS
	$filter = array(
		'/\/\*[\n\r]*(.+?)[\n\r]*\*\//is',
		'/[^a-z0-9]+/i',
	);
	$str = preg_replace($filter, '', $str);
	if(preg_match("/(expression|implode|javascript)/i", $str)) {
		showmessage('css_contains_elements_of_insecurity');
	}
	return true;
}
?>