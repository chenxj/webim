<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: function_template.php 12678 2009-07-15 03:21:21Z xupeng $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$_SGLOBAL['i'] = 0;
$_SGLOBAL['block_search'] = $_SGLOBAL['block_replace'] = array();

function parse_template($tpl) {
	global $_SGLOBAL, $_SC, $_SCONFIG;

	//包含模板
	$_SGLOBAL['sub_tpls'] = array($tpl);

	$tplfile = S_ROOT.'./'.$tpl.'.htm';
	$objfile = S_ROOT.'./data/tpl_cache/'.str_replace('/','_',$tpl).'.php';
	
	//read
	if(!file_exists($tplfile)) {
		$tplfile = str_replace('/'.$_SCONFIG['template'].'/', '/default/', $tplfile);
	}
	$template = sreadfile($tplfile);
	if(empty($template)) {
		exit("Template file : $tplfile Not found or have no access!");
	}

	//模板
	$template = preg_replace("/\<\!\-\-\{template\s+([a-z0-9_\/]+)\}\-\-\>/ie", "readtemplate('\\1')", $template);
	//处理子页面中的代码
	$template = preg_replace("/\<\!\-\-\{template\s+([a-z0-9_\/]+)\}\-\-\>/ie", "readtemplate('\\1')", $template);
	//解析模块调用
	$template = preg_replace("/\<\!\-\-\{block\/(.+?)\}\-\-\>/ie", "blocktags('\\1')", $template);
	//解析广告
	$template = preg_replace("/\<\!\-\-\{ad\/(.+?)\}\-\-\>/ie", "adtags('\\1')", $template);
	//时间处理
	$template = preg_replace("/\<\!\-\-\{date\((.+?)\)\}\-\-\>/ie", "datetags('\\1')", $template);
	//头像处理
	$template = preg_replace("/\<\!\-\-\{avatar\((.+?)\)\}\-\-\>/ie", "avatartags('\\1')", $template);
	//PHP代码
	$template = preg_replace("/\<\!\-\-\{eval\s+(.+?)\s*\}\-\-\>/ies", "evaltags('\\1')", $template);

	//开始处理
	//变量
	$var_regexp = "((\\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)(\[[a-zA-Z0-9_\-\.\"\'\[\]\$\x7f-\xff]+\])*)";
	$template = preg_replace("/\<\!\-\-\{(.+?)\}\-\-\>/s", "{\\1}", $template);
	$template = preg_replace("/([\n\r]+)\t+/s", "\\1", $template);
	$template = preg_replace("/(\\\$[a-zA-Z0-9_\[\]\'\"\$\x7f-\xff]+)\.([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)/s", "\\1['\\2']", $template);
	$template = preg_replace("/\{(\\\$[a-zA-Z0-9_\[\]\'\"\$\.\x7f-\xff]+)\}/s", "<?=\\1?>", $template);
	$template = preg_replace("/$var_regexp/es", "addquote('<?=\\1?>')", $template);
	$template = preg_replace("/\<\?\=\<\?\=$var_regexp\?\>\?\>/es", "addquote('<?=\\1?>')", $template);
	//逻辑
	$template = preg_replace("/\{elseif\s+(.+?)\}/ies", "stripvtags('<?php } elseif(\\1) { ?>','')", $template);
	$template = preg_replace("/\{else\}/is", "<?php } else { ?>", $template);
	//循环
	for($i = 0; $i < 6; $i++) {
		$template = preg_replace("/\{loop\s+(\S+)\s+(\S+)\}(.+?)\{\/loop\}/ies", "stripvtags('<?php if(is_array(\\1)) { foreach(\\1 as \\2) { ?>','\\3<?php } } ?>')", $template);
		$template = preg_replace("/\{loop\s+(\S+)\s+(\S+)\s+(\S+)\}(.+?)\{\/loop\}/ies", "stripvtags('<?php if(is_array(\\1)) { foreach(\\1 as \\2 => \\3) { ?>','\\4<?php } } ?>')", $template);
		$template = preg_replace("/\{if\s+(.+?)\}(.+?)\{\/if\}/ies", "stripvtags('<?php if(\\1) { ?>','\\2<?php } ?>')", $template);
	}
	//常量
	$template = preg_replace("/\{([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\}/s", "<?=\\1?>", $template);
	
	//替换
	if(!empty($_SGLOBAL['block_search'])) {
		$template = str_replace($_SGLOBAL['block_search'], $_SGLOBAL['block_replace'], $template);
	}
	
	//换行
	$template = preg_replace("/ \?\>[\n\r]*\<\? /s", " ", $template);
	
	//附加处理
	$template = "<?php if(!defined('IN_UCHOME')) exit('Access Denied');?><?php subtplcheck('".implode('|', $_SGLOBAL['sub_tpls'])."', '$_SGLOBAL[timestamp]', '$tpl');?>$template<?php ob_out();?>";
	
	//write
	if(!swritefile($objfile, $template)) {
		exit("File: $objfile can not be write!");
	}
}

function addquote($var) {
	return str_replace("\\\"", "\"", preg_replace("/\[([a-zA-Z0-9_\-\.\x7f-\xff]+)\]/s", "['\\1']", $var));
}

function striptagquotes($expr) {
	$expr = preg_replace("/\<\?\=(\\\$.+?)\?\>/s", "\\1", $expr);
	$expr = str_replace("\\\"", "\"", preg_replace("/\[\'([a-zA-Z0-9_\-\.\x7f-\xff]+)\'\]/s", "[\\1]", $expr));
	return $expr;
}

function evaltags($php) {
	global $_SGLOBAL;

	$_SGLOBAL['i']++;
	$search = "<!--EVAL_TAG_{$_SGLOBAL['i']}-->";
	$_SGLOBAL['block_search'][$_SGLOBAL['i']] = $search;
	$_SGLOBAL['block_replace'][$_SGLOBAL['i']] = "<?php ".stripvtags($php)." ?>";
	
	return $search;
}

function blocktags($parameter) {
	global $_SGLOBAL;

	$_SGLOBAL['i']++;
	$search = "<!--BLOCK_TAG_{$_SGLOBAL['i']}-->";
	$_SGLOBAL['block_search'][$_SGLOBAL['i']] = $search;
	$_SGLOBAL['block_replace'][$_SGLOBAL['i']] = "<?php block(\"$parameter\"); ?>";
	return $search;
}

function adtags($pagetype) {
	global $_SGLOBAL;

	$_SGLOBAL['i']++;
	$search = "<!--AD_TAG_{$_SGLOBAL['i']}-->";
	$_SGLOBAL['block_search'][$_SGLOBAL['i']] = $search;
	$_SGLOBAL['block_replace'][$_SGLOBAL['i']] = "<?php adshow('$pagetype'); ?>";
	return $search;
}

function datetags($parameter) {
	global $_SGLOBAL;

	$_SGLOBAL['i']++;
	$search = "<!--DATE_TAG_{$_SGLOBAL['i']}-->";
	$_SGLOBAL['block_search'][$_SGLOBAL['i']] = $search;
	$_SGLOBAL['block_replace'][$_SGLOBAL['i']] = "<?php echo sgmdate($parameter); ?>";
	return $search;
}

function avatartags($parameter) {
	global $_SGLOBAL;

	$_SGLOBAL['i']++;
	$search = "<!--AVATAR_TAG_{$_SGLOBAL['i']}-->";
	$_SGLOBAL['block_search'][$_SGLOBAL['i']] = $search;
	$_SGLOBAL['block_replace'][$_SGLOBAL['i']] = "<?php echo avatar($parameter); ?>";
	return $search;
}

function stripvtags($expr, $statement='') {
	$expr = str_replace("\\\"", "\"", preg_replace("/\<\?\=(\\\$.+?)\?\>/s", "\\1", $expr));
	$statement = str_replace("\\\"", "\"", $statement);
	return $expr.$statement;
}

function readtemplate($name) {
	global $_SGLOBAL, $_SCONFIG;
	
	$tpl = strexists($name,'/')?$name:"template/$_SCONFIG[template]/$name";
	$tplfile = S_ROOT.'./'.$tpl.'.htm';
	
	$_SGLOBAL['sub_tpls'][] = $tpl;
	
	if(!file_exists($tplfile)) {
		$tplfile = str_replace('/'.$_SCONFIG['template'].'/', '/default/', $tplfile);
	}
	$content = sreadfile($tplfile);
	return $content;
}

?>