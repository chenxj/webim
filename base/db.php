<?php
error_reporting(E_ALL);
define('ROOT',dirname(dirname(dirname(__FILE__))));
include_once(ROOT . DIRECTORY_SEPARATOR . "data/sql_config.php");
include_once('base.class.php');

class nextim_db
{
    function nextim_db()
    {
        $nextim_obj = new nextim;
        $plf_config = $nextim_obj->get_plf_config();
		$this->dbhost   = $plf_config['dbhost'];
		$this->dbuser   = $plf_config['dbuser'];
		$this->dbpw     = $plf_config['dbpw'];
		$this->dbname   = $plf_config['dbname'];
		$this->tablepre    = $plf_config['tablepre'];
		$this->charset  = $plf_config['charset'];
		$this->connect();
        unset($plf_config);
    }

	function connect() {
		$this->sql =  @mysql_connect($this->dbhost, $this->dbuser, $this->dbpw, true);
		mysql_errno($this->sql) != 0 && $this->halt('Connect('.$this->pconnect.') to MySQL failed');
		$serverinfo = mysql_get_server_info($this->sql);
		if ($serverinfo > '4.1' && $this->charset) {
			mysql_query("SET character_set_connection=" . $this->charset . ",character_set_results=" . $this->charset . ",character_set_client=binary", $this->sql);
		}
		if ($serverinfo > '5.0') {
			mysql_query("SET sql_mode=''", $this->sql);
		}
		if ($this->dbname && !@mysql_select_db($this->dbname, $this->sql)) {
			$this->halt('Cannot use database');
		}
    }

    function query($sql,$hold=0)
    {
        
        if($hold)
            $this->halt();
    }
}







?>
