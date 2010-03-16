/**/
/*
buddy //联系人
attributes：
data []所有信息 readonly 
methods:
get(id)
handle(data) //handle data and distribute events
online(ids, loadDelay) // 
loadDelay()
offline(ids)
update(ids) 更新用户信息 有更新时触发events:update

events:
online  //  data:[]
onlineDelay
offline  //  data:[]
update 
*/

model("buddy", {
	url:"/webim/buddy"
}, {
	_init: function(){
		var self = this;
		self.data = self.data || [];
		self.dataHash = {};
		self._cacheData = {};
		self.handle(self.data);
	},
	clear:function(){
		var self =this;
		self.data = [];
		self.dataHash = {};
		self._cacheData = {};
	},
	count: function(conditions){
		var data = extend({}, this.dataHash, this._cacheData), count = 0, t;
		for(var key in data){
			if(isObject(conditions)){
				t = true;
				for(var k in conditions){
					if(conditions[k] != data[key][k]) t = false;
				}
				if(t) count++;
			}else{
				count ++;
			}
		}
		return count;
	},
	get: function(id){
		return this.dataHash[id];
	},
	online: function(ids, loadDelay){
		this.changeStatus(ids, "online", true, loadDelay);
	},
	offline: function(ids){
		this.changeStatus(ids, "offline", false);
	},
	loadDelay: function(){
		var self = this, cache = self._cacheData, cache_ids = [];
		for(var key in cache){
			cache_ids.push(key);
		}
		self.load(cache_ids);
	},
	update: function(ids){
		this.load(ids);
	},
	changeStatus:function(ids, type, needLoad, loadDelay){
		ids = idsArray(ids);
		var l = ids.length;
		if(l){
			var self = this, cache = self._cacheData, dataHash = self.dataHash, statusData = [], id, delayData = [], dd;
			for(var i = 0; i < l; i++){
				id = ids[i];
				if(dataHash[id]){
					statusData.push({id:id, presence:type});
				}
				else{
					dd = {id:id, presence:type};
					if(!cache[id] || cache[id].presence != type)delayData.push(dd);
					if(needLoad){
						cache[id] = dd;
					}else{
						if(cache[id])
							delete cache[id];
					}
				}

			}
			self.handle(statusData);
			if(needLoad && !loadDelay)self.loadDelay();
			else if(delayData.length){
				if(needLoad)self.trigger(type + "Delay", [delayData]);
				else self.trigger(type , [delayData]);
			}
		}

	},
	_loadSuccess:function(data){
		var self = this.self || this, cache = self._cacheData, l = data.length, value , id;
		//for(var i = 0; i < l; i++){
		for(var i in data){
			value = data[i];
			id = value["id"];
			if(cache[id]){
				extend(value, cache[id]);
				delete cache[id];
			}
		}
		self.handle(data);
	},
	load: function(ids){
		ids = idsArray(ids);
		if(ids.length){
			var self = this, options = self.options;
			ajax({
				url: options.url,
				cache: false,
				dataType: "json",
				data:{ ids: ids.join(",")},
				context: self,
				success: self._loadSuccess
			});
		}
	},
	handle:function(addData){
		var self = this, data = self.data, dataHash = self.dataHash, status = {};
		addData = addData || [];
		var l = addData.length , v, type, add;
		//for(var i = 0; i < l; i++){
		for(var i in addData){
			v = addData[i], id = v.id;
			if(id){
				if(!dataHash[id]){
					dataHash[id] = {};
					data.push(dataHash[id]);
				}
				add = checkUpdate(dataHash[id], v);
				if(add){
					type = add.presence || "update";
					status[type] = status[type] || [];
					extend(dataHash[id], add);
					status[type].push(dataHash[id]);
				}

			}
		}
		for (var key in status) {
			self.trigger(key, [status[key]]);
		}

	}

});
