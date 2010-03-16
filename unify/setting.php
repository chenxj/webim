<?php
$platform = $_GET['platform'];
switch($platform){
	case 'discuz':
		include_once('discuz.php');
		break;
	case 'uchome':
		include_once('uchome.php');
		break;
}
$data=gp('data');
if(!empty($data)){
        $_SGLOBAL['db']->query("UPDATE ".im_tname('setting')." SET web='$data' WHERE uid=$space[uid]");
}
echo "{success:true}";
