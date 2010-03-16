<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: index.php 13234 2009-08-24 08:20:04Z liguode $
*/

define('IN_UCHOME', TRUE);

error_reporting(0);
$_SGLOBAL = $_SCONFIG = $_SBLOCK = array();

//程序目录
define('S_ROOT', substr(dirname(__FILE__), 0, -7));

//获取时间
$_SGLOBAL['timestamp'] = time();

include_once(S_ROOT.'./source/function_common.php');
if(!@include_once(S_ROOT.'./config.php')) {
	@include_once(S_ROOT.'./config.new.php');
	show_msg('您需要首先将程序根目录下面的 "config.new.php" 文件重命名为 "config.php"', 999);
}

//GPC过滤
if(!(get_magic_quotes_gpc())) {
	$_GET = saddslashes($_GET);
	$_POST = saddslashes($_POST);
}

//启用GIP
if ($_SC['gzipcompress'] && function_exists('ob_gzhandler')) {
	ob_start('ob_gzhandler');
} else {
	ob_start();
}

$formhash = formhash();

$theurl = 'index.php';
$sqlfile = S_ROOT.'./data/install.sql';
if(!file_exists($sqlfile)) {
	show_msg('请上传最新的 install.sql 数据库结构文件到程序的 ./data 目录下面，再重新运行本程序', 999);
}
$configfile = S_ROOT.'./config.php';

//变量
$step = empty($_GET['step'])?0:intval($_GET['step']);
$action = empty($_GET['action'])?'':trim($_GET['action']);
$nowarr = array('','','','','','','');

$lockfile = S_ROOT.'./data/install.lock';
if(file_exists($lockfile)) {
	show_msg('警告!您已经安装过UCenter Home<br>
		为了保证数据安全，请立即手动删除 install/index.php 文件<br>
		如果您想重新安装UCenter Home，请删除 data/install.lock 文件，并到UCenter后台应用管理处删除该应用，再运行安装文件');
}

//检查config是否可写
if(!@$fp = fopen($configfile, 'a')) {
	show_msg("文件 $configfile 读写权限设置错误，请设置为可写，再执行安装程序");
} else {
	@fclose($fp);
}

//提交处理
if (submitcheck('ucsubmit')) {

	//安装UC配置
	$step = 1;

	//判断域名是否解析
	$ucapi = preg_replace("/\/$/", '', trim($_POST['ucapi']));
	$ucip = trim($_POST['ucip']);

	if(empty($ucapi) || !preg_match("/^(http:\/\/)/i", $ucapi)) {
		show_msg('UCenter的URL地址不正确');
	} else {
		//检查服务器 dns 解析是否正常, dns 解析不正常则要求用户输入ucenter的ip地址
		if(!$ucip) {
			$temp = @parse_url($ucapi);
			$ucip = gethostbyname($temp['host']);
			if(ip2long($ucip) == -1 || ip2long($ucip) === FALSE) {
				$ucip = '';
			}
		}
	}

	//验证UCHome是否安装
	if(!@include_once S_ROOT.'./uc_client/client.php') {
		show_msg('uc_client目录不存在，请上传安装包中的 ./upload/uc_client 到程序根目录');
	}
	$ucinfo = uc_fopen2($ucapi.'/index.php?m=app&a=ucinfo&release='.UC_CLIENT_RELEASE, 500, '', '', 1, $ucip);
	list($status, $ucversion, $ucrelease, $uccharset, $ucdbcharset, $apptypes) = explode('|', $ucinfo);
	$dbcharset = strtolower(trim($_SC['dbcharset'] ? str_replace('-', '', $_SC['dbcharset']) : $_SC['dbcharset']));
	$ucdbcharset = strtolower(trim($ucdbcharset ? str_replace('-', '', $ucdbcharset) : $ucdbcharset));
	$apptypes = strtolower(trim($apptypes));
	if($status != 'UC_STATUS_OK') {
		show_header();
		print<<<END
		<form id="theform" method="post" action="$theurl">
		<table class="datatable">
		<tr><td><strong>UCenter无法正常连接，返回错误 ( $status )，请确认UCenter的IP地址是否正确</strong><br><br></td></tr>
		<tr><td>UCenter服务器的IP地址: <input type="text" name="ucip" value="$ucip"> 例如：192.168.0.1</td></tr>
		</table>
		<table class=button>
		<tr><td>
		<input type="hidden" name="ucapi" value="$ucapi">
		<input type="hidden" name="ucfounderpw" value="$_POST[ucfounderpw]">
		<input type="submit" id="ucsubmit" name="ucsubmit" value="确认IP地址"></td></tr>
		</table>
		<input type="hidden" name="formhash" value="$formhash">
		</form>
END;
		show_footer();
		exit();
	} elseif($dbcharset && $ucdbcharset && $ucdbcharset != $dbcharset) {
		show_msg('UCenter 服务端字符集与当前应用的字符集不同，请下载 '.$ucdbcharset.' 编码的 UCenter Home 进行安装，下载地址：http://download.comsenz.com/');
	} elseif(strexists($apptypes, 'uchome')) {
		show_msg('已经安装过一个UCenter Home产品，如果想继续安装，请先到 UCenter 应用管理中删除已有的UCenter Home！');
	}
	$tagtemplates = 'apptagtemplates[template]='.urlencode('<a href="{url}" target="_blank">{subject}</a>').'&'.
		'apptagtemplates[fields][subject]='.urlencode('日志标题').'&'.
		'apptagtemplates[fields][uid]='.urlencode('用户 ID').'&'.
		'apptagtemplates[fields][username]='.urlencode('用户名').'&'.
		'apptagtemplates[fields][dateline]='.urlencode('日期').'&'.
		'apptagtemplates[fields][spaceurl]='.urlencode('空间地址').'&'.
		'apptagtemplates[fields][url]='.urlencode('日志地址');

	$uri = $_SERVER['REQUEST_URI']?$_SERVER['REQUEST_URI']:($_SERVER['PHP_SELF']?$_SERVER['PHP_SELF']:$_SERVER['SCRIPT_NAME']);
	$app_url = strtolower(substr($_SERVER['SERVER_PROTOCOL'], 0, strpos($_SERVER['SERVER_PROTOCOL'], '/'))).'://'.$_SERVER['HTTP_HOST'].preg_replace("/\/*install$/i", '', substr($uri, 0, strrpos($uri, '/install')));

	$postdata = "m=app&a=add&ucfounder=&ucfounderpw=".urlencode($_POST['ucfounderpw'])."&apptype=".urlencode('UCHOME')."&appname=".urlencode('个人家园')."&appurl=".urlencode($app_url)."&appip=&appcharset=".$_SC['charset'].'&appdbcharset='.$_SC['dbcharset'].'&release='.UC_CLIENT_RELEASE.'&'.$tagtemplates;
	$s = uc_fopen2($ucapi.'/index.php', 500, $postdata, '', 1, $ucip);
	if(empty($s)) {
		show_msg('UCenter用户中心无法连接');
	} elseif($s == '-1') {
		show_msg('UCenter管理员帐号密码不正确');
	} else {
		$ucs = explode('|', $s);
		if(empty($ucs[0]) || empty($ucs[1])) {
			show_msg('UCenter返回的数据出现问题，请参考:<br />'.shtmlspecialchars($s));
		} else {

			//处理成功
			$apphidden = '';
			//验证是否可以直接联接MySQL
			$link = mysql_connect($ucs[2], $ucs[4], $ucs[5], 1);
			$connect = $link && mysql_select_db($ucs[3], $link) ? 'mysql' : '';
			//返回
			foreach (array('key', 'appid', 'dbhost', 'dbname', 'dbuser', 'dbpw', 'dbcharset', 'dbtablepre', 'charset') as $key => $value) {
				if($value == 'dbtablepre') {
					$ucs[$key] = '`'.$ucs[3].'`.'.$ucs[$key];
				}
				$apphidden .= "<input type=\"hidden\" name=\"uc[$value]\" value=\"".$ucs[$key]."\" />";
			}
			//内置
			$apphidden .= "<input type=\"hidden\" name=\"uc[connect]\" value=\"$connect\" />";
			$apphidden .= "<input type=\"hidden\" name=\"uc[api]\" value=\"$_POST[ucapi]\" />";
			$apphidden .= "<input type=\"hidden\" name=\"uc[ip]\" value=\"$ucip\" />";

			show_header();
			print<<<END
			<form id="theform" method="post" action="$theurl">
			<table>
			<tr><td>UCenter注册成功！当前程序ID标识为: $ucs[1]</td></tr>
			</table>

			<table class=button>
			<tr><td>$apphidden
			<input type="submit" id="uc2submit" name="uc2submit" value="进入下一步"></td></tr>
			</table>
			<input type="hidden" name="formhash" value="$formhash">
			</form>
END;
			show_footer();
			exit();
		}
	}

} elseif (submitcheck('uc2submit')) {

	//增加congfig配置
	$step = 2;

	//写入config文件
	$configcontent = sreadfile($configfile);
	$keys = array_keys($_POST['uc']);
	foreach ($keys as $value) {
		$upkey = strtoupper($value);
		$configcontent = preg_replace("/define\('UC_".$upkey."'\s*,\s*'.*?'\)/i", "define('UC_".$upkey."', '".$_POST['uc'][$value]."')", $configcontent);
	}
	if(!$fp = fopen($configfile, 'w')) {
		show_msg("文件 $configfile 读写权限设置错误，请设置为可写后，再执行安装程序");
	}
	fwrite($fp, trim($configcontent));
	fclose($fp);

} elseif(!empty($_POST['sqlsubmit'])) {

	$step = 2;

	//先写入config文件
	$configcontent = sreadfile($configfile);
	$keys = array_keys($_POST['db']);
	foreach ($keys as $value) {
		$configcontent = preg_replace("/[$]\_SC\[\'".$value."\'\](\s*)\=\s*[\"'].*?[\"']/is", "\$_SC['".$value."']\\1= '".$_POST['db'][$value]."'", $configcontent);
	}
	if(!$fp = fopen($configfile, 'w')) {
		show_msg("文件 $configfile 读写权限设置错误，请设置为可写后，再执行安装程序");
	}
	fwrite($fp, trim($configcontent));
	fclose($fp);
	
	if(empty($_POST['db']['tablepre'])) {
		show_msg("填写的表名前缀不能为空");
	}

	//判断UCenter Home数据库
	$havedata = false;
	if(!@mysql_connect($_POST['db']['dbhost'], $_POST['db']['dbuser'], $_POST['db']['dbpw'])) {
		show_msg('数据库连接信息填写错误，请确认');
	}
	if(mysql_select_db($_POST['db']['dbname'])) {
		if(mysql_query("SELECT COUNT(*) FROM {$_POST['db']['tablepre']}space")) {
			$havedata = true;
		}
	} else {
		if(!mysql_query("CREATE DATABASE `".$_POST['db']['dbname']."`")) {
			show_msg('设定的UCenter Home数据库无权限操作，请先手工操作后，再执行安装程序');
		}
	}

	if($havedata) {
		show_msg('危险!指定的UCenter Home数据库已有数据，如果继续将会清空原有数据!', ($step+1));
	} else {
		show_msg('数据库配置成功，进入下一步操作', ($step+1), 1);
	}

} elseif (submitcheck('opensubmit')) {

	//检查用户身份
	include_once(S_ROOT.'./data/data_config.php');

	$step = 5;

	dbconnect();

	//同步获取用户源
	$_SGLOBAL['timestamp'] = time();

	//UC注册用户
	if(!@include_once S_ROOT.'./uc_client/client.php') {
		showmessage('system_error');
	}
	$uid = uc_user_register($_POST['username'], $_POST['password'], 'webmastor@yourdomain.com');
	if($uid == -3) {
		//已存在，登录
		if(!$passport = getpassport($_POST['username'], $_POST['password'])) {
			show_msg('输入的用户名密码不正确，请确认');
		}
		$setarr = array(
			'uid' => $passport['uid'],
			'username' => addslashes($passport['username'])
		);
	} elseif($uid > 0) {
		$setarr = array(
			'uid' => $uid,
			'username' => $_POST['username']
		);
	} else {
		show_msg('输入的用户名无法注册，请重新确认');
	}
	$setarr['password'] = md5("$setarr[uid]|$_SGLOBAL[timestamp]");//本地密码随机生成

	//更新本地用户库
	inserttable('member', $setarr, 0, true);

	//开通空间
	include_once(S_ROOT.'./source/function_space.php');
	$space = space_open($setarr['uid'], $_POST['username'], 1);

	//反馈受保护
	$result = uc_user_addprotected($_POST['username'], $_POST['username']);
	$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET flag=1 WHERE username='$_POST[username]'");

	//清理在线session
	insertsession($setarr);

	//设置cookie
	ssetcookie('auth', authcode("$setarr[password]\t$setarr[uid]", 'ENCODE'), 2592000);

	//写log
	if(@$fp = fopen($lockfile, 'w')) {
		fwrite($fp, 'UCenter Home');
		fclose($fp);
	}

	show_msg('<font color="red">恭喜! UCenter Home安装全部完成!</font>
		<br>为了您的数据安全，请登录ftp，删除install目录<br><br>
		您的管理员身份已经成功确认，并已经开通空间。接下来，您可以：<br>
		<br><a href="../space.php" target="_blank">进入我的空间</a>
		<br>进入我的主页，开始UCenter Home之旅
		<br><a href="../admincp.php" target="_blank">进入管理平台</a>
		<br>以管理员身份对站点参数进行设置', 999);

}

if(empty($step)) {

	show_header();

	//检查权限设置
	$checkok = true;
	$perms = array();
	if(!checkfdperm(S_ROOT.'./config.php', 1)) {
		$perms['config'] = '失败';
		$checkok = false;
	} else {
		$perms['config'] = 'OK';
	}
	if(!checkfdperm(S_ROOT.'./attachment/')) {
		$perms['attachment'] = '失败';
		$checkok = false;
	} else {
		$perms['attachment'] = 'OK';
	}
	if(!checkfdperm(S_ROOT.'./data/')) {
		$perms['data'] = '失败';
		$checkok = false;
	} else {
		$perms['data'] = 'OK';
	}
	if(!checkfdperm(S_ROOT.'./uc_client/data/')) {
		$perms['uc_data'] = '失败';
		$checkok = false;
	} else {
		$perms['uc_data'] = 'OK';
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
	<strong>欢迎您使用UCenter Home</strong><br>
	通过 UCenter Home，作为建站者的您，可以轻松构建一个以好友关系为核心的交流网络，让站点用户可以用一句话记录生活中的点点滴滴；方便快捷地发布日志、上传图片；更可以十分方便的与其好友们一起分享信息、讨论感兴趣的话题；轻松快捷的了解好友最新动态。
	<br><a href="javascript:;" onclick="readme()"><strong>请先认真阅读我们的软件使用授权协议</strong></a>
	</td></tr>
	</table>

	<table>
	</td></tr>
	<tr><td>
	<strong>文件/目录权限设置</strong><br>
	在您执行安装文件进行安装之前，先要设置相关的目录属性，以便数据文件可以被程序正确读/写/删/创建子目录。<br>
	推荐您这样做：<br>使用 FTP 软件登录您的服务器，将服务器上以下目录、以及该目录下面的所有文件的属性设置为777，win主机请设置internet来宾帐户可读写属性<br>
	<table class="datatable">
	<tr style="font-weight:bold;"><td>名称</td><td>所需权限属性</td><td>说明</td><td>检测结果</td></tr>
	<tr><td><strong>./config.php</strong></td><td>读/写</td><td>系统配置文件</td><td>$perms[config]</td></tr>
	<tr><td><strong>./attachment/</strong> (包括本目录、子目录和文件)</td><td>读/写/删</td><td>附件目录</td><td>$perms[attachment]</td></tr>
	<tr><td><strong>./data/</strong> (包括本目录、子目录和文件)</td><td>读/写/删</td><td>站点数据目录</td><td>$perms[data]</td></tr>
	<tr><td><strong>./uc_client/data/</strong> (包括本目录、子目录和文件)</td><td>读/写/删</td><td>uc_client数据目录</td><td>$perms[uc_data]</td></tr>
	</table>
	</td></tr>
	</table>
END;

	if(!$checkok) {
		echo "<table><tr><td><b>出现问题</b>:<br>系统检测到以上目录或文件权限没有正确设置<br>强烈建议正常设置权限后再刷新本页面以便继续安装<br>否则系统可能会出现无法预料的问题 [<a href=\"$theurl?step=1\">强制继续</a>]</td></tr></table>";
	} else {
		$ucapi = empty($_POST['ucapi'])?'/':$_POST['ucapi'];
		$ucfounderpw = empty($_POST['ucfounderpw'])?'':$_POST['ucfounderpw'];
		print <<<END
		<form id="theform" method="post" action="$theurl?step=1">
			<table class=button>
				<tr>
					<td><input type="submit" id="startsubmit" name="startsubmit" value="接受授权协议，开始安装UCenter Home"></td>
				</tr>
			</table>
			<input type="hidden" name="ucapi" value="$ucapi" />
			<input type="hidden" name="ucfounderpw" value="$ucfounderpw" />
			<input type="hidden" name="formhash" value="$formhash">
		</form>
END;
	}

	print<<<END
	<table id="tbl_readme" style="display:none;" class="showtable">
	<tr>
	<td><strong>请您务必仔细阅读下面的许可协议:</strong> </td></tr>
	<tr>
	<td>
	<div>中文版授权协议 适用于中文用户
	<p>版权所有 (C) 2001-2009，康盛创想（北京）科技有限公司<br>保留所有权利。
	</p><p>感谢您选择 UCenter Home。希望我们的努力能为您提供一个强大的社会化网络(SNS)解决方案。通过 UCenter Home，建站者可以轻松构建一个以好友关系为核心的交流网络，让站点用户可以用一句话记录生活中的点点滴滴；方便快捷地发布日志、上传图片；更可以十分方便的与其好友们一起分享信息、讨论感兴趣的话题；轻松快捷的了解好友最新动态。
	</p><p>康盛创想（北京）科技有限公司为 UCenter Home 产品的开发商，依法独立拥有 UCenter Home 产品著作权（中国国家版权局 著作权登记号 2006SR12091）。康盛创想（北京）科技有限公司网址为
	http://www.comsenz.com，UCenter Home 官方网站网址为 http://u.discuz.net。
	</p><p>UCenter Home 著作权已在中华人民共和国国家版权局注册，著作权受到法律和国际公约保护。使用者：无论个人或组织、盈利与否、用途如何
	（包括以学习和研究为目的），均需仔细阅读本协议，在理解、同意、并遵守本协议的全部条款后，方可开始使用 UCenter Home 软件。
	</p><p>康盛创想（北京）科技有限公司拥有对本授权协议的最终解释权。
	<ul type=i>
	<p>
	<li><b>协议许可的权利</b>
	<ul type=1>
	<li>您可以在完全遵守本最终用户授权协议的基础上，将本软件应用于非商业用途，而不必支付软件版权授权费用。
	<li>您可以在协议规定的约束和限制范围内修改 UCenter Home 源代码(如果被提供的话)或界面风格以适应您的网站要求。
	<li>您拥有使用本软件构建的站点中全部会员资料、文章及相关信息的所有权，并独立承担与文章内容的相关法律义务。
	<li>获得商业授权之后，您可以将本软件应用于商业用途，同时依据所购买的授权类型中确定的技术支持期限、技术支持方式和技术支持内容，
	自购买时刻起，在技术支持期限内拥有通过指定的方式获得指定范围内的技术支持服务。商业授权用户享有反映和提出意见的权力，相关意见
	将被作为首要考虑，但没有一定被采纳的承诺或保证。 </li></ul>
	<p></p>
	<li><b>协议规定的约束和限制</b>
	<ul type=1>
	<li>未获商业授权之前，不得将本软件用于商业用途（包括但不限于企业网站、经营性网站、以营利为目或实现盈利的网站）。购买商业授权请登陆http://www.discuz.com参考相关说明，也可以致电8610-51657885了解详情。
	<li>不得对本软件或与之关联的商业授权进行出租、出售、抵押或发放子许可证。
	<li>无论如何，即无论用途如何、是否经过修改或美化、修改程度如何，只要使用 UCenter Home 的整体或任何部分，未经书面许可，程序页面页脚处
	的 UCenter Home 名称和康盛创想（北京）科技有限公司下属网站（http://www.comsenz.com、http://u.discuz.net） 的 链接都必须保留，而不能清除或修改。
	<li>禁止在 UCenter Home 的整体或任何部分基础上以发展任何派生版本、修改版本或第三方版本用于重新分发。
	<li>如果您未能遵守本协议的条款，您的授权将被终止，所被许可的权利将被收回，并承担相应法律责任。 </li></ul>
	<p></p>
	<li><b>有限担保和免责声明</b>
	<ul type=1>
	<li>本软件及所附带的文件是作为不提供任何明确的或隐含的赔偿或担保的形式提供的。
	<li>用户出于自愿而使用本软件，您必须了解使用本软件的风险，在尚未购买产品技术服务之前，我们不承诺提供任何形式的技术支持、使用担保，
	也不承担任何因使用本软件而产生问题的相关责任。
	<li>康盛创想（北京）科技有限公司不对使用本软件构建的站点中的文章或信息承担责任。 </li></ul></li></ul>
	<p>有关 UCenter Home 最终用户授权协议、商业授权与技术服务的详细内容，均由 UCenter Home 官方网站独家提供。康盛创想（北京）科技有限公司拥有在不 事先通知的情况下，修改授权协议和服务价目表的权力，修改后的协议或价目表对自改变之日起的新授权用户生效。
	<p>电子文本形式的授权协议如同双方书面签署的协议一样，具有完全的和等同的法律效力。您一旦开始安装 UCenter Home，即被视为完全理解并接受本协议的各项条款，在享有上述条款授予的权力的同时，受到相关的约束和限制。协议许可范围以外的行为，将直接违反本授权协议并构成侵权，我们有权随时终止授权，责令停止损害，并保留追究相关责任的权力。 </p></div>
	</td></tr>
	</table>
END;

	show_footer();

} elseif($step == 1) {

	show_header();
	$ucapi = "http://";
	$ucfounderpw = '';
	$showdiv = 0;
	if($_POST['ucfounderpw']) {
		$showdiv = 1;
		$ucapi = trim($_POST['ucapi']);
		$ucfounderpw = trim($_POST['ucfounderpw']);
	}

	if($showdiv) {
		print<<<END
		<form id="theform" method="post" action="$theurl">
		<div>
			<table class="showtable">
				<tr><td><strong># UCenter 参数自动获取</strong></td></tr>
				<tr><td id="msg2">UCenter的相关信息已成功获取，请直接点击下面的按钮提交配置</td></tr>
			</table>
			<br/>
		</div>
		<div>
END;
	} else {
		$plus = '';
		if(!$ucfounderpw) {
			$plus = '<tr><td id="msg2">
					使用UCenter Home，首先需要您的站点安装有统一存储用户帐号信息的UCenter用户中心系统。<br>
					如果您的站点还没有安装过UCenter，请这样操作：<br>
					1. <a href="http://download.comsenz.com/UCenter/" target="_blank"><b>请点击这里下载最新版本的UCenter</b></a>，并阅读程序包中的说明进行UCenter的安装。<br>
					2. 安装完毕 UCenter 后，在下面填入UCenter的相关信息即可继续进行UCenter Home 的安装。<br>
				</td></tr>';
		}
		print<<<END
		<form id="theform" method="post" action="$theurl">
		<div>
			<table class="showtable">
				<tr><td><strong># 请填写 UCenter 的相关参数</strong></td></tr>
				$plus
			</table>
			<br>
			<p style="font-weight:bold;">请输入已安装UCenter的信息:</p>
END;
	}
	print<<<END
		<table class=datatable>
			<tbody>
				<tr>
					<td>UCenter 的 URL:</td>
					<td><input type="text" id="ucapi" name="ucapi" size="60" value="$ucapi"><br>例如：http://www.discuz.net/ucenter</td>
				</tr>
				<tr>
					<td>UCenter 的创始人密码:</td>
					<td><input type="password" id="ucfounderpw" name="ucfounderpw" size="20" value="$ucfounderpw"></td>
				</tr>
			</tbody>
		</table>
		<br>
	</div>
	<table class=button>
	<tr><td><input type="submit" id="ucsubmit" name="ucsubmit" value="提交UCenter配置信息"></td></tr>
	</table>
	<input type="hidden" id="ucfounder" name="ucfounder" size="20" value="">
	<input type="hidden" name="formhash" value="$formhash">
	</form>
END;
	show_footer();

} elseif ($step == 2) {

	//检测目录属性
	show_header();
	//设置数据库配置
	print<<<END
	<form id="theform" method="post" action="$theurl">

	<table class="showtable">
	<tr><td><strong># 设置UCenter Home数据库信息</strong></td></tr>
	<tr><td id="msg1">这里设置UCenter Home的数据库信息</td></tr>
	</table>
	<table class=datatable>
	<tr>
	<td width="25%">数据库服务器本地地址:</td>
	<td><input type="text" name="db[dbhost]" size="20" value="localhost"></td>
	<td width="30%">一般为localhost</td>
	</tr>
	<tr>
	<td>数据库用户名:</td>
	<td><input type="text" name="db[dbuser]" size="20" value=""></td>
	<td>&nbsp;</td>
	</tr>
	<tr>
	<td>数据库密码:</td>
	<td><input type="password" name="db[dbpw]" size="20" value=""></td>
	<td>&nbsp;</td>
	</tr>
	<tr>
	<td>数据库字符集:</td>
	<td>
	<select name="db[dbcharset]" onchange="addoption(this)">
	<option value="$_SC[dbcharset]">$_SC[dbcharset]</option>
	<option value="addoption" class="addoption">+自定义</option>
	</select>
	</td>
	<td>MySQL版本>4.1有效</td>
	</tr>
	<tr>
	<td>数据库名:</td>
	<td><input type="text" name="db[dbname]" size="20" value=""></td>
	<td>如果不存在，则会尝试自动创建</td>
	</tr>
	<tr>
	<td>表名前缀:</td>
	<td><input type="text" name="db[tablepre]" size="20" value="uchome_"></td>
	<td>不能为空，默认为uchome_</td>
	</tr>
	</table>

	<table class=button>
	<tr><td><input type="submit" id="sqlsubmit" name="sqlsubmit" value="设置完毕,检测我的数据库配置"></td></tr>
	</table>
	<input type="hidden" name="formhash" value="$formhash">
	</form>
END;
	show_footer();

} elseif ($step == 3) {

	//链接数据库
	dbconnect();

	//安装数据库
	//获取最新的sql文
	$newsql = sreadfile($sqlfile);

	if($_SC['tablepre'] != 'uchome_') $newsql = str_replace('uchome_', $_SC['tablepre'], $newsql);//替换表名前缀

	//获取要创建的表
	$tables = $sqls = array();
	if($newsql) {
		preg_match_all("/(CREATE TABLE ([a-z0-9\_\-`]+).+?\s*)(TYPE|ENGINE)+\=/is", $newsql, $mathes);
		$sqls = $mathes[1];
		$tables = $mathes[2];
	}
	if(empty($tables)) {
		show_msg("安装SQL语句获取失败，请确认SQL文件 $sqlfile 是否存在");
	}

	$heaptype = $_SGLOBAL['db']->version()>'4.1'?" ENGINE=MEMORY".(empty($_SC['dbcharset'])?'':" DEFAULT CHARSET=$_SC[dbcharset]" ):" TYPE=HEAP";
	$myisamtype = $_SGLOBAL['db']->version()>'4.1'?" ENGINE=MYISAM".(empty($_SC['dbcharset'])?'':" DEFAULT CHARSET=$_SC[dbcharset]" ):" TYPE=MYISAM";
	$installok = true;
	foreach ($tables as $key => $tablename) {
		if(strpos($tablename, 'session')) {
			$sqltype = $heaptype;
		} else {
			$sqltype = $myisamtype;
		}
		$_SGLOBAL['db']->query("DROP TABLE IF EXISTS `$tablename`");
		if(!$query = $_SGLOBAL['db']->query($sqls[$key].$sqltype, 'SILENT')) {
			$installok = false;
			break;
		}
	}
	if(!$installok) {
		show_msg("<font color=\"blue\">数据表 ($tablename) 自动安装失败</font><br />反馈: ".mysql_error()."<br /><br />请参照 $sqlfile 文件中的SQL文，自己手工安装数据库后，再继续进行安装操作<br /><br /><a href=\"?step=$step\">重试</a>");
	} else {
		show_msg('数据表已经全部安装完成，进入下一步操作', ($step+1), 1);
	}

} elseif ($step == 4) {

	//插入默认数据
	dbconnect();
	$privacy = array(
		'view' => array(
			'index' => 0,
			'profile' => 0,
			'friend' => 0,
			'wall' => 0,
			'feed' => 0,
			'mtag' => 0,
			'event' => 0,
			'doing' => 0,
			'blog' => 0,
			'album' => 0,
			'share' => 0,
			'poll' => 0
		),
		'feed' => array(
			'doing' => 1,
			'blog' => 1,
			'upload' => 1,
			'share' => 1,
			'poll' => 1,
			'joinpoll' => 1,
			'thread' => 1,
			'post' => 1,
			'mtag' => 1,
			'event' => 1,
			'join' => 1,
			'friend' => 1,
			'comment' => 1,
			'show' => 1,
			'spaceopen' => 1,
			'credit' => 1,
			'invite' => 1,
			'task' => 1,
			'profile' => 1,
			'album' => 1,
			'click' => 1
		)
	);
	//config
	$datas = array(
		"('sitename', '我的空间')",
		"('template', 'default')",
		"('adminemail', 'webmaster@".$_SERVER['HTTP_HOST']."')",
		"('onlinehold', '1800')",
		"('timeoffset', '8')",
		"('maxpage', '100')",
		"('starcredit', '100')",
		"('starlevelnum', '5')",
		"('cachemode', 'database')",
		"('cachegrade', '0')",
		"('allowcache', '1')",
		"('allowdomain', '0')",
		"('allowrewrite', '0')",
		"('allowwatermark', '0')",
		"('allowftp', '0')",
		"('holddomain', 'www|*blog*|*space*|x')",
		"('mtagminnum', '5')",
		"('feedday', '7')",
		"('feedmaxnum', '100')",
		"('feedfilternum', '10')",
		"('importnum', '100')",
		"('maxreward', '10')",
		"('singlesent', '50')",
		"('groupnum', '8')",
		"('closeregister', '0')",
		"('closeinvite', '0')",
		"('close', '0')",
		"('networkpublic', '1')",
		"('networkpage', '1')",
		"('seccode_register', '1')",
		"('uc_tagrelated', '1')",
		"('manualmoderator', '1')",
		"('linkguide', '1')",
		"('showall', '1')",
		"('sendmailday', '0')",
		"('realname', '0')",
		"('namecheck', '0')",
		"('namechange', '0')",
		"('name_allowviewspace', '1')",
		"('name_allowfriend', '1')",
		"('name_allowpoke', '1')",
		"('name_allowdoing', '1')",
		"('name_allowblog', '0')",
		"('name_allowalbum', '0')",
		"('name_allowthread', '0')",
		"('name_allowshare', '0')",
		"('name_allowcomment', '0')",
		"('name_allowpost', '0')",
		"('showallfriendnum', '10')",
		"('feedtargetblank', '1')",
		"('feedread', '1')",
		"('feedhotnum', '3')",
		"('feedhotday', '2')",
		"('feedhotmin', '3')",
		"('feedhiddenicon', 'friend,profile,task,wall')",
		"('uc_tagrelatedtime', '86400')",
		"('privacy', '".serialize($privacy)."')",
		"('cronnextrun', '$_SGLOBAL[timestamp]')",
		"('my_status', '0')",
		"('maxreward', '10')",
		"('uniqueemail', '1')",
		"('updatestat', '1')",
		"('my_showgift', '1')",
		"('topcachetime', '60')",
		"('newspacenum', '3')"
	);
	$_SGLOBAL['db']->query("TRUNCATE TABLE ".tname('config'));
	$_SGLOBAL['db']->query("REPLACE INTO ".tname('config')." (var, datavalue) VALUES ".implode(',', $datas));

	//profield
	$datas = array(
		"('自由联盟', 'text', '100', '0', '1')",
		"('地区联盟', 'text', '100', '0', '1')",
		"('兴趣联盟', 'text', '100', '0', '1')"
	);
	$_SGLOBAL['db']->query("TRUNCATE TABLE ".tname('profield'));
	$_SGLOBAL['db']->query("REPLACE INTO ".tname('profield')." (title,formtype,inputnum,manualmoderator,manualmember) VALUES ".implode(',', $datas));

	//用户组
	$datas = array();
	$datas['grouptitle'] = array('站点管理员', '信息管理员', '贵宾VIP', '受限会员', '普通会员', '中级会员', '高级会员', '禁止发言', '禁止访问');

	//核心设置
	$datas['gid'] = array(1, 2, 3, 4, 5, 6, 7, 8, 9);
	$datas['system'] = array(-1, -1, 1, 0, 0, 0, 0, -1, -1);
	$datas['explower'] = array(0, 0, 0, -999999999, 0, 100, 1000, 0, 0);
	$datas['banvisit'] = array(0, 0, 0, 0, 0, 0, 0, 0, 1);
	$datas['searchignore'] = array(1, 1, 1, 0, 0, 0, 1, 0, 0);
	$datas['videophotoignore'] = array(1, 1, 0, 0, 0, 0, 0, 0, 0);
	$datas['spamignore'] = array(1, 1, 1, 0, 0, 0, 0, 0, 0);

	$datas['color'] = array('red', 'blue', 'green', '', '', '', '', '', '');
	$datas['icon'] = array('image/group/admin.gif', 'image/group/infor.gif', 'image/group/vip.gif', '', '', '', '', '', '');

	//基本设置
	$datas['maxfriendnum'] = array(0, 0, 0, 10, 100, 200, 300, 1, 1);
	$datas['maxattachsize'] = array(0, 0, 0, 10, 20, 50, 100, 1, 1);
	$datas['postinterval'] = array(0, 0, 0, 300, 60, 30, 10, 9999, 9999);
	$datas['searchinterval'] = array(0, 0, 0, 600, 60, 30, 10, 9999, 9999);
	
	$datas['verifyevent'] = array(0, 0, 0, 1, 0, 0, 0, 1, 1);

	$datas['domainlength'] = array(1, 3, 3, 0, 0, 5, 3, 99, 99);
	$datas['closeignore'] = array(1, 1, 0, 0, 0, 0, 0, 0, 0);
	$datas['seccode'] = array(0, 0, 0, 1, 0, 0, 0, 1, 1);

	$datas['allowhtml'] = array(1, 1, 1, 0, 0, 0, 1, 0, 0);
	$datas['allowcss'] = array(1, 1, 1, 0, 0, 0, 1, 0, 0);
	$datas['allowviewvideopic'] = array(1, 1, 1, 0, 0, 0, 0, 0, 0);
	
	$datas['allowtopic'] = array(1, 1, 0, 0, 0, 0, 0, 0, 0);
	$datas['allowstat'] = array(1, 1, 0, 0, 0, 0, 0, 0, 0);
	
	foreach (array('comment','blog','poll','doing','upload','share','mtag','thread','post','poke','friend','click','event','magic', 'pm', 'myop') as $value) {
		$datas['allow'.$value] = array(1, 1, 1, 0, 1, 1, 1, 0, 0);
	}

	//管理权限
	//站点设置
	foreach (array('config','usergroup','credit','profilefield','profield','censor','ad','cache','block','template','backup','stat','cron','app', 'network','name','task','report', 'eventclass', 'magic','magiclog','topic', 'batch', 'delspace', 'spacegroup', 'spaceinfo', 'spacecredit', 'spacenote', 'ip', 'hotuser', 'defaultuser', 'click', 'videophoto', 'log') as $value) {
		$datas['manage'.$value] = array(1, 0, 0, 0, 0, 0, 0, 0, 0);
	}

	//信息管理
	foreach (array('tag','mtag','feed','share','doing', 'blog','album','comment','thread', 'event', 'poll') as $value) {
		$datas['manage'.$value] = array(1, 1, 0, 0, 0, 0, 0, 0, 0);
	}

	$keys = array_keys($datas);
	$newdatas = array();
	$g_count = count($datas['grouptitle']);
	for ($i=0; $i<$g_count; $i++) {
		$thes = array();
		foreach ($keys as $value) {
			$thes[] = $datas[$value][$i];
		}
		$newdatas[$i] = "(".simplode($thes).")";
	}
	$_SGLOBAL['db']->query("TRUNCATE TABLE ".tname('usergroup'));
	$_SGLOBAL['db']->query("REPLACE INTO ".tname('usergroup')." (".implode(',', $keys).") VALUES ".implode(',', $newdatas));

	//积分规则
	$ruls = array();
	//加积分
	$ruls[] = "('开通空间', 'register', '0', '0', '1', '1', '10', '0', '0')";
	$ruls[] = "('实名认证', 'realname', '0', '0', '1', '1', '20', '0', '20')";
	$ruls[] = "('邮箱认证', 'realemail', '0', '0', '1', '1', '40', '0', '40')";
	$ruls[] = "('成功邀请好友', 'invitefriend', '4', '0', '20', '1', '10', '0', '10')";
	$ruls[] = "('设置头像', 'setavatar', '0', '0', '1', '1', '15', '0', '15')";
	$ruls[] = "('视频认证', 'videophoto', '0', '0', '1', '1', '40', '0', '40')";
	$ruls[] = "('成功举报', 'report', '4', '0', '0', '1', '2', '0', '2')";
	$ruls[] = "('更新心情', 'updatemood', '1', '0', '3', '1', '3', '0', '3')";
	$ruls[] = "('热点信息', 'hotinfo', '4', '0', '0', '1', '10', '0', '10')";
	$ruls[] = "('每天登陆', 'daylogin', '1', '0', '1', '1', '15', '0', '15')";
	$ruls[] = "('访问别人空间', 'visit', '1', '0', '10', '1', '1', '2', '1')";
	$ruls[] = "('打招呼', 'poke', '1', '0', '10', '1', '1', '2', '1')";
	$ruls[] = "('留言', 'guestbook', '1', '0', '20', '1', '2', '2', '2')";
	$ruls[] = "('被留言', 'getguestbook', '1', '0', '5', '1', '1', '2', '0')";
	$ruls[] = "('发表记录', 'doing', '1', '0', '5', '1', '1', '0', '1')";
	$ruls[] = "('发表日志', 'publishblog', '1', '0', '3', '1', '5', '0', '5')";
	$ruls[] = "('上传图片', 'uploadimage', '1', '0', '10', '1', '2', '0', '2')";
	$ruls[] = "('拍大头贴', 'camera', '1', '0', '5', '1', '3', '0', '3')";
	$ruls[] = "('发表话题', 'publishthread', '1', '0', '5', '1', '5', '0', '5')";
	$ruls[] = "('回复话题', 'replythread', '1', '0', '10', '1', '1', '1', '1')";
	$ruls[] = "('创建投票', 'createpoll', '1', '0', '5', '1', '2', '0', '2')";
	$ruls[] = "('参与投票', 'joinpoll', '1', '0', '10', '1', '1', '1', '1')";
	$ruls[] = "('发起活动', 'createevent', '1', '0', '1', '1', '3', '0', '3')";
	$ruls[] = "('参与活动', 'joinevent', '1', '0', '1', '1', '1', '1', '1')";
	$ruls[] = "('推荐活动', 'recommendevent', '4', '0', '0', '1', '10', '0', '10')";
	$ruls[] = "('发起分享', 'createshare', '1', '0', '3', '1', '2', '0', '2')";
	$ruls[] = "('评论', 'comment', '1', '0', '40', '1', '1', '1', '1')";
	$ruls[] = "('被评论', 'getcomment', '1', '0', '20', '1', '1', '1', '0')";
	$ruls[] = "('安装应用', 'installapp', '4', '0', '0', '1', '5', '3', '5')";
	$ruls[] = "('使用应用', 'useapp', '1', '0', '10', '1', '1', '3', '1')";
	$ruls[] = "('信息表态', 'click', '1', '0', '10', '1', '1', '1', '1')";
	//扣积分
	$ruls[] = "('修改实名', 'editrealname', '0', '0', '1', '0', '5', '0', '0')";
	$ruls[] = "('更改邮箱认证', 'editrealemail', '0', '0', '1', '0', '5', '0', '0')";
	$ruls[] = "('头像被删除', 'delavatar', '0', '0', '1', '0', '10', '0', '10')";
	$ruls[] = "('获取邀请码', 'invitecode', '0', '0', '1', '0', '0', '0', '0')";
	$ruls[] = "('搜索一次', 'search', '0', '0', '1', '0', '1', '0', '0')";
	$ruls[] = "('日志导入', 'blogimport', '0', '0', '1', '0', '10', '0', '0')";
	$ruls[] = "('修改域名', 'modifydomain', '0', '0', '1', '0', '5', '0', '0')";
	$ruls[] = "('日志被删除', 'delblog', '0', '0', '1', '0', '10', '0', '10')";
	$ruls[] = "('记录被删除', 'deldoing', '0', '0', '1', '0', '2', '0', '2')";
	$ruls[] = "('图片被删除', 'delimage', '0', '0', '1', '0', '4', '0', '4')";
	$ruls[] = "('投票被删除', 'delpoll', '0', '0', '1', '0', '4', '0', '4')";
	$ruls[] = "('话题被删除', 'delthread', '0', '0', '1', '0', '4', '0', '4')";
	$ruls[] = "('活动被删除', 'delevent', '0', '0', '1', '0', '6', '0', '6')";
	$ruls[] = "('分享被删除', 'delshare', '0', '0', '1', '0', '4', '0', '4')";
	$ruls[] = "('留言被删除', 'delguestbook', '0', '0', '1', '0', '4', '0', '4')";
	$ruls[] = "('评论被删除', 'delcomment', '0', '0', '1', '0', '2', '0', '2')";
	
	$_SGLOBAL['db']->query("INSERT INTO ".tname('creditrule')." (`rulename`, `action`, `cycletype`, `cycletime`, `rewardnum`, `rewardtype`, `credit`, `norepeat`, `experience`) VALUES ".implode(',', $ruls));
			
	$_SGLOBAL['db']->query("TRUNCATE TABLE ".tname('data'));
	//邮件设置
	$mails = array(
		'mailsend' => 1,
		'maildelimiter' => 0,
		'mailusername' => 1
	);
	data_set('mail', $mails);

	//缩略图设置
	$settings = array(
		'thumbwidth' => 100,
		'thumbheight' => 100,
		'watermarkpos' => 4,
		'watermarktrans' => 75
	);
	data_set('setting', $settings);
	
	//随便看看
	$network = array(
		'blog' => array('hot1'=>3, 'cache'=>600),
		'pic' => array('hot1'=>3, 'cache'=>700),
		'thread' => array('hot1'=>3, 'cache'=>800),
		'event' => array('cache'=>900),
		'poll' => array('cache'=>500),
	);
	data_set('network', $network);

	//计划任务
	$datas = array(
		"1, 'system', '更新浏览数统计', 'log.php', $_SGLOBAL[timestamp], $_SGLOBAL[timestamp], -1, -1, -1, '0	5	10	15	20	25	30	35	40	45	50	55'",
		"1, 'system', '清理过期feed', 'cleanfeed.php', $_SGLOBAL[timestamp], $_SGLOBAL[timestamp], -1, -1, 3, '4'",
		"1, 'system', '清理个人通知', 'cleannotification.php', $_SGLOBAL[timestamp], $_SGLOBAL[timestamp], -1, -1, 5, '6'",
		"1, 'system', '同步UC的feed', 'getfeed.php', $_SGLOBAL[timestamp], $_SGLOBAL[timestamp], -1, -1, -1, '2	7	12	17	22	27	32	37	42	47	52'",
		"1, 'system', '清理脚印和最新访客', 'cleantrace.php', $_SGLOBAL[timestamp], $_SGLOBAL[timestamp], -1, -1, 2, '3'"
	);
	$_SGLOBAL['db']->query("TRUNCATE TABLE ".tname('cron'));
	$_SGLOBAL['db']->query("INSERT INTO ".tname('cron')." (available, type, name, filename, lastrun, nextrun, weekday, day, hour, minute) VALUES (".implode('),(', $datas).")");

	//用户任务
	$datas = array();
	$datas[] = "1, '更新一下自己的头像', '头像就是你在这里的个人形象。<br>设置自己的头像后，会让更多的朋友记住您。', 'avatar.php', 1, '', 0, 20, 'image/task/avatar.gif'";
	$datas[] = "1, '将个人资料补充完整', '把自己的个人资料填写完整吧。<br>这样您会被更多的朋友找到的，系统也会帮您找到朋友。', 'profile.php', '', 2, 0, 20, 'image/task/profile.gif'";
	$datas[] = "1, '发表自己的第一篇日志', '现在，就写下自己的第一篇日志吧。<br>与大家一起分享自己的生活感悟。', 'blog.php', 3, '', 0, 5, 'image/task/blog.gif'";
	$datas[] = "1, '寻找并添加五位好友', '有了好友，您发的日志、图片等会被好友及时看到并传播出去；<br>您也会在首页方便及时的看到好友的最新动态。', 'friend.php', 4, '', 0, 50, 'image/task/friend.gif'";
	$datas[] = "1, '验证激活自己的邮箱', '填写自己真实的邮箱地址并验证通过。<br>您可以在忘记密码的时候使用该邮箱取回自己的密码；<br>还可以及时接受站内的好友通知等等。', 'email.php', 5, '', 0, 10, 'image/task/email.gif'";
	$datas[] = "1, '邀请10个新朋友加入', '邀请一下自己的QQ好友或者邮箱联系人，让亲朋好友一起来加入我们吧。', 'invite.php', 6, '', 0, 100, 'image/task/friend.gif'";
	$datas[] = "1, '领取每日访问大礼包', '每天登录访问自己的主页，就可领取大礼包。', 'gift.php', 99, 'day', 0, 5, 'image/task/gift.gif'";

	$_SGLOBAL['db']->query("TRUNCATE TABLE ".tname('task'));
	$_SGLOBAL['db']->query("INSERT INTO ".tname('task')." (`available`, `name`, `note`, `filename`, `displayorder`, `nexttype`, `nexttime`, `credit`, `image`) VALUES (".implode('),(', $datas).")");

	//活动分类
	$datas = array(
		"1, '生活/聚会', 0, '费用说明：\r\n集合地点：\r\n着装要求：\r\n联系方式：\r\n注意事项：', 1",
		"2, '出行/旅游', 0, '路线说明:\r\n费用说明:\r\n装备要求:\r\n交通工具:\r\n集合地点:\r\n联系方式:\r\n注意事项:', 2",
		"3, '比赛/运动', 0, '费用说明：\r\n集合地点：\r\n着装要求：\r\n场地介绍：\r\n联系方式：\r\n注意事项：', 4",
		"4, '电影/演出', 0, '剧情介绍：\r\n费用说明：\r\n集合地点：\r\n联系方式：\r\n注意事项：', 3",
		"5, '教育/讲座', 0, '主办单位：\r\n活动主题：\r\n费用说明：\r\n集合地点：\r\n联系方式：\r\n注意事项：', 5",
		"6, '其它', 0, '', 6"
	);	
	$_SGLOBAL['db']->query("TRUNCATE TABLE ".tname('eventclass'));
	$_SGLOBAL['db']->query("INSERT INTO ".tname('eventclass')." (classid, classname, poster, template, displayorder) VALUES (".implode('),(', $datas).")");
	
	//道具
	$datas = array();
	$datas[] = "'invisible', '隐身草', '让自己隐身登录，不显示在线，24小时内有效', '5', '50', '86400', '10', '86400', '1'";
	$datas[] = "'friendnum', '好友增容卡', '在允许添加的最多好友数限制外，增加10个好友名额', '3', '30', '86400', '999', '0', '1'";
	$datas[] = "'attachsize', '附件增容卡', '使用一次，可以给自己增加 10M 的附件空间', '3', '30', '86400', '999', '0', '1'";
	$datas[] = "'thunder', '雷鸣之声', '发布一条全站信息，让大家知道自己上线了', '5', '500', '86400', '5', '86400', '1'";
	$datas[] = "'updateline', '救生圈', '把指定对象的发布时间更新为当前时间', '5', '200', '86400', '999', '0', '1'";
		
	$datas[] = "'downdateline', '时空机', '把指定对象的发布时间修改为过去的时间', '5', '250', '86400', '999', '0', '1'";		
	$datas[] = "'color', '彩色灯', '把指定对象的标题变成彩色的', '5', '50', '86400', '999', '0', '1'";
	$datas[] = "'hot', '热点灯', '把指定对象的热度增加站点推荐的热点值', '5', '50', '86400', '999', '0', '1'";
	$datas[] = "'visit', '互访卡', '随机选择10个好友，向其打招呼、留言或访问空间', '2', '20', '86400', '999', '0', '1'";
	$datas[] = "'icon', '彩虹蛋', '给指定对象的标题前面增加图标（最多8个图标）', '2', '20', '86400', '999', '0', '1'";
		
	$datas[] = "'flicker', '彩虹炫', '让评论、留言的文字闪烁起来', '3', '30', '86400', '999', '0', '1'";
	$datas[] = "'gift', '红包卡', '在自己的空间埋下积分红包送给来访者', '2', '20', '86400', '999', '0', '1'";
	$datas[] = "'superstar', '超级明星', '在个人主页，给自己的头像增加超级明星标识', '3', '30', '86400', '999', '0', '1'";
	$datas[] = "'viewmagiclog', '八卦镜', '查看指定用户最近使用的道具记录', '5', '100', '86400', '999', '0', '1'";
	$datas[] = "'viewmagic', '透视镜', '查看指定用户当前持有的道具', '5', '100', '86400', '999', '0', '1'";
		
	$datas[] = "'viewvisitor', '偷窥镜', '查看指定用户最近访问过的10个空间', '5', '100', '86400', '999', '0', '1'";
	$datas[] = "'call', '点名卡', '发通知给自己的好友，让他们来查看指定的对象', '5', '50', '86400', '999', '0', '1'";
	$datas[] = "'coupon', '代金券','购买道具时折换一定量的积分', '0', '0', '0', '0', '0', '1'";
	$datas[] = "'frame', '相框', '给自己的照片添上相框', '3', '30', '86400', '999', '0', '1'";
	$datas[] = "'bgimage', '信纸', '给指定的对象添加信纸背景', '3', '30', '86400', '999', '0', '1'";
		
	$datas[] = "'doodle', '涂鸦板', '允许在留言、评论等操作时使用涂鸦板', '3', '30', '86400', '999', '0', '1'";
	$datas[] = "'anonymous', '匿名卡', '在指定的地方，让自己的名字显示为匿名', '5', '50', '86400', '999', '0', '1'";
	$datas[] = "'reveal', '照妖镜', '可以查看一次匿名用户的真实身份', '5', '100', '86400', '999', '0', '1'";
	$datas[] = "'license', '道具转让许可证', '使用许可证，将道具赠送给指定好友', '1', '10', '3600', '999', '0', '1'";
	$datas[] = "'detector', '探测器', '探测埋了红包的空间', '1', '10', '86400', '999', '0', '1'";
	
	$_SGLOBAL['db']->query("TRUNCATE TABLE ".tname('magic'));
	$_SGLOBAL['db']->query("INSERT INTO ".tname('magic')."(`mid`, `name`, `description`, `experience`, `charge`, `provideperoid`, `providecount`, `useperoid`, `usecount`) VALUES (".implode('),(', $datas).")");		

	//表态
	$datas = array(
		"'1', '路过', 'luguo.gif', 'blogid'",
		"'2', '雷人', 'leiren.gif', 'blogid'",
		"'3', '握手', 'woshou.gif', 'blogid'",
		"'4', '鲜花', 'xianhua.gif', 'blogid'",
		"'5', '鸡蛋', 'jidan.gif', 'blogid'",
		
		"'6', '漂亮', 'piaoliang.gif', 'picid'",
		"'7', '酷毙', 'kubi.gif', 'picid'",
		"'8', '雷人', 'leiren.gif', 'picid'",
		"'9', '鲜花', 'xianhua.gif', 'picid'",
		"'10', '鸡蛋', 'jidan.gif', 'picid'",
		
		"'11', '搞笑', 'gaoxiao.gif', 'tid'",
		"'12', '迷惑', 'mihuo.gif', 'tid'",
		"'13', '雷人', 'leiren.gif', 'tid'",
		"'14', '鲜花', 'xianhua.gif', 'tid'",
		"'15', '鸡蛋', 'jidan.gif', 'tid'"
	);
	$_SGLOBAL['db']->query("TRUNCATE TABLE ".tname('click'));
	$_SGLOBAL['db']->query("INSERT INTO ".tname('click')." (clickid, `name`, icon, idtype) VALUES (".implode('),(', $datas).")");

	show_msg('系统默认数据添加完毕，进入下一步操作', ($step+1), 1);

} elseif ($step == 5) {

	//更新缓存
	dbconnect();
	include_once(S_ROOT.'./source/function_cache.php');

	config_cache();
	usergroup_cache();
	profilefield_cache();
	profield_cache();
	censor_cache();
	block_cache();
	eventclass_cache();
	magic_cache();
	click_cache();
	task_cache();
	ad_cache();
	creditrule_cache();
	userapp_cache();
	app_cache();
	network_cache();

	$msg = <<<EOF
	<form method="post" action="$theurl">
	<table>
	<tr><td colspan="2">程序数据安装完成!<br><br>
	最后，请输入您在用户中心UCenter的用户名和密码<br>系统将自动为您开通站内第一个空间，并将您设为管理员!
	</td></tr>
	<tr><td>您的用户名</td><td><input type="text" name="username" value="" size="30"></td></tr>
	<tr><td>您的密码</td><td><input type="password" name="password" value="" size="30"></td></tr>
	<tr><td></td><td><input type="submit" name="opensubmit" value="开通管理员空间"></td></tr>
	</table>
	<input type="hidden" name="formhash" value="$formhash">
	</form>
EOF;

	show_msg($msg, 999);
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
	<title> UCenter Home 程序安装 </title>
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
	</script>
	</head>
	<body id="append_parent">
	<div class="bodydiv">
	<h1>UCenter Home程序安装</h1>
	<div style="width:90%;margin:0 auto;">
	<table id="menu">
	<tr>
	<td{$nowarr[0]}>1.安装开始</td>
	<td{$nowarr[1]}>2.设置UCenter信息</td>
	<td{$nowarr[2]}>3.设置数据库连接信息</td>
	<td{$nowarr[3]}>4.创建数据库结构</td>
	<td{$nowarr[4]}>5.添加默认数据</td>
	<td{$nowarr[5]}>6.安装完成</td>
	</tr>
	</table>
END;
}

//页面顶部
function show_footer() {
	print<<<END
	</div>
	<iframe id="phpframe" name="phpframe" width="0" height="0" marginwidth="0" frameborder="0" src="about:blank"></iframe>
	<div id="footer">&copy; Comsenz Inc. 2001-2009 u.discuz.net</div>
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
			$nextstr .= "<a href=\"$url_forward\">请稍等...</a><script>setTimeout(\"window.location.href ='$url_forward';\", 1000);</script>";
		} else {
			$nextstr .= "<a href=\"$url_forward\">继续下一步</a>";
			$backstr .= "<a href=\"javascript:history.go(-1);\">返回上一步</a>";
		}
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

//检查权限
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

?>
