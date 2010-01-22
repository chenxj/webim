function imlog(ui){
	var im = ui.im, log = window.webim.log;
	im.connection.bind("data",function(data){
		log(data, "data");
	}).bind("error",function(data){
		log(data, "connect error");
	}).bind("close",function(data){
		log(data, "disconnect");
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
	webim.hotpost.defaults.url = path + "webim/hotpost.php";
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
		layout = imUI.layout;
		imUI.addApp("hotpost");
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

