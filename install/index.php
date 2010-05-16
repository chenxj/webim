<?php

define('S_ROOT', substr(dirname(__FILE__), 0, -13));
$platform = which_platform();
$nextim_version="2.2.28";

if(file_exists('../../data/sql_config.php')){
    $platform = "phpwind";
}
if(file_exists('../../data/cache/')){
    $platform = "uchome";
}
if(file_exists('../../forumdata')){
    $platform = "discuz";
}
switch($platform){
    case 'phpwind':
        include_once(S_ROOT.'./global.php');
	$cache_path = "data/sql_config.php";
       	include("common.php"); 
	$display_name = 'phpwind';
        $basic_configfile = S_ROOT.'./data/sql_config.php';
	$templete_folder = 'template/wind/';
	include_once($basic_configfile);
        break;
    case 'uchome':
	$cache_path = "data/tpl_cache";
        define('IN_UCHOME', TRUE);
        $basic_configfile = S_ROOT.'config.php';
        include_once(S_ROOT.'./config.php');
        include_once(S_ROOT.'./source/function_common.php');
        $display_name = 'uchome';
	$templete_folder = 'template/default/';
        break;
    case 'discuz':
        define('IN_DISCUZ', TRUE);
        $basic_configfile = S_ROOT.'config.inc.php';
        include_once(S_ROOT.'./include/common.inc.php');
        if (file_exists('../lib/discuz_function.php'))
		{
			include_once('../lib/discuz_function.php');
		}
		else if (file_exists('../discuz_function.php'))
		{
			include_once('../discuz_function.php');
		}
        $_SC['gzipcompress'] = true;
        $_SC['tablepre']=$tablepre;
        $_SC['dbcharset']=$dbcharset;
        $_SC['charset']='utf-8';
        $display_name = 'discuz';
	$cache_path = "forumdata/cache";
	$templete_folder = 'templates/default/';
        break;
}

$platform = which_platform();
//timestamp
$_SGLOBAL['timestamp'] = time();

$ERRORCODE = array(
	'no_error' => '0x00000000',
	'all_ready_installed' => '0x00000001',
	'can_not_write_dir' => '0x00000002',
	'can_not_write_file' => '0x00000003',
	'file_not_exist' => '0x00000004',
	'invalid_input' => '0x00000005',
	'create_table_error' => '0x00000006'
	);

if(file_exists(S_ROOT.'/data/webiminstall.lock')) {
	show_msg('您已经安装过IM,如果需要重新安装，请先删除文件 uchome根目录/data/webiminstall.lock', $ERRORCODE['all_ready_installed']);
}

if(file_exists(S_ROOT.'/forumdata/webiminstall.lock')) {
	show_msg('您已经安装过IM,如果需要重新安装，请先删除文件 discuz根目录/forumdata/webiminstall.lock', $ERRORCODE['all_ready_installed']);
}


//Add by Harvey.
$writeable = array(
	$basic_configfile,
	S_ROOT . $templete_folder, 
	S_ROOT . $templete_folder . 'footer.htm', 
	S_ROOT . $cache_path);


//test file and dir is writeable
foreach($writeable as $path)
{
	if ( !is_writeable($path) )
	{
		if(is_dir($path))
		{
			show_msg('目录' . $path . '不可写，请修改该文件或者文件夹的权限为777，然后重新运行此安装程序。', 
				$ERRORCODE['can_not_write_dir']);
		}
		else
		{
			show_msg('文件' . $path . '不可写，请修改该文件或者文件夹的权限为777，然后重新运行此安装程序。', 
				$ERRORCODE['can_not_write_file']);
		}
	}
}

$url_path = $file_path = array();
list($url_path[$platform],$else) = explode('/webim/', "http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);   //平台请求路径
$url_path[$platform] .= "/";
$file_path[$platform] = S_ROOT;//$_ROOT 平台文件夹路径

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
//$formhash = formhash();

if(!file_exists($sqlfile)) {
	show_msg('/webim/install/data/webim.sql 数据库初始化文件不存在，请检查你的安装文件', $ERRORCODE['file_not_exist']);
}

$webim_configfile = S_ROOT.'/webim/config.php';

//variables
$step = empty($_GET['step'])?0:intval($_GET['step']);
$action = empty($_GET['action'])?'':trim($_GET['action']);
$nowarr = array('','','','');

//检查config是否可写
if(!@$fp = fopen($webim_configfile, 'a')) {
	show_msg("请设置 "  .   S_ROOT .  "webim"  .   "及其子目录 为777权限，再执行安装程序", $ERRORCODE['can_not_write_file']);
	#show_msg("文件 $webim_configfile 读写权限设置错误，请设置为可写，再执行安装程序", $ERRORCODE['can_not_write_file']);
} else {
	@fclose($fp);
}
if(!@$fp = fopen($basic_configfile, 'a')) {
	show_msg("文件 $baisc_configfile 读写权限设置错误，请设置为可写，再执行安装程序", $ERRORCODE['can_not_write_file']);
} else {
	@fclose($fp);
}

if($step == 2)
{
	global $platform;
	if($platform == 'uchome'){
		dbconnect();
	}
	$domain = trim($_POST['domain']);
	$apikey = trim($_POST['apikey']);
	$theme = trim($_POST['theme']);
	$charset = trim($_POST['charset']);
	$broadcastid = trim($_POST['broadcastID']);
	
	/*
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
	*/

	if(empty($domain) || empty($apikey)) {
		show_msg('网站域名和API KEY不能为空',$ERRORCODE['invalid_input']);
	} else {
		if($platform != "phpwind"){
			write_basic_config($basic_configfile);
		}else if($platform === "phpwind"){
			write_global();
		}
		write_webim_config($webim_configfile,$domain,$apikey,$theme,$charset,$broadcastid);
		write_template();// write template htm file
		/*
		if($display_name == "uchome"){
			write_ext_config($file_path['uchome']."config.php");
		}else if($display_name == "discuz"){
			write_ext_config($file_path['discuz']."config.inc.php");
		}
		*/
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
			else if($platform == 'discuz' || $platform == 'phpwind'){
				$tablename = 'webim_histories';//$_SC['tablepre'].'im_histories';
                                if($platform == 'discuz'){
        				$tablestatus = $db->fetch_first("SHOW TABLE STATUS LIKE '$tablename'");
        			}else{
        			        $tablestatus = $db->query("SHOW TABLE STATUS LIKE '$tablename'");
        			}
				if($tablestatus){ 
					$tblexist = true;
				}else{
					$tblexist = false;
				}
			}
		}
		
		//////////////////////////////////////
		// install database
		
		if($platform == 'uchome'){
			dbconnect();
			$newsql = sreadfile($sqlfile);    
			if($_SC['tablepre'] != 'uchome_') $newsql = str_replace('uchome_', $_SC['tablepre'], $newsql);
		}else if($platform == 'discuz' || $platform =='phpwind'){
			$newsql = file_get_contents($sqlfile);
		}
		$tables = $sqls = array();
		if($newsql) {
			preg_match_all("/(CREATE TABLE ([a-z0-9\_\-`]+).+?\s*)(TYPE|ENGINE)+\=/is", $newsql, $mathes);
			$sqls = $mathes[1];
			$tables = $mathes[2];
		}
		$alltables = "";
		if($platform == 'uchome'){
			$myisamtype = $_SGLOBAL['db']->version()>'4.1'?" ENGINE=MYISAM".(empty($_SC['dbcharset'])?'':" DEFAULT CHARSET=$_SC[dbcharset]" ):" TYPE=MYISAM";
			foreach ($tables as $key => $tablename) {
				$sqltype = $myisamtype;
				$_SGLOBAL['db']->query("DROP TABLE IF EXISTS $tablename");
				if(!$query = $_SGLOBAL['db']->query($sqls[$key].$sqltype, 'SILENT')) {
					show_msg("安装数据库表格".$tablename."失败！<br />", $ERRORCODE['create_table_error']);
					exit;
				}
				$alltables .= '<li>';
				$alltables .= $tablename;
				$alltables .= '<br>';
				$alltables .= '</li>';
			}
		}else if($platform == 'discuz' || $platform == 'phpwind'){
			$myisamtype = mysql_get_server_info()>'4.1'?" ENGINE=MYISAM".(empty($_SC['dbcharset'])?'':" DEFAULT CHARSET=$_SC[dbcharset]" ):" TYPE=MYISAM";
			foreach ($tables as $key => $tablename) {
				$sqltype = $myisamtype;
				$db->query("DROP TABLE IF EXISTS $tablename");
				if(!$query = $db->query($sqls[$key].$sqltype, 'SILENT')) {
					show_msg("安装数据库表格".$tablename."失败！<br />" . mysql_error(), $ERRORCODE['create_table_error']);
					exit;
				}
				$alltables .= $tablename;
				$alltables .= '<br>';
			}
		}
		//check old file and copy webim_$platform.htm;
		if (file_exists(S_ROOT . $templete_folder . "webim_$platform.htm"))
		{
			unlink(S_ROOT . $templete_folder . "webim_$platform.htm");
		}

        if (file_exists("webim_$platform.htm")){
            copy("webim_$platform.htm", S_ROOT . $templete_folder . "webim_$platform.htm");
        }else{
            show_msg("找不到文件：" . S_ROOT . "webim/webim_$platform.htm", $ERRORCODE['file_not_exist']);
        }
		
		//delete cache files
		$handle = opendir(S_ROOT . $cache_path);
		while (($file=readdir($handle)) != false)
		{
			if (!is_dir(S_ROOT . $cache_path . '/' . $file))
			{
				unlink(S_ROOT . $cache_path . '/' . $file);
			}
		}
		
		//create lock file
		if($platform == 'uchome'){
			@touch(S_ROOT.'./data/webiminstall.lock');
		}else if($platform == 'discuz'){
			@touch(S_ROOT.'./forumdata/webiminstall.lock');
		}else if($platform == 'phpwind'){
			@touch(S_ROOT.'./data/webiminstall.lock');
		}
	
		obclean();
		$step = 2;
		show_header();
		print <<<EOF
	<h1>NextIm配置安装成功，请点击确定按钮完成安装。</h1>
<ul>
新安装数据库：
	$alltables
<br>
新增配置文件：
<li>
	webim/config.php
</li>
</br>
<pre>
备注:
    如果您使用的不是默认模板,
            1.<font color="red">复制</font>webim/install/webim_{$platform}.htm到template/<您使用的模板目录>
            2.修改template/<您使用的模板目录>/footer.htm,在 &lt;/body&gt;前加入<font color="red">&lt;!--{template webim_{$platform}}--&gt;</font>
</pre>
</ul>
	<p style="text-align:center">
	<table class=button>
	<tr><td><a href="../../"><input type="button" value="确定" style="cursor:pointer;" onclick="window.location.href='../'" /></a></td></tr>
	</table>
	</p>
EOF;
show_footer();
exit;	
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
	<strong>NextIm是UCHome社区最出色的、技术架构最先进的WEBIM插件!</strong><br>
	<p>NextIm让您的UCHome网站拥有校内网、同学网、Facebook一样出色的WEBIM!</p>
	<p>专为UCHome1.5定制开发的WEBIM插件，采用与Facebook一样的标准HTML界面设计(没有任何Flash)，可以与UCHOME1.5网站无缝整合，让UC好友间自由的在线聊天，增加网站的用户粘合度。</p>
	<p>Facebook IM相似的技术架构，单服务器100,000并发用户支持，集群服务器1,000,000万并发用户支持，支持以SaaS服务模式提供，安装简单方便。 </p>
	<a href="http://www.nextim.cn" target="_blank"><strong>您可以登录NextIm运营站了解详细</strong></a>
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
		$broadcastid = empty($_POST['broadcastID']) ? '' : $_POST['broadcastID'];
		//$ext_url_path = empty($_POST['ext_url_path']) ? '' : $_POST['ext_url_path'][$display_name];
		//$ext_file_path = empty($_POST['ext_file_path']) ? '' : $_POST['ext_file_path'][$display_name];
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
		<input type="hidden" name="broadcastID" value="$broadcastid" />
		<input type="hidden" name="formhash" value="$formhash">
		<input type="step" name="formhash" value="2">
		</form>
END;
	}
	show_footer();
} elseif($step == 1) {
	show_header();
	$domain = '';
	$apikey= '';
	$plus = '<tr><td id="msg2"> 配置网站域名和API KEY，您需要在<a href="http://www.nextim.cn" TARGET="_blank">NextIm运营站点</a>注册并获得API KEY。 </td></tr>';
	print<<<END
	<form id="theform" method="post" action="$theurl?step=2">
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
	<td>Domain:</td>
	<td>
		<input type="text" id="domain" name="domain" size="60" value="$domain">
		<span style="color:red">
			<br>
			NextIm的即时消息，是通过我们专门提供的服务器转发出去的。<br>
			<span style="color:blue">Domain</span>就是您在<a href="http://www.nextim.cn" TARGET="_blank">www.nextim.cn</a>
			上登记的网站域名。<br>
			<span style="color:blue">ApiKey</span>就是<a href="http://www.nextim.cn" TARGET="_blank">www.nextim.cn</a>
			为您生成的Apikey。<br>
			如果您还没有登记您的站点，请登陆<a href="http://www.nextim.cn" TARGET="_blank">www.nextim.cn</a>
			登记您的站点域名。<br>
		</span> 
	</td>
	</tr>
	<tr>
	<td>ApiKey:</td>
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
	<tr>
					<td>广播ID(以逗号隔开):</td>
					<td>
						<input type="text"  id="broadcastID" name="broadcastID" size="60" value="1,8,888">
						<span style="color:red">
							<br>NextIm允许使用站点广播功能。启用了广播功能的用户，能够发送广播消息。<br>广播消息将会被站点所有的用户接收。<br>
							如填写：1,8,888，代表用户id为1，8以及888的用户拥有广播的权限。<br>
						</span> 
					</td>
	</tr>
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
                &lt;!--{template webim_$platform}--&gt;
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

?>
