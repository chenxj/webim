<?php

function saddslashes($string) {
        if(is_array($string)) {
                foreach($string as $key => $val) {
                        $string[$key] = saddslashes($val);
                }
        } else {
                $string = addslashes($string);
        }
        return $string;
}

function tname($name) {
        global $tablepre;
        return $tablepre.$name;
}

function obclean() {
        global $_SC;

        ob_end_clean();
        if ($_SC['gzipcompress'] && function_exists('ob_gzhandler')) {
                ob_start('ob_gzhandler');
        } else {
                ob_start();
        }
}

?>