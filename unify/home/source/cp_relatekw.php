<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_relatekw.php 9375 2008-11-03 07:53:50Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//强制使用字符集
if(!$_SCONFIG['headercharset']) {
	@header('Content-Type: text/html; charset='.$_SC['charset']);
}
$_SGLOBAL['inajax'] = 1;

$subjectenc = rawurlencode(strip_tags($_GET['subjectenc']));
$messageenc = rawurlencode(strip_tags(preg_replace("/\[.+?\]/U", '', $_GET['messageenc'])));

$data = @implode('', file("http://keyword.discuz.com/related_kw.html?title=$subjectenc&content=$messageenc&ics=$_SC[charset]&ocs=$_SC[charset]"));

if($data) {
	$parser = xml_parser_create();
	xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
	xml_parse_into_struct($parser, $data, $values, $index);
	xml_parser_free($parser);

	$kws = array();

	foreach($values as $valuearray) {
		if($valuearray['tag'] == 'kw' || $valuearray['tag'] == 'ekw') {
			if(PHP_VERSION > '5' && $_SC['charset'] != 'utf-8') {
				$kws[] = siconv(trim($valuearray['value']), $_SC['charset'], 'utf-8');//编码转换
			} else {
				$kws[] = trim($valuearray['value']);
			}
		}
	}

	$return = '';
	if($kws) {
		foreach($kws as $kw) {
			$kw = shtmlspecialchars($kw);
			$return .= $kw.' ';
		}
		$return = trim($return);
	}
	
	showmessage($return);
} else {
	showmessage(' ');
}

?>