<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
		<title>Nextim Update</title>
		<script type="text/javascript" src="jslib/jquery.js"></script>
		<script type="text/javascript" src="jslib/jquery.progressbar.min.js"></script>
<script type="text/javascript">
var newestversion = "";
function poll(version){
alert(version);

}
$(document).ready(function() {
	//init progressbar 
	$("#spaceused1").progressBar({height:12,width:120,
		barImage:'images/progressbg_green.gif'});
	if (newestversion === ""){
		//sync request to get newest version
		$.ajax({
			url:"update_request.php",
			async:"false",
			data:{cmd:"GetNewestVersionInfo"},
			success:function(data){
				poll(data);
			},
			complete:function(){
			}
		}
		);
	}
	$.ajax({url:'update_request.php',data:{cmd:"GetCurrentState"},
		success:function(data){
			switch (data.status){
				//downloading...
			case "":
				break;
				//updating
			case "":
				break;
			}
		}
	});
});
</script>
<style>
	* { padding: 0px; margin: 0px; }
	body, html { font-family: Helvetica, Arial, Tahoma; font-size: 13px; line-height: 20px; font-color: #444; } 
	a { text-decoration: none; color: #3366cc; }
	pre { border: 1px dashed #ddd; color: #444; background-color: #eee; width: 100%; }
	#container {margin-top: 120px;margin-left:380px;}
	#status{margin-left:60px;visibility:;margin-top:20px}
	#control{margin-left:430px;}
	#logo {border:none;margin-bottom:5px;}
	#update_info {border:1px solid blue;padding:3px;width:310px;margin-top:65px;margin-left:-30px;-webkit-border-radius:12px;padding:4px;width:310px;center:center}
	#update_info ul{margin-left:25px;}
	#spaceused1{margin-left:50px;}
	.logo_txt{margin-left:99px;margin-top:-22px;font-size:13px ; text-shadow:1px 1px 1px #ffb69a}
	.btn{color:blue;width:69px;heigth:12px;padding:1px 8px; margin-left:0;margin-top:10px;margin-right:28px;-webkit-border-radius:9px}
	.btn btn1{margin-left:12px;}
	#footer{ width: 100%;margin-top:163px;text-align: center;font-size:12px; border-top: #e5e5e5 1px solid; padding-top: 10px; padding-bottom: 20px; }
	.clearfix:after{visibility:hidden;display:block;font-size:0;content:"";clear:both;height:0;}
	*.html .clearfix{zoom:1}
	*:first-child+html .clearfix{zoom:1}
	#status {background:url(images/view_refresh.png) no-repeat  ; }
	#status ul{list-style-type:none;}
	#status ul li{margin-left:20px}
	#update_info ul span{font:13px ;color:blue;margin:-19px;}
</style>
	</head>
	<body>
<input type="hidden" id="version" value="">
		<div id="container">
			<div id="logo">
			<a href="http://www.nextim.cn/">
				<img id="logo" src="images/nextim.gif"/><div class="logo_txt">领先的社区网站WEBIM</div>
			</a>
			</div>
			<div  id="update_info">
				<ul><span class="txt">Vxx_xx版本新特性</span>
					<li>NextIM 是UC社区最出色，最先进的WEBIM插件</li>
					<li>采用与Facebook一样的标准HTML界面设计</li>
					<li>集群服务器1,000,000并发用户支持</li>
				</ul>

			</div>
			<div id="status">
				<ul>
					<li>下载中...</li>
				<!--	<li>更新中...</li>
					<li>回滚中...</li>
					<li>取消中</li>  -->
				</ul>
			</div>
			<span class="progressBar" id="spaceused1"></span>
		</div>
			<div id="control">
			<input name="btn1" class="btn txt" onclick="$('#spaceused1').progressBar(20);"type="button" value="更新"> <input type="button" class="btn" name="btn2" value="取消"> 
		</div>
		<div id="footer">
联系(QQ) · 6168557 1034997251 30853554 100786001 <br/>
 Copyright  2007-2009 上海几维信息技术有限公司 - KIWI Inc.  苏ICP备10028328
</div>
	</body>
</html>
