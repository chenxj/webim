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

var test1, test2;
var methods = {
	"new window": function(){
		var win = new webimUI.window();
	},
	"new layout": function(){
		var win = new webimUI.layout();
	},
	"new menu": function(){
		var win = new webimUI.menu();
	},
	"new buddy":function(){
		var buddy = new webimUI.buddy();
	},
	"addEvent": function(){
		addEvent(test2,"click",function(){});
		//var win = new webimUI.window();
		//test2.appendChild(win.element);
		//test2.removeChild(win.element);
	},
	"new room":function(){
		var win = new webimUI.room();
	},
	"new setting":function(){
		var win = new webimUI.setting();
	},
	"new broadcast":function(){
		var win = new webimUI.broadcast();
	}
};
var methods2 = {
	"create imUI": function(){
		var imUI = new webimUI();
		imUI.im.autoOnline() && imUI.im.online();
	}
};

ready(function(){
	test1 = document.createElement("div");
	test2 = $("test");
	//var win = new webimUI.window();
	//var win = new webimUI.window();
	//console.log((content));
	new Benchmark(methods, { iterations: -1});
	new Benchmark(methods2, { iterations: 1});
});
