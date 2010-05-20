//
/* webim layout :
 *
 options:
 attributes：

 methods:
 addApp(widget, options)
 addShortcut(title,icon,link, isExtlink)
 chat(id)
 addChat(info, options)
 focusChat(id)
 updateChat(data)
 removeChat(ids)

 online() //
 offline()

 activate(window) // activate a window

 destroy()

 events: 
 displayUpdate //ui displayUpdate

 */

widget("layout",{
        template: '<div id="webim" class="webim webim-state-ready">\
                    <div class="webim-preload ui-helper-hidden-accessible">\
                    <div id="webim-flashlib-c">\
                    </div>\
                    </div>\
<div id=":layout" class="webim-layout webim-layout-minimize"><iframe class="webim-bgiframe" frameborder="0" tabindex="-1" src="about:blank;" ></iframe><div class="webim-layout-bg ui-state-default ui-toolbar"></div><div class="webim-ui ui-helper-clearfix">\
                            <div id=":shortcut" class="webim-shortcut">\
                            </div>\
                            <div class="webim-layout-r">\
                            <div id=":panels" class="webim-panels">\
                                <div class="webim-window-tab-wrap ui-widget webim-panels-next-wrap">\
                                            <div id=":next" class="webim-window-tab webim-panels-next ui-state-default">\
                                                    <div id=":nextMsgCount" class="webim-window-tab-count">\
                                                            0\
                                                    </div>\
                                                    <em class="ui-icon ui-icon-triangle-1-w"></em>\
                                                    <span id=":nextCount">0</span>\
                                            </div>\
                                </div>\
                                <div id=":tabsWrap" class="webim-panels-tab-wrap">\
                                        <div id=":tabs" class="webim-panels-tab">\
                                        </div>\
                                </div>\
                                <div class="webim-window-tab-wrap ui-widget webim-panels-prev-wrap">\
                                            <div id=":prev" class="webim-window-tab webim-panels-prev ui-state-default">\
                                                    <div id=":prevMsgCount" class="webim-window-tab-count">\
                                                            0\
                                                    </div>\
                                                    <span id=":prevCount">0</span>\
                                                    <em class="ui-icon ui-icon-triangle-1-e"></em>\
                                            </div>\
                                </div>\
                                <div class="webim-window-tab-wrap webim-collapse-wrap ui-widget">\
                                            <div id=":collapse" class="webim-window-tab webim-collapse ui-state-default" title="<%=collapse%>">\
                                                    <em class="ui-icon ui-icon-circle-arrow-e"></em>\
                                            </div>\
                                </div>\
                                <div class="webim-window-tab-wrap webim-expand-wrap ui-widget">\
                                            <div id=":expand" class="webim-window-tab webim-expand ui-state-default" title="<%=expand%>">\
                                                    <em class="ui-icon ui-icon-circle-arrow-w"></em>\
                                            </div>\
                                </div>\
                            </div>\
                            <div id=":apps" class="webim-apps">\
                            </div>\
                            </div>\
            </div></div>\
                    </div>',
        shortcutLength:5,
        chatAutoPop: true,
        tpl_shortcut: '<div class="webim-window-tab-wrap ui-widget webim-shortcut-item"><a class="webim-window-tab" href="<%=link%>" target="<%=target%>">\
                                                    <div class="webim-window-tab-tip">\
                                                            <strong><%=title%></strong>\
                                                    </div>\
                                                    <em class="webim-icon" style="background-image:url(<%=icon%>)"></em>\
                                            </a>\
                                            </div>'
},{
	_init: function(element, options){
		var self = this, options = self.options;
		extend(self,{
			window: window,
			apps : {},
			panels: {},
			tabWidth : 136,
			maxVisibleTabs: null,
			animationTime : 210,
			activeTabId : null,
			tabs : {},
			tabIds : [],
			nextCount : 0,
			prevCount : 0

		});

		//self.addShortcut(options.shortcuts);
		//self._initEvents();
		options.isMinimize && self.collapse();
		//self.buildUI();
		//self.element.parent("body").length && self.buildUI();
		//
		//test
	},
	changeState: function(state){
		this.element.className = "webim webim-state-" + state;//ready,go,stop
	},
	_ready:false,
	buildUI: function(e){
		var self = this, $ = self.$;
		//var w = self.element.width() - $.shortcut.outerWidth() - $.apps.outerWidth() - 55;
		var w = (windowWidth() - 45) - $.shortcut.offsetWidth - $.apps.offsetWidth - 70;
		self.maxVisibleTabs = parseInt(w / self.tabWidth);
		self._fitUI();
		self._ready = true;
	},
	_updatePrevCount: function(activeId){
		var self = this, tabIds = self.tabIds, max = self.maxVisibleTabs, len = tabIds.length, id = activeId, count = self.prevCount;
		if (len <= max) 
			return;
		if (!id) {
			count = len - max;
		}
		else {
			var nn = 0;
			for (var i = 0; i < len; i++) {
				if (tabIds[i] == id) {
					nn = i;
					break;
				}
			}
			if (nn <= count) 
				count = nn;
			else 
				if (nn >= count + max) 
					count = nn - max + 1;
		}
		self.prevCount = count;
	},
	_setVisibleTabs: function(all){
		var self = this, numPrev = self.prevCount, upcont = numPrev + self.maxVisibleTabs, tabs = self.tabs, tabIds = self.tabIds;
		var len = tabIds.length, nextN = 0, prevN = 0;
		for (var i = 0; i < len; i++) {
			var tab = tabs[tabIds[i]];
			if (i < numPrev || i >= upcont) {
				if (all) 
					show(tab.element);
				else {
					if (self.activeTabId == tabIds[i]) 
						tab.minimize();
					var n = tab._count();
					if (i < numPrev) {
						prevN += n;
						tab.pos = 1;
					}
					else {
						nextN += n;
						tab.pos = -1;
					}
					hide(tab.element);
				}
			}
			else {
				tab.pos = 0;
				show(tab.element);
			}
		}
		if (!all) {
			self.setNextMsgNum(nextN);
			self.setPrevMsgNum(prevN);
		}
	},
	setNextMsgNum: function(num){
		_countDisplay(this.$.nextMsgCount, num);
	},
	setPrevMsgNum: function(num){
		_countDisplay(this.$.prevMsgCount, num);
	},
	slideing: false,
	_slide: function(direction){
		var self = this, pcount = self.prevCount, ncount = self.nextCount;

		if ((ncount > 0 && direction == -1) || (pcount > 0 && direction == 1)) {

			self.slideing = true;
			if (ncount == 1 && direction == -1 || pcount == 1 && direction == 1) {

				self.slideing = false;
			}

			self._slideSetup(false);
			self._setVisibleTabs(true);

			if (direction == -1) {
				self.nextCount--;
				self.prevCount++;
			}
			else 
				if (direction == 1) {
					self.nextCount++;
					self.prevCount--;
				}

				var tabs = self.$.tabs, old_left = parseFloat(tabs.style.left), 
				left = -1 * self.tabWidth * self.nextCount, 
				times = parseInt(500/13),
				i = 1,
				pre = (left - old_left)/times;
				var time = setInterval(function(){
					tabs.style.left = old_left + pre*i + 'px';
					if(i == times){
						if (self.slideing) 
							self._slide(direction);
						else {
							self._fitUI();
							self._slideReset();
						}
						clearInterval(time);
						return;
					}
					i++;
				},13);
		}

	},
	_slideUp: function(){
		this.slideing = false;

	},
	_slideSetup: function(reset){
		var self = this, $ = self.$, tabsWrap = $.tabsWrap, tabs = $.tabs;

		if (!self._tabsWidth) {
			self._tabsWidth = tabs.clientWidth;
		}
		if (reset) {
			self._tabsWidth = null;
		}
		tabsWrap.style.position = reset ? '' : 'relative';
		tabsWrap.style.overflow = reset ? 'visible' : 'hidden';
		tabsWrap.style.width = reset ? '' : self._tabsWidth + "px";
		tabs.style.width = reset ? '' : self.tabWidth * self.tabIds.length + "px";
		tabs.style.position = reset ? '' : 'relative';
	},
	_slideReset: function(){
		this._slideSetup(true);

	},
	_updateCount: function(){
		var self = this, tabIds = self.tabIds, max = self.maxVisibleTabs, len = tabIds.length, pcount = self.prevCount, ncount = self.nextCount;
		if (len <= max) {
			ncount = 0;
			pcount = 0;
		}
		else {
			ncount = len - max - pcount;
			ncount = ncount < 0 ? 0 : ncount;
			pcount = len - max - ncount;
		}
		self.prevCount = pcount;
		self.nextCount = ncount;
	},
	_updateCountUI: function(){
		var self = this, $ = self.$, pcount = self.prevCount, ncount = self.nextCount;
		if (ncount <= 0) {
			addClass($.next, 'ui-state-disabled');
		}
		else {
			removeClass($.next, 'ui-state-disabled');
		}
		if (pcount <= 0) {
			addClass($.prev, 'ui-state-disabled');
		}
		else {
			removeClass($.prev, 'ui-state-disabled');
		}
		if (pcount > 0 || ncount > 0) {
			$.next.style.display = "block";
			$.prev.style.display = "block";
		}
		else {
			hide($.next);
			hide($.prev);
		}
		$.nextCount.innerHTML = ncount.toString();
		$.prevCount.innerHTML = pcount.toString();
	},
	_initEvents: function(){
		var self = this, win = self.window, $ = self.$;
		addEvent(win,"resize", function(){self.buildUI();});
		addEvent($.next,"mousedown", function(){self._slide(-1);});
		addEvent($.next,"mouseup", function(){self._slideUp();});
		disableSelection($.next);
		addEvent($.prev,"mousedown", function(){self._slide(1);});
		addEvent($.prev,"mouseup", function(){self._slideUp();});
		disableSelection($.prev);
		addEvent($.expand, "click", function(){
			self.expand();
			return false;
		});
		addEvent($.collapse, "click", function(){
			self.collapse();
			return false;
		});
		hoverClass($.collapse, "ui-state-hover", "ui-state-default");
		hoverClass($.expand, "ui-state-hover", "ui-state-default");
	},
	isMinimize: function(){
		return hasClass(this.$.layout, "webim-layout-minimize");
	},
	collapse: function(){
		var self = this;
		if(self.isMinimize()) return;
		addClass(this.$.layout, "webim-layout-minimize");
		self.trigger("collapse");
	},
	expand: function(){
		var self = this;
		if(!self.isMinimize()) return;
		removeClass(self.$.layout, "webim-layout-minimize");
		self.trigger("expand");
	},
	_displayUpdate:function(e){
		this._ready && this.trigger("displayUpdate");
	},
	_fitUI: function(){
		var self = this, $ = self.$, apps = $.apps;
		self._updateCount();
		self.$.tabs.style.left = -1 * self.tabWidth * self.nextCount + 'px';
		self._updateCountUI();
		self._setVisibleTabs();
		//self.tabs.height(h);
		self._displayUpdate();
	},
	_stickyWin: null,
	_appStateChange:function(win, state){
		var self = this;
		if(state != "minimize"){
			each(self.apps, function(key, val){
				if(val.window != win){
					val.window.minimize();
				}
			});
		}
		self._displayUpdate();
	},
	app:function(name){
		return this.apps[name];
	},
	addApp: function(app, options, before, container){
		var self = this, options = extend(options,{closeable: false});
		var win, el = app.element;
		win = new webimUI.window(null, options);
		win.html(el);
		self.$[container ? container : "apps"].insertBefore(win.element, before && self.apps[before] ? self.apps[before].window.element : null);
		app.window = win;
		win.bind("displayStateChange", function(state){ self._appStateChange(this, state);});
		self.apps[app.name] = app;
	},
	focusChat: function(id){
		var self = this, tab = self.tabs[id], panel = self.panels[id];
		tab && tab.isMinimize() && tab.restore();
		panel && panel.focus();
	},
	chat:function(id){
		return this.panels[id];
	},
	updateChat: function(data){
		data = makeArray(data);
		var self = this, info, l = data.length, panel;
		for(var i = 0; i < l; i++){
			info = data[i];
			panel = self.panels[info.id];
			panel && panel.update(info);
		}
	},
	updateAllChat:function(){
		each(this.panels, function(k,v){
			v.update();
		});
	},
	_onChatClose:function(id){
		var self = this;
		self.tabIds = grep(self.tabIds, function(v, i){
			return v != id;
		});
		delete self.tabs[id];
		delete self.panels[id];
		self._changeActive(id, true);
		self._fitUI();
	},
	_onChatChange:function(id, type){
		var self = this;
		if(type == "minimize"){
			self._changeActive(id, true);
			self._displayUpdate();
		}else{
			self._changeActive(id);
			self._fitUI();
		}
	},
	_changeActive: function(id, leave){
		var self = this, a = self.activeTabId;
		if(leave){
			a == id && (self.activeTabId = null);
		}else{
			a && a != id && self.tabs[a].minimize();
			self.activeTabId = id;
			self._updatePrevCount(id);
		}
	},
	addBroadcast:function(info,options,winOptions){
		var self = this,panels = self.panels,id = info.id,chat;
		if(!panels[id]){
			var win = self.tabs[id] = new webimUI.window(null, extend({
				isMinimize: self.activeTabId || !self.options.chatAutoPop,
				tabWidth: self.tabWidth -2,
				title:i18n("broadcast")
			},winOptions)).bind("close", function(){ self._onChatClose(id)}).bind("displayStateChange", function(state){ self._onChatChange(id,state)});
			self.tabIds.push(id);
			self.$.tabs.insertBefore(win.element, self.$.tabs.firstChild);
			chat = panels[id] = new webimUI.broadcast(null, extend({
				window: win,
				//for broadcast
				//user: option.user,
				info: info
			}, options));
			!win.isMinimize() && self._changeActive(id);
			self._fitUI();
		}
	},
	addChat: function(info, options,winOptions){
		var self = this, panels = self.panels, id = info.id, chat,win;
		if(!panels[id]){
			win = self.tabs[id] = new webimUI.window(null, extend({
				isMinimize: self.activeTabId || !self.options.chatAutoPop,
				tabWidth: self.tabWidth -2
			},winOptions)).bind("close", function(){ 
				self._onChatClose(id)}).bind("displayStateChange", 
					function(state){ self._onChatChange(id,state)});
					self.tabIds.push(id);
					self.$.tabs.insertBefore(win.element, self.$.tabs.firstChild);
					chat = panels[id] = new webimUI.chat(null, extend({
						window: win,
						user: self.options.user,
						info: info
					}, options));
				!win.isMinimize() && self._changeActive(id);
				self._fitUI();
		}//else self.focusChat(id);
	},
	removeChat: function(ids){
		ids = idsArray(ids);
		var self = this, id, l = ids.length, tab;
		for(var i = 0; i < l; i++){
			tab = self.tabs[ids[i]];
			tab && tab.close();
		}
	},
	removeAllChat: function(){
		this.removeChat(this.tabIds);
	},
	addShortcut: function(data){
		var self = this;
		if(isArray(data)){
			each(data, function(n,v){
				self.addShortcut(v);
			});
			return;
		}
		if(!isObject(data)) return;
		var content = self.$.shortcut, temp = self.options.tpl_shortcut;
		if(content.childNodes.length > self.options.shortcutLength + 1)return;
		temp = createElement(tpl(temp,{title: i18n(data.title), icon: data.icon, link: data.link, target: data.isExtlink ? "_blank" : ""}));

		hoverClass(temp.firstChild, "ui-state-hover");
		content.appendChild(temp);
	},
	addWindow: function(){
		new webimUI.window(null, {
		});
	},
	online: function(){
		var self = this, $ = self.$;
	},
	offline: function(){
		var self = this, $ = self.$;
	}

});
function windowWidth(){
	return document.compatMode === "CSS1Compat" && document.documentElement.clientWidth || document.body.clientWidth;
}
