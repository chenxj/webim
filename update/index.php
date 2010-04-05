<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
		<link rel="stylesheet" href="main.css" style="text/css">
		<title>Nextim Update</title>
		<script type="text/javascript" src="jslib/jquery-1.4.2.min.js"></script>
		<script type="text/javascript" src="jslib/jquery.progressbar.min.js"></script>
<script type="text/javascript">
function poll(version){
	$.ajax({url:'update_request.php',
		dataType:'json',
		data:{cmd:"GetCurrentState"},
		success:function(data){
			switch (data.status){
				//downloading...
			case "":
				break;
				//updating
			case "":
				break;
			}
		//	$("#status").css("visibility","hidden");
		//	$("#status").css("display","none");;

		//	poll(version);
		}
	});
}
$(document).ready(function() {
	//init progressbar 
	$("#spaceused1").progressBar({height:12,width:120,
		barImage:'images/progressbg_green.gif'});
		//sync request to get newest version
/*	$.getJSON('update_request.php?cmd=GetNewestVersionInfo',function(data){
		var versioninfo = data.GetNewestVersion.Successful.VersionInfo;
		alert(versioninfo);
		$("#version_txt").html("a");
});*/
		$.ajax({
			url:"update_request.php",
			data:{"cmd":"GetNewestVersionInfo"},
			success:function(data){
				var versioninfo = jQuery.parseJSON(data);
				//.GetNewestVersion.Successful.VersionInfo;
			//	$("#version_txt").html(versioninfo);
				poll(data);
			},
			error:function(req,txt,err){
			},
			complete:function(){
				alert('x');
			}
		}
);
});
</script>
</head>
	<body>
		<div id="container">
			<div id="logo">
			<a href="http://www.nextim.cn/">
				<img id="logo" src="images/nextim.gif"/><div class="logo_txt">领先的社区网站WEBIM</div>
			</a>
			</div>
			<div  id="update_info">
				<ul><span class="txt"><span id="version_txt"></span>版本新特性</span>
					<li>NextIM 是UC社区最出色，最先进的WEBIM插件</li>
					<li>采用与Facebook一样的标准HTML界面设计</li>
					<li>集群服务器1,000,000并发用户支持</li>
				</ul>

			</div>
			<div id="status">
				<div id="progress_txt">下载中...</div>
				<span class="progressBar" id="spaceused1"></span>
			</div>
		</div>
			<div id="control">
			<a name="btn1" class="btn txt" onclick="$('#spaceused1').progressBar(20);">更新</a> <a class="btn" name="btn2" >取消</a>
		</div>
		<div id="footer">
联系(QQ) · 6168557 1034997251 30853554 100786001 <br/>
 Copyright  2007-2009 上海几维信息技术有限公司 - KIWI Inc.  苏ICP备10028328
</div>
</body>
</html>
