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
var iscontinue = true;
var pollable = true;
function versionUpdate(){
	pollable = true;
	$("#update_ctl").attr('disabled',true);
	$.ajax({url:'update_request.php',
		data:{cmd:'Update'},
		success:function(data){
			try{
				var info = jQuery.parseJSON(data);
			}catch(e){
					
			}
			poll("RollBack");
		},
		error:function(req,status,err){
				$("#update_ctl").attr('disabled',false);
				pollable = false;
		}
	});
}
function rollBack(){
	pollable = true;
	$("#rollback_ctl").attr('disabled',true);
	$.ajax({url:'update_request.php',
			data:{cmd:"Rollback"},
			success:function(data){
				try{
					var info = jQuery.parseJSON(data);
				}catch(e){
					
				}
				poll("RollBack");
			},
			error:function(req,status,err){
				$("#rollback_ctl").attr('disabled',false);
				pollable = false;
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
	$("#update_ctl").attr('disabled',true);
}

function poll(preAction){
	
	var Action = "";
	$.ajax({
			url:"update_request.php",
			data:{"cmd":"GetCurrentState"},
			success:function(data){
				var iscontinue = true;
				
				try{
					data = jQuery.parseJSON(data);
				}catch(e){
					//alert(e);	
					iscontinue=false;
				}
				
				Action = data.state;
 
				if (data.isok){
					//finished
					$("#status").css("visibility","hidden");
					return ;
				}
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
						showProgress("更新中...",data.percent);
						break;
					}
				}
			});
			iscontinue && pollable && setTimeout(function(){poll(Action);},2500);;
		}
		
 
$(document).ready(function() {
	//init progressbar 
	$("#spaceused1").progressBar({height:12,width:120,	barImage:'images/progressbg_green.gif'});
				// request to get newest version
	//getVersion();
	//poll("");
		$.ajax({
			url:"update_request.php",
			data:{"cmd":"GetNewestVersionInfo"},
			success:function(data){
				try{
					data = jQuery.parseJSON(data);
				}catch(e){
					//err of return data;
					//alert(e);	
				}
				//no update, 
				if (!data.Version){
					$("#version_txt").html("当前为最新版本");
					$("#update_ctl").attr('disabled',true);
					return ;
				}
				$("#version_txt").html("V"+data.Version+"版本新特性");
				//poll("");
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
			<div id="errmsg">
				<font color="red">出错啦</font>	
			</div>
			</div>
			<div id="status">
				<div id="progress"><span id="progress_txt"></span><span id="progress_simbol"></span></div>
				<span class="progressBar" id="spaceused1"></span>
			</div>
		</div>
			<div id="control">
			<input name="btn3" id="update_ctl" class="btn" style="width:63px" type="button" value="升级" onclick="versionUpdate();">
			<input name="btn3" id="rollback_ctl" class="btn" style="width:63px" type="button" value="回滚" onclick="rollback();">
		</div>
		
		
		<div id="footer">
联系(QQ) · 6168557 1034997251 30853554 100786001 <br/>
 Copyright  2007-2009 上海几维信息技术有限公司 - KIWI Inc.  苏ICP备10028328
</div>
</body>
</html>
