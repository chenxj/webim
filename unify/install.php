<?php
$_SGLOBAL = $_SCONFIG = $_SBLOCK = array();
//安装平台根目录
define('S_ROOT', substr(dirname(__FILE__), 0, -5));
$platform = which_platform();
switch($platform){
case 'uchome':
	define('IN_UCHOME', TRUE);
	$basic_configfile = S_ROOT.'./config.php';
	include_once(S_ROOT.'./config.php');
	include_once(S_ROOT.'./source/function_common.php');
	$display_name = 'discuz';
	break;
case 'discuz':
	define('IN_DISCUZ', TRUE);
	$basic_configfile = S_ROOT.'./config.inc.php';
	include_once(S_ROOT.'./include/common.inc.php');
	include_once('./discuz_function.php');
	$_SC['gzipcompress'] = true;
	$_SC['tablepre']=$tablepre;
	$_SC['dbcharset']=$dbcharset;
	$_SC['charset']='utf-8';
	$display_name = 'uchome';
	break;
}
//timestamp
$_SGLOBAL['timestamp'] = time();

if(file_exists(S_ROOT.'./data/webiminstall.lock')) {
	show_msg('您已经安装过IM,如果需要重新安装，请先删除文件 webiminstall.lock', 999);
}

if(file_exists(S_ROOT.'./forumdata/webiminstall.lock')) {
	show_msg('您已经安装过IM,如果需要重新安装，请先删除文件 webiminstall.lock', 999);
}

function which_platform(){
	/*
	*  check the platform 
	*  Uchome ? Discuz ?  PhpWind?
	*
	*/
	if(file_exists(S_ROOT.'./data')){
		return "uchome";
	}
	else if(file_exists(S_ROOT.'./forumdata')){
		return "discuz";
	}
}
///
//$display_name = '';
//用于在安装界面显示用户需要输入路径的另一平台名称
///
$url_path = $file_path = array();
list($url_path[$platform],$else) = explode('/webim/', "http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);   //平台请求路径
//$url_path[$platform] = $url_path[$platform] . "/";
$url_path[$platform] .= "/";
$file_path[$platform] = S_ROOT;//$_ROOT 平台文件夹路径
//$config_file_path = $file_path[0].'./webim/config.php';//IM 配置文件绝对路径 已被 webim_configfile 替代

//GPC filter
if(!(get_magic_quotes_gpc())) {
	$_GET = saddslashes($_GET);
	$_POST = saddslashes($_POST);
}

//enable GIP
if ($_SC['gzipcompress'] && function_exists('ob_gzhandler')) {
	ob_start('ob_gzhandler');
} else {
	ob_start();
}

header("content-type:text/html; charset=utf-8");
$formhash = formhash();

$theurl = 'install.php';
$sqlfile = S_ROOT.'./webim/data/webim.sql';
if(!file_exists($sqlfile)) {
	show_msg('./webim/data/webim.sql 数据库初始化文件不存在，请检查你的安装文件', 999);
}

$webim_configfile = S_ROOT.'./webim/config.php';

//variables
$step = empty($_GET['step'])?0:intval($_GET['step']);
$action = empty($_GET['action'])?'':trim($_GET['action']);
$nowarr = array('','','','');

//检查config是否可写
if(!@$fp = fopen($webim_configfile, 'a')) {
	show_msg("文件 $webim_configfile 读写权限设置错误，请设置为可写，再执行安装程序");
} else {
	@fclose($fp);
}
if(!@$fp = fopen($basic_configfile, 'a')) {
	show_msg("文件 $baisc_configfile 读写权限设置错误，请设置为可写，再执行安装程序");
} else {
	@fclose($fp);
}

if (submitcheck('imsubmit')) {
	//install
	$step = 1;
	if($platform == 'uchome'){
		dbconnect();
	}
	$domain = trim($_POST['domain']);
	$apikey = trim($_POST['apikey']);
	$theme = trim($_POST['theme']);
	$charset = trim($_POST['charset']);
	foreach($_POST['ext_url_path'] as $key=>$value){
		if(!endsWith($value, '/')){
			$value = trim($value).'/';
		}
		$url_path[trim($key)] = trim($value);
	}
	foreach($_POST['ext_file_path'] as $key=>$value){
		if(!endsWith($value, '/')){
			$value = trim($value).'/';
		}
		$file_path[trim($key)] = trim($value);
	}

	if(empty($domain) || empty($apikey)) {
		show_msg('网站域名和API KEY不能为空');
	} else {
		write_basic_config($basic_configfile);
		write_webim_config($webim_configfile,$domain,$apikey,$theme,$charset);
		write_template();// write template htm file
		if($display_name == "uchome"){
			write_ext_config($file_path['uchome']."config.php");
		}else if($display_name == "discuz"){
			write_ext_config($file_path['discuz']."config.inc.php");
		}
		$newsql = file_get_contents($sqlfile);
		if($platform == 'uchome'){
			if($_SC['tablepre'] != 'uchome_') $newsql = str_replace('uchome_', $_SC['tablepre'], $newsql);
		}

		$tables = $sqls = array();
		$tblexist = false;
		if($newsql) {
			if($platform == 'uchome'){
				preg_match_all("/(CREATE TABLE ([a-z0-9\_\-`]+).+?\s*)(TYPE|ENGINE)+\=/is", $newsql, $mathes);
				$tables = $mathes[2];
				foreach ($tables as $key => $tablename)
				{
					$tablestatus = mysql_fetch_assoc($_SGLOBAL['db']->query("SHOW TABLE STATUS LIKE '$tablename'"));
					if($tablestatus){
						$tblexist = true;
						break;
					}else{
						$tblexist = false;
					}
				}
			}//uchome
			else if($platform == 'discuz'){
				$tablename = 'webim_histories';//$_SC['tablepre'].'im_histories';

				$tablestatus = $db->fetch_first("SHOW TABLE STATUS LIKE '$tablename'");
				if($tablestatus){ 
					$tblexist = true;
				}else{
					$tblexist = false;
				}
			}
		}
		$msg = <<<EOF
		<h2>IM相关配置已经加入到平台根目录的config.php文件中,进入下一步:</h2>
		<b style="color:red">安装数据库</b><br>
EOF;
		if($tblexist)
		$msg .= <<<EOF
		!检测到您以前安装过IM数据库，是否重新安装（将清除以前的数据）？<br />
		重新安装<input onclick="this.checked=true;document.getElementById('useold0').checked=false;document.getElementById('nextstepa').href=nextsteph+'&useold=0'" id=useold1 type=checkbox value=0 name=useold  checked=checked>&nbsp;&nbsp;保留已有数据<input type=checkbox value=1 name=useold id=useold0 onclick="this.checked=true;document.getElementById('useold1').checked=false;document.getElementById('nextstepa').href=nextsteph+'&useold=1'">
EOF;
		show_msg($msg, ($step+1));
		exit();
	}
} 

//TODO: handle submit
if(empty($step)) {

	show_header();

	//检查权限设置
	$checkok = true;
	$perms = array();
	if(!checkfdperm($basic_configfile, 1)) {
		$perms['config'] = '失败';
		$checkok = false;
	} else {
		$perms['config'] = 'OK';
	}

	//安装阅读
	print<<<END
	<script type="text/javascript">
	function readme() {
		var tbl_readme = document.getElementById('tbl_readme');
		if(tbl_readme.style.display == '') {
			tbl_readme.style.display = 'none';
		} else {
			tbl_readme.style.display = '';
		}
	}
	</script>
	<table class="showtable">
	<tr><td>
	<strong>UCIM是UCHome社区最出色的、技术架构最先进的WEBIM插件!</strong><br>
	<p>UCIM让您的UCHome网站拥有校内网、同学网、Facebook一样出色的WEBIM!</p>
	<p>专为UCHome1.5定制开发的WEBIM插件，采用与Facebook一样的标准HTML界面设计(没有任何Flash)，可以与UCHOME1.5网站无缝整合，让UC好友间自由的在线聊天，增加网站的用户粘合度。</p>
	<p>Facebook IM相似的技术架构，单服务器100,000并发用户支持，集群服务器1,000,000万并发用户支持，支持以SaaS服务模式提供，安装简单方便。 </p>
	<a href="http://www.nextim.cn" target="_blank"><strong>您可以登录UCIM运营站了解详细</strong></a>
	</td></tr>
	</table>
END;

	if(!$checkok) {
		echo "<table><tr><td><b>出现问题</b>:<br>系统检测到以上目录或文件权限没有正确设置<br>强烈建议正常设置权限后再刷新本页面以便继续安装<br>否则系统可能会出现无法预料的问题 [<a href=\"$theurl?step=1\">强制继续</a>]</td></tr></table>";
	} else {
		$domain = empty($_POST['domain']) ? '' : $_POST['domain'];
		$apikey = empty($_POST['apikey']) ? '' : $_POST['apikey'];
		$theme = empty($_POST['theme']) ? '' : $_POST['theme'];
		$charset = empty($_POST['charset']) ? '' : $_POST['charset'];
		$ext_url_path = empty($_POST['ext_url_path']) ? '' : $_POST['ext_url_path'][$display_name];
		$ext_file_path = empty($_POST['ext_file_path']) ? '' : $_POST['ext_file_path'][$display_name];
		print <<<END
		<form id="theform" method="post" onsubmit="dosubmit()" action="$theurl?step=1">
		<table class=button>
		<tr>
		<td><input type="submit" id="startsubmit" name="startsubmit" value="开始安装"></td>
		</tr>
		</table>
		<input type="hidden" name="domain" value="$domain" />
		<input type="hidden" name="apikey" value="$apikey" />
		<input type="hidden" name="theme" value="$theme" />
		<input type="hidden" name="charset" value="$charset" />
		<input type="hidden" name="ext_url_path" value="$ext_url_path" />
		<input type="hidden" name="ext_file_path" value="$ext_file_path" />
		<input type="hidden" name="formhash" value="$formhash">
		</form>
END;
	}
	show_footer();
} elseif($step == 1) {
	show_header();
	$domain = '';
	$apikey= '';
	$plus = '<tr><td id="msg2"> 配置网站域名和API KEY，您需要在<a href="http://www.nextim.cn">UCIM运营站点</a>注册并获得API KEY。 </td></tr>';
	print<<<END
	<form id="theform" method="post" action="$theurl">
	<div>
	<table class="showtable">
	$plus
	</table>
	<br>
END;
	print<<<END
	<table class=datatable>
	<tbody>
	<tr>
	<td>网站域名:</td>
	<td><input type="text" id="domain" name="domain" size="60" value="$domain"><br>例如：www.nextim.cn</td>
	</tr>
	<tr>
	<td>API KEY:</td>
	<td><input type="text" id="apikey" name="apikey" size="60" value="$apikey"></td>
	</tr>
	<tr>
	<td>外观:</td>
	<td><select id="theme" name="theme">
	<option value="flick">flick</option>
	<option value="eggplant">eggplant</option>
	<option value="base">base</option>
	<option value="blitzer">blitzer</option>
	<option value="dark-hive">dark-hive</option>
	<option value="humanity">humanity</option>
	<option value="pepper-grinder">mint-choc</option>
	<option value="smoothness">smoothness</option>
	<option value="start">start</option>
	<option value="swanky-purse">swanky-purse</option>
	<option value="black-tie">black-tie</option>
	<option value="cupertino">cupertino</option>
	<option value="dot-luv">dot-luv</option>
	<option value="excite-bike">excite-bike</option>
	<option value="le-frog">hot-sneaks</option>
	<option value="overcast">overcast</option>
	<option value="redmond">redmond</option>
	<option value="south-street">south-street</option>
	<option value="sunny">sunny</option>
	<option value="trontastic">trontastic</option>
	<option value="ui-darkness">ui-darkness</option>
	<option value="ui-lightness">ui-lightness</option>
	<option value="vader">vader</option>
	</select>(推荐使用flick、eggplant)</td>  
	</tr>

	<tr>
	<td>语言&amp;编码:</td>
	<td><select id="charset" name="charset">
	<option value="zh-CN_gbk">简体中文（GBK）</option>
	<option value="zh-CN_utf8">简体中文（UTF-8）</option>
	<option value="zh-TW_big5">繁体中文（BIG5）</option>
	<option value="zh-TW_utf8">繁体中文（UTF-8）</option>
	<option value="en_utf8">英文（UTF-8）</option></select></td>
	</tr>
		<!--		<tr>
					<td>广播权限用户ID(以逗号隔开):</td>
					<td><input type="text"  name="broadcastID" size="60" value=""></td>
				</tr>			<td colspan=2>如果您需要安装$display_name 平台下的NextIM ,请配置以下选项</td>
				<tr>
					<td>$display_name 本地文件路径:</td>
					<td><input type="text" id="ext_file_path" name="ext_file_path" size="60" value=""></td>
				</tr>
				<tr>
					<td>$display_name URL路径:</td>
					<td><input type="text" id="ext_url_path" name="ext_url_path" size="60" value="http://"></td>
				</tr>
        -->
	</tbody>
	</table>
	<br>
	</div>
	<table class=button>
	<tr><td><input type="submit" id="imsubmit" onclick="dosubmit()" name="imsubmit" value="提交"></td></tr>
	</table>
	<input type="hidden" name="formhash" value="$formhash">
	</form>
END;
	show_footer();
} elseif ($step == 2) {

	if($platform == 'uchome'){
		dbconnect();
		$newsql = sreadfile($sqlfile);    
		if($_SC['tablepre'] != 'uchome_') $newsql = str_replace('uchome_', $_SC['tablepre'], $newsql);
	}else if($platform == 'discuz'){
		$newsql = file_get_contents($sqlfile);
	}

	$tables = $sqls = array();
	if($newsql) {
		preg_match_all("/(CREATE TABLE ([a-z0-9\_\-`]+).+?\s*)(TYPE|ENGINE)+\=/is", $newsql, $mathes);
		$sqls = $mathes[1];
		$tables = $mathes[2];
		if($platform == 'uchome'){
			var_dump($tables);
		}
	}
	if(empty($tables)) {
		show_msg("安装SQL语句获取失败  ， 请确认SQL文件 $sqlfile 是否存在");
	}
	if($platform == 'uchome'){
		$heaptype = $_SGLOBAL['db']->version()>'4.1'?" ENGINE=MEMORY".(empty($_SC['dbcharset'])?'':" DEFAULT CHARSET=$_SC[dbcharset]" ):" TYPE=HEAP";
		$myisamtype = $_SGLOBAL['db']->version()>'4.1'?" ENGINE=MYISAM".(empty($_SC['dbcharset'])?'':" DEFAULT CHARSET=$_SC[dbcharset]" ):" TYPE=MYISAM";
		$installok = true;
		$useold=$_REQUEST['useold'];
		$db_oldversion=true;
	}else if($platform == 'discuz'){
		$heaptype = mysql_get_server_info()>'4.1'?" ENGINE=MEMORY".(empty($_SC['dbcharset'])?'':" DEFAULT CHARSET=$_SC[dbcharset]" ):" TYPE=HEAP";
		$myisamtype = mysql_get_server_info()>'4.1'?" ENGINE=MYISAM".(empty($_SC['dbcharset'])?'':" DEFAULT CHARSET=$_SC[dbcharset]" ):" TYPE=MYISAM";
		$installok = true;
		$msg='';
		$useold=$_REQUEST['useold'];
		$db_oldversion=true;
		
		foreach ($tables as $key => $tablename) {
			//if(!$useold||(!$db->fetch_first("SHOW TABLE STATUS LIKE '$tablename'")))
			// {
			$sqltype = $myisamtype;
			$db->query("DROP TABLE IF EXISTS $tablename");
			if(!$query = $db->query($sqls[$key].$sqltype, 'SILENT')) {
				$installok = false;
				break;
			}else{
				$msg.= "已经创建表($tablename)<br />";
			}
			//  }
		}
	}
	if($platform == 'uchome'){
		if($useold){

			$table_desc = $_SGLOBAL['db']->query("desc `".$_SC['tablepre']."im_histories`");
			while($col=mysql_fetch_assoc($table_desc)){if($col['Field']=='fromdel')$db_oldversion=false;}
			if($db_oldversion){
				$sql="RENAME TABLE `".$_SC['tablepre']."im_histories` TO `".$_SC['tablepre']."im_histories_tmp`";
				$_SGLOBAL['db']->query($sql);
			}
		}
		foreach ($tables as $key => $tablename) {
			if(!$useold||(!@mysql_fetch_assoc($_SGLOBAL['db']->query("SHOW TABLE STATUS LIKE '$tablename'"))))
			{
				$sqltype = $myisamtype;
				$_SGLOBAL['db']->query("DROP TABLE IF EXISTS $tablename");
				if(!$query = $_SGLOBAL['db']->query($sqls[$key].$sqltype, 'SILENT')) {
					$installok = false;
					break;
				}else{
					$msg.= "已经创建表($tablename)<br />";
				}
			}
		}
		if($useold&&$db_oldversion){
			$sql="insert into `".$_SC['tablepre']."im_histories` (select *,0,0 from `".$_SC['tablepre']."im_histories_tmp`)";
			$_SGLOBAL['db']->query($sql); 

			$sql="drop table `".$_SC['tablepre']."im_histories_tmp`";
			$_SGLOBAL['db']->query($sql); 
		}
		if(!$installok) {
			show_msg("<font color=\"blue\">数据表 ($tablename) 自动安装失败</font><br />反馈: ".mysql_error()."<br /><br />请参照 $sqlfile 文件中的SQL文，自己手工安装数据库后，再继续进行安装操作<br /><br /><a href=\"?step=$step\">重试</a>");
		} else {
			$_SGLOBAL['db']->query("delete from ".tname('cron')." where filename='./../../webim/source/cron/cleanhis.php'");
			
			$datas = array(
			"1, 'system', '清理历史聊天记录', './../../webim/source/cron/cleanhis.php', $_SGLOBAL[timestamp], $_SGLOBAL[timestamp], -1, -1, 4, '0'"
			);
			$_SGLOBAL['db']->query("INSERT INTO ".tname('cron')." (available, type, name, filename, lastrun, nextrun, weekday, day, hour, minute) VALUES (".implode('),(', $datas).")");
			show_msg($msg.'<br /><b>数据表已经全部安装完成，进入下一步操作</b>', ($step+1));
		}
	}else if($platform == 'discuz'){
		if($useold){
			$table_desc = $db->query("desc `".$_SC['tablepre']."im_histories`");
			while($col=mysql_fetch_assoc($table_desc)){if($col['Field']=='fromdel')$db_oldversion=false;}
			if($db_oldversion)//`id`,`to` ,`from` ,`body` ,`style`,`timestamp` ,`todel`,`fromdel` ,`send` ,`type`
			$sql="insert into `webim_histories`(`id`,`to` ,`from` ,`body` ,`style`,`timestamp` ,`todel`,`fromdel` ,`send` ,`type`) (select `id`,`to` ,`from` ,`body` ,`style`,`timestamp`,0,0,1,'msg' from `".$_SC['tablepre']."im_histories`)";
			else
			$sql="insert into `webim_histories`(`id`,`to` ,`from` ,`body` ,`style`,`timestamp` ,`todel`,`fromdel` ,`send` ,`type`) (select `id`,`to` ,`from` ,`body` ,`style`,`timestamp`,`todel`,`fromdel`,1,'msg' from `".$_SC['tablepre']."im_histories`)";
			$db->query($sql); 
		}
		$db->query("DROP TABLE IF EXISTS `".$_SC['tablepre']."im_histories`");
		$db->query("DROP TABLE IF EXISTS `".$_SC['tablepre']."im_config`");

		if(!$installok) {
			show_msg("<font color=\"blue\">数据表 ($tablename) 自动安装失败</font><br />反馈: ".mysql_error()."<br /><br />请参照 $sqlfile 文件中的SQL文，自己手工安装数据库后，再继续进行安装操作<br /><br /><a href=\"?step=$step\">重试</a>");
		} else {

			show_msg($msg.'<br /><b>数据表已经全部安装完成，进入下一步操作</b>', ($step+1));
		}
	}
} elseif ($step == 3) {
	if($platform == 'uchome'){
        $cache_path = "data/tpl_cache";
		$tplcode = 'global $_SCOOKIE,$_IMC;<br>'.
		'if($_IMC[\'enable\'] && $_SCOOKIE[\'auth\']) {<br>'.
		'include_once(S_ROOT.\'./webim/webim_template.php\');<br>'.
		'$template = webim_template($template);<br>}';
		@touch(S_ROOT.'./data/webiminstall.lock');
		@include($basic_configfile);
	}else if($platform == 'discuz'){
        $cache_path = "forumdata/cache";
		@touch(S_ROOT.'./forumdata/webiminstall.lock');
		@include($basic_configfile);
	}
	$pathcur = getcwd();
	$msg = <<<EOF
	<h1>请继续下述配置，完成安装:</h1>
<ul>
<li>
	1. 复制 <font color="red">webim/webim_$platform .htm</font> 到 <font color="red"> $platform 平台根目录下的template/default/</font>
           修改<font color="red">template/default/footer.htm</font>
                <p>在“&lt;/body&gt;”前添加如下代码：<span  style="color:blue"><pre>
                &lt;!--{template webim_$platform }--&gt;
                </pre></span></p>
</li>
<li>
    2. 开通站长广播功能, 修改<font color="red">webim/config.php</font>,替换<span  style="color:blue">BROADCAST</span>
    为允许使用站长广播的用户ID,用“逗号”隔开.
    <p> 如 <span  style="color:blue">\$_['admin_ids'] = "1,8,888";</span>  则代表id为1 或 8 或 888的用户拥有使用站长广播的权限.</pre>

</li>
<li>
    <p></p>
	3. 删除 $platform 根目录下 $cache_path 中的模板缓存(或者通过UCenter的"更新缓存")
</li>
</ul>
	<p style="text-align:center">
	<table class=button>
	<tr><td><a href="../"><input type="button" value="完成" style="cursor:pointer;" onclick="window.location.href='../'" /></a></td></tr>
	</table>
	</p>
EOF;
	show_msg($msg, 999);
}
//}//install end

//check permission
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
	global $_SGLOBAL, $nowarr, $step, $theurl, $_SC;

	$nowarr[$step] = ' class="current"';
	print<<<END
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=$_SC[charset]" />
	<title> UCIM2.0透明幻想(Transparent Fantasy)版本程序安装 </title>
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
	<h1>UCIM2.0版本程序安装 </h1>
	<div style="width:90%;margin:0 auto;">
	<table id="menu">
	<tr>
	<td{$nowarr[0]}>1.安装开始</td>
	<td{$nowarr[1]}>2.基本配置</td>
	<td{$nowarr[2]}>3.导入数据</td>
	<td{$nowarr[3]}>4.安装完成</td>
	</tr>
	</table>
END;
}

//页面顶部
function show_footer() {
	print<<<END
	</div>
	<iframe id="phpframe" name="phpframe" width="0" height="0" marginwidth="0" frameborder="0" src="about:blank"></iframe>
	<div id="footer">&copy; <a href="http://www.nextim.cn">WEBIM20.CN</a> Inc.2007-2009 <a href="http://www.nextim.cn">www.nextim.cn</a></div>
	</div>
	<br>
	</body>
	</html>
END;
}

//显示
function show_msg($message, $next=0, $jump=0) {
	global $theurl;

	$nextstr = '';
	$backstr = '';

	obclean();
	if(empty($next)) {
		$backstr .= "<a href=\"javascript:history.go(-1);\">返回上一步</a>";
	} elseif ($next == 999) {
	} else {
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
	<tr><td>$message</td></tr>
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

function write_webim_config($file,$domain,$apikey,$theme,$charset) {
	global $url_path, $file_path, $platform;
	foreach($file_path as &$var){
		$var = str_replace('\\', '/', $var);
		$var = str_replace('//', '/', $var);
	}
	if(isset($file_path["uchome"])){
		$uchome_path = $file_path['uchome'];
		$uchome_url = $url_path['uchome'];
	}
	if(isset($file_path["discuz"])){
		$discuz_path = $file_path['discuz'];
		$discuz_url = $url_path['discuz'];
	}
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
	$configfile = insertconfig($configfile, '/\$_IMC\["admin_ids"\] =\s*.*?;/i', '$_IMC["admin_ids"] = "BROADCAST";');
	$configfile = insertconfig($configfile, '/\$_IMC\["uchome_path"\] =\s*.*?;/i', '$_IMC["uchome_path"] = "'.$uchome_path.'";');
	$configfile = insertconfig($configfile, '/\$_IMC\["uchome_url"\] =\s*.*?;/i', '$_IMC["uchome_url"] = "'.$uchome_url.'";');
	$configfile = insertconfig($configfile, '/\$_IMC\["discuz_path"\] =\s*.*?;/i', '$_IMC["discuz_path"] = "'.$discuz_path.'";');
	$configfile = insertconfig($configfile, '/\$_IMC\["discuz_url"\] =\s*.*?;/i', '$_IMC["discuz_url"] = "'.$discuz_url.'";');
	$configfile = insertconfig($configfile, '/\$_IMC\["install_url"\] =\s*.*?;/i', '$_IMC["install_url"] = "'.$install_url.'";');
	$configfile = insertconfig($configfile, '/\$_IMC\["install_path"\] =\s*.*?;/i', '$_IMC["install_path"] = "'.$install_path.'";');
	$configfile = insertconfig($configfile, '/\$_IMC\["version"\] =\s*.*?;/i', '$_IMC["version"] = "2.1.0";');
	$configfile = insertconfig($configfile, '/\$_IMC\["update"\] =\s*.*?;/i', '$_IMC["update"] = 0;');
	$configfile = insertconfig($configfile, '/\$_IMC_LOG_TYPE\["update_file"\] =\s*.*?;/i', '$_IMC_LOG_TYPE["update_file"] = "UPDATE";');
	$configfile = insertconfig($configfile, '/\$_IMC_LOG_TYPE\["backup_project"\] =\s*.*?;/i', '$_IMC_LOG_TYPE["backup_project"] = "BACKUP";');
	$configfile = insertconfig($configfile, '/\_IMC_LOG_FILE\["name"\] =\s*.*?;/i', '$_IMC_LOG_FILE["name"] = "./update.log";');
	$configfile = insertconfig($configfile, '/\$_IMC_BACKUP\["director"\] =\s*.*?;/i', '$_IMC_BACKUP["director"] = "../WEBIM_BAK";');
	$configfile = insertconfig($configfile, '/\$_IMC\["update_url"\] =\s*.*?;/i', '$_IMC["update_url"] = "http://update.nextim.cn/";');

	$fp = fopen($file, 'w');
	if(!($fp = @fopen($file, 'w'))) {
		show_msg('请确认文件 webim/config.php 可写');
	}
	@fwrite($fp, trim($configfile));
	@fclose($fp);
}

function write_template(){
	global $file_path;
	foreach($file_path as $key=>$path){
		@$fp = fopen($path.'template/default/footer.htm', 'r');
		$htmfile = fread($fp, filesize($path.'template/default/footer.htm'));
		$htmfile = trim($htmfile);
		list($htmfile, $foot) = explode("</body>", $htmfile);
		fclose($fp);
		if($key == "uchome")
			$htmfile .= "\r\n"."<!--{template webim_uchome}-->\r\n</body>".$foot;
		else
			$htmfile .= "\r\n"."<!--{template webim_discuz}-->\r\n</body>".$foot;
		@$fp = fopen($path.'template/default/footer.htm', 'w');
		fwrite($fp, trim($htmfile));
		fclose($fp);
	}
}

function write_ext_config($file) {
	global $file_path, $platform;
	$fp = fopen($file, 'r');
	$configfile = fread($fp, filesize($file));
	$configfile = trim($configfile);
	$configfile = substr($configfile, -2) == '?>' ? substr($configfile, 0, -2) : $configfile;
	fclose($fp);

	$configfile = insertconfig($configfile, '.*', "include_once '".$file_path['$platform']."webim".DIRECTORY_SEPARATOR."config.php';");
	$fp = fopen($file, 'w');
	if(!($fp = @fopen($file, 'w'))) {
		show_msg('请确认文件 config.php 可写');
	}
	@fwrite($fp, trim($configfile));
	@fclose($fp);
}

function write_basic_config($file) {
	global $file_path, $platform;
	$fp = fopen($file, 'r');
	$configfile = fread($fp, filesize($file));
	$configfile = trim($configfile);
	$configfile = substr($configfile, -2) == '?>' ? substr($configfile, 0, -2) : $configfile;
	fclose($fp);

	$configfile = insertconfig($configfile, '.*', "include_once '".$file_path['$platform']."webim".DIRECTORY_SEPARATOR."config.php';");
	$fp = fopen($file, 'w');
	if(!($fp = @fopen($file, 'w'))) {
		show_msg('请确认文件 config.php 可写');
	}
	@fwrite($fp, trim($configfile));
	@fclose($fp);
}
?>
