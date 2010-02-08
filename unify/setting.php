<?php
$platform = $_GET['platform'];
switch($platform){
	case 'discuz':
	include_once('common_discuz.php');
	break;
	case 'uchome':
		include_once('common_uchome.php');
		break;
}
$data=gp('data');
if(!empty($data)){
        $_SGLOBAL['db']->query("UPDATE ".im_tname('setting')." SET web='$data' WHERE uid=$space[uid]");
}
echo "{success:true}";
