<?php
include_once('common.php');
$data=gp('data');
if(!empty($data)){
        $_SGLOBAL['db']->query("UPDATE ".im_tname('setting')." SET web='$data' WHERE uid=$space[uid]");
}
echo "{success:true}";
