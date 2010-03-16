<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: function_cache.php 13229 2009-08-24 04:17:00Z zhengqingpeng $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//更新配置文件
function config_cache($updatedata=true) {
	global $_SGLOBAL;

	$_SCONFIG = array();
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('config'));
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if($value['var'] == 'privacy') {
			$value['datavalue'] = empty($value['datavalue'])?array():unserialize($value['datavalue']);
		}
		$_SCONFIG[$value['var']] = $value['datavalue'];
	}
	cache_write('config', '_SCONFIG', $_SCONFIG);

	if($updatedata) {
		$setting = data_get('setting');
		$_SGLOBAL['setting'] = empty($setting)?array():unserialize($setting);
		cache_write('setting', "_SGLOBAL['setting']", $_SGLOBAL['setting']);

		$mail = data_get('mail');
		$_SGLOBAL['mail'] = empty($mail)?array():unserialize($mail);
		cache_write('mail', "_SGLOBAL['mail']", $_SGLOBAL['mail']);

		$spam = data_get('spam');
		$_SGLOBAL['spam'] = empty($spam)?array():unserialize($spam);
		cache_write('spam', "_SGLOBAL['spam']", $_SGLOBAL['spam']);
	}
}

//更新network配置文件
function network_cache() {
	global $_SGLOBAL, $_SCONFIG;

	$setting = data_get('network');
	$_SGLOBAL['network'] = empty($setting)?array():unserialize($setting);
	cache_write('network', "_SGLOBAL['network']", $_SGLOBAL['network']);
}

//更新用户组CACHE
function usergroup_cache() {
	global $_SGLOBAL;

	$usergroup = $_SGLOBAL['grouptitle'] = array();
	$highest = true;
	$lower = '';
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('usergroup')." ORDER BY explower DESC");
	while ($group = $_SGLOBAL['db']->fetch_array($query)) {
		$group['maxattachsize'] = intval($group['maxattachsize']) * 1024 * 1024;//M
		if($group['system'] == 0) {
			//是否是最高上限
			if($highest) {
				$group['exphigher'] = 999999999;
				$highest = false;
				$lower = $group['explower'];
			} else {
				$group['exphigher'] = $lower - 1;
				$lower = $group['explower'];
			}
		}
		$group['magicaward'] = unserialize($group['magicaward']);
		
		$usergroup = array($group['gid'] => $group);
		
		$_SGLOBAL['grouptitle'][$group['gid']] = array(
			'grouptitle' => $group['grouptitle'],
			'color' => $group['color'],
			'icon' => $group['icon']
		);
		
		//生成缓存文件
		cache_write('usergroup_'.$group['gid'], "_SGLOBAL['usergroup']", $usergroup);
	}
	
	//生成缓存文件
	cache_write('usergroup', "_SGLOBAL['grouptitle']", $_SGLOBAL['grouptitle']);

}

//更新用户栏目缓存
function profilefield_cache() {
	global $_SGLOBAL;

	$_SGLOBAL['profilefield'] = array();
	$query = $_SGLOBAL['db']->query('SELECT fieldid, title, formtype, maxsize, required, invisible, allowsearch, choice FROM '.tname('profilefield')." ORDER BY displayorder");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$_SGLOBAL['profilefield'][$value['fieldid']] = $value;
	}
	cache_write('profilefield', "_SGLOBAL['profilefield']", $_SGLOBAL['profilefield']);
}

//更新群组栏目缓存
function profield_cache() {
	global $_SGLOBAL;

	$_SGLOBAL['profield'] = array();
	$query = $_SGLOBAL['db']->query('SELECT fieldid, title, formtype, inputnum, mtagminnum, manualmoderator, manualmember FROM '.tname('profield')." ORDER BY displayorder");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$_SGLOBAL['profield'][$value['fieldid']] = $value;
	}
	cache_write('profield', "_SGLOBAL['profield']", $_SGLOBAL['profield']);
}

//更新词语屏蔽
function censor_cache() {
	global $_SGLOBAL;

	$_SGLOBAL['censor'] = $banned = $banwords = array();

	$censorarr = explode("\n", data_get('censor'));
	foreach($censorarr as $censor) {
		$censor = trim($censor);
		if(empty($censor)) continue;

		list($find, $replace) = explode('=', $censor);
		$findword = $find;
		$find = preg_replace("/\\\{(\d+)\\\}/", ".{0,\\1}", preg_quote($find, '/'));
		switch($replace) {
			case '{BANNED}':
				$banwords[] = preg_replace("/\\\{(\d+)\\\}/", "*", preg_quote($findword, '/'));
				$banned[] = $find;
				break;
			default:
				$_SGLOBAL['censor']['filter']['find'][] = '/'.$find.'/i';
				$_SGLOBAL['censor']['filter']['replace'][] = $replace;
				break;
		}
	}
	if($banned) {
		$_SGLOBAL['censor']['banned'] = '/('.implode('|', $banned).')/i';
		$_SGLOBAL['censor']['banword'] = implode(', ', $banwords);
	}

	cache_write('censor', "_SGLOBAL['censor']", $_SGLOBAL['censor']);
}

//更新积分规则
function creditrule_cache() {
	global $_SGLOBAL;

	$_SGLOBAL['creditrule'] = array();

	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('creditrule'));
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$_SGLOBAL['creditrule'][$value['action']] = $value;
	}
	cache_write('creditrule', "_SGLOBAL['creditrule']", $_SGLOBAL['creditrule']);
}

//更新广告缓存
function ad_cache() {
	global $_SGLOBAL;

	$_SGLOBAL['ad'] = array();
	$query = $_SGLOBAL['db']->query('SELECT adid, pagetype FROM '.tname('ad')." WHERE system='1' AND available='1'");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$_SGLOBAL['ad'][$value['pagetype']][] = $value['adid'];
	}
	cache_write('ad', "_SGLOBAL['ad']", $_SGLOBAL['ad']);
}

//更新用户向导任务
function task_cache() {
	global $_SGLOBAL;

	$_SGLOBAL['task'] = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('task')." WHERE available='1' ORDER BY displayorder");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if((empty($value['endtime']) || $value['endtime'] >= $_SGLOBAL['timestamp']) && (empty($value['maxnum']) || $value['maxnum']>$value['num'])) {
			$_SGLOBAL['task'][$value['taskid']] = $value;
		}
	}
	cache_write('task', "_SGLOBAL['task']", $_SGLOBAL['task']);
}

//更新点击器
function click_cache() {
	global $_SGLOBAL;

	$_SGLOBAL['click'] = array();
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('click')." ORDER BY displayorder");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$_SGLOBAL['click'][$value['idtype']][$value['clickid']] = $value;
	}
	cache_write('click', "_SGLOBAL['click']", $_SGLOBAL['click']);
}

//更新模块
function block_cache() {
	global $_SGLOBAL;

	$_SGLOBAL['block'] = array();
	$query = $_SGLOBAL['db']->query('SELECT bid, cachetime FROM '.tname('block'));
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$_SGLOBAL['block'][$value['bid']] = $value['cachetime'];
	}
	cache_write('block', "_SGLOBAL['block']", $_SGLOBAL['block']);
}

//更新模板文件
function tpl_cache() {
	include_once(S_ROOT.'./source/function_cp.php');

	$dir = S_ROOT.'./data/tpl_cache';
	$files = sreaddir($dir);
	foreach ($files as $file) {
		@unlink($dir.'/'.$file);
	}
}

//更新模块缓存
function block_data_cache() {
	global $_SGLOBAL, $_SCONFIG;

	if($_SCONFIG['cachemode'] == 'database') {
		$query = $_SGLOBAL['db']->query("SHOW TABLE STATUS LIKE '".tname('cache')."%'");
		while($table = $_SGLOBAL['db']->fetch_array($query)) {
			$_SGLOBAL['db']->query("TRUNCATE TABLE `$table[Name]`");
		}
	} else {
		include_once(S_ROOT.'./source/function_cp.php');
		deltreedir(S_ROOT.'./data/block_cache');
	}
}

//更新MYOP默认应用
function userapp_cache() {
	global $_SGLOBAL, $_SCONFIG;

	$_SGLOBAL['userapp'] = array();
	if($_SCONFIG['my_status']) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('myapp')." WHERE flag='1' ORDER BY displayorder", 'SILENT');
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$_SGLOBAL['userapp'][$value['appid']] = $value;
		}
	}
	cache_write('userapp', "_SGLOBAL['userapp']", $_SGLOBAL['userapp']);
}

//更新应用名
function app_cache() {
	global $_SGLOBAL;

	$relatedtag = unserialize(data_get('relatedtag'));
	$default_open = 0;
	if(empty($relatedtag)) {
		//从UC取应用
		$relatedtag = array();
		include_once S_ROOT.'./uc_client/client.php';
		$relatedtag['data'] = uc_app_ls();
		$default_open = 1;
	}

	$_SGLOBAL['app'] = array();
	foreach($relatedtag['data'] as $appid => $data) {
		if($default_open) {
			$data['open'] = 1;
		}
		if($appid == UC_APPID) {//当前应用
			$data['open'] = 0;
		}
		$_SGLOBAL['app'][$appid] = array(
			'name' => $data['name'],
			'url' => $data['url'],
			'type' => $data['type'],
			'open'=>$data['open'],
			'icon' => $data['type']=='OTHER'?'default':strtolower($data['type'])
			);
	}
	cache_write('app', "_SGLOBAL['app']", $_SGLOBAL['app']);
}

// 更新活动分类
function eventclass_cache(){
    global $_SGLOBAL;

	$_SGLOBAL['eventclass'] = array();
	// 从数据库获取
	$query = $_SGLOBAL['db']->query("SELECT * FROM " . tname("eventclass") . " ORDER BY displayorder");
	while($value = $_SGLOBAL['db']->fetch_array($query)){
		if($value['poster']) {
			$value['poster'] = "data/event/".$value['classid'].".jpg";
		} else {
			$value['poster'] = "image/event/default.jpg";
		}
	    $_SGLOBAL['eventclass'][$value['classid']] = $value;
	}
	cache_write('eventclass', "_SGLOBAL['eventclass']", $_SGLOBAL['eventclass']);
}

//更新道具信息
function magic_cache(){
    global $_SGLOBAL;

	$_SGLOBAL['magic'] = array();
	// 从数据库获取
	$query = $_SGLOBAL['db']->query("SELECT mid, name FROM ".tname('magic')." WHERE close='0'");
	while($value = $_SGLOBAL['db']->fetch_array($query)){
	    $_SGLOBAL['magic'][$value['mid']] = $value['name'];
	}
	cache_write('magic', "_SGLOBAL['magic']", $_SGLOBAL['magic']);
}

//递归清空目录
function deltreedir($dir) {
	$files = sreaddir($dir);
	foreach ($files as $file) {
		if(is_dir("$dir/$file")) {
			deltreedir("$dir/$file");
		} else {
			@unlink("$dir/$file");
		}
	}
}

//数组转换成字串
function arrayeval($array, $level = 0) {
	$space = '';
	for($i = 0; $i <= $level; $i++) {
		$space .= "\t";
	}
	$evaluate = "Array\n$space(\n";
	$comma = $space;
	foreach($array as $key => $val) {
		$key = is_string($key) ? '\''.addcslashes($key, '\'\\').'\'' : $key;
		$val = !is_array($val) && (!preg_match("/^\-?\d+$/", $val) || strlen($val) > 12 || substr($val, 0, 1)=='0') ? '\''.addcslashes($val, '\'\\').'\'' : $val;
		if(is_array($val)) {
			$evaluate .= "$comma$key => ".arrayeval($val, $level + 1);
		} else {
			$evaluate .= "$comma$key => $val";
		}
		$comma = ",\n$space";
	}
	$evaluate .= "\n$space)";
	return $evaluate;
}

//写入
function cache_write($name, $var, $values) {
	$cachefile = S_ROOT.'./data/data_'.$name.'.php';
	$cachetext = "<?php\r\n".
		"if(!defined('IN_UCHOME')) exit('Access Denied');\r\n".
		'$'.$var.'='.arrayeval($values).
		"\r\n?>";
	if(!swritefile($cachefile, $cachetext)) {
		exit("File: $cachefile write error.");
	}
}

?>