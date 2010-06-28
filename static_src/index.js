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
	var path = "";
	//webim.extend(webim.setting.defaults.data,{});
	//webim.extend(webim.setting.defaults.data,{block_list: ["1000001"]});
	
	path = document.location.href.split("/webim");
	path = path.length > 1 ? (path[0] + "/") : "";
	var menu = [{"title":"doing","icon": path + "image\/app\/doing.gif","link":"space.php?do=doing"},{"title":"album","icon": path + "image\/app\/album.gif","link":"space.php?do=album"},{"title":"blog","icon": path + "image\/app\/blog.gif","link":"space.php?do=blog"},{"title":"thread","icon": path + "image\/app\/mtag.gif","link":"space.php?do=thread"},{"title":"share","icon": path + "image\/app\/share.gif","link":"space.php?do=share"},{"title":"doing","icon": path + "image\/app\/doing.gif","link":"space.php?do=doing"},{"title":"doing","icon": path + "image\/app\/doing.gif","link":"space.php?do=doing"},{"title":"doing","icon": path + "image\/app\/doing.gif","link":"space.php?do=doing"},{"title":"doing","icon": path + "image\/app\/doing.gif","link":"space.php?do=doing"},{"title":"doing","icon": path + "image\/app\/doing.gif","link":"space.php?do=doing"},{"title":"doing","icon": path + "image\/app\/doing.gif","link":"space.php?do=doing"},{"title":"doing","icon": path + "image\/app\/doing.gif","link":"space.php?do=doing"},{"title":"doing","icon": path + "image\/app\/doing.gif","link":"space.php?do=doing"},{"title":"doing","icon": path + "image\/app\/doing.gif","link":"space.php?do=doing"},{"title":"doing","icon": path + "image\/app\/doing.gif","link":"space.php?do=doing"},{"title":"doing","icon": path + "image\/app\/doing.gif","link":"space.php?do=doing"},{"title":"doing","icon": path + "image\/app\/doing.gif","link":"space.php?do=doing"}];
	var log = webim.log;
	var platform_switch = "phpwind";
	webim.defaults.urls = {
		online:path + "webim/api/online.php?platform="+platform_switch,
		online_list:path + "webim/api/online_list.php?platform="+platform_switch,
		offline:path + "webim/api/offline.php?platform="+platform_switch,
		message:path + "webim/api/message.php?platform="+platform_switch,
		refresh:path + "webim/api/refresh.php?platform="+platform_switch,
		status:path + "webim/api/status.php?platform="+platform_switch
	};
	webim.setting.defaults.url = path + "webim/api/setting.php?platform="+platform_switch;
	webim.history.defaults.urls = {
		load: path + "webim/api/histories.php?platform="+platform_switch,
		clear: path + "webim/api/clear_history.php?platform="+platform_switch
	};
	webim.room.defaults.urls = {
		member: path + "webim/api/members.php?platform="+platform_switch,
		join: path + "webim/api/join.php?platform="+platform_switch,
		leave: path + "webim/api/leave.php?platform="+platform_switch
	};
	webim.buddy.defaults.url = path + "webim/api/buddies.php?platform="+platform_switch;
	webim.notification.defaults.url = path + "webim/api/notifications.php?platform="+platform_switch;
	webim.hotpost.defaults.url = path + "webim/api/hotpost.php?platform="+platform_switch;
	webim.ui.emot.init({"dir": path + "webim/static/images/emot/default"});
	var soundUrls = {
		lib: path + "webim/static/assets/sound.swf",
		msg: path + "webim/static/assets/sound/msg.mp3"
	};

	var body , imUI, im, layout;
	function create(){
		body = document.body;
		imUI = new webim.ui(null,{menu: menu});
		im = imUI.im;
		im.admins = ["25","26"];
		layout = imUI.layout;
		//imUI.addApp("hotpost");
		imUI.addApp("room");
		imUI.addApp("chatlink");
		body.appendChild(layout.element);
		//need timeout
		setTimeout(function(){imUI.initSound(soundUrls)},1000);
		//log
	//	imlog(imUI);
	}
	function init(){
		layout.buildUI();
		im.autoOnline() && im.online();
	}
	(document.body ? create() : webim.ui.ready(create));
	webim.ui.ready(init);

})(window.webim);

