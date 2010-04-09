<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
		<link rel="stylesheet" href="main.css" style="text/css">
		<title>Nextim Update</title>
		<script type="text/javascript" src="jslib/jquery-1.4.2.min.js"></script>
		<script type="text/javascript" src="jslib/jquery.progressbar.min.js"></script>
<script type="text/javascript">
function preProcess(data){
	var data = data.replace(/[\r\n]/g,"");
	data = data.trim();
	data = data.substr(1,data.length);
	return data;
}
function versionUpdate(){
	$.ajax({url:'update_request.php',
		data:{cmd:'Update'},
		success:function(data){
			var info = jQuery.parseJSON(preProcess(data));
			
		}
	});
}
function rollBack(){
	$.ajax({url:'update_request.php',
			data:{cmd:"Rollback"},
			success:function(data){
				var info = jQuery.parseJSON(preProcess(data));
				
			}
	});

}
function progressing(){
	var dom = $("#progress_simbol");
	while(true){
		if (dom.html().length > 3){
			dom.html(".");
		}else{
			dom.html(dom.html() + ".");
		}
	}
}
function showProgress(msg,percent){
	$("#status").css("visibility","visible");
	$("#progress_txt").html(msg);
	$("#spaceuesd1").progressBar(percent);
}

function poll(preAction){
	var iscontinue = true;
	var Action = "";
	$.ajax({
			url:"update_request.php",
			data:{"cmd":"GetCurrentState"},
			success:function(data){
				
				var data = data.replace(/[\r\n]/g,"");
				data = data.trim();
				data = data.substr(1,data.length);

				data = jQuery.parseJSON(data);
				var iscontinue = true;
				Action = data.state;
				switch (data.state){
					case "Backup":
						showProgress("备份现有版本...",data.percent);
						break;
					//downloading...
					case "Download":
						showProgress("下载中...",data.percent);
						//disable update button
						break;
					//updating
					case "Update":
						if (!data.isok){
							iscontinue = false;
							$("#progress_txt").html(data.errmsg);
							
						}else{
							showProgress("更新中...",data.percent);
							//disable update button
						}
						break;
					case "GetNewestVersion":
						iscontinue = false;
						break;
					}
				}
			});
			iscontinue &&  setTimeout(function(){poll();},2500);;
		}
		
 
 
function getVersion(){
	$.ajax({url:'update_request.php',
		async:false,
		data:{cmd:'GetNewestVersionInfo'},
		success:function(data){
			if (data){
				data = data.replace(/[\r\n]/g,"");
				data = data.substr(1,data.length);
				$("#version_txt").html(data.Version+"版本新特性");

			}
		}
	});
 
}
$(document).ready(function() {
	//init progressbar 
	$("#spaceused1").progressBar({height:12,width:120,	barImage:'images/progressbg_green.gif'});
				// request to get newest version
	//getVersion();
	poll("");
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
				var versioninfo = $.evalJSON(data);
				alert(versioninfo);
				//.GetNewestVersion.Successful.VersionInfo;
				//$("#version_txt").html(versioninfo);
				poll();
			},
			error:function(req,txt,err){
			},
			complete:function(){
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
				<span id="version_txt"></span>
				<ul>
					<li>NextIM 是UC社区最出色，最先进的WEBIM插件</li>
					<li>采用与Facebook一样的标准HTML界面设计</li>
					<li>集群服务器1,000,000并发用户支持</li>
				</ul>

			</div>
			<div id="status">
				<div id="progress"><span id="progress_txt"></span><span id="progress_simbol"></span></div>
				<span class="progressBar" id="spaceused1"></span>
			</div>
		</div>
			<div id="control">
			<input name="btn3" class="btn" style="width:63px" type="button" value="更新" onclick="versionUpdate();">
			<input name="btn3" class="btn" style="width:63px" type="button" value="回滚" onclick="versionUpdate();">
		</div>
		<div id="footer">
联系(QQ) · 6168557 1034997251 30853554 100786001 <br/>
 Copyright  2007-2009 上海几维信息技术有限公司 - KIWI Inc.  苏ICP备10028328
</div>
</body>
</html>
