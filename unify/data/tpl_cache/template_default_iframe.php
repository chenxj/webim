<?php if(!defined('IN_UCHOME')) exit('Access Denied');?><?php subtplcheck('template/default/iframe', '1268703028', 'template/default/iframe');?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=<?=$_SC['charset']?>" />
<title><?php if($space) { ?><?=$_SN[$space['uid']]?> - <?php } ?><?=$_SCONFIG['sitename']?> - Powered by UCenter Home</title>
<script language="javascript" type="text/javascript" src="source/script_common.js"></script>
<script language="javascript" type="text/javascript" src="source/script_menu.js"></script>
<script language="javascript" type="text/javascript" src="source/script_ajax.js"></script>
<style type="text/css">
* { word-break: break-all; word-wrap: break-word; }
body, th, td, input, select, textarea, button { font: 12px/1.5em Verdana, "Lucida Grande",Arial, Helvetica,sans-serif; }
body, h1, h2, h3, h4, h5, h6, p, ul, dl, dt, dd, form, fieldset { margin: 0; padding: 0; }
h1, h2, h3, h4, h5, h6 { font-size: 1em; }
ul li { list-style: none; }
a {color: #2C629E; text-decoration: none; }
a:hover { text-decoration: underline; }
a img { border: none; }
.link_td { width: 100%; height: 26px; border-bottom: 1px solid #DDD; background: #EEE; padding-left:1em; font-size:12px; }
.link_td a { color: #333; }
textarea { border: 1px solid #ddd; overflow: auto; }
.t_input { padding: 3px 2px; border: 1px solid #ddd; line-height: 16px; }
</style>
</head>

<body scroll="no">
<div id="append_parent"><iframe id="ajaxframe" name="ajaxframe" width="0" height="0" marginwidth="0" frameborder="0" src="about:blank"></iframe></div>
<div id="ajaxwaitid"></div>

<table border="0" cellPadding="0" cellSpacing="0" height="100%" width="100%">
<tr>
<td height="26" class="link_td">
<img src="image/icon/profile.gif" align="absmiddle"> <a href="<?=$_SGLOBAL['refer']?>">返回我的空间</a> <span class="pipe">|</span>
<img src="image/icon/share.gif" align="absmiddle"> <a href="javascript:;" onclick="do_share();">分享</a> <span class="pipe">|</span>
<img src="image/icon/network.gif" align="absmiddle"> <a target="_blank" href="<?=$url?>"><?=$url?></a> (<a href="javascript:;" onclick="javascript:setCopy('<?=$url?>');return false;">复制</a>)
</td>
</tr>
<tr>
<td>
<iframe id="url_mainframe" frameborder="0" scrolling="yes" name="main" src="<?=$url?>" style="height: 100%; visibility: inherit; width: 100%; z-index: 1;overflow: visible;"></iframe>
</td>
</tr>
</table>

<div id="divshare" style="position: absolute; left: 50%; top: 100px; width: 450px; w\idth: 434px; margin-left: -225px; border: 8px solid #999; background: #FFF; display: none; padding: 20px;">
<form method="post" id="shareform" name="shareform" action="cp.php?ac=share&type=link">
<table>
<tr>
<td>
<a href="javascript:hideShare()" title="关闭" style="float: right;">关闭</a>
<h2>分享链接:</h2>
<input type="text" id="link" name="link" value="<?=$url?>" class="t_input" style="width: 380px;" onkeyup="showPreview(this.value, 'preview')">
<h2>你的分享说明:</h2>
<textarea id="general" name="general" cols="40" rows="5" style="width: 380px;" onkeyup="showPreview(this.value, 'quote')"></textarea>
</td>
</tr>
<?php if(checkperm('seccode')) { ?>
<tr>
<td>
<?php if($_SCONFIG['questionmode']) { ?>
<p>请正确回答下面问题后再提交</p>
<p><?php question(); ?></p>
<p><input type="text" id="seccode" name="seccode" value="" size="15" class="t_input"></p>
<?php } else { ?>
<p><script>seccode();</script></p>
<p>请输入上面的4位字母或数字，看不清可<a href="javascript:updateseccode()">更换一张</a></p>
<p><input type="text" id="seccode" name="seccode" value="" size="15" class="t_input"></p>
<?php } ?>
</td>
</tr>
<?php } ?>
<tr>
<td>
<input type="hidden" name="refer" value="space.php?do=share">
<button name="sharesubmit" id="sharesubmit" type="submit" class="submit" value="true" onclick="ajaxpost('shareform', 'frame_share_add')">确定</button>
<span id="__shareform"></span>
</td>
</tr>
</table>
<br />
<ul class="box" style="text-align: left;">
<li id="share_li">
<div class="title">
<a href="space.php?uid=<?=$space['uid']?>"><?=$_SN[$space['uid']]?></a> 分享了一个网址
<span class="gray"><?php echo sgmdate('Y-m-d H:i',$_SGLOBAL[timestamp],1); ?></span>
</div>
<div class="feed">
<div id="preview" class="detail">
<?=$url?>
</div>
<div class="quote"><span id="quote" class="q"></span></div>
</div>
</li>
</ul>
<input type="hidden" name="formhash" value="<?php echo formhash(); ?>" /></form>
</div>

<script type="text/javascript">
function do_share() {
$('divshare').style.display = 'block';
}
function hideShare() {
$('divshare').style.display = 'none';
}
function frame_share_add(sid, result) {
if(result) {
$('divshare').innerHTML = '<br><br>分享完成了 [<a href="javascript:hideShare()">关闭</a>]<br><br><br>';
setTimeout("hideShare();", 1000);
}
}

</script>

</body>
</html><?php ob_out();?>