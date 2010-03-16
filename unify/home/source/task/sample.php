<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: sample.php 11056 2009-02-09 01:59:47Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//内置变量：$task['done'] (完成标识变量) $task['result'] (结果文字) $task['guide'] (向导文字)

//判断用户是否完成了任务
$done = 0;

//---------------------------------------------------
//	编写代码，判读用户是否完成任务要求 $done = 1;
//---------------------------------------------------

if($done) {

	$task['done'] = 1;//任务完成
	$task['result'] = '......';//用户参与任务看到的文字说明。支持html代码
	
} else {

	//任务完成向导
	$task['guide'] = '......'; //指导用户如何参与任务的文字说明。支持html代码

}

?>