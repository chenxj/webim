<?php
include_once('common.php');
$uid = $space['uid'];
$ids = ids_array(gp("ids"));
if(!empty($ids)){
        for($i=0;$i<count($ids);$i++){
                $id = $ids[$i];
                $_SGLOBAL['db']->query("DELETE FROM ".im_tname('histories')." WHERE `uid`='$uid' and (`to`='$id' or `to`='$uid' ) and (`from`='$uid' or `from`='$id')");
        }
}
echo '{success:true}';
?>
