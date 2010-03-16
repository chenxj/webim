<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: uc.php 10988 2009-01-19 05:44:31Z zhengqingpeng $
*/

define('UC_CLIENT_VERSION', '1.5.0');	//note UCenter 版本标识
define('UC_CLIENT_RELEASE', '20081212');

define('API_DELETEUSER', 1);		//用户删除 API 接口开关
define('API_RENAMEUSER', 1);		//用户名修改 API 接口开关
define('API_GETTAG', 1);		//获取标签 API 接口开关
define('API_SYNLOGIN', 1);		//同步登录 API 接口开关
define('API_SYNLOGOUT', 1);		//同步登出 API 接口开关
define('API_UPDATEPW', 1);		//更改用户密码 开关
define('API_UPDATEBADWORDS', 1);	//更新关键字列表 开关
define('API_UPDATEHOSTS', 1);		//更新HOST文件 开关
define('API_UPDATEAPPS', 1);		//更新应用列表 开关
define('API_UPDATECLIENT', 1);		//更新客户端缓存 开关
define('API_UPDATECREDIT', 1);		//更新用户积分 开关
define('API_GETCREDIT', 1);	//向 UC 提供积分 开关
define('API_GETCREDITSETTINGS', 1);	//向 UC 提供积分设置 开关
define('API_UPDATECREDITSETTINGS', 1);	//更新应用积分设置 开关
define('API_ADDFEED', 1);	//向 UCHome 添加feed 开关

define('API_RETURN_SUCCEED', '1');
define('API_RETURN_FAILED', '-1');
define('API_RETURN_FORBIDDEN', '-2');

define('IN_UCHOME', TRUE);
define('S_ROOT', substr(dirname(__FILE__), 0, -3));

$_SGLOBAL = $_SCONFIG = $_SBLOCK = $_TPL = $_SCOOKIE = $space = array();

//获取时间
$_SGLOBAL['timestamp'] = time();

if(defined('IN_UC')) {
	
	global $_SGLOBAL, $_SCONFIG, $_SC, $space, $_SCOOKIE, $_SBLOCK, $_TPL;
	
	include_once S_ROOT.'./config.php';
	include_once S_ROOT.'./data/data_config.php';
	include_once S_ROOT.'./source/function_common.php';

	//链接数据库
	dbconnect();

} else {

	error_reporting(0);
	set_magic_quotes_runtime(0);

	defined('MAGIC_QUOTES_GPC') || define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());

	include_once S_ROOT.'./config.php';
	include_once S_ROOT.'./data/data_config.php';
	include_once S_ROOT.'./source/function_common.php';

	//链接数据库
	dbconnect();

	$get = $post = array();

	$code = @$_GET['code'];
	parse_str(authcode($code, 'DECODE', UC_KEY), $get);
	if(MAGIC_QUOTES_GPC) {
		$get = sstripslashes($get);
	}

	if($_SGLOBAL['timestamp'] - $get['time'] > 3600) {
		exit('Authracation has expiried');
	}
	if(empty($get)) {
		exit('Invalid Request');
	}

	include_once S_ROOT.'./uc_client/lib/xml.class.php';
	$post = xml_unserialize(file_get_contents('php://input'));

	if(in_array($get['action'], array('test', 'deleteuser', 'renameuser', 'gettag', 'synlogin', 'synlogout', 'updatepw', 'updatebadwords', 'updatehosts', 'updateapps', 'updateclient', 'updatecredit', 'getcredit', 'getcreditsettings', 'updatecreditsettings', 'addfeed'))) {
		$uc_note = new uc_note();
		echo $uc_note->$get['action']($get, $post);
		exit();
	} else {
		exit(API_RETURN_FAILED);
	}
}


class uc_note {

	var $dbconfig = '';
	var $db = '';
	var $tablepre = '';
	var $appdir = '';

	function _serialize($arr, $htmlon = 0) {
		if(!function_exists('xml_serialize')) {
			include_once S_ROOT.'./uc_client/lib/xml.class.php';
		}
		return xml_serialize($arr, $htmlon);
	}

	function uc_note() {
		global $_SGLOBAL, $_SC;
		$this->appdir = substr(dirname(__FILE__), 0, -3);
		$this->dbconfig = S_ROOT.'./config.php';
		$this->db = $_SGLOBAL['db'];
		$this->tablepre = $_SC['tablepre'];
	}

	function test($get, $post) {
		return API_RETURN_SUCCEED;
	}
	
	function deleteuser($get, $post) {
		global $_SGLOBAL;
		
		if(!API_DELETEUSER) {
			return API_RETURN_FORBIDDEN;
		}
	
		//note 用户删除 API 接口
		include_once S_ROOT.'./source/function_delete.php';
	
		//获得用户
		$uids = $get['ids'];
		$query = $_SGLOBAL['db']->query("SELECT uid FROM ".tname('member')." WHERE uid IN ($uids)");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			deletespace($value['uid'], 1);
		}
		return API_RETURN_SUCCEED;
	}
	
	function renameuser($get, $post) {
		global $_SGLOBAL;
		
		if(!API_RENAMEUSER) {
			return API_RETURN_FORBIDDEN;
		}
	
		//编辑用户
		$old_username = $get['oldusername'];
		$new_username = $get['newusername'];
	
		$_SGLOBAL['db']->query("UPDATE ".tname('member')." SET username='$new_username' WHERE username='$old_username'");
		$_SGLOBAL['db']->query("UPDATE ".tname('thread')." SET username='$new_username' WHERE username='$old_username'");
		$_SGLOBAL['db']->query("UPDATE ".tname('tagspace')." SET username='$new_username' WHERE username='$old_username'");
		$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET username='$new_username' WHERE username='$old_username'");
		$_SGLOBAL['db']->query("UPDATE ".tname('session')." SET username='$new_username' WHERE username='$old_username'");
		$_SGLOBAL['db']->query("UPDATE ".tname('post')." SET username='$new_username' WHERE username='$old_username'");
		$_SGLOBAL['db']->query("UPDATE ".tname('poke')." SET fromusername='$new_username' WHERE fromusername='$old_username'");
		$_SGLOBAL['db']->query("UPDATE ".tname('notification')." SET author='$new_username' WHERE author='$old_username'");
		$_SGLOBAL['db']->query("UPDATE ".tname('friend')." SET fusername='$new_username' WHERE fusername='$old_username'");
		$_SGLOBAL['db']->query("UPDATE ".tname('feed')." SET username='$new_username' WHERE username='$old_username'");
		$_SGLOBAL['db']->query("UPDATE ".tname('doing')." SET username='$new_username' WHERE username='$old_username'");
		$_SGLOBAL['db']->query("UPDATE ".tname('comment')." SET author='$new_username' WHERE author='$old_username'");
		$_SGLOBAL['db']->query("UPDATE ".tname('blog')." SET username='$new_username' WHERE username='$old_username'");
		$_SGLOBAL['db']->query("UPDATE ".tname('album')." SET username='$new_username' WHERE username='$old_username'");
		$_SGLOBAL['db']->query("UPDATE ".tname('share')." SET username='$new_username' WHERE username='$old_username'");
		$_SGLOBAL['db']->query("UPDATE ".tname('poll')." SET username='$new_username' WHERE username='$old_username'");
		$_SGLOBAL['db']->query("UPDATE ".tname('event')." SET username='$new_username' WHERE username='$old_username'");
	
		return API_RETURN_SUCCEED;
	}
	
	function gettag($get, $post) {
		global $_SGLOBAL;
		
		if(!API_GETTAG) {
			return API_RETURN_FORBIDDEN;
		}
	
		$name = trim($get['id']);
		if(empty($name) || !preg_match('/^([\x7f-\xff_-]|\w)+$/', $name) || strlen($name) > 20) {
			return API_RETURN_FAILED;
		}
	
		$tag = $_SGLOBAL['db']->fetch_array($_SGLOBAL['db']->query("SELECT * FROM ".tname('tag')." WHERE tagname='$name'"));
		if($tag['closed']) {
			return API_RETURN_FAILED;
		}
	
		$PHP_SELF = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
		$siteurl = 'http://'.$_SERVER['HTTP_HOST'].preg_replace("/\/+(api)?\/*$/i", '', substr($PHP_SELF, 0, strrpos($PHP_SELF, '/'))).'/';
	
		$query = $_SGLOBAL['db']->query("SELECT b.*
			FROM ".tname('tagblog')." tb, ".tname('blog')." b
			WHERE b.blogid=tb.blogid AND tb.tagid='$tag[tagid]' AND b.friend=0
			ORDER BY b.dateline DESC
			LIMIT 0,10");
		$bloglist = array();
		while($value = $_SGLOBAL['db']->fetch_array($query)) {
			$bloglist[] = array(
				'subject' => $value['subject'],
				'uid' => $value['uid'],
				'username' => $value['username'],
				'dateline' => $value['dateline'],
				'url' => $siteurl."space.php?uid=$value[uid]&amp;do=blog&amp;id=$value[blogid]",
				'spaceurl' => $siteurl."space.php?uid=$value[uid]"
			);
		}
	
		$return = array($name, $bloglist);
		return $this->_serialize($return, 1);
	}
	
	function synlogin($get, $post) {
		global $_SGLOBAL;
		
		if(!API_SYNLOGIN) {
			return API_RETURN_FORBIDDEN;
		}
	
		//note 同步登录 API 接口
		obclean();
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
	
		$cookietime = 31536000;
		$uid = intval($get['uid']);
		$query = $_SGLOBAL['db']->query("SELECT uid, username, password FROM ".tname('member')." WHERE uid='$uid'");
		if($member = $_SGLOBAL['db']->fetch_array($query)) {
			include_once S_ROOT.'./source/function_space.php';
			$member = saddslashes($member);
			$space = insertsession($member);
			//设置cookie
			ssetcookie('auth', authcode("$member[password]\t$member[uid]", 'ENCODE'), $cookietime);
		}
		ssetcookie('loginuser', $get['username'], $cookietime);
	}
	
	function synlogout($get, $post) {
		global $_SGLOBAL;
		
		if(!API_SYNLOGOUT) {
			return API_RETURN_FORBIDDEN;
		}
	
		//note 同步登出 API 接口
		obclean();
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
	
		clearcookie();
	}
	
	function updatepw($get, $post) {
		global $_SGLOBAL;
		
		if(!API_UPDATEPW) {
			return API_RETURN_FORBIDDEN;
		}
	
		$username = $get['username'];
		$newpw = md5(time().rand(100000, 999999));
		$_SGLOBAL['db']->query("UPDATE ".tname('member')." SET password='$newpw' WHERE username='$username'");
	
		return API_RETURN_SUCCEED;
	}
	
	function updatebadwords($get, $post) {
		global $_SGLOBAL;
		
		if(!API_UPDATEBADWORDS) {
			return API_RETURN_FORBIDDEN;
		}
	
		$data = array();
		if(is_array($post)) {
			foreach($post as $k => $v) {
				$data['findpattern'][$k] = $v['findpattern'];
				$data['replace'][$k] = $v['replacement'];
			}
		}
		$cachefile = S_ROOT.'./uc_client/data/cache/badwords.php';
		$fp = fopen($cachefile, 'w');
		$s = "<?php\r\n";
		$s .= '$_CACHE[\'badwords\'] = '.var_export($data, TRUE).";\r\n";
		fwrite($fp, $s);
		fclose($fp);
	
		return API_RETURN_SUCCEED;
	}
	
	function updatehosts($get, $post) {
		global $_SGLOBAL;
		
		if(!API_UPDATEHOSTS) {
			return API_RETURN_FORBIDDEN;
		}
	
		$cachefile = S_ROOT.'./uc_client/data/cache/hosts.php';
		$fp = fopen($cachefile, 'w');
		$s = "<?php\r\n";
		$s .= '$_CACHE[\'hosts\'] = '.var_export($post, TRUE).";\r\n";
		fwrite($fp, $s);
		fclose($fp);
	
		return API_RETURN_SUCCEED;
	}
	
	function updateapps($get, $post) {
		global $_SGLOBAL;
		
		if(!API_UPDATEAPPS) {
			return API_RETURN_FORBIDDEN;
		}
	
		$UC_API = '';
		if($post['UC_API']) {
			$UC_API = $post['UC_API'];
			unset($post['UC_API']);
		}
		
		$cachefile = S_ROOT.'./uc_client/data/cache/apps.php';
		$fp = fopen($cachefile, 'w');
		$s = "<?php\r\n";
		$s .= '$_CACHE[\'apps\'] = '.var_export($post, TRUE).";\r\n";
		fwrite($fp, $s);
		fclose($fp);
		
		//配置文件
		if($UC_API && is_writeable(S_ROOT.'./config.php')) {
			$configfile = trim(file_get_contents(S_ROOT.'./config.php'));
			$configfile = substr($configfile, -2) == '?>' ? substr($configfile, 0, -2) : $configfile;
			$configfile = preg_replace("/define\('UC_API',\s*'.*?'\);/i", "define('UC_API', '$UC_API');", $configfile);
			if($fp = @fopen(S_ROOT.'./config.php', 'w')) {
				@fwrite($fp, trim($configfile));
				@fclose($fp);
			}
		}
		return API_RETURN_SUCCEED;
	}
	
	function updateclient($get, $post) {
		global $_SGLOBAL;
		
		if(!API_UPDATECLIENT) {
			return API_RETURN_FORBIDDEN;
		}
	
		$cachefile = S_ROOT.'./uc_client/data/cache/settings.php';
		$fp = fopen($cachefile, 'w');
		$s = "<?php\r\n";
		$s .= '$_CACHE[\'settings\'] = '.var_export($post, TRUE).";\r\n";
		fwrite($fp, $s);
		fclose($fp);
	
		return API_RETURN_SUCCEED;
	}

	function updatecredit($get, $post) {
		global $_SGLOBAL;
		
		if(!API_UPDATECREDIT) {
			return API_RETURN_FORBIDDEN;
		}
	
		$amount = $get['amount'];
		$uid = intval($get['uid']);
	
		$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET credit=credit+'$amount' WHERE uid='$uid'");
	
		return API_RETURN_SUCCEED;
	}
	
	function getcredit($get, $post) {
		global $_SGLOBAL;
		
		if(!API_GETCREDIT) {
			return API_RETURN_FORBIDDEN;
		}
	
		$uid = intval($get['uid']);
		$credit = getcount('space', array('uid'=>$uid), 'credit');
		return $credit;
	}
	
	function getcreditsettings($get, $post) {
		global $_SGLOBAL;
		
		if(!API_GETCREDITSETTINGS) {
			return API_RETURN_FORBIDDEN;
		}
	
		$credits = array();
		$credits[1] = array(lang('credit'), lang('credit_unit'));
	
		return $this->_serialize($credits);
	}
	
	function updatecreditsettings($get, $post) {
		global $_SGLOBAL;
		
		if(!API_UPDATECREDITSETTINGS) {
			return API_RETURN_FORBIDDEN;
		}
	
		$outextcredits = array();
	
		foreach($get['credit'] as $appid => $credititems) {
			if($appid == UC_APPID) {
				foreach($credititems as $value) {
					$outextcredits[$value['appiddesc'].'|'.$value['creditdesc']] = array(
						'creditsrc' => $value['creditsrc'],
						'title' => $value['title'],
						'unit' => $value['unit'],
						'ratio' => $value['ratio']
					);
				}
			}
		}
	
		$cachefile = S_ROOT.'./uc_client/data/cache/creditsettings.php';
		$fp = fopen($cachefile, 'w');
		$s = "<?php\r\n";
		$s .= '$_CACHE[\'creditsettings\'] = '.var_export($outextcredits, TRUE).";\r\n";
		fwrite($fp, $s);
		fclose($fp);
	
		return API_RETURN_SUCCEED;
	}
	
	function addfeed($get, $post) {
		global $_SGLOBAL;
		
		if(!API_ADDFEED) {
			return API_RETURN_FORBIDDEN;
		}
		
		$_SGLOBAL['supe_uid'] = intval($post['uid']);
		$_SGLOBAL['supe_username'] = trim($post['username']);
		
		$images = array($post['image_1'],$post['image_2'],$post['image_3'],$post['image_4']);
		$image_links = array($post['image_1_link'],$post['image_2_link'],$post['image_3_link'],$post['image_4_link']);
		
		include_once(S_ROOT.'./source/function_cp.php');
		return feed_add($post['icon'], $post['title_template'], $post['title_data'], $post['body_template'], $post['body_data'], $post['body_general'], $images, $image_links, $post['target_ids'], '', $post['appid']);
	}
}