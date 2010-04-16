/*
*
* Depends:
* 	core.js
*
* options:
*
* attributes:
* 	data
* 	status
* 	setting
* 	history
* 	buddy
* 	connection
*
*
* methods:
* 	online
* 	offline
* 	autoOnline
* 	sendMsg
* 	sendStatus
* 	setStranger
*
* events:
* 	ready
* 	go
* 	stop
*
* 	message
* 	presence
* 	status
*/


function webim(element, options){
	var self = this;
	self.options = extend({}, webim.defaults, options);
	this._init(element, options);
}

extend(webim.prototype, objectExtend,{
	_init:function(){
		var self = this;
		self.data = {user:{}};
		self.status = new webim.status();
		self.setting = new webim.setting();
		self.buddy = new webim.buddy();
		self.room = new webim.room();
		self.history = new webim.history();
		//self.notification = new webim.notification();
                //self.hotpost= new webim.hotpost();
		self.connection = new comet(null,{jsonp:true});
		self._initEvents();
		//self.online();
	},
	ready: function(){
		var self = this;
		self._unloadFun = window.onbeforeunload;
		window.onbeforeunload = function(){
			self.refresh();
		};
		self.trigger("ready");
	},
	go: function(){
		var self = this, data = self.data, history = self.history, buddy = self.buddy, room = self.room;
		self.connection.connect(data.connection);
		history.option("userInfo", data.user);
		history.init(data.histories);
		buddy.handle(data.buddies);
		//buddy load delay
		buddy.online(data.buddy_online_ids, true);
		//rooms
		//blocked rooms
		var b = self.setting.get("block_list"), roomData = data.rooms;
		isArray(b) && roomData && each(b,function(n,v){
			roomData[v] && (roomData[v].blocked = true);
		});
		room.handle(roomData);
		room.options.ticket = data.connection.ticket;
		//handle new messages
		var n_msg = data.new_messages;
		if(n_msg && n_msg.length)
			self.trigger("message",[n_msg]);

		self.trigger("go",[data]);
	},
	stop: function(msg){
		var self = this;
		window.onbeforeunload = self._unloadFun;
		self.data.user.presence = "offline";
		self.buddy.clear();
		self.trigger("stop", msg);

	},
	autoOnline: function(){
		return !this.status.get("o");
	},
	_initEvents: function(){
		var self = this, status = self.status, setting = self.setting, history = self.history, connection = self.connection;
                connection.bind("connect",function(e, data){
                }).bind("data",function(data){
                        self.handle(data);
                }).bind("error",function(data){
                        self.stop("connect error");
                }).bind("close",function(data){
                        self.stop("disconnect");
                });
	},
	handle:function(data){
		var self = this;
		data.messages && data.messages.length && self.trigger("message",[data.messages]);
		data.presences && data.presences.length && self.trigger("presence",[data.presences]);
		data.statuses && data.statuses.length && self.trigger("status",[data.statuses]);
	},
	sendMsg: function(msg){
		var self = this;
		msg.ticket = self.data.connection.ticket;
		ajax({
			type: 'post',
			url: self.options.urls.message,
			type: 'post',
			cache: false,
			data: msg
		});
	},
	sendStatus: function(msg){
		var self = this;
		msg.ticket = self.data.connection.ticket;
		ajax({
			type: 'post',
			url: self.options.urls.status,
			type: 'post',
			cache: false,
			data: msg
		});
	},
	//        online_list:function(){
	//                var self = this;
	//                ajax({
	//                        type:"post",
	//                        dataType: "json",
	//                        url: self.options.urls.online_list,
	//                        success: function(data){
	//                                self.trigger("online_list", [data]);
	//                        },
	//                        error: function(data){
	//                                log(data, "online:error");
	//                        }
	//                });
	//
	//        },
	setStranger: function(ids){
		this.stranger_ids = idsArray(ids);
	},
	stranger_ids:[],
	online:function(){
		var self = this, status = self.status, buddy_ids = [], tabs = status.get("tabs"), tabIds = status.get("tabIds");
		//set auto open true
		status.set("o", false);
		self.ready();
		tabIds && tabIds.length && tabs && each(tabs, function(k,v){
			v["t"] == "buddy" && buddy_ids.push(k);
		});
		ajax({
			type:"post",
			dataType: "json",
			data:{                                
				buddy_ids: buddy_ids.join(","),
				stranger_ids: self.stranger_ids.join(",")
			},
			url: self.options.urls.online,
			success: function(data){
				if(!data || !data.user || !data.connection){
					self.stop("online error");
				}else{
					data.user = extend(self.data.user, data.user);
					self.data = data;
					self.go();
				}
			},
			error: function(data){
				self.stop("online error");
			}
		});

	},
	offline:function(){
		var self = this, data = self.data;
		self.status.set("o", true);
		self.connection.close();
		self.stop("offline");
		ajax({
			type: 'post',
			url: self.options.urls.offline,
			type: 'post',
			cache: false,
			data: {
				status: 'offline',
				ticket: data.connection.ticket
			}
		});

	},
	refresh:function(){
		var self = this, data = self.data;
		if(!data || !data.connection || !data.connection.ticket) return;
		ajax({
			type: 'post',
			url: self.options.urls.refresh,
			type: 'post',
			cache: false,
			data: {
				ticket: data.connection.ticket
			}
		});
	}

});
function idsArray(ids){
	return ids && ids.split ? ids.split(",") : (isArray(ids) ? ids : (parseInt(ids) ? [parseInt(ids)] : []));
}
function model(name, defaults, proto){
	function m(data,options){
		var self = this;
		self.data = data;
		self.options = extend({}, m.defaults,options);
		isFunction(self._init) && self._init();
	}
	m.defaults = defaults;
	extend(m.prototype, objectExtend, proto);
	webim[name] = m;
}
//_webim = window.webim;
window.webim = webim;

extend(webim,{
	version:"@VERSION",
	defaults:{},
	//log:log,
	idsArray: idsArray,
	now: now,
	isFunction: isFunction,
	isArray: isArray,
	isObject: isObject,
	trim: trim,
	makeArray: makeArray,
	extend: extend,
	each: each,
	inArray: inArray,
	grep: grep,
	map: map,
	JSON: JSON,
	ajax: ajax,
	model: model,
	objectExtend: objectExtend
});

