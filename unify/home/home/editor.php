<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: editor.php 12727 2009-07-16 03:23:01Z zhengqingpeng $
*/

if(empty($_GET['charset']) || !in_array(strtolower($_GET['charset']), array('gbk', 'big5', 'utf-8'))) $_GET['charset'] = '';
$allowhtml = empty($_GET['allowhtml'])?0:1;

$doodle = empty($_GET['doodle'])?0:1;

if(empty($_GET['op'])) {
//工具条
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=<?=$_GET['charset']?>" />
<title>Editor</title>
<script language="javascript" src="image/editor/editor_base.js"></script>
<style type="text/css">
body{margin:0;padding:0;}
body, td, input, button, select {font: 12px/1.5em Tahoma, Arial, Helvetica, snas-serif;}
.submit { padding: 0 10px; height: 22px; border: 1px solid; border-color: #DDD #264F6E #264F6E #DDD; background: #2782D6; color: #FFF; line-height: 20px; letter-spacing: 1px; cursor: pointer; }
a.dm{text-decoration:none}
a.dm:hover{text-decoration:underline}
a{font-size:12px}
img{border:0}
td.icon{width:24px;height:24px;text-align:center;vertical-align:middle}
td.sp{width:8px;height:24px;text-align:center;vertical-align:middle}
td.xz{width:47px;height:24px;text-align:center;vertical-align:middle}
td.bq{width:49px;height:24px;text-align:center;vertical-align:middle}
div a.n{height:16px;line-height:16px;display:block;padding:2px;color:#000000;text-decoration:none}
div a.n:hover{background:#E5E5E5}
.r_op { float: right; }
.eMenu{position:absolute;margin-top: -2px;background:#FFFFFF;border:1px solid #C5C5C5;padding:4px}
	.eMenu ul, .eMenu ul li { margin: 0; padding: 0; }
	.eMenu ul li{list-style: none;float:left}
	#editFaceBox { padding: 5px; }
		#editFaceBox li { width: 25px; height: 25px; overflow: hidden; }
.t_input { padding: 3px 2px; border-style: solid; border-width: 1px; border-color: #7C7C7C #C3C3C3 #DDD; line-height: 16px; }
a.n1{height:16px;line-height:16px;display:block;padding:2px;color:#000000;text-decoration:none}
a.n1:hover{background:#E5E5E5}
a.cs{height:15px;position:relative}
*:lang(zh) a.cs{height:12px}
.cs .cb{font-size:0;display:block;width:10px;height:8px;position:absolute;left:4px;top:3px;cursor:hand!important;cursor:pointer}
.cs span{position:absolute;left:19px;top:0px;cursor:hand!important;cursor:pointer;color:#333}

.fRd1 .cb{background-color:#800}
.fRd2 .cb{background-color:#800080}
.fRd3 .cb{background-color:#F00}
.fRd4 .cb{background-color:#F0F}
.fBu1 .cb{background-color:#000080}
.fBu2 .cb{background-color:#00F}
.fBu3 .cb{background-color:#0FF}
.fGn1 .cb{background-color:#008080}
.fGn2 .cb{background-color:#008000}
.fGn3 .cb{background-color:#808000}
.fGn4 .cb{background-color:#0F0}
.fYl1 .cb{background-color:#FC0}
.fBk1 .cb{background-color:#000}
.fBk2 .cb{background-color:#808080}
.fBk3 .cb{background-color:#C0C0C0}
.fWt0 .cb{background-color:#FFF;border:1px solid #CCC}

.mf_nowchose{height:30px;background-color:#DFDFDF;border:1px solid #B5B5B5;border-left:none}
.mf_other{height:30px;border-left:1px solid #B5B5B5}
.mf_otherdiv{height:30px;width:30px;border:1px solid #FFF;border-right-color:#D6D6D6;border-bottom-color:#D6D6D6;background-color:#F8F8F8}
.mf_otherdiv2{height:30px;width:30px;border:1px solid #B5B5B5;border-left:none;border-top:none}
.mf_link{font-size:12px;color:#000000;text-decoration:none}
.mf_link:hover{font-size:12px;color:#000000;text-decoration:underline}

.ico{height:24px;width:24px;vertical-align:middle;text-align:center}
.ico2{height:24px;width:27px;vertical-align:middle;text-align:center}
.ico3{height:24px;width:25px;vertical-align:middle;text-align:center}
.ico4{height:24px;width:8px;vertical-align:middle;text-align:center}

.icons a,.edTb,.sepline,.switch,.tbri{background-image:url(image/editor/editor_boolbar.gif)}

.toobar{position:relative;height:29px;overflow:hidden}
.tble{position:absolute;left:0;top:2px }
*:lang(zh) .tble{top:2px}
.tbri{width:20px;position:absolute;right:-3px;top:2px;background-position:0 -33px}
*:lang(zh) .tbri{top:2px;background-position:0 -31px}

.icons a{width:23px;height:23px;background-repeat:no-repeat;display:block;float:left;border:1px solid #efefef;border-top:1px solid #EFEFEF;border-bottom:1px solid #F2F3F2}
*:lang(zh) .icons a{margin-right:1px}
.icons a:hover{border-top:1px solid #CCC;border-right:1px solid #999;border-bottom:1px solid #999;border-left:1px solid #CCC;background-color:#FFF}
a.icoCut{background-position:1px 2px;}
a.icoCpy{background-position:-27px 1px;}
a.icoPse{background-position:-55px 1px}
a.icoFfm{background-position:-82px 1px;width:27px}
a.icoFsz{background-position:-111px 1px;}
*:lang(zh) a.icoFsz{margin:0}
a.icoWgt{background-position:-139px 0;}
*:lang(zh) a.icoWgt{width:21px}
a.icoIta{background-position:-166px 0;}
*:lang(zh) a.icoIta{width:21px}
a.icoUln{background-position:-196px 1px;}
*:lang(zh) a.icoUln{margin:0}
a.icoAgn{background-position:-224px 1px}
a.icoLst{background-position:-252px 1px}
a.icoOdt{background-position:-309px 1px}
a.icoIdt{background-position:-308px 1px}
a.icoFcl{background-position:-335px 1px}
a.icoBcl{background-position:-362px 1px}
a.icoUrl{background-position:-392px 1px;}
a.icoMoveUrl{background-position:-486px 1px}
a.icoRenew {background-position:-519px 1px}
a.icoFace {background-position:-553px 1px}
a.icoDoodle {background-position:-584px 1px}
a.icoImg{background-position:-420px 1px}
a.icoSwf{background-position:-447px 1px}
a.icoSwitchTxt{background-position:-638px 0px;width:23px;float:right}
a.icoSwitchMdi{background-position:-671px 0px;width:23px}

.edTb{border-bottom:1px solid #c5c5c5;background-position:0 -28px}
.sepline{width:4px;height:20px;margin-top:2px;margin-right:3px;background-position:-476px 0;background-repeat:no-repeat;float:left }
-->
</style>
<script language="JavaScript">
<!--
function fontname(obj){format('fontname',obj.innerHTML);obj.parentNode.style.display='none'}
function fontsize(size,obj){format('fontsize',size);obj.parentNode.style.display='none'}
//-->
</SCRIPT>
</head>
<body style="overflow-y:hidden">
<table cellpadding="0" cellspacing="0" width="100%" id="dvHtmlLnk" style="display:none">
<tr>
<td height="31">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="edTb">
<tr>
<td height="31" style="padding-left:5px;">

<!--纯文本状态工具栏-->
<div class="toobar">
<div class="icons tble">
<a href="javascript:;" class="icoSwitchMdi" title="切换到多媒体" onClick="changeEditType(true);return false;"></a>
</div>
</div>
</td></tr></table>
</td></tr></table>
<textarea id="dvtext" style="height:91%;width:100%;border:0px;display:none;border-top:0px;padding:7px 4px 8px 8px;font-size:14px;font-family:Arial, Helvetica, sans-serif;line-height:1.8em;"></textarea>
<div >

<table cellpadding="0" cellspacing="0" width="100%" height="100%" id="dvhtml">
<tr>
<td height="31">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="edTb">
<tr>
<td height="31" style=" padding-left:3px">

<!--多媒体状态工具栏-->
<div class="toobar">
<div class="icons tble">
<a href="javascript:;" class="icoCut" title="剪切" onClick="format('Cut');return false;"></a>
<a href="javascript:;" class="icoCpy" title="复制" onClick="format('Copy');return false;"></a>
<a href="javascript:;" class="icoPse" title="粘贴" onClick="format('Paste');return false;"></a>
<div class="sepline"></div>
<a href="javascript:;" class="icoFfm" id="imgFontface" title="字体" onClick="fGetEv(event);fDisplayElement('fontface','');return false;"></a>
<a href="javascript:;" class="icoFsz" id="imgFontsize" title="字号" onClick="fGetEv(event);fDisplayElement('fontsize','');return false;"></a>
<a href="javascript:;" class="icoWgt" onClick="format('Bold');return false;" title="加粗"></a>
<a href="javascript:;" class="icoIta" title="斜体" onClick="format('Italic');return false;"></a>
<a href="javascript:;" class="icoUln" onClick="format('Underline');return false;" title="下划线"></a>
<a href="javascript:;" class="icoFcl" title="字体颜色" onClick="foreColor(event);return false;" id="imgFontColor"></a>
<a href="javascript:;" class="icoAgn" id="imgAlign" onClick="fGetEv(event);fDisplayElement('divAlign','');return false;" title="对齐"></a>
<a href="javascript:;" class="icoLst" id="imgList" onClick="fGetEv(event);fDisplayElement('divList','');return false;"title="编号"></a>
<a href="javascript:;" class="icoOdt" id="imgInOut" onClick="fGetEv(event);fDisplayElement('divInOut','');return false;" title="缩进"></a>
<div class="sepline"></div>
<a href="javascript:;" class="icoUrl" id="icoUrl" onClick="createLink(event, 1);return false;" title="超链接"></a>
<a href="javascript:;" class="icoMoveUrl" onClick="clearLink();return false;" title="移除链接"></a>
<a href="javascript:;" class="icoImg" id="icoImg" onClick="createImg(event, 1);return false;" title="引用图片"></a>
<a href="javascript:;" class="icoSwf" id="icoSwf" onClick="createFlash(event, 1);return false;" title="引用视频FLASH"></a>
<a href="javascript:;" class="icoFace" id="faceBox" onClick="faceBox(event);return false;" title="插入表情"></a>
<?php if($doodle) { ?>
<a href="javascript:;" class="icoDoodle" id="doodleBox" onClick="doodleBox(event, this.id);return false;" title="涂鸦"></a>
<?php }?>
<a href="javascript:;" class="icoRenew" onClick="renewContent();return false;" title="恢复内容"></a>
<?php if($allowhtml) {?>
<input type="checkbox" value="1" name="switchMode" id="switchMode" style="float:left;margin-top:6px!important;margin-top:2px" onClick="setMode(this.checked)" onMouseOver="fSetModeTip(this)" onMouseOut="fHideTip()">
<?php } else {?>
<input type="hidden" value="1" name="switchMode" id="switchMode">
<?php }?>
</div>
<div class="icons tbri">
<a href="javascript:;" class="icoSwitchTxt" title="切换到纯文本" onClick="changeEditType(false, event);return false;"></a>
</div>
</div>


<!--纯文本状态工具栏-->
<div class="toobar" style="display:none" id="dvHtmlLnk">
<div class="icons tble">
<a href="javascript:;" class="icoSwitchMdi" title="切换到多媒体" onClick="changeEditType(true, event);return false;"></a>
</div>
</div>
</td>
</tr>
</table>

<div style="width:100px;height:100px;position:absolute;display:none;top:-500px;left:-500px" ID="dvPortrait"></div>
<div id="fontface" class="eMenu" style="z-index:99;display:none;top:35px;left:2px;width:110px;height:265px">
<a href="javascript:void(0)" onClick="fontname(this)" class="n" style="font:normal 12px '宋体';">宋体</a>
<a href="javascript:void(0)" onClick="fontname(this)" class="n" style="font:normal 12px '黑体';">黑体</a>
<a href="javascript:void(0)" onClick="fontname(this)" class="n" style="font:normal 12px '楷体_GB2312';">楷体_GB2312</a>
<a href="javascript:void(0)" onClick="fontname(this)" class="n" style="font:normal 12px '隶书';">隶书</a>
<a href="javascript:void(0)" onClick="fontname(this)" class="n" style="font:normal 12px '幼圆';">幼圆</a>
<a href="javascript:void(0)" onClick="fontname(this)" class="n" style="font:normal 12px Arial;">Arial</a>
<a href="javascript:void(0)" onClick="fontname(this)" class="n" style="font:normal 12px 'Arial Narrow';">Arial Narrow</a>
<a href="javascript:void(0)" onClick="fontname(this)" class="n" style="font:normal 12px 'Arial Black';">Arial Black</a>
<a href="javascript:void(0)" onClick="fontname(this)" class="n" style="font:normal 12px 'Comic Sans MS';">Comic Sans MS</a>
<a href="javascript:void(0)" onClick="fontname(this)" class="n" style="font:normal 12px Courier;">Courier</a>
<a href="javascript:void(0)" onClick="fontname(this)" class="n" style="font:normal 12px System;">System</a>
<a href="javascript:void(0)" onClick="fontname(this)" class="n" style="font:normal 12px 'Times New Roman';">Times New Roman</a>
<a href="javascript:void(0)" onClick="fontname(this)" class="n" style="font:normal 12px Verdana;">Verdana</a>
</div>
<div id="fontsize" class="eMenu" style="display:none;top:35px;left:26px;width:125px;height:120px">
<a href="javascript:void(0)" onClick="fontsize(1,this)" class="n" style="font-size:xx-small;line-height:120%;">极小</a>
<a href="javascript:void(0)" onClick="fontsize(2,this)" class="n" style="font-size:x-small;line-height:120%;">特小</a>
<a href="javascript:void(0)" onClick="fontsize(3,this)" class="n" style="font-size:small;line-height:120%;">小</a>
<a href="javascript:void(0)" onClick="fontsize(4,this)" class="n" style="font-size:medium;line-height:120%;">中</a>
<a href="javascript:void(0)" onClick="fontsize(5,this)" class="n" style="font-size:large;line-height:120%;">大</a>
</div>

<div id="divList" class="eMenu" style="display:none;top:35px;left:26px;width:60px;height:40px;"><a href="javascript:void(0)" onClick="format('Insertorderedlist');fHide(this.parentNode)" class="n">数字列表</a><a href="javascript:void(0)" onClick="format('Insertunorderedlist');fHide(this.parentNode)" class="n">符号列表</a></div>
<div id="divAlign" class="eMenu" style="display:none;top:35px;left:26px;width:60px;height:60px;"><a href="javascript:void(0)" onClick="format('Justifyleft');fHide(this.parentNode)" class="n">左对齐</a><a href="javascript:void(0)" onClick="format('Justifycenter');fHide(this.parentNode)" class="n">居中对齐</a><a href="javascript:void(0)" onClick="format('Justifyright');fHide(this.parentNode)" class="n">右对齐</a></div>
<div id="divInOut" class="eMenu" style="display:none;top:35px;left:26px;width:60px;height:40px;"><a href="javascript:void(0)" onClick="format('Indent');fHide(this.parentNode)" class="n">增加缩进</a><a href="javascript:void(0)" onClick="format('Outdent');fHide(this.parentNode)" class="n">减少缩进</a></div>

<div id="dvForeColor" class="eMenu" style="display:none;top:35px;left:26px;width:90px;">
<a href="javascript:void(0)" onClick="format(gSetColorType,'#800000')" class="n cs fRd1"><b class="cb"></b><span>暗红色</span></a>
<a href="javascript:void(0)" onClick="format(gSetColorType,'#800080')" class="n cs fRd2"><b class="cb"></b><span>紫色</span></a>
<a href="javascript:void(0)" onClick="format(gSetColorType,'#F00000')" class="n cs fRd3"><b class="cb"></b><span>红色</span></a>
<a href="javascript:void(0)" onClick="format(gSetColorType,'#F000F0')" class="n cs fRd4"><b class="cb"></b><span>鲜粉色</span></a>
<a href="javascript:void(0)" onClick="format(gSetColorType,'#000080')" class="n cs fBu1"><b class="cb"></b><span>深蓝色</span></a>
<a href="javascript:void(0)" onClick="format(gSetColorType,'#0000F0')" class="n cs fBu2"><b class="cb"></b><span>蓝色</span></a>
<a href="javascript:void(0)" onClick="format(gSetColorType,'#00F0F0')" class="n cs fBu3"><b class="cb"></b><span>湖蓝色</span></a>
<a href="javascript:void(0)" onClick="format(gSetColorType,'#008080')" class="n cs fGn1"><b class="cb"></b><span>蓝绿色</span></a>
<a href="javascript:void(0)" onClick="format(gSetColorType,'#008000')" class="n cs fGn2"><b class="cb"></b><span>绿色</span></a>
<a href="javascript:void(0)" onClick="format(gSetColorType,'#808000')" class="n cs fGn3"><b class="cb"></b><span>橄榄色</span></a>
<a href="javascript:void(0)" onClick="format(gSetColorType,'#00F000')" class="n cs fGn4"><b class="cb"></b><span>浅绿色</span></a>
<a href="javascript:void(0)" onClick="format(gSetColorType,'#F0C000')" class="n cs fYl1"><b class="cb"></b><span>橙黄色</span></a>
<a href="javascript:void(0)" onClick="format(gSetColorType,'#000000')" class="n cs fBk1"><b class="cb"></b><span>黑色</span></a>
<a href="javascript:void(0)" onClick="format(gSetColorType,'#808080')" class="n cs fBk2"><b class="cb"></b><span>灰色</span></a>
<a href="javascript:void(0)" onClick="format(gSetColorType,'#C0C0C0')" class="n cs fBk3"><b class="cb"></b><span>银色</span></a>
<a href="javascript:void(0)" onClick="format(gSetColorType,'#FFFFFF')" class="n cs fWt0"><b class="cb"></b><span>白色</span></a>
</div>

<div id="editFaceBox" class="eMenu" style="display:none;top:35px;left:26px;width:165px;"></div>

<div id="createUrl" class="eMenu" style="display:none;top:35px;left:26px;width:300px;font-size:12px">
	请输入选定文字链接地址:<br/>
	<input type="text" id="insertUrl" name="url" value="http://" class="t_input" style="width: 190px;"> <input type="button" onclick="createLink();" name="createURL" value="确定" class="submit" /> <a href="javascript:;" onclick="fHide($('createUrl'));">取消</a>
</div>
<div id="createImg" class="eMenu" style="display:none;top:35px;left:26px;width:300px;font-size:12px">
	请输入图片URL地址:<br/>
	<input type="text" id="imgUrl" name="imgUrl" value="http://" class="t_input" style="width: 190px;" /> <input type="button" onclick="createImg();" name="createURL" value="确定" class="submit" /> <a href="javascript:;" onclick="fHide($('createImg'));">取消</a>
</div>
<div id="createSwf" class="eMenu" style="display:none;top:35px;left:26px;width:400px;font-size:12px">
	请输入视频URL地址:<br/>
	<select name="vtype" id="vtype">
		<option value="0">Flash动画</option>
		<option value="1">Media视频</option>
		<option value="2">Real视频</option>
	</select>
	<input type="text" id="videoUrl" name="videoUrl" value="http://"  class="t_input" style="width: 200px;" />
	<input type="button" onclick="createFlash();" name="createURL" value="确定" class="submit" />
	<a href="javascript:;" onclick="fHide($('createSwf'));">取消</a>
</div>

</td></tr>	 
<tr><td>
<table cellpadding="0" cellspacing="0" style=" background-color:#999999;height:100%;width:100%;overflow:hidden">
<tr>
<td>
<SCRIPT LANGUAGE="JavaScript">
<!--
	function blank_load() {
		var inihtml = '';
		var obj = parent.document.getElementById('uchome-ttHtmlEditor');
		if(obj) {
			inihtml = obj.value;
		}
		if(! inihtml && !window.Event) {
			inihtml = '<div></div>';
		}
		window.frames['HtmlEditor'].document.body.innerHTML = inihtml;
	}
	if(document.all){
		document.write('<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0" id="divEditor"><tr><td style=""><IFRAME class="HtmlEditor" ID="HtmlEditor" name="HtmlEditor" style="height:100%;width:100%" frameBorder="0" marginHeight=0 marginWidth=0 src="editor.php?op=blank&charset=<?=$_GET['charset']?>" onload="blank_load();"></IFRAME></td></tr></table>');
	}else{
		document.write('<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0" id="divEditor"><tr><td style="background-color:#ffffff"><IFRAME class="HtmlEditor" ID="HtmlEditor" name="HtmlEditor" style="height:100%;width:100%;margin-left:1px;margin-bottom:1px;" frameBorder="0" marginHeight=0 marginWidth=0 src="editor.php?op=blank&charset=<?=$_GET['charset']?>" onload="blank_load();"></IFRAME></td></tr></table>');
	}
//-->
</SCRIPT>
<textarea id="sourceEditor" style="height:100%;width:100%;display:none;border:0px;font-family: Courier New,Helvetica,Arial,sans-serif;" wrap="off"></textarea>
</td>
</tr>
</table>
</td>
</tr>
</table>
</div>
<input type="hidden" name="uchome-editstatus" id="uchome-editstatus" value="html">
</body>
</html>
<?php

} else {

//空白页面
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<meta http-equiv="Content-Type" content="text/html;charset=<?=$_GET['charset']?>" />
<title>New Document</title>
<style>
body { font-size:14px; line-height:1.8em; padding-right: 4px; padding-left: 8px; padding-bottom: 8px; margin: 0px; padding-top: 8px; }
</style>
<meta content="mshtml 6.00.2900.3132" name=generator>
</head>
<body>
</body>
</html>
<?}?>