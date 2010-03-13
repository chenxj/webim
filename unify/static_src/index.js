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
	webim.defaults.urls = {
		online:path + "webim/online.php?platform=uchome",
		online_list:path + "webim/online_list.php?platform=uchome",
		offline:path + "webim/offline.php?platform=uchome",
		message:path + "webim/message.php?platform=uchome",
		refresh:path + "webim/refresh.php?platform=uchome",
		status:path + "webim/status.php?platform=uchome"
	};
	webim.setting.defaults.url = path + "webim/setting.php?platform=uchome";
	webim.history.defaults.urls = {
		load: path + "webim/histories.php?platform=uchome",
		clear: path + "webim/clear_history.php?platform=uchome"
	};
	webim.room.defaults.urls = {
		member: path + "webim/members.php?platform=uchome",
		join: path + "webim/join.php?platform=uchome",
		leave: path + "webim/leave.php?platform=uchome"
	};
	webim.buddy.defaults.url = path + "webim/buddies.php?platform=uchome";
	webim.notification.defaults.url = path + "webim/notifications.php?platform=uchome";
	webim.hotpost.defaults.url = path + "webim/hotpost.php?platform=uchome";
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
		im.admins = ["25","22"];
		layout = imUI.layout;
		//imUI.addApp("hotpost");
		imUI.addApp("chatlink");
		imUI.addApp("room");
		body.appendChild(layout.element);
		//need timeout
		setTimeout(function(){imUI.initSound(soundUrls)},1000);
		//log
		imlog(imUI);
	}
	function init(){
		layout.buildUI();
		im.autoOnline() && im.online();
	}
	(document.body ? create() : webim.ui.ready(create));
	webim.ui.ready(init);

})(window.webim);

