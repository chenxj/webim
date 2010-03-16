<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: help.php 12059 2009-05-04 02:43:18Z liguode $
*/

include_once('./common.php');

if(empty($_GET['ac'])) $_GET['ac'] = 'register';

$actives = array($_GET['ac'] => ' style="font-weight:bold;"');

include template('help');

?>