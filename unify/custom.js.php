<?php
header("Content-type: application/javascript");
$platform = $_GET['platform'];
switch($platform){
	case 'discuz':
	include_once('common_discuz.php');
	break;
	case 'uchome':
		include_once('common_uchome.php');
		break;
}

$menu = array(
	array("title" => 'doing',"icon" =>"image\app\doing.gif","link" => "space.php?do=doing"),
	array("title" => 'album',"icon" =>"image\app\album.gif","link" => "space.php?do=album"),
	array("title" => 'blog',"icon" =>"image\app\blog.gif","link" => "space.php?do=blog"),
	array("title" => 'thread',"icon" =>"image\app\mtag.gif","link" => "space.php?do=thread"),
	array("title" => 'share',"icon" =>"image\app\share.gif","link" => "space.php?do=share")
);
if($_SCONFIG['my_status']) {
	if(is_array($_SGLOBAL['userapp'])) { 
		foreach($_SGLOBAL['userapp'] as $value) { 
			$menu[] = array("title" => $value['appname'],"icon" =>"http://appicon.manyou.com/icons/".$value['appid'],"link" => "userapp.php?id=".$value['appid']);
		}
	}
}
$setting = json_encode(setting());
?>
 
//custom
(function(webim){
	var path = "";
    var platform = "<?php echo $platform ?>";
	path = document.location.href.split("/webim");
	path = path.length > 1 ? (path[0] + "/") : "";
        var menu = webim.JSON.decode('<?php echo json_encode($menu) ?>');
	webim.extend(webim.setting.defaults.data, webim.JSON.decode('<?php echo $setting ?>'));
	var webim = window.webim, log = webim.log;
	webim.defaults.urls = {
		online: "http://localhost/home/webim/online.php?platform=" + platform,
		online_list: "http://localhost/home/webim/online_list.php?platform=" + platform,
		offline: "http://localhost/home/webim/offline.php?platform=" + platform,
		message: "http://localhost/home/webim/message.php?platform=" + platform,
		refresh: "http://localhost/home/webim/refresh.php?platform=" + platform,
		status: "http://localhost/home/webim/status.php?platform=" + platform
	};
	webim.setting.defaults.url = path + "http://localhost/home/webim/setting.php?platform=" + platform;
	webim.history.defaults.urls = {
		load: path + "http://localhost/home/webim/histories.php?platform=" + platform,
		clear: path + "http://localhost/home/webim/clear_history.php?platform=" + platform
	};
    webim.room.defaults.urls = {
                    member: path + "http://localhost/home/webim/members.php?platform=" + platform,
                    join: path + "http://localhost/home/webim/join.php?platform=" + platform,
                    leave: path + "http://localhost/home/webim/leave.php?platform=" + platform
    };
	webim.buddy.defaults.url = path + "http://localhost/home/webim/buddies.php?platform=" + platform;
	webim.notification.defaults.url = path + "http://localhost/home/webim/notifications.php?platform=" + platform;
	webim.ui.emot.init({"dir": path + "http://localhost/home/webim/static/images/emot/default"});
	var soundUrls = {
		lib: path + "http://localhost/home/webim/static/assets/sound.swf",
		msg: path + "http://localhost/home/webim/static/assets/sound/msg.mp3"
	};
	function mapIds(data){
		return webim.map(data, function(v,i){ return v.id});
	}
 
	var body , imUI, im, layout, chatlink;
	function create(){
		body = document.body;
		imUI = new webim.ui(null,{menu: menu});
		im = imUI.im;
		layout = imUI.layout;
                imUI.addApp("room");
                //imUI.addApp("chatlink");
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
		im.autoOnline() && im.online();
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
	(document.body ? create() : webim.ui.ready(create));
	webim.ui.ready(init);
 
})(webim);