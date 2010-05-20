function imlog(ui){
	var im = ui.im ;//log = window.webim.log;
	im.connection.bind("data",function(data){
	//	log(data, "data");
	}).bind("error",function(data){
		//log(data, "connect error");
	}).bind("close",function(data){
		//log(data, "disconnect");
	});
}
(function(webim){
	var path = "http://b.nextim.cn/pw/u/";
	//webim.extend(webim.setting.defaults.data,{});
	//webim.extend(webim.setting.defaults.data,{block_list: ["1000001"]});
	
	//path = document.location.href.split("/webim");
	//path = path.length > 1 ? (path[0] + "/") : "";
	var menu = [{"title":"doing","icon": path + "image\/app\/doing.gif","link":"space.php?do=doing"},{"title":"album","icon": path + "image\/app\/album.gif","link":"space.php?do=album"},{"title":"blog","icon": path + "image\/app\/blog.gif","link":"space.php?do=blog"},{"title":"thread","icon": path + "image\/app\/mtag.gif","link":"space.php?do=thread"},{"title":"share","icon": path + "image\/app\/share.gif","link":"space.php?do=share"},{"title":"doing","icon": path + "image\/app\/doing.gif","link":"space.php?do=doing"},{"title":"doing","icon": path + "image\/app\/doing.gif","link":"space.php?do=doing"},{"title":"doing","icon": path + "image\/app\/doing.gif","link":"space.php?do=doing"},{"title":"doing","icon": path + "image\/app\/doing.gif","link":"space.php?do=doing"},{"title":"doing","icon": path + "image\/app\/doing.gif","link":"space.php?do=doing"},{"title":"doing","icon": path + "image\/app\/doing.gif","link":"space.php?do=doing"},{"title":"doing","icon": path + "image\/app\/doing.gif","link":"space.php?do=doing"},{"title":"doing","icon": path + "image\/app\/doing.gif","link":"space.php?do=doing"},{"title":"doing","icon": path + "image\/app\/doing.gif","link":"space.php?do=doing"},{"title":"doing","icon": path + "image\/app\/doing.gif","link":"space.php?do=doing"},{"title":"doing","icon": path + "image\/app\/doing.gif","link":"space.php?do=doing"},{"title":"doing","icon": path + "image\/app\/doing.gif","link":"space.php?do=doing"}];
	var log = webim.log;
	webim.defaults.urls = {
		online:path + "webim/api/test/online.php?platform=phpwind",
		online_list:path + "webim/api/test/online_list.php?platform=phpwind",
		offline:path + "webim/api/test/offline.php?platform=phpwind",
		message:path + "webim/api/test/message.php?platform=phpwind",
		refresh:path + "webim/api/test/refresh.php?platform=phpwind",
		status:path + "webim/api/test/status.php?platform=phpwind"
	};
	webim.setting.defaults.url = path + "webim/api/test/setting.php?platform=phpwind";
	webim.history.defaults.urls = {
		load: path + "webim/api/test/histories.php?platform=phpwind",
		clear: path + "webim/api/test/clear_history.php?platform=phpwind"
	};
	webim.room.defaults.urls = {
		member: path + "webim/api/test/members.php?platform=phpwind",
		join: path + "webim/api/test/join.php?platform=phpwind",
		leave: path + "webim/api/test/leave.php?platform=phpwind"
	};
	webim.buddy.defaults.url = path + "webim/api/test/buddies.php?platform=phpwind";
	webim.notification.defaults.url = path + "webim/api/test/notifications.php?platform=phpwind";
	webim.hotpost.defaults.url = path + "webim/api/test/hotpost.php?platform=phpwind";
	webim.ui.emot.init({"dir": path + "webim/static/images/emot/default"});
	var soundUrls = {
		lib: path + "webim/static/assets/sound.swf",
		msg: path + "webim/static/assets/sound/msg.mp3"
	};

	var body , imUI, im, layout;
	function create(){
		body = document.body;
		var admins = ["25","26","1","6"];
		imUI = new webim.ui(null,{menu: menu,admins:admins,uid:6,broadcastID:0});
		im = imUI.im;
		im.isadmin = true;
		im.bridge = document.getElementById("webim_bridge");
		im.crossdomain = true;
		im.uid = 6;
		im.broadcastID = 0;
		layout = imUI.layout;
		imUI.addApp("room");
		imUI.addApp("broadcast");
		imUI.addApp("hotpost");
		imUI.addApp("chatlink");
		body.appendChild(layout.element);
		hide(layout.app("room").window.element);
		hide(layout.app("broadcast").window.element);
		setTimeout(function(){imUI.initSound(soundUrls)},1000);
		//log
	//	imlog(imUI);
	}
	function init(){
		layout.buildUI();
		if(im.autoOnline()){
	       	 im.online();
		}
	}
	(document.body ? create() : webim.ui.ready(create));
	webim.ui.ready(init);

})(window.webim);

