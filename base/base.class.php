<?php
define(ROOT,dirname(dirname(dirname(__FILE__))));


class nextim
{
    /*
    *  check the platform 
    *  Uchome ? Discuz ?  PhpWind?
    */
    function __construct() {
       // nextim(){
        if(file_exists(ROOT.'/data/avatar'))
            $this->platform = "uchome";
        if(file_exists(ROOT.'/forumdata'))
            $this->platform = "discuz";
        if(file_exists(ROOT.'/data/bbscache'))
            $this->platform = "phpwind";
    }


    /*
    *  get user info in platform 
    */
    function  cur_user_info()
    {
        if($this->platform == "phpwind")
            $user_info = User_info();
        return $user_info;
    }

    function get_plf_config()
    {
        if($this->platform == "phpwind")
        {
            include_once(ROOT . DIRECTORY_SEPARATOR . "data/sql_config.php");
            global $dbhost,$dbuser,$dbpw,$dbname,$dbpre,$charset;
            $config = array();
            $config['dbhost'] = $dbhost;
            $config['dbuser'] = $dbuser;
            $config['dbpw']   = $dbpw;
            $config['dbname'] = $dbname;
            $config['tablepre'] = $dbpre;
            $config['charset'] = $charset;
            $config['dbcharset'] = $charset;
            return $config;
        }
    }

    function get_webim_config()
    {
        include_once(ROOT . DIRECTORY_SEPARATOR . "webim/config.php");
        return $_IMC;
    }



}

new nextim;

?>
