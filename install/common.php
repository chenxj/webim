<?php
//error_reporting(0);
$_SGLOBAL = $_SCONFIG = $_SBLOCK = array();
//安装平台根目录
//define('S_ROOT', substr(dirname(__FILE__), 0, -13));
//$platform = which_platform();
$nextim_version="2.2.28";
$theurl = 'index.php';
$sqlfile = S_ROOT.'/webim/install/data/webim.sql';



/*
*  check the platform 
*  Uchome ? Discuz ?  PhpWind?
*/
function which_platform(){
	if(file_exists(S_ROOT.'./data/sql_config.php')){
		return "phpwind";
	}
	if(file_exists(S_ROOT.'./data')){
		return "uchome";
	}
	if(file_exists(S_ROOT.'./forumdata')){
		return "discuz";
	}
}

function checkfdperm($path, $isfile=0) {
	if($isfile) {
		$file = $path;
		$mod = 'a';
	} else {
		$file = $path.'./install_tmptest.data';
		$mod = 'w';
	}
	if(!@$fp = fopen($file, $mod)) {
		return false;
	}
	if(!$isfile) {
		//是否可以删除
		fwrite($fp, ' ');
		fclose($fp);
		if(!@unlink($file)) {
			return false;
		}
		//检测是否可以创建子目录
		if(is_dir($path.'./install_tmpdir')) {
			if(!@rmdir($path.'./install_tmpdir')) {
				return false;
			}
		}
		if(!@mkdir($path.'./install_tmpdir')) {
			return false;
		}
		//是否可以删除
		if(!@rmdir($path.'./install_tmpdir')) {
			return false;
		}
	} else {
		fclose($fp);
	}
	return true;
}

//页面头部
function show_header() {
	global $_SGLOBAL, $nowarr, $step, $theurl, $_SC,$nextim_version;

	$nowarr[$step] = ' class="current"';
	print<<<END
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title> NextIm {$nextim_version} 透明幻想(Transparent Fantasy)版本程序安装 </title>
	<style type="text/css">
	* {font-size:12px; font-family: Verdana, Arial, Helvetica, sans-serif; line-height: 1.5em; word-break: break-all; }
	body { text-align:center; margin: 0; padding: 0; background: #F5FBFF; }
	.bodydiv { margin: 40px auto 0; width:720px; text-align:left; border: solid #86B9D6; border-width: 5px 1px 1px; background: #FFF; }
	h1 { font-size: 18px; margin: 1px 0 0; line-height: 50px; height: 50px; background: #E8F7FC; color: #5086A5; padding-left: 10px; }
	#menu {width: 100%; margin: 10px auto; text-align: center; }
	#menu td { height: 30px; line-height: 30px; color: #999; border-bottom: 3px solid #EEE; }
	.current { font-weight: bold; color: #090 !important; border-bottom-color: #F90 !important; }
	.showtable { width:100%; border: solid; border-color:#86B9D6 #B2C9D3 #B2C9D3; border-width: 3px 1px 1px; margin: 10px auto; background: #F5FCFF; }
	.showtable td { padding: 3px; }
	.showtable strong { color: #5086A5; }
	.datatable { width: 100%; margin: 10px auto 25px; }
	.datatable td { padding: 5px 0; border-bottom: 1px solid #EEE; }
	input { border: 1px solid #B2C9D3; padding: 5px; background: #F5FCFF; }
	.button { margin: 10px auto 20px; width: 100%; }
	.button td { text-align: center; }
	.button input, .button button { border: solid; border-color:#F90; border-width: 1px 1px 3px; padding: 5px 10px; color: #090; background: #FFFAF0; cursor: pointer; }
	#footer { font-size: 10px; line-height: 40px; background: #E8F7FC; text-align: center; height: 38px; overflow: hidden; color: #5086A5; margin-top: 20px; }
	</style>
	<script type="text/javascript">
	function $(id) {
		return document.getElementById(id);
	}
	function dosubmit(){
		$('ext_file_path').name = "ext_file_path['$display_name']";
		$('ext_url_path').name = "ext_url_path['$display_name']";
	}

	//添加Select选项
	function addoption(obj) {
		if (obj.value=='addoption') {
			var newOption=prompt('请输入:','');
			if (newOption!=null && newOption!='') {
				var newOptionTag=document.createElement('option');
				newOptionTag.text=newOption;
				newOptionTag.value=newOption;
				try {
					obj.add(newOptionTag, obj.options[0]); // doesn't work in IE
				}
				catch(ex) {
					obj.add(newOptionTag, obj.selecedIndex); // IE only
				}
				obj.value=newOption;
			} else {
				obj.value=obj.options[0].value;
			}
		}
	}
	function plat_toggle(){
		var tmp = $('ext_plat');
		var showflag = tmp.style.display;
		tmp.style.display = (showflag == "none")?"":"none";
	}
	</script>
	</head>
	<body id="append_parent">
	<div class="bodydiv">
	<h1>NextIm {$nextim_version}版本程序安装 </h1>
	<div style="width:90%;margin:0 auto;">
	<table id="menu">
	<tr>
	<td{$nowarr[0]}>1.安装开始</td>
	<td{$nowarr[1]}>2.基本配置</td>
	<td{$nowarr[2]}>3.完成安装</td>
	</tr>
	</table>
END;
}



//页面顶部
function show_footer() {
	print<<<END
	</div>
	<iframe id="phpframe" name="phpframe" width="0" height="0" marginwidth="0" frameborder="0" src="about:blank"></iframe>
	<div id="footer">&copy; <a target="_blank" href="http://www.nextim.cn">WEBIM20.CN</a> Inc.2007-2009 <a target="_blank" href="http://www.nextim.cn">www.nextim.cn</a></div>
	</div>
	<br>
	</body>
	</html>
END;
}



//提示 显示
function show_msg($message, $next=0, $jump=0) {
	global $theurl;

	$nextstr = '';
	$backstr = '';

	//obclean();

	if(empty($next)) {
		$backstr .= "<a href=\"javascript:history.go(-1);\">返回上一步</a>";
	} elseif ($next == 999) {
	} elseif(is_numeric($next))
	{
		$url_forward = "$theurl?step=$next";
		if($jump) {
			$nextstr .= "<a id=\"nextstepa\" href=\"$url_forward\">请稍等...</a><script>setTimeout(\"window.location.href ='$url_forward';\", 1000);</script>";
		} else {
			$nextstr .= "<a id=\"nextstepa\" href=\"$url_forward\">继续下一步</a>";
			$backstr .= "<a href=\"javascript:history.go(-1);\">返回上一步</a>";
		}
		$nextstr .= "<script>var nextsteph=document.getElementById('nextstepa').href;var useold=document.getElementsByName('useold');
		for(var i in useold)
		if(useold[i].checked==true)document.getElementById('nextstepa').href=nextsteph+'&useold='+useold[i].value;
		</script>";
	}

	show_header();
	print<<<END
	<table>
	<tr>安装程序遇到以下错误：</tr>
	<tr><td><span style="color:red">$message</span></td></tr>
	<tr><td>错误代码：<span style="color:blue">$next</span></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td>$backstr $nextstr</td></tr>
	</table>
END;
	show_footer();
	exit();
}


function insertconfig($s, $find, $replace) {
	if(preg_match($find, $s)) {
		$s = preg_replace($find, $replace, $s);
	} else {
		$s .= "\r\n".$replace;
	}
	return $s;
}

function write_webim_config($file,$domain,$apikey,$theme,$charset,$broadcastid=null) {
	global $url_path, $file_path, $platform,$nextim_version;
    	foreach($file_path as &$var){ 
        	$var = str_replace("\\", '/', $var); $var = str_replace("//", '/', $var);
	}
	/*
	if(isset($file_path["uchome"])){
		$uchome_path = $file_path['uchome'];
		$uchome_url = $url_path['uchome'];
	}
	if(isset($file_path["discuz"])){
		$discuz_path = $file_path['discuz'];
		$discuz_url = $url_path['discuz'];
	}
	*/
	if($platform == "uchome"){
		$install_url = $url_path['uchome'];
		$install_path = $file_path['uchome'];
	}else if($platform == "discuz"){
		$install_url = $url_path['discuz'];
		$install_path = $file_path['discuz'];
	}

	$fp = fopen($file, 'r');
	$configfile = fread($fp, filesize($file));
	$configfile = trim($configfile);
	$configfile = substr($configfile, -2) == '?>' ? substr($configfile, 0, -2) : $configfile;
	fclose($fp);

    	$configfile = insertconfig($configfile, '/\<\?php/i', '<?php');
	$configfile = insertconfig($configfile, '/\$_IMC = array\(\);/i', '$_IMC = array();');
	$configfile = insertconfig($configfile, '/\$_IMC\["enable"\] =\s*.*?;/i', '$_IMC["enable"] = true;');
	$configfile = insertconfig($configfile, '/\$_IMC\["domain"\] =\s*".*?";/i', '$_IMC["domain"] = "'.$domain.'";');
	$configfile = insertconfig($configfile, '/\$_IMC\["apikey"\] =\s*".*?";/i', '$_IMC["apikey"] = "'.$apikey.'";');
	$configfile = insertconfig($configfile, '/\$_IMC\["imsvr"\] =\s*".*?";/i', '$_IMC["imsvr"] = "im.nextim.cn";');
	$configfile = insertconfig($configfile, '/\$_IMC\["impost"\] =\s*.*?;/i', '$_IMC["impost"] = 9000;');
	$configfile = insertconfig($configfile, '/\$_IMC\["impoll"\] =\s*.*?;/i', '$_IMC["impoll"] = 8000;');
	$configfile = insertconfig($configfile, '/\$_IMC\["theme"\] =\s*".*?";/i', '$_IMC["theme"] = "'.$theme.'";');
	$configfile = insertconfig($configfile, '/\$_IMC\["local"\] =\s*".*?";/i', '$_IMC["local"] = "'.substr($charset,0,5).'";');
	$configfile = insertconfig($configfile, '/\$_IMC\["charset"\] =\s*".*?";/i', '$_IMC["charset"] = "'.$charset.'";');
	$configfile = insertconfig($configfile, '/\$_IMC\["buddy_name"\] =\s*".*?";/i', '$_IMC["buddy_name"] = "username";');
	$configfile = insertconfig($configfile, '/\$_IMC\["room_id_pre"\] =\s*.*?;/i', '$_IMC["room_id_pre"] = 1000000;');
	$configfile = insertconfig($configfile, '/\$_IMC\["groupchat"\] =\s*.*?;/i', '$_IMC["groupchat"] = true;');
	$configfile = insertconfig($configfile, '/\$_IMC\["emot"\] =\s*".*?";/i', '$_IMC["emot"] = "default";');
	$configfile = insertconfig($configfile, '/\$_IMC\["opacity"\] =\s*.*?;/i', '$_IMC["opacity"] = 80;');
	//$configfile = insertconfig($configfile, '/\$_IMC\["uchome_path"\] =\s*.*?;/i', '$_IMC["uchome_path"] = "'.$uchome_path.'";');
	//$configfile = insertconfig($configfile, '/\$_IMC\["uchome_url"\] =\s*.*?;/i', '$_IMC["uchome_url"] = "'.$uchome_url.'";');
	//$configfile = insertconfig($configfile, '/\$_IMC\["discuz_path"\] =\s*.*?;/i', '$_IMC["discuz_path"] = "'.$discuz_path.'";');
	//$configfile = insertconfig($configfile, '/\$_IMC\["discuz_url"\] =\s*.*?;/i', '$_IMC["discuz_url"] = "'.$discuz_url.'";');
	$configfile = insertconfig($configfile, '/\$_IMC\["install_url"\] =\s*.*?;/i', '$_IMC["install_url"] = "'.$install_url.'";');
	$configfile = insertconfig($configfile, '/\$_IMC\["install_path"\] =\s*.*?;/i', '$_IMC["install_path"] = "'.$install_path.'";');
	$configfile = insertconfig($configfile, '/\$_IMC\["version"\] =\s*.*?;/i', '$_IMC["version"] = "'.$nextim_version.'";');
	$configfile = insertconfig($configfile, '/\$_IMC\["update"\] =\s*.*?;/i', '$_IMC["update"] = 0;');
	$configfile = insertconfig($configfile, '/\$_IMC_LOG_TYPE\["update_file"\] =\s*.*?;/i', '$_IMC_LOG_TYPE["update_file"] = "UPDATE";');
	$configfile = insertconfig($configfile, '/\$_IMC_LOG_TYPE\["backup_project"\] =\s*.*?;/i', '$_IMC_LOG_TYPE["backup_project"] = "BACKUP";');
	$configfile = insertconfig($configfile, '/\$_IMC_LOG_FILE\["name"\] =\s*.*?;/i', '$_IMC_LOG_FILE["name"] = "./update.log";');
	$configfile = insertconfig($configfile, '/\$_IMC_BACKUP\["director"\] =\s*.*?;/i', '$_IMC_BACKUP["director"] = "../WEBIM_BAK";');
	$configfile = insertconfig($configfile, '/\$_IMC\["update_url"\] =\s*.*?;/i', '$_IMC["update_url"] = "http://update.nextim.cn/";');
	$configfile = insertconfig($configfile, '/\$_IMC\["admin_ids"\] =\s*.*?;/i', '$_IMC["admin_ids"] = "' . $broadcastid . '";');
	$configfile = insertconfig($configfile, '/\$_IMC\["timestamp"\] =\s*.*?;/i', '$_IMC["timestamp"] = 10;');
	$fp = fopen($file, 'w');
	if(!($fp = @fopen($file, 'w'))) {
		show_msg('请确认文件夹webim可');
	}
	@fwrite($fp, trim($configfile));
	@fclose($fp);
}

function write_template(){
	global $templete_folder;
	global $file_path;
    	global $platform;
	$path = S_ROOT;
	if($platform === "uchome" || $platform === "discuz"){
		foreach($file_path as $key=>$path){
			@$fp = fopen($path. $templete_folder . 'footer.htm', 'r');
			$fileLen = filesize($path . $templete_folder . 'footer.htm');
			$htmfile = fread($fp, $fileLen);
			$htmfile = trim($htmfile);
			list($htmfile, $foot) = explode("</body>", $htmfile);
			fclose($fp);
			
			if (strpos($htmfile, '<!--{template webim_') === false)
			{
				$htmfile .= "\r\n"."<!--{template webim_{$platform}}-->\r\n</body>".$foot;
				@$fp = fopen($path . $templete_folder . 'footer.htm', 'w');
				fwrite($fp, trim($htmfile));
				fclose($fp);
			}
		}
	}else if($platform === "phpwind"){
		// non-realize
	}
}

/*
function write_ext_config($file) {
	global $file_path, $platform;
	$fp = fopen($file, 'r');
	$configfile = fread($fp, filesize($file));
	$configfile = trim($configfile);
	$configfile = substr($configfile, -2) == '?>' ? substr($configfile, 0, -2) : $configfile;
	fclose($fp);

	//$configfile = insertconfig($configfile, '.*', "include_once '".$file_path['$platform']."webim".DIRECTORY_SEPARATOR."config.php';");
	$fp = fopen($file, 'w');
	if(!($fp = @fopen($file, 'w'))) {
		show_msg('请确认 文件夹webim 可写');
	}
	@fwrite($fp, trim($configfile));
	@fclose($fp);
}
*/

function write_basic_config($file) { # do not use in PHPWIND Install
	global $file_path, $platform;
	$fp = fopen($file, 'r');
	$configfile = fread($fp, filesize($file));
	$configfile = trim($configfile);
	$configfile = substr($configfile, -2) == '?>' ? substr($configfile, 0, -2) : $configfile;
	fclose($fp);

	if(strpos($configfile, 'webim'.DIRECTORY_SEPARATOR.'config.php') === false)
	{
		$configfile = insertconfig($configfile, '.*', "include_once '".$file_path['$platform']."webim".DIRECTORY_SEPARATOR."config.php';");
		$fp = fopen($file, 'w');
		if(!($fp = @fopen($file, 'w'))) {
			show_msg('请确认文件 config.php 可写', $ERRORCODE['can_not_write_file']);
		}
		@fwrite($fp, trim($configfile));
		@fclose($fp);
	}
}

?>
