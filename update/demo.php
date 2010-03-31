<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
		<title>jQuery Progress Bar v1.1</title>
		<link rel="stylesheet" type="text/css" href="demo.css" />
		<script type="text/javascript" src="jslib/jquery.js"></script>
		<script type="text/javascript" src="jslib/jquery.progressbar.min.js"></script>
		<script>
			var progress_key = '<?= $uuid ?>';
			
			$(document).ready(function() {
				$("#spaceused1").progressBar({height:12,width:120,barImage:'images/progressbg_green.gif'});
				(function poll(){
					$.ajax({url:'index.php',success:function(data){
						switch (data.status){
							//downloading...
							case "":

								break;
							//updating
							case "":

								break;
						}
						poll();
					}});
				})();
			});
		</script>
<style>
	* { padding: 0px; margin: 0px; }
	body, html { font-family: Helvetica, Arial, Tahoma; font-size: 12px; line-height: 20px; font-color: #444; } 
	a { text-decoration: none; color: #3366cc; }
	pre { border: 1px dashed #ddd; color: #444; background-color: #eee; width: 100%; }
	#container {margin-top: 120px;margin-left:380px;}
	#status{margin-left:450px;visibility:hidden;}
	#control{margin-left:410px;}
	#logo {margin-bottom:5px}
	.btn{padding:0 4px; margin-left:9px;margin-top:10px}
</style>
	</head>
	<body>
		<div id="container">
			<div id="logo">
				<img id="logo" src="images/nextim.gif">
			</div>
			<span class="progressBar" id="spaceused1"></span>
			<strong>Some controls: </strong>
			<a href="#" onclick="$('#spaceused1').progressBar(20);">20</a> |
			<a href="#" onclick="$('#spaceused1').progressBar(40);">40</a> |
			<a href="#" onclick="$('#spaceused1').progressBar(60);">60</a> |
			<a href="#" onclick="$('#spaceused1').progressBar(80);">80</a> |
			<a href="#" onclick="$('#spaceused1').progressBar(100);">100</a>
		</div>
		<div id="status">
			<ul>
				<li>下载中...</li>
				<li>更新中...</li>
				<li>回滚中...</li>
				<li>取消中</li>
			</ul>
		</div>
		<div id="control">
			<input name="btn1" class="btn" type="button" value="更新"> <input type="button" class="btn" name="btn2" value="取消"> 
		</div>
	</body>
</html>
