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
var percent = 10;
var step = 1;
function versionUpdate(){
 
	pollable = true;
    $("#version_txt").html("请等待 NEXTIM 正在自动下载更新文件");
	$("#update_ctl").attr('disabled',true);
	$("#rollback_ctl").attr('disabled',true);
	$.ajax({url:'update.php',
		success:function(data){
 
			try{
				var info = jQuery.parseJSON(data);
			}catch(e){
					
			}
 
			var data = jQuery.parseJSON(data);
            if(data.isok==true)
            {
                $("#version_txt").html("非常棒！NextIM已经升级至最新版本");
			    $("#update_ctl").attr('disabled',true);
                $("#rollback_ctl").attr('disabled',false);
                return ;
            }
                $("#version_txt").html("请确保webim文件夹(包括子目录)为777权限");
			    $("#update_ctl").attr('disabled',false);
                $("#rollback_ctl").attr('disabled',false);
 
		},
		error:function(req,status,err){
				$("#update_ctl").attr('disabled',false);
				pollable = false;
		}
	});
	poll("Update");
}
function rollBack(){
	pollable = true;
	$("#rollback_ctl").attr('disabled',true);
	$.ajax({url:'rollback.php',
			success:function(data){
					var info = jQuery.parseJSON(data);
                    if(data.is_ok==true)
                    {
                        $("#version_txt").html("成功！NextIM已经恢复至至更新前版本");
                        $("#update_ctl").attr('disabled',false);
                        $("#rollback_ctl").attr('disabled',false);
                        return ;
                    }
                        $("#version_txt").html("请确保webim文件夹(包括子目录)为777权限");
                        $("#update_ctl").attr('disabled',false);
                        $("#rollback_ctl").attr('disabled',false);

				
 
			},
			error:function(req,status,err){
				$("#rollback_ctl").attr('disabled',false);
				pollable = false;
			}
	});
	poll("RollBack");
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
/*	 $("#spaceused1").progressBar(percent);
	$("#update_ctl").attr('disabled',true);*/
}
function checkbtnusable(){
		$.ajax({
			url:"update_request.php",
			data:{"cmd":"GetCurrentState"},
			success:function(data){
				try{
					data = jQuery.parseJSON(data);	
				}catch(e){
					$("#errmsg").css("display","");	
					
				}
				if ((data.state == "Update" || data.state == "Download" || data.state == "Backup") && data.percent != "100"){
						$("#update_ctl").attr('disabled',true);
				}else if ((data.state == "Rollback") && data.percent != "100"){
							$("#rollback_ctl").attr('disabled',true);
				}
			},
			error:function(req,txt,err){
				
			}});
}

function init(){
		$("#spaceused1").progressBar({height:12,width:120,	barImage:'images/progressbg_green.gif'});
 		$("#errmsg").css("display","none");
 		$("#update_ctl").attr('disabled',true);
		$("#rollback_ctl").attr('disabled',true);
}

function control(){
		$("#update_ctl").attr('disabled',false);
		$("#rollback_ctl").attr('disabled',false);	
}
function poll(preAction){
	 
	var Action = "";
  
	$.ajax({
			url:"status.php",
			success:function(data){
				var iscontinue = true;
				if (data == ""){
						control();
						return;
				}
				try{
					data = jQuery.parseJSON(data);
				}catch(e){
					//alert(e);	
					iscontinue=false;
					return;
				}
				
				Action = data.state;
 				if (data.percent && data.percent == 100){
 					percent = 100;	
 				}else{
 					percent += (step/20);	
 				}
				if (!data.isok){
					//finished
					$("#status").css("visibility","hidden");
					return ;
				}
				switch (data.state){
					case "Rollback":
						showProgress("回滚现有版本...",percent);
						break;
					//downloading...
					case "Download":
						showProgress("下载中...",percent);
						//disable update button
						break;
					//updating
					case "Update":
						showProgress("更新中...",percent);
						break;
					case "Backup":
						showProgress("备份现有版本...",percent);
						break;
					}
				},
			error:function(req,txt,err){
				iscontinue = false;		
			}
		});
			iscontinue && pollable && setTimeout(function(){poll(Action); },2500);
}
		
 
$(document).ready(function() {
	//init progressbar 
 
		$.ajax({
			url:"check.php",
			success:function(data){
				try{
					data = jQuery.parseJSON(data);
				}catch(e){
					$("#errmsg").css("display","");
				}
				
				//no update, 
				/*if (!data.Version){
					$("#version_txt").html("当前为最新版本");
 
				// request to get newest version
	//getVersion();
	//poll("");
		$.ajax({
			url:"check.php",
			success:function(data){
				data = jQuery.parseJSON(data);

				//no update, 
				if (data.update_now==false){
					$("#version_txt").html("NextIM当前为最新版本");
 
					$("#update_ctl").attr('disabled',true);
                    $("#rollback_ctl").attr('disabled',false);
					return ;
				}*/
				if (data.updata_now == 1){
					$("#version_txt").html("可更新版本 "+data.version );
				}
 
		 
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
			<div id="errmsg">
				<font color="red">出错啦,请尝试刷新页面</font>	
			</div>
			
			<div id="status">
				<div id="progress"><span id="progress_txt"></span><span id="progress_simbol"></span></div>
				<span class="progressBar" id="spaceused12"></span>
 
			</div>
		</div>
			<div id="control">
			<input name="btn3" id="update_ctl" class="btn" style="width:63px" type="button" value="升级" onclick="versionUpdate();">
			<input name="btn3" id="rollback_ctl" class="btn" style="width:63px" type="button" value="回滚" onclick="rollBack();">
		</div>
		
		
		<div id="footer">
联系(QQ) · 6168557 1034997251 30853554 100786001 <br/>
 Copyright  2007-2009 上海几维信息技术有限公司 - KIWI Inc.  苏ICP备10028328
</div>
</body>
</html>
