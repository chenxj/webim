<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_domain.php 12141 2009-05-12 07:07:52Z zhengqingpeng $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//二级域名
$domainlength = checkperm('domainlength');

if($_SCONFIG['allowdomain'] && $_SCONFIG['domainroot'] && $domainlength) {
	$reward = getreward('modifydomain', 0);
} else {
	showmessage('no_privilege');
}

if(submitcheck('domainsubmit')) {

	$setarr = array();
	//二级域名
	$_POST['domain'] = strtolower(trim($_POST['domain']));
	if($_POST['domain'] != $space['domain']) {
		
		//积分
		if($space['domain'] && ($reward['credit'] || $reward['experience'])) {
			if($space['experience'] >= $reward['experience']) {
				$setarr['experience'] = $space['experience'] - $reward['experience'];
			} else {
				showmessage('experience_inadequate', '', 1, array($space['experience'], $reward['experience']));
			}
			if($space['credit'] >= $reward['credit']) {
				$setarr['credit'] = $space['credit'] - $reward['credit'];
			} else {
				showmessage('integral_inadequate', '', 1, array($space['credit'], $reward['credit']));
			}
		}
		
		if(empty($domainlength) || empty($_POST['domain'])) {
			$setarr['domain'] = '';
		} else {
			if(strlen($_POST['domain']) < $domainlength) {
				showmessage('domain_length_error', '', 1, array($domainlength));
			}
			if(strlen($_POST['domain']) > 30) {
				showmessage('two_domain_length_not_more_than_30_characters');
			}
			if(!preg_match("/^[a-z][a-z0-9]*$/", $_POST['domain'])) {
				showmessage('only_two_names_from_english_composition_and_figures');
			}

			if(isholddomain($_POST['domain'])) {
				showmessage('domain_be_retained');//debug
			}

			$count = getcount('space', array('domain'=>$_POST['domain']));
			if($count) {
				showmessage('two_domain_have_been_occupied');
			}
			
			$setarr['domain'] = $_POST['domain'];
		}
	}
	if($setarr) updatetable('space', $setarr, array('uid'=>$_SGLOBAL['supe_uid']));
	
	showmessage('do_success', 'cp.php?ac=domain');
}

$actives = array($ac => ' class="active"');

include_once template("cp_domain");

?>