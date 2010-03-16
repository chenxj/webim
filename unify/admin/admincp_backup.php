<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: admincp_backup.php 11889 2009-03-30 08:20:43Z xupeng $
*/

if(!defined('IN_UCHOME') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
//权限
if(!checkperm('managebackup') || !ckfounder($_SGLOBAL['supe_uid'])) {
	cpmessage('no_authority_management_operation_backup');
}
//获取分卷编号
$volume = isset($_GET['volume']) ? (intval($_GET['volume']) + 1) : 1;
$backupdir = data_get('backupdir');
$x_ver = X_VER;

//备份文件目录
if(empty($backupdir)) {
	$backupdir = random(6);
	data_set('backupdir', $backupdir);
}
$backupdir = 'backup_'.$backupdir;
if(!is_dir(S_ROOT.'./data/'.$backupdir)) {
	@mkdir(S_ROOT.'./data/'.$backupdir, 0777);
}

//删除备份文件
if(submitcheck('delexportsubmit')) {
	if(!empty($_POST['delexport']) && is_array($_POST['delexport'])) {
		foreach($_POST['delexport'] as $value) {
			$fileext = fileext($value);
			if($fileext != 'sql' && $fileext != 'zip') {
				continue;
			}
			$value = str_replace('..', '', $value);
			if (file_exists(S_ROOT.'./data/'.$value)){
				@unlink(S_ROOT.'./data/'.$value);
			}
		}
	}
	cpmessage('do_success', 'admincp.php?ac=backup');
} elseif (submitcheck('importsubmit')) {
	$_POST['datafile'] = str_replace('..', '', $_POST['datafile']);
	if(!file_exists(S_ROOT.'./data/'.$_POST['datafile'])) {
		cpmessage('data_import_failed_the_file_does_not_exist');
	} else {
		$fileext = fileext($_POST['datafile']);
		if($fileext == 'sql') {
			cpmessage('start_transferring_data', 'admincp.php?ac=backup&op=import&do=import&datafile='.$_POST['datafile']);
		} elseif($fileext == 'zip') {
			cpmessage('start_transferring_data', 'admincp.php?ac=backup&op=import&do=zip&datafile='.$_POST['datafile']);
		} else {
			cpmessage('wrong_data_file_format_into_failure');
		}
	}
}

if(empty($_GET['op'])) {
	$shelldisabled = function_exists('shell_exec') ? '' : 'disabled';
	$zipdisplay = function_exists('gzcompress') ? true : false;
	$filename = date('ymd').'_'.random(8);
	$dbversion = intval($_SGLOBAL['db']->version());
	$uchome_tablelist = fetchtablelist($_SC['tablepre']);

	//备份列表
	$exportlog = array();
	if(is_dir(S_ROOT.'./data/'.$backupdir)) {
		$dir = dir(S_ROOT.'./data/'.$backupdir);
		while(FALSE !== ($entry = $dir->read())){
			$backupfile = S_ROOT.'./data/'.$backupdir.'/'.$entry;
			$basefile = $backupdir.'/'.$entry;
			if(is_file($backupfile)){
				$filesize = filesize($backupfile);
				if(preg_match('/\.sql$/i', $backupfile)) {
					$fp = fopen($backupfile, 'rb');
					$identify = explode(',', base64_decode(preg_replace('/^# Identify:\s*(\w+).*/s', '\\1', fgets($fp, 256))));
					fclose($fp);
					if($identify[3] != 'multivol') {
						$identify[4] = '';
					}
					$exportlog[] = array(
						'version' => $identify[1],
						'type' => $identify[2],
						'method' => $identify[3],
						'volume' => $identify[4],
						'filename' => $basefile,
						'dateline' => sgmdate('Y-m-d H:i:s',filemtime($backupfile)),
						'size' => formatsize($filesize)
						);
				} elseif(preg_match('/\.zip$/i', $backupfile)) {
					$exportlog[] = array(
						'type' => 'zip',
						'filename' => $basefile,
						'size' => formatsize($filesize),
						'dateline' => sgmdate('Y-m-d H:i:s',filemtime($backupfile)),
						'method' => '',
						'volume' => ''
					);
				}
			}
		}
		$dir->close();
	} else {
		cpmessage('directory_does_not_exist_or_can_not_be_accessed', '', 0, array(S_ROOT.'./data/'));//debug
	}
} elseif($_GET['op'] == 'export') {
	
	$filename = getval('filename');
	$type = getval('type');
	$method = getval('method');
	$usezip = intval(getval('usezip'));
	$sqlcharset = getval('sqlcharset');
	$extendins = getval('extendins');
	$sqlcompat = getval('sqlcompat');
	$usehex = intval(getval('usehex'));
	
	$_SGLOBAL['db']->query('SET SQL_QUOTE_SHOW_CREATE=0', 'SILENT');//无报错执行关闭我的创建表和列时不加引号
	if(empty($filename) || strlen($filename) > 40){	//文件名长度判断
		cpmessage('documents_were_incorrect_length');
	} else {
		$filename = preg_replace("/[^a-z0-9_]/i", '',(str_replace('.', '_', $filename)));
	}

	$tables = array();
	//备份方式
	if($type == 'uchomes') {
		$tables = arraykeys2(fetchtablelist($tablepre), 'Name');
	} elseif($type == 'custom') {
		if(isset($_POST['setup'])) {//POST提交备份
			$tables = empty($_POST['customtables']) ? array() : $_POST['customtables'];
			data_set('custombackup', $tables);
		} else {
			//自动跳转备份
			$tables = data_get('custombackup');
			$tables = unserialize($tables);
		}
	}

	if(empty($tables) || !is_array($tables)) {
		cpmessage('backup_table_wrong');
	}

	$time = sgmdate('Y-m-d H:i:s', $_SGLOBAL['timestamp']);
	$idstring = '# Identify: '.base64_encode("$_SGLOBAL[timestamp],".X_VER.",$type,$method,$volume")."\n";
	$dumpcharset = $sqlcharset ? $sqlcharset : str_replace('-', '', $_SC['charset']);
	$setnames = ($sqlcharset && $_SGLOBAL['db']->version() > '4.1' && (!$sqlcompat || $sqlcompat == 'MYSQL41')) ? "SET NAMES '$dumpcharset';\n\n" : '';

	if($_SGLOBAL['db']->version() > '4.1') {
		if($sqlcharset) {
			$_SGLOBAL['db']->query("SET NAMES '$sqlcharset'");
		}
		if($sqlcompat == 'MYSQL40') {
			$_SGLOBAL['db']->query("SET SQL_MODE='MYSQL40'");
		} elseif ($sqlcompat == 'MYSQL41') {
			$_SGLOBAL['db']->query("SET SQL_MODE=' '");
		}
	}

	$backupfile = S_ROOT.'./data/'.$backupdir.'/'.$filename;
	if($usezip) {
		include_once S_ROOT.'./source/class_zib.php';
	}

	if($method == 'multivol') {//分卷备份
		$sqldump = '';
		$sizelimit = intval(getval('sizelimit'));
		$tableid = intval(getval('tableid'));//表ID
		$startfrom = intval(getval('startfrom'));//起始位置
		$tablenum = count($tables);
		$filesize = $sizelimit * 1000;
		$complate = true;
		for( ; $complate && $tableid < $tablenum && strlen($sqldump) + 500 < $filesize; ++$tableid) {
			$sqldump .= sqldumptable($tables[$tableid], $startfrom, strlen($sqldump));
			if($complate) {
				$startfrom = 0;
			}
		}
		$dumpfile = sprintf($backupfile.'-%s'.'.sql', $volume);
		!$complate && $tableid --;
		if(trim($sqldump)) {
			$sqldump = "$idstring".
			"# <?exit();?>\n".
			"# UCenter Home Multi-Volume Data Dump Vol.$volume\n".
			"# Version: UCenter Home ".X_VER."\n".
			"# Time: $time\n".
			"# Type: $type\n".
			"# Table Prefix: $_SC[tablepre]\n".
			"#\n".
			"# UCenter Home: http://u.discuz.net\n".
			"# Please visit our website for newest infomation about UCenter Home\n".
			"# ---------------------------------------------------------\n\n\n".
			"$setnames".
			$sqldump;
			$fp = fopen($dumpfile, 'wb');
			@flock($fp, 2);
			if(!fwrite($fp, $sqldump)) {
				fclose($fp);
				cpmessage('failure_writes_the_document_check_file_permissions', 'admincp.php?ac=backup');
			} else {
				fclose($fp);
				if($usezip == 2) {
					$zipfile = sprintf($backupfile.'-%s'.'.zip', $volume);
					$zipfile = new Zip($zipfile);
					if(!$zipfile->create($dumpfile, PCLZIP_OPT_REMOVE_PATH, S_ROOT.'./data/'.$backupdir)) {
						cpmessage('failure_writes_the_document_check_file_permissions', 'admincp.php?ac=backup');
					} else {
						@unlink($dumpfile);
					}
					fclose($fp);
				}
				cpmessage('vol_backup_database', 'admincp.php?ac=backup&op=export&type='.rawurldecode($type).'&filename='.rawurlencode($filename).'&method=multivol&sizelimit='.$sizelimit.'&tableid='.intval($tableid).'&startfrom='.intval($startrows).'&extendins='.$extendins.'&sqlcharset='.rawurlencode($sqlcharset).'&sqlcompat='.rawurlencode($sqlcompat).'&usehex='.$usehex.'&usezip='.$usezip.'&volume='.intval($volume), 1, array($volume));//debug
			}
		} else {
			if($usezip == 1){
				$zipfile = $backupfile.'.zip';
				$zipfile = new Zip($zipfile);
				$unlinks = '';
				$arrayzipfile = array();
				for($i = 1; $i < $volume; ++$i){
					$dumpfile = sprintf($backupfile.'-%s'.'.sql', $i);
					$arrayzipfile[] = $dumpfile;
					$unlinks .= "@unlink('$dumpfile');";
				}
				if($zipfile->create($arrayzipfile, PCLZIP_OPT_REMOVE_PATH, S_ROOT.'./data/'.$backupdir)) {
					@eval($unlinks);
				} else {
					cpmessage('complete_database_backup', 'admincp.php?ac=backup', 1, array($volume-1));
				}
				fclose(fopen(S_ROOT.'./data/'.$backupdir.'/index.htm', 'a'));
				cpmessage('successful_data_compression_and_backup_server_to', 'admincp.php?ac=backup');
			} else {
				fclose(fopen(S_ROOT.'./data/'.$backupdir.'/index.htm', 'a'));
				cpmessage('complete_database_backup', 'admincp.php?ac=backup', 1, array($volume-1));
			}
		}
	} else {
		$tablesstr = '';
		foreach($tables as $value) {
			$tablesstr .= $value.' ';
		}
		list($_SC['dbhost'], $_SC['dbport']) = explode(':', $_SC['dbhost']);
		$query = $_SGLOBAL['db']->query("SHOW VARIABLES LIKE 'basedir'");
		list(, $mysql_base) = $_SGLOBAL['db']->fetch_array($query, MYSQL_NUM);
		$dumpfile = $backupfile.'.sql';
		@unlink($dumpfile);

		$mysqlbin = $mysql_base == '/' ? '' : addslashes($mysql_base).'bin/';
		$_SC['dbcharset'] = empty($_SC['dbcharset']) ? $_SC['charset'] : $_SC['dbcharset'];
		@shell_exec('"'.$mysqlbin.'mysqldump" --force --quick --default-character-set='.$_SC['dbcharset'].' '.($_SGLOBAL['db']->version() > 4.1 ? '--skip-opt --create-options' : '-all').' --add-drop-table'.($extendins == 1 ? '--extended-insert' : '').''.($_SGLOBAL['db']->version() > '4.1' && $sqlcompat == 'MYSQL40' ? '--compatible=mysql40' : '').' --host='.$_SC['dbhost'].($_SC['dbport'] ? (is_numeric($_SC['dbport']) ? ' --port='.$_SC['dbport'] : ' --sock='.$_SC['dbport']) : '').' --user='.$_SC['dbuser'].' --password='.$_SC['dbpw'].' '.$_SC['dbname'].' '.$tablesstr.' > '.$dumpfile);

		if(file_exists($dumpfile)) {
			if(is_writable($dumpfile)) {
				$fp = fopen($dumpfile, 'rb+');
				fwrite($fp,  $idstring."# <?exit();?>\n ".$setnames."\n #");
				fclose($fp);
			}

			if($usezip) {
				include_once S_ROOT.'./source/class_zib.php';
				$zipfilename = $backupfile.'.zip';
				$zipfile = new Zip($zipfilename);
				if($zipfile->create($dumpfile, PCLZIP_OPT_REMOVE_PATH, S_ROOT.'./data/'.$backupdir)) {
					@unlink($dumpfile);
					fclose(fopen(S_ROOT.'./data/'.$backupdir.'/index.htm', 'a'));
					cpmessage('successful_data_compression_and_backup_server_to', 'admincp.php?ac=backup');
				} else {
					cpmessage('backup_file_compression_failure', 'admincp.php?ac=backup');
				}
			} else {
				fclose(fopen(S_ROOT.'./data/'.$backupdir.'/index.htm', 'a'));
				cpmessage('successful_data_compression_and_backup_server_to', 'admincp.php?ac=backup');
			}
		} else {
			cpmessage('shell_backup_failure', 'admincp.php?ac=backup');
		}
	}
} elseif($_GET['op'] == 'import') {

	if($_GET['do'] == 'zip') {
		include_once S_ROOT.'./source/class_zib.php';
		$unzip = new SimpleUnzip();
		$unzip->ReadFile(S_ROOT.'./data/'.$_GET['datafile']);

		if($unzip->Count() == 0 || $unzip->GetError(0) != 0 || !preg_match('/\.sql$/i', $importfile = $unzip->GetName(0))) {
			cpmessage('data_file_does_not_exist');
		}

		$identify = explode(',', base64_decode(preg_replace('/^# Identify:\s*(\w+).*/s', '\\1', substr($unzip->GetData(0), 0, 256))));
		//检查版本号
		$_GET['confirm'] = isset($_GET['confirm']) ? 1 : 0;
		if(!$_GET['confirm'] && $identify[1] != X_VER) {
			$showform = 1;
			include template('admin/tpl/backup');
			exit();
		}

		$sqlfilecount = 0;
		foreach($unzip->Entries as $entry) {
			if(preg_match('/\.sql$/i', $entry->Name)) {
				$fp = fopen(S_ROOT.'./data/'.$backupdir.'/'.$entry->Name, 'w');
				fwrite($fp, $entry->Data);
				fclose($fp);
				$sqlfilecount++;
			}
		}

		if(!$sqlfilecount) {
			cpmessage('data_file_does_not_exist');
		}

		$_GET['multivol'] = isset($_GET['multivol']) ? $_GET['multivol'] : 0;
		$_GET['datafile_vol1'] = isset($_GET['datafile_vol1']) ? $_GET['datafile_vol1'] : '';

		if(!empty($_GET['multivol'])) {
			$_GET['multivol']++;
			$_GET['datafile'] = preg_replace('/-(\d+)(\..+)$/', "-$_GET[multivol]\\2", $_GET['datafile']);
			if(file_exists(S_ROOT.'./data/'.$_GET['datafile'])) {
				cpmessage('decompress_data_files_success', 'admincp.php?ac=backup&op=import&do=zip&multivol='.$_GET['multivol'].'&datafile_vol1='.$_GET['datafile_vol1'].'&datafile='.$_GET['datafile'].'&confirm=yes', 1, array($_GET['multivol']));//debug
			} else {
				$showform = 2;
				include template('admin/tpl/backup');
				exit();
			}
		}

		if($identify[3] == 'multivol' && $identify[4] == 1 && preg_match("/-1(\..+)$/", $_GET['datafile'])) {
			$_GET['datafile_vol1'] = $_GET['datafile'];
			$_GET['datafile'] = preg_replace('/-1(\..+)$/', '-2\\1', $_GET['datafile']);
			if(file_exists(S_ROOT.'./data/'.$_GET['datafile'])) {
				$showform = 3;
				include template('admin/tpl/backup');
				exit();
			}
		}
		$showform = 4;
		include template('admin/tpl/backup');
		exit();
	} elseif($_GET['do'] == 'import') {
		$sqldump = '';
		$_GET['datafile'] = str_replace("..", '', $_GET['datafile']);
		$datafile_root = S_ROOT.'./data/'.$_GET['datafile'];
		if($fp = @fopen($datafile_root, 'rb')) {
			$sqldump = fgets($fp, 256);
			$identify = explode(',', base64_decode(preg_replace('/^# Identify:\s*(\w+).*/s', '\\1', $sqldump)));
			if($identify[3] == 'multivol') {
				$sqldump .= fread($fp,filesize($datafile_root));
			}
			fclose($fp);
		} else {
			if(isset($_GET['autoimport'])) {
				cpmessage('the_volumes_of_data_into_databases_success', 'admincp.php?ac=backup');
			} else {
				cpmessage('data_file_does_not_exist');
			}
		}

		if($identify[3] == 'multivol') {
			$sqlquery = splitsql($sqldump);
			unset($sqldump);
			foreach($sqlquery as $sql) {
				$sql = syntablestruct(trim($sql), $_SGLOBAL['db']->version() > '4.1', $_SC['dbcharset']);
				if(!empty($sql)) {
					$_SGLOBAL['db']->query($sql, 'SILENT');//屏蔽错误
					if($_SGLOBAL['db']->error() && $_SGLOBAL['db']->errno() != 1062) {
						$_SGLOBAL['db']->halt('MySQL Query Error', $sql);
					}
				}
			}

			if(isset($_GET['delunzip'])) {
				@unlink(S_ROOT.'./data/'.$_GET['datafile']);
			}

			$identify[4] = intval($identify[4]);
			$datafile_next = preg_replace("/-($identify[4])(\..+)$/", '-'.($identify[4] + 1).'\\2', $_GET['datafile']);

			if($identify[4] == 1) {
				$showform = 5;
				include template('admin/tpl/backup');
				exit();	
			} elseif (isset($_GET['autoimport'])) {
				cpmessage('data_files_into_success', "admincp.php?ac=backup&op=import&do=import&datafile=$datafile_next&autoimport=yes".(isset($unzip) ? '&delunzip=yes' : ''), 1, array($identify[4]));
			} else {
				cpmessage('the_volumes_of_data_into_databases_success', 'admincp.php?ac=backup');
			}
		} elseif($identify[3] == 'shell') {

			list($dbhost, $dbport) = explode(':', $dbhost);

			$query = $_SGLOBAL['db']->query("SHOW VARIABLES LIKE 'basedir'");
			list(, $mysql_base) = $_SGLOBAL['db']->fetch_array($query, MYSQL_NUM);

			$mysqlbin = $mysql_base == '/' ? '' : addslashes($mysql_base).'bin/';
			$dbcharset = empty($_SC['dbcharset']) ? $_SC['charset'] : $_SC['dbcharset'];
			@shell_exec('"'.$mysqlbin.'mysql" --default-character-set='.$dbcharset.' -h '.$dbhost.($dbport ? (is_numeric($dbport) ? ' -P'.$dbport : ' -S'.$dbport.'') : '').' -u'.$dbuser.' -p'.$dbpw.' '.$dbname.' < '.$_GET['datafile']);

			cpmessage('the_volumes_of_data_into_databases_success', 'admincp.php?ac=backup');
		} else {
			cpmessage('data_file_format_is_wrong_not_into');
		}
	}
}

function fetchtablelist($tablepre = '') {
	global $_SGLOBAL, $_SC;
	!$tablepre && $tablepre = '*';
	$tables = $table = array();
	$query = $_SGLOBAL['db']->query("SHOW TABLE STATUS LIKE '$_SC[tablepre]%'");
	while($table = $_SGLOBAL['db']->fetch_array($query)) {
		if(!strexists($table['Name'], 'cache')) {
			$tables[] = $table;
		}
	}
	return $tables;
}

function arraykeys2($array, $key2) {
	$return = array();
	foreach($array as $value) {
		$return[] = $value[$key2];
	}
	return $return;
}

function sqldumptable($table, $startfrom = 0, $currsize = 0) {
	global $_SGLOBAL, $filesize, $startrows, $_GET, $dumpcharset, $complate;

	$offset = 300;
	$tabledump = '';
	$tablefields = array();

	$query = $_SGLOBAL['db']->query('SHOW FULL COLUMNS FROM '.$table, 'SILENT');
	if(strexists($table, 'cache')) {
		return;
	} elseif (!$query && $_SGLOBAL['db']->errno() == '1146') {
		return;
	} elseif (!$query) {
		$_GET['usehex'] = FALSE;
	} else {
		while($result = $_SGLOBAL['db']->fetch_array($query)) {
			$tablefields[] = $result;
		}
	}
	if(!$startfrom) {
		$createtable = $_SGLOBAL['db']->query('SHOW CREATE TABLE '.$table, 'SILENT');

		if(!$_SGLOBAL['db']->errno()) {
			$tabledump = "DROP TABLE IF EXISTS $table;\n";
		} else {
			return;
		}
		
		$create = $_SGLOBAL['db']->fetch_row($createtable);
		$tabledump .= $create[1];
		if($_GET['sqlcompat'] == 'MYSQL41' && $_SGLOBAL['db']->version() < '4.1') {
			$tabledump = preg_replace('/TYPE=(.+)/', "ENGINE=\\1 DEFAULT CHARSET=".$dumpcharset, $tabledump);
		}
		if($_SGLOBAL['db']->version() > '4.1' && $_GET['sqlcharset']) {
			$tabledump = preg_replace('/(DEFAULT)*\s*CHARSET=.+/', 'DEFAULT CHARSET='.$dumpcharset, $tabledump);
		}

		$query = $_SGLOBAL['db']->query("SHOW TABLE STATUS LIKE '$table'");
		$tablestatus = $_SGLOBAL['db']->fetch_array($query);
		$tabledump .= ($tablestatus['Auto_increment'] ? " AUTO_INCREMENT=$tablestatus[Auto_increment]" : '').";\n\n";
		if($_GET['sqlcompat'] == 'MYSQL40' && $_SGLOBAL['db']->version() >= '4.1' && $_SGLOBAL['db']->version() < '5.1') {
			if(!empty($tablestatus['Auto_increment'])) {
				$temppos = strpos($tabledump, ',');
				$tabledump = substr($tabledump, 0, $temppos).' auto_increment'.substr($tabledump, $temppos);
			}

			if($tablestatus['Engine'] == 'MEMORY') {
				$tabledump = str_replace('TYPE=MEMORY', 'TYPE=HEAP', $tabledump);
			}
		}
	}

	$tabledumped = 0;
	$numrows = $offset;
	$firstfield = $tablefields[0];

	if($_GET['extendins'] == 0){
		while($currsize + strlen($tabledump) + 500 < $filesize && $numrows == $offset){
			if($firstfield['Extra'] == 'auto_increment'){
				$selectsql = 'SELECT * FROM '.$table." WHERE $firstfield[Field] > $startfrom LIMIT $offset";
			} else {
				$selectsql = 'SELECT * FROM '.$table." LIMIT $startfrom, $offset";
			}
			$tabledumped = 1;
			$query = $_SGLOBAL['db']->query($selectsql);
			$numfields = $_SGLOBAL['db']->num_fields($query);	//取得列数

			if($numrows = $_SGLOBAL['db']->num_rows($query)) {
				while($row = $_SGLOBAL['db']->fetch_row($query)) {	//以枚举形式取得行值
					$dumpsql = $comma = '';
					for($i = 0; $i < $numfields; ++$i) {
						$dumpsql .= $comma.($_GET['usehex'] && !empty($row[$i]) && (strexists($tablefields[$i]['Type'], 'char') || strexists($tablefields[$i]['Type'], 'text')) ? '0x'.bin2hex($row[$i]) : '\''.mysql_escape_string($row[$i]).'\'');
						$comma = ',';
					}
					if(strlen($dumpsql) + $currsize + strlen($tabledump) + 500 < $filesize ) {
						if($firstfield['Extra'] == 'auto_increment') {
							$startfrom = $row[0];
						} else {
							$startfrom ++;
						}
						$tabledump .= "INSERT INTO $table VALUES ($dumpsql);\n";
					} else {
						$complate = FALSE;
						break 2;
					}
				}
			}
		}
	} else {
		while($currsize + strlen($tabledump) + 500 < $filesize && $numrows == $offset) {
			if($firstfield['Extra'] == 'auto_increment'){
				$selectsql = 'SELECT * FROM '.$table." WHERE $firstfield[Field] > $startfrom LIMIT $offset";
			} else {
				$selectsql = 'SELECT * FROM '.$table." LIMIT $startfrom, $offset";
			}
			$tabledumped = 1;
			$query = $_SGLOBAL['db']->query($selectsql);
			$numfields = $_SGLOBAL['db']->num_fields($query);
			
			if($numrows = $_SGLOBAL['db']->num_rows($query)) {
				$extdumpsql = $extcomma = '';
				while($row = $_SGLOBAL['db']->fetch_row($query)) {
					$dumpsql = $comma = '';
					for($i = 0; $i < $numfields; ++$i) {
						$dumpsql .= $comma.($_GET['usehex'] && !empty($row[$i]) && (strexists($tablefields[$i]['Type'], 'char') || strexists($tablefields[$i]['Type'], 'text')) ? '0x'.bin2hex($row[$i]) : '\''.mysql_escape_string($row[$i]).'\'');
						$comma = ',';
					}
					if(strlen($extdumpsql) + $currsize + strlen($tabledump) + 500 < $filesize ) {
						if($firstfield['Extra'] == 'auto_increment') {
							$startfrom = $row[0];
						} else {
							$startfrom ++;
						}
						$extdumpsql .= "$extcomma ($dumpsql)";
						$extcomma = ',';
					} else {
						$tabledump .= "INSERT INTO $table VALUES $extdumpsql;\n";
						$complate = FALSE;
						break 2;
					}
				}
				$tabledump .= "INSERT INTO $table VALUES $extdumpsql;\n";
			}
		}
	}
	$startrows = $startfrom;
	$tabledump .= "\n";

	return $tabledump;
}

function getval($key) {
	return isset($_GET[$key]) ? $_GET[$key] : $_POST[$key];
}

function splitsql($sqldump) {
	$sql = str_replace("\r", "\n", $sqldump);
	$ret = array();
	$num = 0;
	$queriesarray = explode(";\n", trim($sql));
	unset($sql);
	foreach($queriesarray as $query) {
		$queries = explode("\n", trim($query));
		foreach($queries as $subquery) {
			if(!empty($subquery[0])){
				$ret[$num] .= $subquery[0] == '#' ? NULL : $subquery;
			}
		}
		$num++;
	}
	return $ret;
}

function syntablestruct($sql, $version, $dbcharset) {
	if(strpos(trim(substr($sql, 0, 18)), 'CREATE TABLE') === FALSE) {
		return $sql;
	}

	$sqlversion = strpos($sql, 'ENGINE=') === FALSE ? FALSE : TRUE;

	if($sqlversion === $version ) {
		return $sqlversion && $dbcharset ? preg_replace(array('/ character set \w+/i', '/ collate \w+/i', '/DEFAULT CHARSET=\w+/is'), array('', '', "DEFAULT CHARSET=$dbcharset"), $sql) : $sql;
	}

	if($version) {
		return preg_replace(array('/TYPE=HEAP/i', '/TYPE=(\w+)/is'), array("ENGINE=MEMORY DEFAULT CHARSET=$dbcharset", "ENGINE=\\1 DEFAULT CHARSET=$dbcharset"), $sql);
	} else {
		return preg_replace(array('/character set \w+/i', '/collate \w+/i', '/ENGINE=MEMORY/i', '/\s*DEFAULT CHARSET=\w+/is', '/\s*COLLATE=\w+/is', '/ENGINE=(\w+)(.*)/is'), array('', '', 'ENGINE=HEAP', '', '', 'TYPE=\\1\\2'), $sql);
	}
}
?>