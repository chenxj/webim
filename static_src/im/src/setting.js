/*
* 配置(数据库永久存储)
* Methods:
* 	get
* 	set
*
* Events:
* 	update
* 	
*/
model("setting",{
	url:"/webim/setting",
	data:{
		play_sound:true,
		buddy_sticky:true,
		minimize_layout: true,
		msg_auto_pop:true
	}
},{
	_init:function(){
		var self = this;
		self.data = extend({}, self.options.data, self.data);
	},
	get: function(key){
		return this.data[key];
	},
	set: function(key, value){
		var self = this, options = key;
		if(!key)return;
		if (typeof key == "string") {
			options = {};
			options[key] = value;
		}
		var _old = self.data,
			up = checkUpdate(_old, options);
		if ( up ) {
			each(up,function(key,val){
				self.trigger("update",[key,val]);
			});
			var _new = extend({}, _old, options);
			self.data = _new;
			ajax({
				type: 'post',
				url: self.options.url,
				dataType: 'json',
				cache: false,
				data: {data: JSON.encode(_new)}
			});
		}
	}
});
