<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_import.php 13000 2009-08-05 05:58:30Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//判断是否有权限
if(!checkperm('allowblog')) {
	ckspacelog();
	showmessage('no_privilege');
}
	
//实名认证
ckrealname('blog');

//视频认证
ckvideophoto('blog');

//新用户见习
cknewuser();

//判断是否发布太快
$waittime = interval_check('post');
if($waittime > 0) {
	showmessage('operating_too_fast','',1,array($waittime));
}
		
//检查是否支持
if(!function_exists('fsockopen')) {
	showmessage('support_function_has_not_yet_opened fsockopen');
}

$userfile = S_ROOT."./data/temp/{$_SGLOBAL['supe_uid']}.data";
$results = array();

if(file_exists($userfile)) {
	$result = sreadfile($userfile);
	$results = empty($result)?'':unserialize($result);
}
$reward = getreward('blogimport', 0);
$siteurl = getsiteurl();

if(submitcheck('importsubmit')) {
	
	//验证经验值
	if($space['experience'] < $reward['experience']) {
		showmessage('experience_inadequate', '', 1, array($space['experience'], $reward['experience']));
	}
				
	//检查积分
	if($space['credit'] < $reward['credit']) {
		showmessage('integral_inadequate','',1,array($space['credit'],$reward['credit']));
	}
	
	//站点URL
	$_POST['url'] = trim($_POST['url']);
	if(empty($_POST['url']) || !$urls = parse_url($_POST['url'])) {
		showmessage('url_is_not_correct');
	}
	
	//构建串
	$xmldata  = '<?xml version="1.0" encoding="utf-8"?>';
	$xmldata .= '<methodCall><methodName>metaWeblog.getRecentPosts</methodName>';
	$xmldata .= '<params>';
	$xmldata .= '<param><value><string>blog</string></value></param>';
	$xmldata .= '<param><value><string>'.shtmlspecialchars(siconv($_POST['username'], 'utf-8')).'</string></value></param>';
	$xmldata .= '<param><value><string>'.shtmlspecialchars($_POST['password']).'</string></value></param>';
	$xmldata .= '<param><value><int>'.intval($_SCONFIG['importnum']).'</int></value></param>';
	$xmldata .= '</params>';
	$xmldata .= '</methodCall>';
	
	//发生请求
	$result = '';
	$urls['port'] = empty($urls['port'])?'80':$urls['port'];
	
	if(@$fp = fsockopen($urls['host'], $urls['port'], $errno, $errstr, 30)) {
		$header = "POST $urls[path] HTTP/1.1\r\n";
		$header .= "Host: $urls[host]\r\n";
		$header .= "Content-Type: text/xml; charset=utf-8\r\n";
		$header .= "Content-Length: ".strlen($xmldata)."\r\n";
		$header .= "Connection: Close\r\n\r\n";
		$header .= $xmldata."\r\n";
		fputs($fp, $header);

		$inheader = 1;
		$org_result = '';
		while (!feof($fp)) {
			$line = fgets($fp, 1024);
			$org_result .= $line;
			if ($inheader && ($line == "\n" || $line == "\r\n")) {
				$inheader = 0;
			}
			if (!$inheader) {
				$result .= trim($line);
			}
		}
		fclose($fp);
	}
	
	if(empty($result)) {
		showmessage('blog_import_no_result', '', 1, array(shtmlspecialchars($org_result)));
	}
	
	//解析结果
	$results = xmltoarray($result);
	$ones = array_pop(array_slice($results, -1));
	if(!isset($ones['postid'])) {
		if(empty($ones)) {
			$return = "<textarea name=\"tmp[]\" style=\"width:98%;\" rows=\"4\">".shtmlspecialchars($result)."</textarea>";
		} else {
			$return = implode(', ', $ones);
		}
		showmessage('blog_import_no_data', '', 1, array($return));
	}
	
	//缓存结果
	swritefile($userfile, serialize($results));

} elseif (submitcheck('import2submit')) {
	
	include_once(S_ROOT.'./source/function_blog.php');
	
	if(empty($results) || empty($_POST['ids'])) {
		showmessage('choose_at_least_one_log', 'cp.php?ac=import');
	}
	
	$allcount = $incount = 0;
	krsort($results);//倒序
	foreach ($results as $key => $value) {
		$allcount = $allcount + 1;
		if(in_array($key, $_POST['ids'])) {
			$value = saddslashes($value);
			
			$dateline = intval(sstrtotime($value['dateCreated']));
			
			$subject = getstr($value['title'], 80, 1, 1, 1);

			$message = isset($value['description'])?$value['description']:$value['content'];
			$message = getstr($message, 0, 1, 1, 1, 0, 1);
			$message = checkhtml($message);

			if(empty($subject) || empty($message)) {
				$results[$key]['status'] = '--';
				$results[$key]['blogid'] = 0;
				continue;
			}
						
			//开始导入
			$blogarr = array(
				'uid' => $_SGLOBAL['supe_uid'],
				'username' => $_SGLOBAL['supe_username'],
				'subject' => $subject,
				'pic' => getmessagepic($message),
				'dateline' => $dateline?$dateline:$_SGLOBAL['timestamp']
			);
			$blogid = inserttable('blog', $blogarr, 1);
			
			//插入内容
			$fieldarr = array(
				'blogid' => $blogid,
				'uid' => $_SGLOBAL['supe_uid'],
				'message' => $message,
				'postip' => getonlineip()
			);
			inserttable('blogfield', $fieldarr);
			
			//统计
			$incount = $incount + 1;
			
			$results[$key]['status'] = 'OK';
			$results[$key]['blogid'] = $blogid;
		} else {
			$results[$key]['status'] = '--';
			$results[$key]['blogid'] = 0;
		}
	}
	if($incount) {
		//扣除积分
		getreward('blogimport');
		@unlink($userfile);
	}
} elseif (submitcheck('resubmit')) {
	@unlink($userfile);
	$results = array();
}

include template('cp_import');

//xmlrpc结果解析
function xmltoarray($xmldata){
	global $_SC;
	
	$struct = array();
	$__type = 0;
	$tmp_value = '';
	
	$parser = xml_parser_create();
	xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
	xml_parse_into_struct($parser, $xmldata, $values, $indexs);
	xml_parser_free($parser);
	
	$loop = count($indexs['member']) / (count($indexs['struct']) / 2 ); 
	
	for ($i = 0; $i < count($indexs['member']); $i += $loop){
		$_struct = array_slice($indexs['member'], $i, $loop);
		$_array_pop = array_pop($_struct);
		$_array_shift = array_shift($_struct);
		$__struct = array_slice($values, $_array_shift, $_array_pop - $_array_shift + 1);
		
		$keys = array();
		foreach($__struct as $_value){
			if("complete" == $_value['type']){
				if($__type == 0){
					$tmp_value = $_value['value'];
					$keys[] = $tmp_value;
				} else {
					if(($tmp_value == 'content' && in_array('description', $keys)) || ($tmp_value == 'description' && in_array('content', $keys))) {
					} else {
						$struct[$i][$tmp_value] = siconv( $_value['value'], $_SC['charset'], 'UTF-8');
					}
				}				
				$__type == 0 ? $__type = 1:$__type = 0 ;
			}
		}
	}
	return $struct;
} 


?>