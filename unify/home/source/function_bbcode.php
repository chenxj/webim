<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: function_bbcode.php 13104 2009-08-11 06:19:32Z xupeng $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//处理模块
function bbcode($message, $parseurl=0) {
	global $_SGLOBAL;
	
	if(empty($_SGLOBAL['search_exp'])) {
		$_SGLOBAL['search_exp'] = array(
			"/\s*\[quote\][\n\r]*(.+?)[\n\r]*\[\/quote\]\s*/is",
			"/\[url\]\s*(https?:\/\/|ftp:\/\/|gopher:\/\/|news:\/\/|telnet:\/\/|rtsp:\/\/|mms:\/\/|callto:\/\/|ed2k:\/\/){1}([^\[\"']+?)\s*\[\/url\]/i",
			"/\[em:(.+?):\]/i",
		);
		$_SGLOBAL['replace_exp'] = array(
			"<div class=\"quote\"><span class=\"q\">\\1</span></div>",
			"<a href=\"\\1\\2\" target=\"_blank\">\\1\\2</a>",
			"<img src=\"image/face/\\1.gif\" class=\"face\">"
		);
		$_SGLOBAL['search_str'] = array('[b]', '[/b]','[i]', '[/i]', '[u]', '[/u]');
		$_SGLOBAL['replace_str'] = array('<b>', '</b>', '<i>','</i>', '<u>', '</u>');
	}
	
	if($parseurl==2) {//深度解析
		$_SGLOBAL['search_exp'][] = "/\[img\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/ies";
		$_SGLOBAL['replace_exp'][] = 'bb_img(\'\\1\')';
		$message = parseurl($message);
	}
	@$message = str_replace($_SGLOBAL['search_str'], $_SGLOBAL['replace_str'],preg_replace($_SGLOBAL['search_exp'], $_SGLOBAL['replace_exp'], $message, 20));
	return nl2br(str_replace(array("\t", '   ', '  '), array('&nbsp; &nbsp; &nbsp; &nbsp; ', '&nbsp; &nbsp;', '&nbsp;&nbsp;'), $message));
}

//自动解析url
function parseurl($message) {
	return preg_replace("/(?<=[^\]a-z0-9-=\"'\\/])((https?|ftp|gopher|news|telnet|mms|rtsp):\/\/)([a-z0-9\/\-_+=.~!%@?#%&;:$\\()|]+)/i", "[url]\\1\\3[/url]", ' '.$message);
}

//html转化为bbcode
function html2bbcode($message) {
	global $_SGLOBAL;
	
	if(empty($_SGLOBAL['html_s_exp'])) {
		$_SGLOBAL['html_s_exp'] = array(
			"/\<div class=\"quote\"\>\<span class=\"q\"\>(.*?)\<\/span\>\<\/div\>/is",
			"/\<a href=\"(.+?)\".*?\<\/a\>/is",
			"/(\r\n|\n|\r)/",
			"/<br.*>/siU",
			"/[ \t]*\<img src=\"image\/face\/(.+?).gif\".*?\>[ \t]*/is",
			"/\s*\<img src=\"(.+?)\".*?\>\s*/is"
		);
		$_SGLOBAL['html_r_exp'] = array(
			"[quote]\\1[/quote]",
			"\\1",
			'',
			"\n",
			"[em:\\1:]",
			"\n[img]\\1[/img]\n"
		);
		$_SGLOBAL['html_s_str'] = array('<b>', '</b>', '<i>','</i>', '<u>', '</u>', '&nbsp; &nbsp; &nbsp; &nbsp; ', '&nbsp; &nbsp;', '&nbsp;&nbsp;', '&lt;', '&gt;', '&amp;');
		$_SGLOBAL['html_r_str'] = array('[b]', '[/b]','[i]', '[/i]', '[u]', '[/u]', "\t", '   ', '  ', '<', '>', '&');
	}	
	
	@$message = str_replace($_SGLOBAL['html_s_str'], $_SGLOBAL['html_r_str'],
		preg_replace($_SGLOBAL['html_s_exp'], $_SGLOBAL['html_r_exp'], $message));
		
	$message = shtmlspecialchars($message);
	
	return trim($message);
}

function bb_img($url) {
	$url = addslashes($url);
	return "<img src=\"$url\">";
}

?>