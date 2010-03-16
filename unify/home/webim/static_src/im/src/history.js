/*
history // 消息历史记录
attributes：
data []所有信息 readonly 
methods:
get(id)
load(ids)
clear(ids)
init(data)
handle(data) //handle data and distribute events

events:
data //id,data
clear //
*/

model("history",{
	urls:{load:"", clear:""}
}, {
	_init:function(){
		this.data = this.data || {};
	},
	get: function(id){
		return this.data[id];
	},
	handle:function(addData){
		var self = this, data = self.data, cache = {};
		addData = makeArray(addData);
		var l = addData.length , v, id, userId = self.options.userInfo.id;
		if(!l)return;
		for(var i = 0; i < l; i++){
			//for(var i in addData){
			v = addData[i];
			id = v.to == userId ? v.from : v.to;
			if(id){
				cache[id] = cache[id] || [];
				cache[id].push(v);
			}
		}
		var ids = [];
		for (var key in cache) {
			var v = cache[key];
			if(data[key]){
				data[key] = data[key].concat(v);
				self._triggerMsg(key, v);
			}else{

				ids.push(key);
			}
		}
		self.load(ids);

	},
	_triggerMsg: function(id, data){
		//this.trigger("message." + id, [data]);
		this.trigger("data", [id, data]);
	},
	clear: function(ids){
		ids = idsArray(ids);
		var self = this, l = ids.length, options = self.options, id;
		if(l){
			for(var i = 0; i < l; i++){
				id = ids[i];
				self.data[id] = [];
				self.trigger("clear", [id]);
			}
			ajax({
				url: options.urls.clear,
				cache: false,
				dataType: "json",
				data:{ ids: ids.join(",")}
			});
		}

	},
	init:function(data){
		var self = this.self || this, v;
		for(var key in data){
			v = data[key];
			self.data[key] = v;
			self._triggerMsg(key, v);
		}
	},
	load: function(ids){
		ids = idsArray(ids);
		if(ids.length){
			var self = this, options = self.options;
			for(var i = 0; i < ids.length; i++){
				self.data[ids[i]] = [];
			}
			ajax({
				url: options.urls.load,
				cache: false,
				dataType: "json",
				data:{ ids: ids.join(",")},
				context: self,
				success: self.init
			});
		}
	}

});

