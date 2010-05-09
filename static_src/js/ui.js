/* webim UI:
*
* options:
* attributes:
* 	im
* 	layout
* 	setting
* 	buddy
* 	notification
* 	menu
*
* methods:
*
* events:
*
*/

function webimUI(element, options){
	var self = this;
	self.element = element;
	self.options = extend({}, webimUI.defaults, options);
	self._init();
}
extend(webimUI.prototype, objectExtend, {
	_init:function(){
		var self = this,
		im = self.im = new webim(),
		layout = self.layout = new webimUI.layout(null,{
			chatAutoPop: im.setting.get("msg_auto_pop")
		}),
		options = self.options;
		//self.notification = new webimUI.notification();
		var d = im.setting.data;
		self.setting = new webimUI.setting(null,{
			data: {
				"buddy_sticky": d["buddy_sticky"],
				"play_sound": d["play_sound"],
				"msg_auto_pop": d["msg_auto_pop"],
				"minimize_layout": d["minimize_layout"]
			}
		});
		self.buddy = new webimUI.buddy(null,{
		});
		self.room = new webimUI.room(null,{});
		var menuData = self.options.menu;
	       	self.menu = new webimUI.menu(null,{
			data: menuData
		});
					    //render start
		layout.addApp(self.menu, {
			title: i18n("menu"),
			icon: "home",
			sticky: false,
			onlyIcon: false,
			isMinimize: true
		}, null,"shortcut");
		layout.addShortcut(menuData);

		layout.addApp(self.buddy, {
			title: i18n("chat"),
			icon: "buddy",
			sticky: im.setting.get("buddy_sticky"),
			className: "webim-buddy-window",
			//       onlyIcon: true,
			isMinimize: !im.status.get("b"),
			titleVisibleLength: 19
		});
		layout.addApp(self.setting, {
			title: i18n("setting"),
			icon: "setting",
			sticky: false,
			onlyIcon: true,
			isMinimize: true
		});
		layout.addApp(self.room, {
			title: i18n("room"),
			icon: "room",
			sticky: false,
			className: "webim-room-window",
			isMinimize: true
		}, "setting");
	/*	layout.addApp(self.notification, {
			title: i18n("notification"),
			icon: "notification",
			sticky: false,
			onlyIcon: true,
			isMinimize: true
		});
		*/
 			im.setting.get("play_sound") ? sound.enable() : sound.disable() ;
		im.setting.get("minimize_layout") ? layout.collapse() : layout.expand(); 
		self.buddy.offline();
		//document.body.appendChild(layout.element);
		//layout.buildUI();

		//render end

		self._initEvents();
	},
	addApp: function(name){
		var e = webimUI.apps[name];
		if(!e)return;
		var self = this;
		isFunction(e.init) && e.init.apply(self, []);
		self.im.bind("ready", function(){
			isFunction(e.ready) && e.ready.apply(self, []);
		}).bind("go", function(){
			isFunction(e.go) && e.go.apply(self, []);
		}).bind("stop", function(){
			isFunction(e.stop) && e.stop.apply(self, []);
		});
	},
	initSound: function(urls){
		sound.init(urls || this.options.soundUrls);
	},
	_initEvents: function(){
		var self = this, im = self.im, buddy = im.buddy, history = im.history, status = im.status, setting = im.setting, buddyUI = self.buddy,chatlink = im.chatlink, layout = self.layout, /*notificationUI = self.notification,*/ settingUI = self.setting, room = im.room;
		//im events
		im.bind("ready",function(){
			layout.changeState("ready");
      			show(layout.app("room").window.element);
			buddyUI.online();
      			settingUI.online();
		}).bind("go",function(data){
			layout.changeState("active");
      			//hide(layout.app("room").window.element);
			layout.option("user", data.user);
			date.init(data.server_time);
			self._initStatus();
			!buddyUI.window.isMinimize() && buddy.loadDelay();
			buddyUI.notice("count", buddy.count({presence:"online"}));
			setting.set(data.setting);
		}).bind("stop", function(type){
			layout.changeState("stop");
		        hide(layout.app("room").window.element);
			type == "offline" && layout.removeAllChat();
			layout.updateAllChat();
			buddyUI.offline();
			type && buddyUI.notice(type);
      			settingUI.offline();
		});
		//setting events
		setting.bind("update",function(key, val){

			switch(key){
				case "buddy_sticky": buddyUI.window.option("sticky", val);
				settingUI.check_tag(key, val);
				break;
				case "play_sound": (val ? sound.enable() : sound.disable() ); 
				settingUI.check_tag(key, val);
				break;
				case "msg_auto_pop": layout.option("chatAutoPop", val); 
				settingUI.check_tag(key, val);
				break;
				case "minimize_layout": 
					settingUI.check_tag(key, val);
				(val ? layout.collapse() : layout.expand()); 
				break;
			}
		});
		settingUI.bind("change",function(key, val){
			setting.set(key, val);
		});
    //handle 
    settingUI.bind("offline",function(e){
      preventDefault(e);
      im.trigger("stop");
      im.offline();
    });
    settingUI.bind("online",function(e){
      preventDefault(e);
      im.trigger("ready");  
      im.online();
    });

		layout.bind("collapse", function(){
			setting.set("minimize_layout", true);
		});
		layout.bind("expand", function(){
			setting.set("minimize_layout", false);
		});

		//display status
		layout.bind("displayUpdate", function(e){
			self._updateStatus(); //save status
		});

		//buddy events

		//select a buddy
		buddyUI.bind("select", function(info){
			self.addChat(info.id, {type: "buddy"});
		}).bind("online",function(){
			im.online();
		}).bind("broadcastselect",function(e){
			self.addBroadcast(e);
		});
		buddyUI.window.bind("displayStateChange",function(type){
			if(type != "minimize")buddy.loadDelay();
		});
		//some buddies online.
		buddy.bind("online", function(data){
			buddyUI.add(data);
			layout.updateChat(data);
			buddyUI.notice("count", buddy.count({presence:"online"}));
		});
		buddy.bind("onlineDelay", function(data){
			buddyUI.notice("count", buddy.count({presence:"online"}));
		});

		//some buddies offline.
		var mapId = function(a){ return isObject(a) ? a.id : a };
		buddy.bind("offline", function(data){
			buddyUI.remove(map(data, mapId));
			layout.updateChat(data);
			buddyUI.notice("count", buddy.count({presence:"online"}));
		});
		//some information has been modified.
		buddy.bind("update", function(data){
			buddyUI.update(data);
			layout.updateChat(data);
		});

		//all ready.
		//message
		im.bind("message",function(data){
			var show = false,roomData = this.room.dataHash,
         			 l = data.length, d, uid = im.data.user.id, id, c, online_ids = [], count = "+1";
			for(var i = 0; i < l; i++){
				d = data[i];
				id = d.to == uid ? d.from : d.to;
				if(!d["new"])online_ids.push(id);
				c = layout.chat(id);
				c && isFunction(c.status) && c.status("");//clear status
				if(!c){	
				   var titlename = "";
				   if (d.type === "broadcast"){
				   	titlename = "站长广播";
				   }else{
			           	titlename = (d.type === "unicast")?d.nick:roomData[id].name;
				   }
           			   if (d.type === "unicast"){
				   	if (d.from === "0"){
						self.addBroadcast(id,"update",null,"NextIM");
					}else{
				   		self.addChat(id, null, null, titlename);
					}
          			   }else if (d.type === "broadcast"){
				   	self.addBroadcast(id,null,null,titlename);
	  			   }else{
			              self.addChat(id,{type:"room"});  
          			}
			 	c = layout.chat(id);
			  }
				c && setting.get("msg_auto_pop") && !layout.activeTabId && layout.focusChat(id);
				c.window.notifyUser("information", count);
				var p = c.window.pos;
				(p == -1) && layout.setNextMsgNum(count);
				(p == 1) && layout.setPrevMsgNum(count);
				if(d.from != uid)show = true;
			}
			if(show){
				sound.play('msg');
				titleShow(i18n("new message"), 5);
			}
			history.handle(data);
			buddy.online(online_ids, 1);
		});
		function grepOffline(msg){
			return msg.type == "offline";
		}
		function grepOnline(msg){
			return msg.type == "online";
		}
		function mapFrom(a){ return a.from; }

		im.bind("presence",function(data){
			var offline = [];
			var online = [];
			if (data){
				for (var i = 0 ; i < data.length; i++){
					if (data[i].type == "offline"){
						offline.push(data[i]);
					}else if (data[i].type == "online"){
						online.push(data[i]);
					}
				}
			}
			buddy.online(map(online, mapFrom), buddyUI.window.isMinimize());
			buddy.offline(map(offline, mapFrom));
			//chatlink.online(online);
			online.length && buddyUI.notice("buddyOnline", online.pop()["nick"]);
		});
		im.bind("status",function(data){
			each(data,function(n,msg){
				var userId = im.data.user.id;
				var id = msg['from'];
				if (userId != msg.to && userId != msg.from) {
					id = msg.to; //群消息
					var nick = msg.nick;
				}else{
					var c = layout.chat(id);
					c && c.status(msg['show']);
				}
			});
		});
				//for test
		history.bind("data", function( id, data){
			var c = layout.chat(id), count = "+" + data.length;
			if(c){
				c.history.add(data);
			}
			//(c ? c.history.add(data) : im.addChat(id));
		});
		history.bind("clear", function( id){
			var c = layout.chat(id);
			c && c.history.clear();
		});

		///notification
		/*
		im.notification.bind("data",function( data){
			notificationUI.window.notifyUser("information", "+" + data.length);
			notificationUI.add(data);
		});
    setTimeout(function(){
			im.notification.load();
		}, 2000);  
		*/

	},
	__status: false,
	_initStatus: function(){
		var self = this, layout = self.layout;
		if(self.__status)return layout.updateAllChat();
		// status start
		self.__status = true;
		var status = self.im.status,
		tabs = status.get("tabs"), 
		tabIds = status.get("tabIds"),
		//prev num
		p = status.get("p"), 
		//focus tab
		a = status.get("a");

		tabIds && tabIds.length && tabs && each(tabs, function(k,v){
			//broadcast
			if (k == 0 || k === "0"){
			//when the type is broadcast ,don't show
				//self.addBroadcast(k,{type:"broadcast"},{isMinimize:true});

			}
			else{
				self.addChat(k, {type: v["t"]}, { isMinimize: true});
				layout.chat(k).window.notifyUser("information", v["n"]);
			}
		});
		p && (layout.prevCount = p) && layout._fitUI();
		a && layout.focusChat(a);
		// status end
	},
	addBroadcast: function(id,type,y,title){
		var self = this,layout = self.layout,im = self.im,history = self.im.history,u = im.data.user,isadmin =self._isAdmin(u.id);
		var _info = "";

		if (layout.chat(0))return;

		var h = history.get(0);
		if(!h)history.load('0');

		if (type === "update"){
			_info = {id:0,name:"NextIM",isadmin:false};
			layout.addBroadcast(_info,extend({user:u,history:null,block:true,emot:false,clearHistory:false,member:false,msgType:"broadcast"},{name:"NextIM"}), null);
			layout.chat(0);

		}else{
			_info = {id:0,name:tpl("<%=broadcast%>"),isadmin:isadmin};
			layout.addBroadcast(_info,extend({user:u,history:h,block:true,emot:isadmin,clearHistory:true,member:false,msgType:"broadcast"},{name:tpl("<%=broadcast%>")}), null);
			var broadcast = layout.chat(0);
			broadcast.bind("sendMsg",function(msg){
				im.sendMsg(msg);		
				history.handle(msg);
			});
		}
	},
	_isAdmin:function(id){
		var self = this,im = self.im,ids = im.admins;
       		for (var i = 0; i < ids.length; i++){
			if(id == trim(ids[i])){
				return true;
			}
		}		
		return false; 
	},
	addChat: function(id, options, winOptions, name){
		var self = this, layout = self.layout, im = self.im, history = self.im.history, buddy = im.buddy, room = im.room;
		if(layout.chat(id))return;
		if(options && options.type == "room"){
			var h = history.get(id), info = room.get(id), _info = info || {id:id, name: name || id};
			_info.presence = "online";
			layout.addChat(_info, extend({history: h, block: true, emot:true, clearHistory: false, member: true, msgType: "multicast"}, options), winOptions);
			if(!h) history.load(id);
			var chat = layout.chat(id);
			chat.bind("sendMsg", function(msg){
				im.sendMsg(msg);
				history.handle(msg);
			}).bind("select", function(info){
				buddy.online(info.id, 1);//online
				self.addChat(info.id, {type: "buddy"}, null, info.name);
				layout.focusChat(info.id);
			}).bind("block", function(d){
				room.block(d.id);
			}).bind("unblock", function(d){
				room.unblock(d.id);
			}).window.bind("close",function(){
				chat.options.info.blocked && room.leave(id);
			});
			setTimeout(function(){
				if(chat.options.info.blocked)room.join(id);
				else room.initMember(id);
			}, 500);
			isArray(info.members) && each(info.members, function(n, info){
				chat.addMember(info, info.id == im.data.user.id);
			});

		}else{
			var h = history.get(id), info = buddy.get(id);
			var _info = info || {id:id, name: name || id};
			layout.addChat(_info, extend({history: h, block: false, emot:true, clearHistory: true, member: false, msgType: "unicast"}, options), winOptions);
			if(!info) buddy.update(id);
			if(!h) history.load(id);
			layout.chat(id).bind("sendMsg", function(msg){
				im.sendMsg(msg);
				history.handle(msg);
			}).bind("sendStatus", function(msg){
				im.sendStatus(msg);
			}).bind("clearHistory", function(info){
				history.clear(info.id);
			});
		}
	},
	_updateStatus: function(){
		var self = this, layout = self.layout, _tabs = {}, panels = layout.panels;
		each(layout.tabs, function(n, v){
			_tabs[n] = {
				n: v._count(),
				t: panels[n].options.type //type: buddy,room
			};
		});
		var d = {
			//o:0, //has offline
			tabs: _tabs, // n -> notice count
			tabIds: layout.tabIds,
			p: layout.prevCount, //tab prevCount
			a: layout.activeTabId, //tab activeTabId
			b: layout.app("buddy").window.isMinimize() ? 0 : 1 //is buddy open
		}
		self.im.status.set(d);
	}
});

var _countDisplay = function(element, count){
	if (count === undefined){
		return parseInt(element.innerHTML);
	}
	else if (count){
		count = (typeof count == "number") ? count : (parseInt(element.innerHTML) + parseInt(count));
		element.innerHTML = count.toString();
		show(element);
	}
	else {
		element.innerHTML = '0';
		hide(element);
	}
	return count;
};

function mapElements(obj){
	var elements = obj.getElementsByTagName("*"), el, id, need = {}, pre = ":", preLen = pre.length;
	for(var i = elements.length - 1; i > -1; i--){
		el = elements[i];
		id = el.id;
		if(id && id.indexOf(pre) == 0)need[id.substring(preLen, id.length)] = el;
	}
	return need;
}
function createElement(str){
	var el = document.createElement("div");
	el.innerHTML = str;
	el = el.firstChild; // release memory in IE ???
	return el;
}
var tpl = (function(){
	var dic = null, re = /\<\%\=(.*?)\%\>/ig;
	function call(a, b){
		return dic && dic[b] !=undefined ? dic[b] : i18n(b);
	}
	return function(str, hash){
		if(!str)return '';
		dic = hash;
		return str.replace(re, call);
	};
})();



var plugin = {
	add: function(module, option, set) {
		var proto = webimUI[module].prototype;
		for(var i in set){
			proto.plugins[i] = proto.plugins[i] || [];
			proto.plugins[i].push([option, set[i]]);
		}
	},
	call: function(instance, name, args) {
		var set = instance.plugins[name];
		if(!set || !instance.element.parentNode) { return; }

		for (var i = 0; i < set.length; i++) {
			if (instance.options[set[i][0]]) {
				set[i][1].apply(instance.element, args);
			}
		}
	}
};

/*
* widget
* options:
* 	template
* 	className
*
* attributes:
* 	id
* 	name
* 	className
* 	element
* 	$
*
* methods:
* 	template
*
*/
var _widgetId = 1;
function widget(name, defaults, prototype){
	function m(element, options){
		var self = this;
		self.id = _widgetId++;
		self.name = name;
		self.className = "webim-" + name;
		self.options = extend({}, m['defaults'], options);

		isFunction(self._preInit) && self._preInit();
		//template
		self.element = element || (self.template && createElement(self.template())) || ( self.options.template && createElement(tpl(self.options.template)));
		if(self.element){
			self.options.className && addClass(self.element, self.options.className);
			self.$ = mapElements(self.element);
		}
		isFunction(self._init) && self._init();
		//isFunction(self._initEvents) && setTimeout(function(){self._initEvents()}, 0);
		isFunction(self._initEvents) && self._initEvents();
	}
	m.defaults = defaults;// default options;
	// add prototype
	extend(m.prototype, objectExtend, widget.prototype, prototype);
	webimUI[name] = m;
}

extend(widget.prototype, {
	_init: function(){
	}
});
function app(name, events){
	webimUI.apps[name] = events || {};
}
extend(webimUI,{
	version: "@VERSION",
	widget: widget,
	app: app,
	plugin: plugin,
	i18n: i18n,
	date: date,
	ready: ready,
	createElement: createElement,
	apps:{}
});
webim.ui = webimUI;

