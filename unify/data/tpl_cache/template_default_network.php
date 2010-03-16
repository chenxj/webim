<?php if(!defined('IN_UCHOME')) exit('Access Denied');?><?php subtplcheck('template/default/network|template/default/header|template/default/footer|template/default/webim_uchome', '1268655850', 'template/default/network');?><?php $_TPL['nosidebar']=1; ?>
<?php if(empty($_SGLOBAL['inajax'])) { ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=<?=$_SC['charset']?>" />
<meta http-equiv="x-ua-compatible" content="ie=7" />
<title><?php if($_TPL['titles']) { ?><?php if(is_array($_TPL['titles'])) { foreach($_TPL['titles'] as $value) { ?><?php if($value) { ?><?=$value?> - <?php } ?><?php } } ?><?php } ?><?php if($_SN[$space['uid']]) { ?><?=$_SN[$space['uid']]?> - <?php } ?><?=$_SCONFIG['sitename']?> - Powered by UCenter Home</title>
<script language="javascript" type="text/javascript" src="source/script_cookie.js"></script>
<script language="javascript" type="text/javascript" src="source/script_common.js"></script>
<script language="javascript" type="text/javascript" src="source/script_menu.js"></script>
<script language="javascript" type="text/javascript" src="source/script_ajax.js"></script>
<script language="javascript" type="text/javascript" src="source/script_face.js"></script>
<script language="javascript" type="text/javascript" src="source/script_manage.js"></script>
<style type="text/css">
@import url(template/default/style.css);
<?php if($_TPL['css']) { ?>
@import url(template/default/<?=$_TPL['css']?>.css);
<?php } ?>
<?php if(!empty($_SGLOBAL['space_theme'])) { ?>
@import url(theme/<?=$_SGLOBAL['space_theme']?>/style.css);
<?php } elseif($_SCONFIG['template'] != 'default') { ?>
@import url(template/<?=$_SCONFIG['template']?>/style.css);
<?php } ?>
<?php if(!empty($_SGLOBAL['space_css'])) { ?>
<?=$_SGLOBAL['space_css']?>
<?php } ?>
</style>
<link rel="shortcut icon" href="image/favicon.ico" />
<link rel="edituri" type="application/rsd+xml" title="rsd" href="xmlrpc.php?rsd=<?=$space['uid']?>" />
</head>
<body>

<div id="append_parent"></div>
<div id="ajaxwaitid"></div>
<div id="header">
<?php if($_SGLOBAL['ad']['header']) { ?><div id="ad_header"><?php adshow('header'); ?></div><?php } ?>
<div class="headerwarp">
<h1 class="logo"><a href="index.php"><img src="template/<?=$_SCONFIG['template']?>/image/logo.gif" alt="<?=$_SCONFIG['sitename']?>" /></a></h1>
<ul class="menu">
<?php if($_SGLOBAL['supe_uid']) { ?>
<li><a href="space.php?do=home">首页</a></li>
<li><a href="space.php">个人主页</a></li>
<li><a href="space.php?do=friend">好友</a></li>
<li><a href="network.php">随便看看</a></li>
<?php } else { ?>
<li><a href="index.php">首页</a></li>
<?php } ?>

<?php if($_SGLOBAL['appmenu']) { ?>
<?php if($_SGLOBAL['appmenus']) { ?>
<li class="dropmenu" id="ucappmenu" onclick="showMenu(this.id)">
<a href="javascript:;">站内导航</a>
</li>
<?php } else { ?>
<li><a target="_blank" href="<?=$_SGLOBAL['appmenu']['url']?>" title="<?=$_SGLOBAL['appmenu']['name']?>"><?=$_SGLOBAL['appmenu']['name']?></a></li>
<?php } ?>
<?php } ?>

<?php if($_SGLOBAL['supe_uid']) { ?>
<li><a href="space.php?do=pm<?php if(!empty($_SGLOBAL['member']['newpm'])) { ?>&filter=newpm<?php } ?>">消息<?php if(!empty($_SGLOBAL['member']['newpm'])) { ?>(新)<?php } ?></a></li>
<?php if($_SGLOBAL['member']['allnotenum']) { ?><li class="notify" id="membernotemenu" onmouseover="showMenu(this.id)"><a href="space.php?do=notice"><?=$_SGLOBAL['member']['allnotenum']?>个提醒</a></li><?php } ?>
<?php } else { ?>
<li><a href="help.php">帮助</a></li>
<?php } ?>
</ul>

<div class="nav_account">
<?php if($_SGLOBAL['supe_uid']) { ?>
<a href="space.php?uid=<?=$_SGLOBAL['supe_uid']?>" class="login_thumb"><?php echo avatar($_SGLOBAL[supe_uid]); ?></a>
<a href="space.php?uid=<?=$_SGLOBAL['supe_uid']?>" class="loginName"><?=$_SN[$_SGLOBAL['supe_uid']]?></a>
<?php if($_SGLOBAL['member']['credit']) { ?>
<a href="cp.php?ac=credit" style="font-size:11px;padding:0 0 0 5px;"><img src="image/credit.gif"><?=$_SGLOBAL['member']['credit']?></a>
<?php } ?>
<br />
<?php if(empty($_SCONFIG['closeinvite'])) { ?>
<a href="cp.php?ac=invite">邀请</a> 
<?php } ?>
<a href="cp.php?ac=task">任务</a> 
<a href="cp.php?ac=magic">道具</a>
<a href="cp.php">设置</a> 
<a href="cp.php?ac=common&op=logout&uhash=<?=$_SGLOBAL['uhash']?>">退出</a>
<?php } else { ?>
<a href="do.php?ac=<?=$_SCONFIG['register_action']?>" class="login_thumb"><?php echo avatar($_SGLOBAL[supe_uid]); ?></a>
欢迎您<br>
<a href="do.php?ac=<?=$_SCONFIG['login_action']?>">登录</a> | 
<a href="do.php?ac=<?=$_SCONFIG['register_action']?>">注册</a>
<?php } ?>
</div>
</div>
</div>

<div id="wrap">

<?php if(empty($_TPL['nosidebar'])) { ?>
<div id="main">
<div id="app_sidebar">
<?php if($_SGLOBAL['supe_uid']) { ?>
<ul class="app_list" id="default_userapp">
<li><img src="image/app/doing.gif"><a href="space.php?do=doing">记录</a></li>
<li><img src="image/app/album.gif"><a href="space.php?do=album">相册</a><em><a href="cp.php?ac=upload" class="gray">上传</a></em></li>
<li><img src="image/app/blog.gif"><a href="space.php?do=blog">日志</a><em><a href="cp.php?ac=blog" class="gray">发表</a></em></li>
<li><img src="image/app/poll.gif"/><a href="space.php?do=poll">投票</a><em><a href="cp.php?ac=poll" class="gray">发起</a></em></li>
<li><img src="image/app/mtag.gif"><a href="space.php?do=mtag">群组</a><em><a href="cp.php?ac=thread" class="gray">话题</a></em></li>
<li><img src="image/app/event.gif"/><a href="space.php?do=event">活动</a><em><a href="cp.php?ac=event" class="gray">发起</a></em></li>
<li><img src="image/app/share.gif"><a href="space.php?do=share">分享</a></li>
<li><img src="image/app/topic.gif"><a href="space.php?do=topic">热闹</a></li>
</ul>

<ul class="app_list topline" id="my_defaultapp">
<?php if($_SCONFIG['my_status']) { ?>
<?php if(is_array($_SGLOBAL['userapp'])) { foreach($_SGLOBAL['userapp'] as $value) { ?>
<li><img src="http://appicon.manyou.com/icons/<?=$value['appid']?>"><a href="userapp.php?id=<?=$value['appid']?>"><?=$value['appname']?></a></li>
<?php } } ?>
<?php } ?>
</ul>

<?php if($_SCONFIG['my_status']) { ?>
<ul class="app_list topline" id="my_userapp">
<?php if(is_array($_SGLOBAL['my_menu'])) { foreach($_SGLOBAL['my_menu'] as $value) { ?>
<li id="userapp_li_<?=$value['appid']?>"><img src="http://appicon.manyou.com/icons/<?=$value['appid']?>"><a href="userapp.php?id=<?=$value['appid']?>" title="<?=$value['appname']?>"><?=$value['appname']?></a></li>
<?php } } ?>
</ul>
<?php } ?>

<?php if($_SGLOBAL['my_menu_more']) { ?>
<p class="app_more"><a href="javascript:;" id="a_app_more" onclick="userapp_open();" class="off">展开</a></p>
<?php } ?>

<?php if($_SCONFIG['my_status']) { ?>
<div class="app_m">
<ul>
<li><img src="image/app_add.gif"><a href="cp.php?ac=userapp&my_suffix=%2Fapp%2Flist" class="addApp">添加应用</a></li>
<li><img src="image/app_set.gif"><a href="cp.php?ac=userapp&op=menu" class="myApp">管理应用</a></li>
</ul>
</div>
<?php } ?>

<?php } else { ?>
<div class="bar_text">
<form id="loginform" name="loginform" action="do.php?ac=<?=$_SCONFIG['login_action']?>&ref" method="post">
<input type="hidden" name="formhash" value="<?php echo formhash(); ?>" />
<p class="title">登录站点</p>
<p>用户名</p>
<p><input type="text" name="username" id="username" class="t_input" size="15" value="" /></p>
<p>密码</p>
<p><input type="password" name="password" id="password" class="t_input" size="15" value="" /></p>
<p><input type="checkbox" id="cookietime" name="cookietime" value="315360000" checked /><label for="cookietime">记住我</label></p>
<p>
<input type="submit" id="loginsubmit" name="loginsubmit" value="登录" class="submit" />
<input type="button" name="regbutton" value="注册" class="button" onclick="urlto('do.php?ac=<?=$_SCONFIG['register_action']?>');">
</p>
</form>
</div>
<?php } ?>
</div>

<div id="mainarea">

<?php if($_SGLOBAL['ad']['contenttop']) { ?><div id="ad_contenttop"><?php adshow('contenttop'); ?></div><?php } ?>
<?php } ?>

<?php } ?>

<div id="network">

<script>
function setintro(type) {
var intro = '';
var bPosition = '';
if(type == 'doing') {
intro = '用一句话记录自己生活中的点点滴滴';
bPosition = '0';
} else if(type == 'mtag') {
intro = '创建自己的小圈子，与大家交流感兴趣的话题';
bPosition = '175px';
} else if(type == 'pic') {
intro = '上传照片，分享生活中的精彩瞬间';
bPosition = '55px';
} else if(type == 'app') {
intro = '与好友一起玩转游戏和游戏，增加好友感情';
bPosition = '118px';
} else {
intro = '马上注册，与好友分享日志、照片，一起玩转游戏';
bPosition = '0';
}
$('guest_intro').innerHTML = intro + '......';
$('guest_intro').style.backgroundPosition = bPosition + ' 100%'
}
function scrollPic(e, LN, Width, Price, Speed) {
id = e.id;
if(LN == 'Last'){ scrollNum = Width; } else if(LN == 'Next'){ scrollNum = 0 - Width; }
scrollStart = parseInt(e.style.marginLeft, 10);
scrollEnd = parseInt(e.style.marginLeft, 10) + scrollNum;

MaxIndex = (e.getElementsByTagName('li').length / Price).toFixed(0);
sPicMaxScroll = 0 - Width * MaxIndex;

if(scrollStart == 0 && scrollEnd == Width){
scrollEnd = -1806;
e.style.marginLeft = parseInt(e.style.marginLeft, 10) - Speed + 'px';
} else if(scrollStart == sPicMaxScroll + Width && scrollEnd == sPicMaxScroll){
scrollEnd = 0;
e.style.marginLeft = parseInt(e.style.marginLeft, 10) + Speed + 'px';
}
scrollShowPic = setInterval(scrollShow, 1);

function scrollShow() {
if(scrollStart > scrollEnd) {
if(parseInt(e.style.marginLeft, 10) > scrollEnd) {
$(id + '_last').onclick = function(){ return false; };
$(id + '_next').onclick = function(){ return false; };
e.style.marginLeft = parseInt(e.style.marginLeft, 10) - Speed + 'px';
} else {
clearInterval(scrollShowPic);
$(id + '_last').onclick = function(){ scrollPic(e, 'Last', Width, Price, Speed);return false; };
$(id + '_next').onclick = function(){ scrollPic(e, 'Next', Width, Price, Speed);return false; };
}
} else {
if(parseInt(e.style.marginLeft, 10) < scrollEnd) {
$(id + '_last').onclick = function(){ return false; };
$(id + '_next').onclick = function(){ return false; };
e.style.marginLeft = parseInt(e.style.marginLeft, 10) + Speed + 'px';
} else {
clearInterval(scrollShowPic);
$(id + '_last').onclick = function(){ scrollPic(e, 'Last', Width, Price, Speed);return false; };
$(id + '_next').onclick = function(){ scrollPic(e, 'Next', Width, Price, Speed);return false; };
}					
}
}
}
function scrollShowNav(e, Width, Price, Speed) {
id = e.id;
$(id + '_last').onclick = function(){ scrollPic(e, 'Last', Width, Price, Speed);return false; };
$(id + '_next').onclick = function(){ scrollPic(e, 'Next', Width, Price, Speed);return false; };

}
function getUserTip(obj) {
var tipBox = $('usertip_box');
tipBox.childNodes[0].innerHTML = '<strong>' + obj.rel + ':<\/strong> ' + obj.rev + '...';

var showLeft;
if(obj.parentNode.offsetLeft > 730) {
showLeft = $('showuser').offsetLeft + obj.parentNode.offsetLeft - 148;
tipBox.childNodes[0].style.right = 0;
} else {
tipBox.childNodes[0].style.right = 'auto';
showLeft = $('showuser').offsetLeft + obj.parentNode.offsetLeft;
}
tipBox.style.left = showLeft + 'px';

var showTop; 
if(obj.className == 'uonline') {
showTop = $('showuser').offsetTop + obj.parentNode.offsetTop - tipBox.childNodes[0].clientHeight;
} else {
showTop = $('showuser').offsetTop + obj.parentNode.offsetTop + 48;
}
tipBox.style.top = showTop + 'px';

tipBox.style.visibility = 'visible';
}
</script>

<?php if(empty($_SGLOBAL['supe_uid'])) { ?>
<div id="guestbar" class="nbox">
<div class="nbox_c">
<p id="guest_intro">马上注册，与好友分享日志、照片，一起玩转游戏......</p>
<a href="do.php?ac=<?=$_SCONFIG['register_action']?>" id="regbutton" onmouseover="setintro('register');">马上注册</a>
<p id="guest_app">
<a href="javascript:;" class="appdoing" onmouseover="setintro('doing');">记录</a>
<a href="javascript:;" class="appphotos" onmouseover="setintro('pic');">照片</a>
<a href="javascript:;" class="appgames" onmouseover="setintro('app');">游戏</a>
<a href="javascript:;" class="appgroups" onmouseover="setintro('mtag');">群组</a> 
</p>
</div>	
<div class="nbox_s side_rbox" id="nlogin_box">
<h3 class="ntitle">请登录</h3>
<div class="side_rbox_c">
<form name="loginform" action="do.php?ac=<?=$_SCONFIG['login_action']?>&<?=$url_plus?>&ref" method="post">
<p><label for="username">用户名</label> <input type="text" name="username" id="username" class="t_input" value="<?=$membername?>" /></p>
<p><label for="password">密　码</label> <input type="password" name="password" id="password" class="t_input" value="<?=$password?>" /></p>
<p class="checkrow"><input type="checkbox" id="cookietime" name="cookietime" value="315360000" <?=$cookiecheck?> style="margin-bottom: -2px;" /><label for="cookietime">下次自动登录</label></p>
<p class="submitrow">
<input type="hidden" name="refer" value="space.php?do=home" />
<input type="submit" id="loginsubmit" name="loginsubmit" value="登录" class="submit" />
<a href="do.php?ac=lostpasswd">忘记密码?</a>
<input type="hidden" name="formhash" value="<?php echo formhash(); ?>" />
</p>
</form>
</div>
</div>
</div>
<?php } ?>

<div class="nbox">
<div class="nbox_c">
<h2 class="ntitle"><span class="r_option"><a href="space.php?do=blog&view=all">更多日志</a></span> 日志 &raquo;</h2>
<ul class="bloglist">
<?php if(is_array($bloglist)) { foreach($bloglist as $key => $value) { ?>
<li <?php if($key%2==1) { ?>class="list_r"<?php } ?>>
<h3><a href="space.php?uid=<?=$value['uid']?>&do=blog&id=<?=$value['blogid']?>" target="_blank"><?=$value['subject']?></a></h3>
<div class="d_avatar avatar48"><a href="space.php?uid=<?=$value['uid']?>" title="<?=$_SN[$value['uid']]?>" target="_blank"><?php echo avatar($value[uid],small); ?></a></div>
<p class="message"><?=$value['message']?> ...</p>
<p class="nhot"><a href="space.php?uid=<?=$value['uid']?>&do=blog&id=<?=$value['blogid']?>"><?=$value['hot']?> 人推荐</a></p>
<p class="gray"><a href="space.php?uid=<?=$value['uid']?>"><?=$_SN[$value['uid']]?></a> 发表于 <?php echo sgmdate('m-d H:i',$value[dateline],1); ?></p>
</li>
<?php } } ?>
</ul>
</div>
<div class="nbox_s side_rbox side_rbox_w">
<h2 class="ntitle"><span class="r_option"><a href="space.php?do=doing&view=all">更多记录</a></span> 记录 &raquo;</h2>
<div class="side_rbox_c">
<ul class="side_rbox_c doinglist">
<?php if(is_array($dolist)) { foreach($dolist as $value) { ?>
<li>
<p>
<a href="space.php?uid=<?=$value['uid']?>&do=doing&doid=<?=$value['doid']?>" target="_blank" class="gray r_option dot" style="margin:0;background-position-y: 0;"><?php echo sgmdate('H:i',$value[dateline],1); ?></a>
<a href="space.php?uid=<?=$value['uid']?>" title="<?=$_SN[$value['uid']]?>" class="s_avatar"><?php echo avatar($value[uid],small); ?></a>
<a href="space.php?uid=<?=$value['uid']?>"><?=$_SN[$value['uid']]?></a>
</p>
<p class="message" title="<?=$value['title']?>"><?=$value['message']?></p>
</li>
<?php } } ?>
</ul>
</div>
</div>
</div>



<div class="nbox" id="photolist">
<h2 class="ntitle">
<a href="space.php?do=album&view=all" class="r_option">更多图片</a>
图片 &raquo;
</h2>
<div id="p_control">
<a href="javascript:;" id="spics_last">上一页</a>
<a href="javascript:;" id="spics_next">下一页</a>
<ul id="p_control_pages">
<li>第一页</li>
<li>第二页</li>
<li>第三页</li>
<li>第四页</li>
</li>
</div>
<div id="spics_wrap">
<ul id="spics" style="margin-left: 0;">
<?php if(is_array($piclist)) { foreach($piclist as $key => $value) { ?>
<li class="spic_<?=$key?>">
<div class="spic_img"><a href="space.php?uid=<?=$value['uid']?>&do=album&picid=<?=$value['picid']?>" target="_blank"><strong><?=$value['hot']?></strong><img src="<?=$value['pic']?>" alt="<?=$value['albumname']?>" /></a></div>
<p><a href="space.php?uid=<?=$value['uid']?>"><?=$_SN[$value['uid']]?></a></p>
<p><?php echo sgmdate('m-d H:i',$value[dateline],1); ?></p>
</li>
<?php } } ?>
</ul>
</div>
</div>
<script type="text/javascript">
scrollShowNav($('spics'), 903, 7, 43);
</script>

<div id="searchbar" class="nbox s_clear">
<div class="floatleft">
<form method="get" action="cp.php">
<input name="searchkey" value="" size="15" class="t_input" type="text" style="padding:5px;">
<input name="searchsubmit" value="找人" class="submit" type="submit"> &nbsp;
查找：<a href="cp.php?ac=friend&op=search&view=sex" target="_blank">男女朋友</a><span class="pipe">|</span>
<a href="cp.php?ac=friend&op=search&view=reside" target="_blank">同城</a><span class="pipe">|</span>
<a href="cp.php?ac=friend&op=search&view=birth" target="_blank">老乡</a><span class="pipe">|</span>
<a href="cp.php?ac=friend&op=search&view=birthyear" target="_blank">同年同月同日生</a><span class="pipe">|</span>
<a href="cp.php?ac=friend&op=search&view=edu" target="_blank">同学</a><span class="pipe">|</span>
<a href="cp.php?ac=friend&op=search&view=work" target="_blank">同事</a><span class="pipe">|</span>
<a href="space.php?do=top&view=online" target="_blank">在线会员(<?=$olcount?>)</a>
<input type="hidden" name="searchmode" value="1" />
<input type="hidden" name="ac" value="friend" />
<input type="hidden" name="op" value="search" />
</form>
</div>
<div class="floatright">
<form method="get" action="space.php">
<input name="searchkey" value="" size="15" class="t_input" type="text" style="padding:5px;">
<select name="do">
<option value="blog">日志</option>
<option value="album">相册</option>
<option value="thread">话题</option>
<option value="poll">投票</option>
<option value="event">活动</option>
</select>
<input name="searchsubmit" value="搜索" class="submit" type="submit">
<input type="hidden" name="view" value="all" />
<input type="hidden" name="orderby" value="dateline" />
</form>
</div>
</div>

<div id="showuser" class="nbox">
<div id="user_recomm">
<h2>站长推荐</h2>
<?php if(is_array($star)) { foreach($star as $value) { ?>
<div class="s_avatar"><a href="space.php?uid=<?=$value['uid']?>" target="_blank"><?php echo avatar($value[uid],middle); ?></a></div>
<div class="s_cnts">
<h3><a href="space.php?uid=<?=$value['uid']?>" title="<?=$_SN[$value['uid']]?>"><?=$_SN[$value['uid']]?></a></h3>
<p>访问: <?=$value['viewnum']?></p>
<p>积分: <?=$value['credit']?></p>
<hr />
<p>好友: <?=$value['friendnum']?></p>
<p>更新: <?php echo sgmdate('H:i',$value[updatetime],1); ?></p>
</div>
<?php } } ?>
</div>
<div id="user_wall" onmouseout="javascript:$('usertip_box').style.visibility = 'hidden';">
<div id="user_pay" class="s_clear">
<h2><a href="space.php?do=top">竞价排名</a></h2>
<ul>
<?php if(is_array($showlist)) { foreach($showlist as $value) { ?>
<li><a href="space.php?uid=<?=$value['uid']?>" target="_blank" rel="<?=$_SN[$value['uid']]?>" rev="<?=$value['note']?>" onmouseover="getUserTip(this)"><?php echo avatar($value[uid],small); ?></a></li>
<?php } } ?>
</ul>
<p><a href="space.php?do=top">我要上榜</a></p>
</div>
<div id="user_online" class="s_clear">
<h2><a href="space.php?do=top&view=online">在线会员</a></h2>
<ul>
<?php if(is_array($onlinelist)) { foreach($onlinelist as $value) { ?>
<li><a href="space.php?uid=<?=$value['uid']?>" target="_blank" rel="<?=$_SN[$value['uid']]?>" rev="<?=$value['note']?>" class="uonline" onmouseover="getUserTip(this)"><?php echo avatar($value[uid],small); ?></a></li>
<?php } } ?>
</ul>
</div>
</div>
</div>
<div id="usertip_box"><div></div></div>

<div class="nbox">
<div class="nbox_c">
<h2 class="ntitle"><span class="r_option"><a href="space.php?do=thread&view=all">更多话题</a></span>话题 &raquo;</h2>
<div class="tlist">
<table cellpadding="0" cellspacing="1">
<tbody>
<?php if(is_array($threadlist)) { foreach($threadlist as $key => $value) { ?>
<tr <?php if($key%2==1) { ?>class="color_row"<?php } ?>>
<td class="ttopic"><div class="ttop"><div><span><?=$value['hot']?></span></div></div><a href="space.php?uid=<?=$value['uid']?>&do=thread&id=<?=$value['tid']?>" target="_blank"><?=$value['subject']?></a></td>
<td class="tuser"><a href="space.php?uid=<?=$value['uid']?>" target="_blank"><?php echo avatar($value[uid],small); ?></a> <a href="space.php?uid=<?=$value['uid']?>" target="_blank"><?=$_SN[$value['uid']]?></a></td>
<td class="tgp"><a href="space.php?do=mtag&tagid=<?=$value['tagid']?>"><?=$value['tagname']?></a></td>
</tr>
<?php } } ?>
</tbody>
</table>
</div>
</div>
<div id="npoll" class="nbox_s side_rbox side_rbox_w">
<div class="side_rbox_c">
<h2 class="ntitle"><span class="r_option"><a href="space.php?do=poll">更多投票</a></span>投票 &raquo;</h2>
<ul>
<?php if(is_array($polllist)) { foreach($polllist as $key => $value) { ?>
<li class="poll_<?=$key?>"><a href="space.php?uid=<?=$value['uid']?>&do=poll&pid=<?=$value['pid']?>" target="_blank"><?=$value['subject']?></a><?php if($key == 0) { ?><p><a href="">已有 <?=$value['voternum']?> 位会员投票</a></p><?php } ?></li>
<?php } } ?>
</ul>
</div>
</div>
</div>

<?php if($myappcount) { ?>
<div class="nbox">
<div class="nbox_c" style="border: none;">
<ul class="applist">
<?php if(is_array($myapplist)) { foreach($myapplist as $value) { ?>
<li>
<p class="aimg"><a href="userapp.php?id=<?=$value['appid']?>" target="_blank"><img src="http://appicon.manyou.com/logos/<?=$value['appid']?>" alt="<?=$value['appname']?>" /></a></p>
<p><a href="userapp.php?id=<?=$value['appid']?>" target="_blank"><?=$value['appname']?></a></p>
</li>
<?php } } ?>
</ul>
</div>
<div class="susb">
<div class="ye_r_t"><div class="ye_l_t"><div class="ye_r_b"><div class="ye_l_b">
<div class="appmo">
<p>共有 <span><?=$myappcount?></span> 个游戏</p>
<p class="appmobutton"><a href="cp.php?ac=userapp&my_suffix=%2Fapp%2Flist">查看更多应用</a></p>
</div>
</div></div></div></div>	
</div>
</div>
<?php } ?>

<div class="nbox">
<div class="nbox_c">
<h2 class="ntitle"><span class="r_option"><a href="space.php?do=event&view=recommend">更多活动</a></span> 活动 &raquo; 
<?php if(is_array($_SGLOBAL['eventclass'])) { foreach($_SGLOBAL['eventclass'] as $value) { ?>
&nbsp; <a href="space.php?do=event&view=all&type=going&classid=<?=$value['classid']?>"><?=$value['classname']?></a></li>
<?php } } ?>
</h2>
<ul class="elist">
<?php if(is_array($eventlist)) { foreach($eventlist as $value) { ?>
<li>
<h3><a href="space.php?do=event&id=<?=$value['eventid']?>" target="_blank"><?=$value['title']?></a></h3>
<p class="eimage"><a href="space.php?do=event&id=<?=$value['eventid']?>" target="_blank"><img src="<?=$value['pic']?>" alt=""/></a></p>
<p><span class="gray">时间:</span> <?php echo sgmdate('n-j H:i',$value[starttime]); ?> - <?php echo sgmdate('n-j H:i',$value[endtime]); ?></p>
<p><span class="gray">地点:</span> <?=$value['province']?> <?=$value['city']?> <?=$value['location']?></p>
<p><span class="gray">发起:</span> <a href="space.php?uid=<?=$value['uid']?>"><?=$_SN[$value['uid']]?></a></p>
<p class="egz"><?=$value['membernum']?> 人参加<span class="pipe">|</span><?=$value['follownum']?> 人关注</p>
</li>
<?php } } ?>
</ul>
</div>
<div id="nshare" class="nbox_s side_rbox side_rbox_w">
<h2 class="ntitle"><span class="r_option"><a href="space.php?do=share&view=all">更多分享</a></span>分享 &raquo;</h2>
<div class="side_rbox_c">
<ul>
<?php if(is_array($sharelist)) { foreach($sharelist as $value) { ?>
<li><a href="space.php?uid=<?=$value['uid']?>"><?=$_SN[$value['uid']]?></a> <em><a href="space.php?uid=<?=$value['uid']?>&do=share&view=me"><?=$value['title_template']?></a></em></li>
<?php } } ?>
</ul>
</div>
</div>
</div>

<div class="footerbar">
<div class="fbtop"></div>
<div class="nbox_c">
<div class="foobox">
<div class="fbox">
<h2 class="ntitle">常用</h2>
<ul>
<li><a href="space.php?do=doing">记录</a></li>
<li><a href="space.php?do=blog">日志</a></li>
<li><a href="space.php?do=album">相册</a></li>
<li><a href="space.php?do=mtag">群组</a></li>
<li><a href="space.php?do=poll">投票</a></li>
<li><a href="space.php?do=event">活动</a></li>
<li><a href="space.php?do=share">分享</a></li>
</ul>
</div>
<div class="fbox">
<h2 class="ntitle">应用</h2>
<ul>
<?php if($myappcount) { ?>
<?php if(is_array($myapplist)) { foreach($myapplist as $value) { ?>
<li><a href="userapp.php?id=<?=$value['appid']?>"><?=$value['appname']?></a></li>
<?php } } ?>
<li><a href="cp.php?ac=userapp&my_suffix=%2Fapp%2Flist" class="alink">查看全部 <?=$myappcount?> 个应用</a></li>
<?php } else { ?>
<li><a href="#">还没有应用</a></li>
<?php } ?>
</ul>
</div>
<div class="fbox">
<h2 class="ntitle">发现</h2>
<ul>
<li><a href="space.php?do=blog&view=all">大家发表的日志</a></li>
<li><a href="space.php?do=album&view=all">大家上传的图片</a></li>
<li><a href="space.php?do=thread&view=all">大家的话题</a></li>
</ul>
</div>
</div>
</div>
<div class="nbox_s">
<h2 class="ntitle">邀请</h2>
<ul>
<li><a href="cp.php?ac=invite">邀请好友加入，获赠积分奖励</a></li>
<li><a href="cp.php?ac=invite">QQ 好友</a></li>
<li><a href="cp.php?ac=invite">163 邮箱</a></li>
<li><a href="cp.php?ac=invite">新浪邮箱</a></li>
<li><a href="cp.php?ac=invite">搜狐邮箱</a></li>
<li><a href="cp.php?ac=invite">Google Gmail</a></li>
<li><a href="cp.php?ac=invite">MSN 联系人</a></li>
<li><a href="cp.php?ac=invite">Yahoo! 邮箱</a></li>
<li><a href="cp.php?ac=invite" class="alink">更多联系人……</a></li>
</ul>
</div>
<div class="fbbottom"></div>
</div>

</div>

<?php if(empty($_SGLOBAL['inajax'])) { ?>
<?php if(empty($_TPL['nosidebar'])) { ?>
<?php if($_SGLOBAL['ad']['contentbottom']) { ?><br style="line-height:0;clear:both;"/><div id="ad_contentbottom"><?php adshow('contentbottom'); ?></div><?php } ?>
</div>

<!--/mainarea-->
<div id="bottom"></div>
</div>
<!--/main-->
<?php } ?>

<div id="footer">
<?php if($_TPL['templates']) { ?>
<div class="chostlp" title="切换风格"><img id="chostlp" src="<?=$_TPL['default_template']['icon']?>" onmouseover="showMenu(this.id)" alt="<?=$_TPL['default_template']['name']?>" /></div>
<ul id="chostlp_menu" class="chostlp_drop" style="display: none">
<?php if(is_array($_TPL['templates'])) { foreach($_TPL['templates'] as $value) { ?>
<li><a href="cp.php?ac=common&op=changetpl&name=<?=$value['name']?>" title="<?=$value['name']?>"><img src="<?=$value['icon']?>" alt="<?=$value['name']?>" /></a></li>
<?php } } ?>
</ul>
<?php } ?>

<p class="r_option">
<a href="javascript:;" onclick="window.scrollTo(0,0);" id="a_top" title="TOP"><img src="image/top.gif" alt="" style="padding: 5px 6px 6px;" /></a>
</p>

<?php if($_SGLOBAL['ad']['footer']) { ?>
<p style="padding:5px 0 10px 0;"><?php adshow('footer'); ?></p>
<?php } ?>

<?php if($_SCONFIG['close']) { ?>
<p style="color:blue;font-weight:bold;">
提醒：当前站点处于关闭状态
</p>
<?php } ?>
<p>
<?=$_SCONFIG['sitename']?> - 
<a href="mailto:<?=$_SCONFIG['adminemail']?>">联系我们</a>
<?php if($_SCONFIG['miibeian']) { ?> - <a  href="http://www.miibeian.gov.cn" target="_blank"><?=$_SCONFIG['miibeian']?></a><?php } ?>
</p>
<p>
Powered by <a  href="http://u.discuz.net" target="_blank"><strong>UCenter Home</strong></a> <span title="<?php echo X_RELEASE; ?>"><?php echo X_VER; ?></span>
<?php if(!empty($_SCONFIG['licensed'])) { ?><a  href="http://license.comsenz.com/?pid=7&host=<?=$_SERVER['HTTP_HOST']?>" target="_blank">Licensed</a><?php } ?>
&copy; 2001-2010 <a  href="http://www.comsenz.com" target="_blank">Comsenz Inc.</a>
</p>
<?php if($_SCONFIG['debuginfo']) { ?>
<p><?php echo debuginfo(); ?></p>
<?php } ?>
</div>
</div>
<!--/wrap-->

<?php if($_SGLOBAL['appmenu']) { ?>
<ul id="ucappmenu_menu" class="dropmenu_drop" style="display:none;">
<li><a href="<?=$_SGLOBAL['appmenu']['url']?>" title="<?=$_SGLOBAL['appmenu']['name']?>" target="_blank"><?=$_SGLOBAL['appmenu']['name']?></a></li>
<?php if(is_array($_SGLOBAL['appmenus'])) { foreach($_SGLOBAL['appmenus'] as $value) { ?>
<li><a href="<?=$value['url']?>" title="<?=$value['name']?>" target="_blank"><?=$value['name']?></a></li>
<?php } } ?>
</ul>
<?php } ?>

<?php if($_SGLOBAL['supe_uid']) { ?>
<ul id="membernotemenu_menu" class="dropmenu_drop" style="display:none;">
<?php $member = $_SGLOBAL['member']; ?>
<?php if($member['notenum']) { ?><li><img src="image/icon/notice.gif" width="16" alt="" /> <a href="space.php?do=notice"><strong><?=$member['notenum']?></strong> 个新通知</a></li><?php } ?>
<?php if($member['pokenum']) { ?><li><img src="image/icon/poke.gif" alt="" /> <a href="cp.php?ac=poke"><strong><?=$member['pokenum']?></strong> 个新招呼</a></li><?php } ?>
<?php if($member['addfriendnum']) { ?><li><img src="image/icon/friend.gif" alt="" /> <a href="cp.php?ac=friend&op=request"><strong><?=$member['addfriendnum']?></strong> 个好友请求</a></li><?php } ?>
<?php if($member['mtaginvitenum']) { ?><li><img src="image/icon/mtag.gif" alt="" /> <a href="cp.php?ac=mtag&op=mtaginvite"><strong><?=$member['mtaginvitenum']?></strong> 个群组邀请</a></li><?php } ?>
<?php if($member['eventinvitenum']) { ?><li><img src="image/icon/event.gif" alt="" /> <a href="cp.php?ac=event&op=eventinvite"><strong><?=$member['eventinvitenum']?></strong> 个活动邀请</a></li><?php } ?>
<?php if($member['myinvitenum']) { ?><li><img src="image/icon/userapp.gif" alt="" /> <a href="space.php?do=notice&view=userapp"><strong><?=$member['myinvitenum']?></strong> 个应用消息</a></li><?php } ?>
</ul>
<?php } ?>

<?php if($_SGLOBAL['supe_uid']) { ?>
<?php if(!isset($_SCOOKIE['checkpm'])) { ?>
<script language="javascript"  type="text/javascript" src="cp.php?ac=pm&op=checknewpm&rand=<?=$_SGLOBAL['timestamp']?>"></script>
<?php } ?>
<?php if(!isset($_SCOOKIE['synfriend'])) { ?>
<script language="javascript"  type="text/javascript" src="cp.php?ac=friend&op=syn&rand=<?=$_SGLOBAL['timestamp']?>"></script>
<?php } ?>
<?php } ?>
<?php if(!isset($_SCOOKIE['sendmail'])) { ?>
<script language="javascript"  type="text/javascript" src="do.php?ac=sendmail&rand=<?=$_SGLOBAL['timestamp']?>"></script>
<?php } ?>

<?php if($_SGLOBAL['ad']['couplet']) { ?>
<script language="javascript" type="text/javascript" src="source/script_couplet.js"></script>
<div id="uch_couplet" style="z-index: 10; position: absolute; display:none">
<div id="couplet_left" style="position: absolute; left: 2px; top: 60px; overflow: hidden;">
<div style="position: relative; top: 25px; margin:0.5em;" onMouseOver="this.style.cursor='hand'" onClick="closeBanner('uch_couplet');"><img src="image/advclose.gif"></div>
<?php adshow('couplet'); ?>
</div>
<div id="couplet_rigth" style="position: absolute; right: 2px; top: 60px; overflow: hidden;">
<div style="position: relative; top: 25px; margin:0.5em;" onMouseOver="this.style.cursor='hand'" onClick="closeBanner('uch_couplet');"><img src="image/advclose.gif"></div>
<?php adshow('couplet'); ?>
</div>
<script type="text/javascript">
lsfloatdiv('uch_couplet', 0, 0, '', 0).floatIt();
</script>
</div>
<?php } ?>
<?php if($_SCOOKIE['reward_log']) { ?>
<script type="text/javascript">
showreward();
</script>
<?php } ?>
<?php if($_IMC['enable']) { ?>
        <?php if(!empty($_SGLOBAL['supe_uid'])) { ?>
        <link href="<?=$_IMC['install_url']?>webim/static/webim.min.css" media="all" type="text/css" rel="stylesheet"/>
        <link href="<?=$_IMC['install_url']?>webim/static/themes/<?=$_IMC['theme']?>/ui.theme.css" media="all" type="text/css" rel="stylesheet"/>
        <script src="<?=$_IMC['install_url']?>webim/static/webim_uchome.all.min.js" type="text/javascript"></script>
        <script src="<?=$_IMC['install_url']?>webim/static/i18n/webim-<?=$_IMC['local']?>.js" type="text/javascript"></script>
        <script src="<?=$_IMC['install_url']?>webim/custom.js.php?platform=uchome" type="text/javascript"></script>
        <?php } else { ?>
        <script type="text/javascript">
                (function(c){c && c.apply(null, ["_webim","", null, "/", document.domain]);})((window.Cookie && window.Cookie.set) || window.setcookie);
        </script>
        <?php } ?>
<?php } ?>

</body>
</html>
<?php } ?>
<?php ob_out();?>