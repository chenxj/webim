//
/* ui.chatlink:
*
options:

methods:
add(ids)
remove(ids)
online(ids)
offline(ids)
disable()
idsArray()
enable()
destroy()

events: 
select

*/

app("chatlink",{
	init: function(){
		var ui = this, im = ui.im;
		var chatlink = ui.chatlink = new webim.ui.chatlink(null).bind("select",function(id){
			ui.addChat(id);
			ui.layout.focusChat(id);
		});
/*		im.bind("go",function(data){
			chatlink.updateUI(data,"on");
		});
		im.bind("presence",function(data){
			chatlink.updateUI(data,"new");
		});*/
		im.buddy.bind("online",function(data){
			chatlink.online(mapIds(data));
		}).bind("onlineDelay",function(data){
			chatlink.online(mapIds(data));
		}).bind("offline",function(data){
			chatlink.offline(mapIds(data));
		});
		im.setStranger(chatlink.idsArray());
		function mapIds(data){
			return webim.map(data, function(v,i){ return v.id});
		}
	},
	ready: function(){
		this.chatlink.enable();
	},
	go: function(){
		this.chatlink.remove(this.im.data.user.id);
	},
	stop: function(){
		this.chatlink.disable();
		this.chatlink.offline(this.chatlink.idsArray());
	}
});
widget("chatlink",{
	filterId: function(link){
		if(!link)return false;
		var ex = /space\.php\?uid=(\d+)$|space\-(\d+)\.html$|u\.php?action=show&uid=(\d+)$/i.exec(link);
		return ex && (ex[1] || ex[2] || ex[3]);
	},
	offline: true
},{
	_init: function(){
		var self = this, element = self.element, ids = {}, options = self.options, filterId = options.filterId, anthors = {}, offline = options.offline;
		var a = document.getElementsByTagName("a"), b;

		a && each(a, function(i, el){
			var id = filterId(el.href), text = el.innerHTML;
			if(id &&  children(el).length == 0 && text){
				ids[id] = true;
				b = self._temp({id: id, title: i18n('chat with',{name: text}), title2: ""});
				el.parentNode.insertBefore(b, el.nextSibling);
				anthors[id] ? anthors[id].push(b) :(anthors[id] = [b]);
			}
		});
		var id = filterId(window.location.href);
		if(id){
			ids[id] = true;
			var el = self._temp({id: id, title: "", title2: "<a href='javascript:void 0'>"+i18n('chat with me')+"</a>" });
			removeClass(el, "webim-chatlink-disable");
			b = document.createElement("li");
			b.className = "webim-chatlink-disable";
			b.appendChild(el);
			var els = document.getElementsByTagName("*"), l = els.length;
			for(var i = 0; i < l ; i++){
				el = els[i], n = el.className;
				if(n.indexOf("spacemenu_list")!= -1 || n.indexOf("line_list")!= -1)
					{
						el.appendChild(b);
						break;
					}
			}
			anthors[id] ? anthors[id].push(b) :(anthors[id] = [b]);
		}
		self.ids = ids;
		self.anthors = anthors;
	},
	_temp:function(attr){
		var self = this;
		var el = createElement(tpl('<span id="<%=id%>" title="<%=title%>" class="webim-chatlink-disable webim-chatlink'+(self.options.offline ? '' : ' webim-chatlink-no-offline')+'"><span class="webim-chatlink-off-i"></span></span>', attr));
		addEvent(el, "click", function(e){
			self.trigger("select", this.id);
			stopPropagation(e);
			preventDefault(e);
		});
		return el;
	},
	idsArray: function(){
		var _ids = [];
		each(this.ids,function(k,v){_ids.push(k)});
		return _ids;
	},
	disable: function(){
		var self = this, ids = self.ids;
		for(var id in ids){
			var lis = self.anthors[id];
			lis && each(lis, function(i, li){ addClass(li, "webim-chatlink-disable")});
		}
	},
	enable: function(){
		var self = this, ids = self.ids;
		for(var id in ids){
			var lis = self.anthors[id];
			lis && each(lis, function(i, li){ removeClass(li, "webim-chatlink-disable")});
		}
	},
	remove: function(ids){
		ids = idsArray(ids);
		var self = this, l = ids.length, id;
		for(var i = 0; i < l; i++){
			id = ids[i];
			var lis = self.anthors[id];
			if(lis){
				each(lis, function(i, li){remove(li)});
				delete self.anthors[id];
				delete self.ids[id];
			}
		}
	},
	online: function(ids){
		ids = idsArray(ids);
		var self = this, l = ids.length;
		for(var i = 0; i < l; i++){
			var lis = self.anthors[ids[i]];
			lis && each(lis, function(i, li){ addClass(li, "webim-chatlink-on")});

		}
	},
	offline: function(ids){
		ids = idsArray(ids);
		var self = this, l = ids.length;
		for(var i = 0; i < l; i++){
			var lis = self.anthors[ids[i]];
			lis && each(lis, function(i, li){ removeClass(li, "webim-chatlink-off")});
		}
	},
	updateUI:function(ids,type){
	}

});

