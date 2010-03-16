/*
* 状态(cookie临时存储[刷新页面有效])
* webim.status.init(status);//初始化状态
* webim.status.all //所有状态
* webim.status(key);//get
* webim.status(key,value);//set
*/
//var d = {
//        tabs:{1:{n:5}}, // n -> notice count
//        tabIds:[1],
//        p:5, //tab prevCount
//        a:5, //tab activeTabId
//        b:0, //is buddy open
//        o:0 //has offline
//}
model("status",{
	key:"_webim"
},{
	_init:function(){
		var self = this, data = self.data;
		if (!data){
			var c = cookie(self.options.key);
			self.data = c ? JSON.decode(c) : {};
		}else{
			self._save(data);
		}
	},
	set: function(key, value){
		var options = key, self = this;
		if (typeof key == "string") {
			options = {};
			options[key] = value;
		}
		var old = self.data;
		if (checkUpdate(old, options)) {
			var _new = extend({}, old, options);
			self._save(_new);
		}
	},
	get: function(key){
		return this.data[key];
	},
	clear:function(){
		this._save({});
	},
	_save: function(data){
		this.data = data;
		cookie(this.options.key, JSON.encode(data), {
			path: '/',
			domain: document.domain
		});
	}
});

