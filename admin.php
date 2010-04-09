<?php
include_once 'config.php';
?>
<html>
<head>
	<meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
	<title>Coda-Slider 1.1.1</title>
	<meta http-equiv="Content-Language" content="en-us" />
	
	<meta name="author" content="Niall Doherty" />
	
	<script src="lib/jquery-1.2.1.pack.js" type="text/javascript"></script>
	<script src="lib/jquery-easing.1.2.pack.js" type="text/javascript"></script>
	<script src="lib/jquery-easing-compatibility.1.2.pack.js" type="text/javascript"></script>
	<script src="lib/coda-slider.1.1.1.pack.js" type="text/javascript"></script>
		<style type="text/css">
		
		* { margin: 0; padding: 0 }
		/* I've used a hard CSS reset above, but you should consider a more sophisticated reset, such as this one: http://meyerweb.com/eric/thoughts/2007/05/01/reset-reloaded/ */
		
		p { text-align: left; margin: 15px 0 }
		
		p, ul { font-size: 13px; line-height: 1.4em } 
		
		p a, li a { color: #39c; text-decoration: none }
		
		p.intro { border-bottom: 1px solid #ccc; margin-bottom: 20px; padding: 20px 0 30px 0; text-align: center; width: 100% }
		
		p#cross-links { text-align: center }
		
		p#cross-links { border-bottom: 1px solid #ccc; margin-bottom: 30px; padding-bottom: 30px }
		
		noscript p, noscript ol { color: #a00; font-size: 13px; line-height: 1.4em; text-align: left }
		noscript a { color: #a00; text-decoration: underline }
		noscript ol { margin-left: 25px; }
		
		a:focus { outline:none }
		
		img { border: 0 }
		
		h3 { border-bottom: 1px solid silver; margin-bottom: 5px; padding-bottom: 3px; text-align: left }
		
		body {
			font-family: Verdana, Arial;
			background: #ebebeb url("images/body-bg.png") repeat-y center;
			color: #000;
			width: 800px;
			margin: auto;
			text-align: center;
			padding-bottom: 20px;
		}
		
		.stripViewer .panelContainer .panel ul {
			text-align: left;
			margin: 0 15px 0 30px;
		}
		
		.slider-wrap { /* This div isn't entirely necessary but good for getting the side arrows vertically centered */
			margin: 20px 0;
			position: relative;
			width: 100%;
		}

		/* These 2 lines specify style applied while slider is loading */
		.csw {width: 100%; height: 460px; background: #fff; overflow: scroll}
		.csw .loading {margin: 200px 0 300px 0; text-align: center}

		.stripViewer { /* This is the viewing window */
			position: relative;
			overflow: hidden; 
			border: 5px solid #000; /* this is the border. should have the same value for the links */
			margin: auto;
			width: 700px; /* Also specified in  .stripViewer .panelContainer .panel  below */
			height: 460px;
			clear: both;
			background: #fff;
		}
		
		.stripViewer .panelContainer { /* This is the big long container used to house your end-to-end divs. Width is calculated and specified by the JS  */
			position: relative;
			left: 0; top: 0;
			width: 100%;
			list-style-type: none;
			/* -moz-user-select: none; // This breaks CSS validation but stops accidental (and intentional - beware) panel highlighting in Firefox. Some people might find this useful, crazy fools. */
		}
		
		.stripViewer .panelContainer .panel { /* Each panel is arranged end-to-end */
			float:left;
			height: 100%;
			position: relative;
			width: 700px; /* Also specified in  .stripViewer  above */
		}
		
		.stripViewer .panelContainer .panel .wrapper { /* Wrapper to give some padding in the panels, without messing with existing panel width */
			padding: 10px;
		}
		
		.stripNav { /* This is the div to hold your nav (the UL generated at run time) */
			margin: auto;
		}
		
		.stripNav ul { /* The auto-generated set of links */
			list-style: none;
		}
		
		.stripNav ul li {
			float: left;
			margin-right: 2px; /* If you change this, be sure to adjust the initial value of navWidth in coda-slider.1.1.1.js */
		}
		
		.stripNav a { /* The nav links */
			font-size: 10px;
			font-weight: bold;
			text-align: center;
			line-height: 32px;
			background: #c6e3ff;
			color: #fff;
			text-decoration: none;
			display: block;
			padding: 0 15px;
		}
		
		.stripNav li.tab1 a { background: #60f }
		.stripNav li.tab2 a { background: #60c }
		.stripNav li.tab3 a { background: #63f }
		.stripNav li.tab4 a { background: #63c }
		.stripNav li.tab5 a { background: #00e }
		
		.stripNav li a:hover {
			background: #333;
		}
		
		.stripNav li a.current {
			background: #000;
			color: #fff;
		}
		
		.stripNavL, .stripNavR { /* The left and right arrows */
			position: absolute;
			top: 230px;
			text-indent: -9000em;
		}
		
		.stripNavL a, .stripNavR a {
			display: block;
			height: 40px;
			width: 40px;
		}
		
		.stripNavL {
			left: 0;
		}
		
		.stripNavR {
			right: 0;
		}
		
		.stripNavL {
			background: url("images/arrow-left.gif") no-repeat center;
		}
		
		.stripNavR {
			background: url("images/arrow-right.gif") no-repeat center;
		}
		
	</style>
		<script type="text/javascript">
		jQuery(window).bind("load", function() {
			jQuery("div#slider1").codaSlider()
			 jQuery("div#slider2").codaSlider()
			// etc, etc. Beware of cross-linking difficulties if using multiple sliders on one page.
		});
	</script>
	
</head>
<body>
<form name="config">
	<div class="slider-wrap">
		<div id="slider1" class="csw">
			<div class="panelContainer">
				<div class="panel" title="基本设置">
				<fieldset>
					<label>是否启用IM</label>
					<input name="enable" type="radio" checked="<?php echo $_IMC['enable']?"checked":"" ?>">是	
					<input name="enable" type="radio" checked="<?php echo $_IMC['enable']?"":"checked" ?>">否	
					<br>
					<label>域名(domain)</label>
					<input name="domain" value="<?php echo $_IMC['domain'] ?>" type="text">
					<br>
					<label>apikey</label>
					<input name="domain" value="<?php echo $_IMC['apikey'] ?>" type="text">
					<br>
				
				</fieldset>
				</div>
				<div class="panel" title="个性设置">
				<fieldset>
					<label>主题</label>
					<input name="theme" value="<?php echo $_IMC['theme'] ?>" type="text">
					<br>
					<label>透明度</label>
					<input name="opacity" value="<?php echo $_IMC['opacity'] ?>" type="text">
					<br>
					<label>是否开启陌生人</label>
					<input name="isStrangerOn" value="<?php echo ($_IMC['isStrangerOn']==="on")?"checked":"" ?>" type="radio">是
					<input name="isStrangerOn" value="<?php echo ($_IMC['isStrangerOn']==="off")?"checked":"" ?>" type="radio">否

					<br>
					<label>表情</label>
					<input name="emot_url" value="<?php echo $_IMC['apikey'] ?>" type="file">
					<br>
				</fieldset>
				</div>
				<div class="panel" title="高级设置">
				<fieldset>
					<label>IM服务器设置</label>
					<input name="imsvr" value="<?php echo $_IMC['imsvr'] ?>" type="text">
					<br>
					<label>IM post端口</label>
					<input name="impost" value="<?php echo $_IMC['impost'] ?>" type="text">
					<br>
					<label>IM poll端口</label>
					<input name="impoll" value="<?php echo $_IMC['impoll'] ?>" type="text">
					<br>
				</fieldset>

				</div>
			</div>
		</div>
	</div>
</form>
</body>
</html>
