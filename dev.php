<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>NextIM for PLU 开发文档</title>
<style type="text/css">
<!--
.style1 {color: #FF0000}
.style2 {
	font-size: 18px;
	color: #3333FF;
}
.style3 {
	font-size: 18px;
	color: #3333FF;
	font-weight: bold;
}
-->
</style>
</head>

<body>
<p class="style2">1.安装NextIM</p>
<p>在需要嵌入的页面上的&lt;/body&gt;前加入 <span class="style1">&lt;?php include(&quot;webim/start.php&quot;); ?&gt;</span></p>
<p>(提示：如果想求简，则在引用次数较高的PHP文件(如config/iplu.php)中加入 <span class="style1"> include(&quot;webim/start.php&quot;); </span>)</p>
<p class="style3">2. 传递平台用户信息</p>
<p>在<span class="style1"> webim/config.php </span>的 <span class="style1">$_IMC_PLF</span> 全局变量中设置平台的用户名及用户ID</p>
<p>(提示：目前我们工程师已完成相关工作)</p>
<p class="style3">3.修改左侧快捷方式图标</p>
<p>修改<span class="style1"> webim/custom.js.php</span> 中的<span class="style1"> $menu</span> 变量</p>
<p> 如:array(&quot;title&quot; =&gt; 'search',&quot;icon&quot; =&gt;&quot;webim/static/images/search.png&quot;,&quot;link&quot; =&gt; &quot;search.php&quot;)</p>
<p>title代表图标名称，icon代表图片文件，link代表URL路径</p>
<p>工程师可根据用户需求控制增减</p>
<p class="style3">4.站长广播配置</p>
<p>在  webim/config.php 中 $_IMC['admin_ids'] 的值里添加用户UID , 用逗号隔开</p>
<p>(提示：本站所有 NextIM 在线用户都能收到 )</p>
<p>&nbsp;</p>
<p class="style3">&nbsp;</p>
</body>
</html>
