<?php
/*
	[Ucenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: config.new.php 9293 2008-10-30 06:44:42Z liguode $
*/

//Ucenter Home配置参数
$_SC = array();
$_SC['dbhost']  		= 'localhost'; //服务器地址
$_SC['dbuser']  		= 'root'; //用户
$_SC['dbpw'] 	 		= 'root'; //密码
$_SC['dbcharset'] 		= 'utf8'; //字符集
$_SC['pconnect'] 		= 0; //是否持续连接
$_SC['dbname']  		= 'uchome'; //数据库
$_SC['tablepre'] 		= 'uchome_'; //表名前缀
$_SC['charset'] 		= 'utf-8'; //页面字符集

$_SC['gzipcompress'] 	= 0; //启用gzip

$_SC['cookiepre'] 		= 'uchome_'; //COOKIE前缀
$_SC['cookiedomain'] 	= ''; //COOKIE作用域
$_SC['cookiepath'] 		= '/'; //COOKIE作用路径

$_SC['attachdir']		= './attachment/'; //附件本地保存位置(服务器路径, 属性 777, 必须为 web 可访问到的目录, 相对目录务必以 "./" 开头, 末尾加 "/")
$_SC['attachurl']		= 'attachment/'; //附件本地URL地址(可为当前 URL 下的相对地址或 http:// 开头的绝对地址, 末尾加 "/")

$_SC['siteurl']			= ''; //站点的访问URL地址(http:// 开头的绝对地址, 末尾加 "/")，为空的话，系统会自动识别。

$_SC['tplrefresh']		= 0; //判断模板是否更新的效率等级，数值越大，效率越高; 设置为0则永久不判断

//Ucenter Home安全相关
$_SC['founder'] 		= '1'; //创始人 UID, 可以支持多个创始人，之间使用 “,” 分隔。部分管理功能只有创始人才可操作。
$_SC['allowedittpl']	= 0; //是否允许在线编辑模板。为了服务器安全，强烈建议关闭

//应用的UCenter配置信息(可以到UCenter后台->应用管理->查看本应用->复制里面对应的配置信息进行替换)
define('UC_CONNECT', 'mysql'); // 连接 UCenter 的方式: mysql/NULL, 默认为空时为 fscoketopen(), mysql 是直接连接的数据库, 为了效率, 建议采用 mysql
define('UC_DBHOST', 'localhost'); // UCenter 数据库主机
define('UC_DBUSER', 'root'); // UCenter 数据库用户名
define('UC_DBPW', 'root'); // UCenter 数据库密码
define('UC_DBNAME', 'ucenter'); // UCenter 数据库名称
define('UC_DBCHARSET', 'utf8'); // UCenter 数据库字符集
define('UC_DBTABLEPRE', 'uc_'); // UCenter 数据库表前缀
define('UC_DBCONNECT', '0'); // UCenter 数据库持久连接 0=关闭, 1=打开
define('UC_KEY', '1234567890'); // 与 UCenter 的通信密钥, 要与 UCenter 保持一致
define('UC_API', 'http://localhost/uc_server'); // UCenter 的 URL 地址, 在调用头像时依赖此常量
define('UC_CHARSET', 'gbk'); // UCenter 的字符集
define('UC_IP', ''); // UCenter 的 IP, 当 UC_CONNECT 为非 mysql 方式时, 并且当前应用服务器解析域名有问题时, 请设置此值
define('UC_APPID', '1'); // 当前应用的 ID
define('UC_PPP', 20);

?>