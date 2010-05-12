//
/* ui.room:
 *
 options:
 attributes：

 methods:
 add(data, [index]) //
 remove(ids)
 select(id)
 update(data, [index])
 notice
 online
 offline

 destroy()
 events: 
 select
 offline
 online

 */
app("room",{
	init: function(){
		var ui = this, im = ui.im, room = im.room, setting = im.setting,u = im.data.user, layout = ui.layout;
		var roomUI = ui.room = new webim.ui.room(null).bind("select",function(info){
			ui.addChat(info.id, {type: "room"});
			ui.layout.focusChat(info.id);
		});
		layout.addApp(roomUI, {
			title: i18n("room"),
			icon: "room",
			sticky: false,
			isMinimize: true
		}, "setting");

		ui.window && ui.window.title(i18n("room"));

		room.bind("join",function(info){
			updateRoom(info);
		}).bind("leave", function(rooms){

		}).bind("block", function(id, list){
			setting.set("block_list",list);
			updateRoom(room.get(id));
		        room.leave(id,u);
		}).bind("unblock", function(id, list){
			setting.set("block_list",list);
			updateRoom(room.get(id));
      			room.join(id,u);
		}).bind("addMember", function(room_id, info){
			var c = layout.chat(room_id);
			c && c.addMember(info, info.id == im.data.user.id);
			updateRoom(room.get(room_id));
		}).bind("removeMember", function(room_id, info){
			var c = layout.chat(room_id);
			c && c.removeMember(info.id, info.name);
			updateRoom(room.get(room_id));
		});
		//room
		function updateRoom(info){
			var name = info.name;
			info = extend({},info,{group:"group", name: name + "(" + (parseInt(info.count) + "/"+ parseInt(info.all_count)) + ")"});
			layout.updateChat(info);
			info.blocked && (info.name = name + "(" + i18n("blocked") + ")");
			roomUI.li[info.id] ? roomUI.update(info) : roomUI.add(info);
		}
	},
	ready: function(){
	},
	go: function(){
	},
	stop: function(){
	}
});
widget("room",{
        template: '<div id="webim-room" class="webim-room">\
                        <div id=":search" class="webim-room-search ui-state-default ui-corner-all"><em class="ui-icon ui-icon-search"></em><input id=":searchInput" type="text" value="" /></div>\
                        <div class="webim-room-content">\
                                <div id=":empty" class="webim-room-empty"><%=empty room%></div>\
                                <ul id=":ul"></ul>\
                        </div>\
                  </div>',
        tpl_li: '<li title=""><a href="<%=url%>" rel="<%=id%>" class="ui-helper-clearfix"><img width="25" src="<%=pic_url%>" defaultsrc="<%=default_pic_url%>" onerror="var d=this.getAttribute(\'defaultsrc\');if(d && this.src!=d)this.src=d;" /><strong><%=name%></strong><span><%=status%></span></a></li>'
},{
	_init: function(){
		var self = this;
		self.li = {
		};
		self._count = 0;
		hide(self.$);
		//self._initEvents();
	},
	_initEvents: function(){
		var self = this, $ = self.$, search = $.search, input = $.searchInput, placeholder = i18n("search room"), activeClass = "ui-state-active";
		addEvent(search.firstChild, "click",function(){
			input.focus();
		});
		input.value = placeholder;
		addEvent(input, "focus", function(){
			addClass(search, activeClass);
			if(this.value == placeholder)this.value = "";
		});
		addEvent(input, "blur", function(){
			removeClass(search, activeClass);
			if(this.value == "")this.value = placeholder;
		});
		addEvent(input, "keyup", function(){
			var list = self.li, val = this.value;
			each(self.li, function(n, li){
				if(val && (li.text || li.innerHTML.replace(/<[^>]*>/g,"")).indexOf(val) == -1) hide(li);
				else show(li);
			});
		});

	},
	_titleCount: function(){
		var self = this, _count = self._count, win = self.window, empty = self.$.empty, element = self.element;
		win && win.title(i18n("chat") + "(" + (_count ? _count : "0") + ")");
		if(!_count){
			show(empty);
		}else{
			hide(empty);
		}
		if(_count > 8){
			self.scroll(true);
		}else{
			self.scroll(false);
		}
	},
	scroll:function(is){
		toggleClass(this.element, "webim-room-scroll", is);
	},
	_updateInfo:function(el, info){
		el = el.firstChild;
		el.setAttribute("href", info.url);
		el = el.firstChild;
		el.setAttribute("defaultsrc", info.default_pic_url ? info.default_pic_url : "");
		el.setAttribute("src", info.pic_url);
		el = el.nextSibling;
		el.innerHTML = info.name;
		el = el.nextSibling;
		el.innerHTML = info.status;
		return el;
	},
	_addOne:function(info, end){
		var self = this, li = self.li, id = info.id, ul = self.$.ul;
		if(!li[id]){
			if(!info.default_pic_url)info.default_pic_url = "";
			var el = li[id] = createElement(tpl(self.options.tpl_li, info));
			//self._updateInfo(el, info);
			var a = el.firstChild;
			addEvent(a, "click",function(e){
				preventDefault(e);
				self.trigger("select", [info]);
				this.blur();
			});
			ul.appendChild(el);
		}
	},
	_updateOne:function(info){
		var self = this, li = self.li, id = info.id;
		li[id] && self._updateInfo(li[id], info);
	},
	update: function(data){
		data = makeArray(data);
		for(var i=0; i < data.length; i++){
			this._updateOne(data[i]);
		}
	},
	add: function(data){
		data = makeArray(data);
		for(var i=0; i < data.length; i++){
			this._addOne(data[i]);
		}
	},
	removeAll: function(){
		var ids = [], li = this.li;
		for(var k in li){
			ids.push(k);
		}
		this.remove(ids);
	},
	remove: function(ids){
		var id, el, li = this.li, group, li_group = this.li_group;
		ids = idsArray(ids);
		for(var i=0; i < ids.length; i++){
			id = ids[i];
			el = li[id];
			if(el){
				group = li_group[id];
				if(group){
					group.count --;
					if(group.count == 0)hide(group.el);
					group.title.innerHTML = group.name + "("+ group.count+")";
				}
				remove(el);
				delete(li[id]);
			}
		}
	},
	select: function(id){
		var self = this, el = self.li[id];
		el && el.firstChild.click();
		return el;
	},
	destroy: function(){
	}
});
