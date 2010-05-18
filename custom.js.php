<?php
error_reporting(0);
header("Content-type: application/javascript");
<<<<<<< HEAD
include("config.php");
=======
include_once ('config.php');


>>>>>>> 59088b1da88a54417ffa1fdf1a6891a20b077936
$platform = $_IMC['platform'] ? $_IMC['platform'] : $_GET['platform'];

include_once("lib/{$platform}.php");

<<<<<<< HEAD
=======

>>>>>>> 59088b1da88a54417ffa1fdf1a6891a20b077936
if($platform === 'uchome'){
	$menu = array(
		array("title" => 'doing',"icon" =>"image/app/doing.gif","link" => "space.php?do=doing"),
		array("title" => 'album',"icon" =>"image/app/album.gif","link" => "space.php?do=album"),
		array("title" => 'blog',"icon" =>"image/app/blog.gif","link" => "space.php?do=blog"),
		array("title" => 'thread',"icon" =>"image/app/mtag.gif","link" => "space.php?do=thread"),
		array("title" => 'share',"icon" =>"image/app/share.gif","link" => "space.php?do=share")
	);
}else if($platform === 'discuz'){
	$menu = array(
		array("title" => 'search',"icon" =>"webim/static/images/search.png","link" => "search.php"),
		array("title" => 'faq',"icon" =>"webim/static/images/faq.png","link" => "faq.php"),
		array("title" => 'nav',"icon" =>"webim/static/images/nav.png","link" => "misc.php?action=nav"),
		array("title" => 'feeds',"icon" =>"webim/static/images/feeds.png","link" => "index.php?op=feeds"),
		array("title" => 'sms',"icon" =>"webim/static/images/msm.png","link" => "pm.php")
	);
}
$menu[] = array("title" => 'imlogo',"icon" =>"webim/static/images/nextim.gif","link" => "http://www.nextim.cn");
if($_SCONFIG['my_status']) {
	if(is_array($_SGLOBAL['userapp'])) { 
		foreach($_SGLOBAL['userapp'] as $value) { 
			$menu[] = array("title" => iconv(UC_DBCHARSET,'utf-8',$value['appname']),"icon" =>"http://appicon.manyou.com/icons/".$value['appid'],"link" => "userapp.php?id=".$value['appid']);
		}
	}
}
$setting = json_encode(setting());
?>

//custom
(function(webim){
    var path = "";
    var platform = "<?php echo $platform; ?>";

    var menu = webim.JSON.decode('<?php echo json_encode($menu) ?>');
	webim.extend(webim.setting.defaults.data, webim.JSON.decode('<?php echo $setting ?>'));
	var webim = window.webim;
	webim.defaults.urls = {
		online:path + "webim/api/online.php?platform=" + platform,
		online_list:path + "webim/api/online_list.php?platform=" + platform,
		offline:path + "webim/api/offline.php?platform=" + platform,
		message:path + "webim/api/message.php?platform=" + platform,
		refresh:path + "webim/api/refresh.php?platform=" + platform,
		status:path + "webim/api/status.php?platform=" + platform
	};
	webim.setting.defaults.url = path + "webim/api/setting.php?platform="+platform;
	webim.history.defaults.urls = {
		load: path + "webim/api/histories.php?platform=" + platform,
		clear: path + "webim/api/clear_history.php?platform=" + platform
	};
    	webim.room.defaults.urls = {
                    member: path + "webim/api/members.php?platform=" + platform,
                    join: path + "webim/api/join.php?platform=" + platform,
                    leave: path + "webim/api/leave.php?platform=" + platform
    	};
	webim.buddy.defaults.url = path + "webim/api/user_info.php?platform=" + platform;
	//webim.notification.defaults.url = path + "webim/api/notifications.php?platform=" + platform;
    
	if ( platform === "discuz" ){
		webim.hotpost.defaults.url = path + "webim/api/hotpost.php?platform=" + platform;
		webim.defaults.urls.online = path + "webim/api/online.php?platform=" + platform + "&room_ids=" + getTid();
	}
	if (platform === "phpwind"){
		webim.hotpost.defaults.url = path + "webim/api/hotpost.php?platform=" + platform;
	}

	webim.ui.emot.init({"dir": path + "webim/static/images/emot/default"});
	var soundUrls = {
		lib: path + "webim/static/assets/sound.swf",
		msg: path + "webim/static/assets/sound/msg.mp3"
	};
	function mapIds(data){
		return webim.map(data, function(v,i){ return v.id});
	}
	function getTid(){
		var url = location;
		var reg = /tid=(\d*)/;
		if (reg.test(url)){
			return RegExp.$1;
		}
		return "";
	}
	function isAdmin(ids,userid){
		for (var i = 0; i < ids.length; i++){
			if (ids[i] == userid)return true;
		}
		return false;
	}
	var body , imUI, im, layout, chatlink;
	function create(){
		body = document.body;
		imUI = new webim.ui(null,{menu: menu});
		im = imUI.im;
		var adminids = "<?php echo $_IMC['admin_ids'] ?>";
		var userid = "<?php echo $_SGLOBAL['supe_uid']?>";
		im.admins = adminids?adminids.split(","):"";
		im.isadmin = isAdmin(adminids,userid);
		im.userid = userid;
		im.broadcastID = 0;
        	im.isStrangerOn = "on";
		imUI.addApp("room");
		layout = imUI.layout;
		if ( platform === "discuz"  || platform === "phpwind" ){
			imUI.addApp("hotpost");
		}
                //imUI.addApp("chatlink");
		imUI.addApp("broadcast");
		webim.hide(layout.app("broadcast").window.element);
		webim.hide(layout.app("room").window.element);
		body.appendChild(layout.element);
                setTimeout(function(){imUI.initSound(soundUrls)},1000);
		im.bind("ready",ready).bind("go",go).bind("stop",stop);
		//log
	}
	function init(){
		layout.buildUI();
		chatlink = new webim.ui.chatlink(null).bind("select",function(id){
			imUI.addChat(id);
			layout.focusChat(id);
		});
		im.buddy.bind("online",function(data){
			chatlink.online(mapIds(data));
		}).bind("onlineDelay",function(data){
			chatlink.online(mapIds(data));
		}).bind("offline",function(data){
			chatlink.offline(mapIds(data));
		});
		im.setStranger(chatlink.idsArray());
		im.autoOnline() &&  im.online() ;
	}
	function ready(){
		chatlink.enable();
	}
	function go(){
		chatlink.remove(im.data.user.id);
	}
	function stop(){
		chatlink.disable();
		chatlink.offline(chatlink.idsArray());
	}
	if (window.ActiveXObject){
		setTimeout(function(){document.body?create():webim.ui.ready(create);},1000);
		setTimeout(function(){webim.ui.ready(init);},1000);
	}else{
		document.body?create():webim.ui.ready(create);
		//webim.ui.ready(init);
		init();
	}
})(webim);
