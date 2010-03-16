<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: admincp_template.php 12901 2009-07-27 07:59:27Z liguode $
*/

if(!defined('IN_UCHOME') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(empty($_SC['allowedittpl']) || !checkperm('managetemplate') || !ckfounder($_SGLOBAL['supe_uid'])) {
	cpmessage('no_authority_management_operation_edittpl');
}

$turl = 'admincp.php?ac=template';
//模板目录
$tpldir = S_ROOT.'./template/default/';

if(submitcheck('editsubmit')) {

	$filename = checkfilename($_POST['filename']);
	$filefullname = $tpldir.$filename;

	//复制当前的文件
	$d_file = $filefullname.'.bak';
	if(!file_exists($d_file)) {
		if(!@copy($filefullname, $d_file)) {
			swritefile($d_file, sreadfile($filefullname));
		}
	}
	
	$fp = fopen($filefullname, 'wb');
	flock($fp, 2);
	fwrite($fp, stripslashes($_POST['content']));
	fclose($fp);
	
	//清空模板缓存
	$filename = substr($filename, 0, strlen($filename)-4);
	$tpl = strexists($filename,'/')?$filename:"template/$_SCONFIG[template]/$filename";
	$objfile = S_ROOT.'./data/tpl_cache/'.str_replace('/','_',$tpl).'.php';
	@unlink($objfile);
	
	cpmessage('do_success', $turl);
}

if(empty($_GET['op'])) {

	//获取模板列表
	$tpls = array();
	if($dh = opendir($tpldir)) {
		while (($file = readdir($dh)) !== false) {
			if(is_file($tpldir.'/'.$file) && fileext($file) == 'htm') {
				
				$status = 0;
				if(file_exists($tpldir.'/'.$file.'.bak')) {
					$status = 1;
				}
				$tplname = substr($file, 0, -4);
				$pos = strpos($file, '_');
				if($pos) {
					$tpls[substr($tplname, 0, $pos)][] = array($file, $status);
				} else {
					$tpls['base'][] = array($file, $status);
				}				
			}
		}
		closedir($dh);
	}
	
	
} elseif($_GET['op'] == 'edit') {

	$filename = checkfilename($_GET['filename']);
	
	$filefullname = $tpldir.$filename;
	$fp = fopen($filefullname, 'rb');
	$content = trim(shtmlspecialchars(fread($fp, filesize($filefullname))));
	fclose($fp);
	
} elseif($_GET['op'] == 'repair') {
	
	$filename = checkfilename($_GET['filename']);
	$filefullname = $tpldir.$filename;

	//复制当前的文件
	$d_file = $filefullname.'.bak';
	if(file_exists($d_file)) {
		if(!@copy($d_file, $filefullname)) {
			swritefile($filefullname, sreadfile($d_file));
			@unlink($d_file);
		} else {
			@unlink($d_file);
		}
	} else {
		cpmessage('designated_template_files_can_not_be_restored');
	}
	
	cpmessage('do_success', $turl);
}

function checkfilename($filename) {
	global $tpldir;
	
	$isedit = false;
	if(!empty($filename)) {
		$filename = str_replace(array('..', '/', '\\'), array('', '', ''), $filename);
		if(!empty($filename) && fileext($filename) == 'htm') {
			if(is_writeable($tpldir.$filename)) {
				$isedit = true;
			}
		}
	}
	if(!$isedit) {
		cpmessage('template_files_editing_failure_check_directory_competence');
	}
	
	return $filename;
}

?>