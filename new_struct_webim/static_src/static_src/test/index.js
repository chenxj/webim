var path = "";
path = document.location.href.split("/webim");
path = path.length > 1 ? (path[0] + "/") : "";
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
webim.buddy.defaults.url = path + "webim/buddies.php";
webim.notification.defaults.url = path + "webim/notifications.php";
webim.ui.emot.init({"dir": path + "webim/static/images/emot/default"});

ready(function(){
	var imUI = new webimUI();
	//imUI.im.setStranger("2,3,4");
	//imUI.im.status.set("tabIds",[]);
	imUI.im.autoOnline() && imUI.im.online();
	//document.body.appendChild(layout.element);

});
