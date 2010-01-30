<?php
header("Content-type: application/javascript");
include_once('common.php');
if($ucappopen['UCHOME']){
$menu = array(
	array("title" => 'doing',"icon" =>$uchomeurl."/image/app/doing.gif","link" => $uchomeurl."/space.php?do=doing"),
	array("title" => 'album',"icon" =>$uchomeurl."/image/app/album.gif","link" => $uchomeurl."/space.php?do=album"),
	array("title" => 'blog',"icon" =>$uchomeurl."/image/app/blog.gif","link" => $uchomeurl."/space.php?do=blog"),
	array("title" => 'thread',"icon" =>$uchomeurl."/image/app/mtag.gif","link" => $uchomeurl."/space.php?do=thread"),
	array("title" => 'share',"icon" =>$uchomeurl."/image/app/share.gif","link" => $uchomeurl."/space.php?do=share")
);
}
if($_SCONFIG['my_status']) {
	if(is_array($_SGLOBAL['userapp'])) { 
		foreach($_SGLOBAL['userapp'] as $value) { 
			$menu[] = array("title" => $value['appname'],"icon" =>"http://appicon.manyou.com/icons/".$value['appid'],"link" => "userapp.php?id=".$value['appid']);
		}
	}
}
if(!empty($_SGLOBAL['supe_uid'])) {
	$setting  = $_SGLOBAL['db']->fetch_array($_SGLOBAL['db']->query("SELECT * FROM ".im_tname('setting')." WHERE uid='$_SGLOBAL[supe_uid]'"));
	if(empty($setting)){
        $setting = array('uid'=>$space['uid'],'web'=>"");
        $_SGLOBAL['db']->query("INSERT INTO ".im_tname('setting')." (uid,web) VALUES ($_SGLOBAL[supe_uid],'')");
	}
	$setting = $setting["web"];
}
$setting = empty($setting) ? "{}" : $setting;
?>

//custom
(function(webim){
	var path = "";
	path = document.location.href.split("/webim");
	path = path.length > 1 ? (path[0] + "/") : "";
        var menu = webim.JSON.decode('<?php echo json_encode($menu) ?>');
	webim.extend(webim.setting.defaults.data, webim.JSON.decode('<?php echo $setting ?>'));
	var webim = window.webim, log = webim.log;
	webim.defaults.urls = {
		online:path + "webim/online.php",
		online_list:path + "webim/online_list.php",
		offline:path + "webim/offline.php",
		message:path + "webim/message.php",
		refresh:path + "webim/refresh.php",
		status:path + "webim/status.php"
	};
	webim.setting.defaults.url = path + "webim/setting.php";
	webim.history.defaults.urls = {
		load: path + "webim/histories.php",
		clear: path + "webim/clear_history.php"
	};
    webim.room.defaults.urls = {
                    member: path + "webim/members.php",
                    join: path + "webim/join.php",
                    leave: path + "webim/leave.php"
    };
	webim.buddy.defaults.url = path + "webim/buddies.php";
	webim.notification.defaults.url = path + "webim/notifications.php";
	webim.ui.emot.init({"dir": path + "webim/static/images/emot/default"});
	var soundUrls = {
		lib: path + "webim/static/assets/sound.swf",
		msg: path + "webim/static/assets/sound/msg.mp3"
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
