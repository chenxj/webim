/* webim ui window:
 *
 options:
 attributes：
 active //boolean
 displayState //normal, maximize, minimize

 methods:
 html()
 title(str, icon)
 notifyUser(type,count)  //type in [air.NotificationType.INFORMATIONAL, air.NotificationType.CRITICAL]
 isMinimize()
 isMaximize()
 activate()
 deactivate()
 maximize()
 restore()
 minimize()
 close() //
 height()

 events: 
 //ready
 activate
 deactivate
 displayStateChange
 close
 resize
 move
 */
widget("window", {
        isMinimize: false,
        minimizable:true,
        maximizable:false,
        closeable:true,
        sticky: true,
        titleVisibleLength: 12,
        count: 0, // notifyUser if count > 0
	//A box with position:absolute next to a float may disappear
	//http://www.brunildo.org/test/IE_raf3.html
	//here '<div><div id=":window"'
        template:'<div id=":webim-window" class="webim-window ui-widget">\
                                            <div class="webim-window-tab-wrap">\
                                            <div id=":tab" class="webim-window-tab ui-state-default">\
                                            <div class="webim-window-tab-inner">\
                                                    <div id=":tabTip" class="webim-window-tab-tip">\
                                                            <strong id=":tabTipC"><%=tooltip%></strong>\
                                                    </div>\
                                                    <a id=":tabClose" title="<%=close%>" class="webim-window-close" href="#close"><em class="ui-icon ui-icon-close"><%=close%></em></a>\
                                                    <div id=":tabCount" class="webim-window-tab-count">\
                                                            0\
                                                    </div>\
                                                    <em id=":tabIcon" class="webim-icon webim-icon-chat"></em>\
                                                    <h4 id=":tabTitle"><%=title%></h4>\
                                            </div>\
                                            </div>\
                                            </div>\
                                            <div><div id=":window" class="webim-window-window">\
						<iframe id=":bgiframe" class="webim-bgiframe" frameborder="0" tabindex="-1" src="about:blank;" ></iframe>\
                                                    <div id=":header" class="webim-window-header ui-widget-header ui-corner-top">\
                                                            <span id=":actions" class="webim-window-actions">\
                                                                    <a id=":minimize" title="<%=minimize%>" class="webim-window-minimize" href="#minimize"><em class="ui-icon ui-icon-minus"><%=minimize%></em></a>\
                                                                    <a id=":maximize" title="<%=maximize%>" class="webim-window-maximize" href="#maximize"><em class="ui-icon ui-icon-plus"><%=maximize%></em></a>\
                                                                    <a id=":close" title="<%=close%>" class="webim-window-close" href="#close"><em class="ui-icon ui-icon-close"><%=close%></em></a>\
                                                            </span>\
                                                            <h4 id=":headerTitle"><%=title%></h4>\
                                                    </div>\
                                                    <div id=":content" class="webim-window-content ui-widget-content">\
                                                    </div>\
                                            </div>\
                                            </div>\
                                            </div>'
},
{
	html: function(obj){
		return this.$.content.appendChild(obj);
	},
	_init: function(element, options){
		var self = this, options = extend(self.options,options), $ = self.$;
		element = self.element;
		element.window = self;
		//$.title = $.headerTitle.add($.tabTitle);
		options.tabWidth && ($.tab.style.width = options.tabWidth + "px");
		self.title(options.title, options.icon);
		!options.minimizable && hide($.minimize);
		!options.maximizable && hide($.maximize);
		if(!options.closeable){
		       	hide($.tabClose);
		       	hide($.close);
		}
		if(options.isMinimize){
			self.minimize();
		}else{
			self.restore();
		}
		if(options.onlyIcon){
			hide($.tabTitle);
		}else{
			remove($.tabTip);
		}
		options.count && self.notifyUser("information", options.count);
		options.className && addClass(self,options.className);
		self.dragable = options.dragable;
		//self._initEvents();
		//self._fitUI();
		//setTimeout(function(){self.trigger("ready");},0);
		winManager(self);
	},
	notifyUser: function(type, count){
		var self = this, $ = self.$;
		if(type == "information"){
			if(self.isMinimize()){
				if(_countDisplay($.tabCount, count)){
					addClass($.tab,"ui-state-highlight");
					removeClass($.tab, "ui-state-default");
				}
			}
		}
	},
	_count: function(){
		return _countDisplay(this.$.tabCount);
	},
	title: function(title, icon){
		var self = this, $ = self.$, tabIcon = $.tabIcon;
		if(icon){
			if(isUrl(icon)){
				tabIcon.className = "webim-icon";
				tabIcon.style.backgroundImage = "url("+ icon +")";
			}
			else{
				tabIcon.className = "webim-icon webim-icon-" + icon;
			}
		}
		$.tabTipC.innerHTML = title;
		var t = subVisibleLength(title, 0, self.options.titleVisibleLength);
		$.tabTitle.innerHTML = t;
		t && title && t.length < title.length && $.tabTitle.setAttribute("title",title);
		$.headerTitle.innerHTML = title;
	},
	_changeState:function(state){
		var el = this.element, className = state == "restore" ? "normal" : state;
		replaceClass(el, 
			"webim-window-normal webim-window-maximize webim-window-minimize", "webim-window-" + className);
		this.trigger("displayStateChange", [state]);
	},
	active: function(){
		return hasClass(this.element, "webim-window-active");
	},
	activate: function(){
		var self = this;
		if(self.active())return;
		addClass(self.element, "webim-window-active");
		self.trigger("activate");
	},
	deactivate: function(){
		var self = this;
		if(!self.active())return;
		removeClass(self.element, "webim-window-active");
		if(!self.options.sticky) self.minimize();
		self.trigger("deactivate");
	},
	_setVisibile: function(){
		var self = this, $ = self.$;
		replaceClass($.tab, "ui-state-default ui-state-highlight", "ui-state-active");
		self.activate();
		_countDisplay($.tabCount, 0);
	},
	maximize: function(){
		var self = this;
		if(self.isMaximize())return;
		self._setVisibile();
		self._changeState("maximize");
	},
	restore: function(){
		var self = this;
		if(hasClass(self.element, "webim-window-normal"))return;
		self._setVisibile();
		self._changeState("restore");
	},
	minimize: function(){
		var self = this;
		if(self.isMinimize())return;
		replaceClass(self.$.tab, "ui-state-active", "ui-state-default");
		self.deactivate();
		self._changeState("minimize");
	},
	tabClose: function(){
		this.close();
	},
	close: function(){
		var self = this;
		self.deactivate();
		self.trigger("close");
		remove(self.element);
	},
	_initEvents:function(){
		var self = this, element = self.element,
	       	$ = self.$, tab = $.tab,
		options = self.options;

		var stop = function(e){
			stopPropagation(e);
			preventDefault(e);
		};
		//resize
		var minimize = function(e){
			self.minimize();
		};
		if (self.dragable){
			addEvent($.header, "click", function(e){
				if(isShow($.content))
					hide($.content);		
				else
					show($.content);
			});
		}
		addEvent(tab, "click", function(e){
			if(self.isMinimize()){
				self.restore();
				options.widget&& options.widget.trigger("history");
			}
			else self.minimize();
			stop(e);
		});
		addEvent(tab,"mouseover",function(){
			addClass(this, "ui-state-hover");
			removeClass(this, "ui-state-default");
		});
		addEvent(tab,"mouseout",function(){
			removeClass(this, "ui-state-hover");
			this.className.indexOf("ui-state-") == -1 && 
				addClass(this, "ui-state-default");
		});
		addEvent(tab,"mousedown",stop);
		
var Drag = {

	obj : null,
	init : function(o,or)
	{
		o.onmousedown	= Drag.start;

		o.hmode			= true ;
		o.vmode			= true ;

		o.root = or ;

		if (isNaN(parseInt(o.root.style.left  ))) o.root.style.left   =  "-21px";
		if (isNaN(parseInt(o.root.style.top   ))) o.root.style.top    = "-218px";

	},

	start : function(e)
	{
		var o = Drag.obj = this;
		e = Drag.fixE(e);

		o.lastMouseX	= e.clientX;
		o.lastMouseY	= e.clientY;

		document.onmousemove	= Drag.drag;
		document.onmouseup		= Drag.end;

		return false;
	},

	drag : function(e)
	{
		e = Drag.fixE(e);
		var o = Drag.obj;

		var ey	= e.clientY;
		var ex	= e.clientX;
		var y = parseInt(o.root.style.top );
		var x = parseInt( o.root.style.left  );
		var nx, ny;
		nx = x + ex - o.lastMouseX ;
		ny = y + ey - o.lastMouseY;

		o.root.style[ "left"] = nx + "px";
		o.root.style["top"] = ny + "px";
		o.lastMouseX	= ex;
		o.lastMouseY	= ey;

		return false;
	},

	end : function()
	{
		document.onmousemove = null;
		document.onmouseup   = null;
	},

	fixE : function(e)
	{
		if (typeof e == 'undefined') e = window.event;
		if (typeof e.layerX == 'undefined') e.layerX = e.offsetX;
		if (typeof e.layerY == 'undefined') e.layerY = e.offsetY;
		return e;
	}
};
var dragobj;
if (self.dragable){
	Drag.init($.header,$.window);
	$.header.style.cursor = "move";
	/*
	var o = $.header;
	o.root = $.window;
	o.root.style.position = "absolute";
	o.style.cursor = "move";
	o.root.style.display = "block";
	o.root.style.left = "-21px";
	o.root.style.top =  "-218px";
	*/
}


/*
		self.dragable && addEvent($.header,"mousedown",function(e){
			stop(e);
			var o = dragobj = this;
			
			o.lastMouseX = e.clientX;
			o.lastMouseY = e.clientY;

			document.onmouseover = function(e){
				stop(e);
				var o = dragobj;//$.header;
				var ex = e.clientX;
				var ey = e.clientY;
				var x = parseInt(o.root.style.left);
				var y = parseInt(o.root.style.top);
			
				var nx = x + (ex - o.lastMouseX) ;
				var ny = y + (ey - o.lastMouseY) ;

				o.root.style.left = nx + "px";
				o.root.style.top = ny + "px";
				o.lastMouseX = ex;
				o.lastMouseY = ey;
				return false;
			};
			document.onmouseup = function(){
				document.onmouseover = null;
				document.onmouseup = null;
			};
			return false;
		});
*/		
		//disableSelection(tab);

		each(["minimize", "maximize", "close", "tabClose"], function(n,v){
			addEvent($[v], "click", function(e){
				if(!this.disabled)self[v]();
				stop(e);
			});
			addEvent($[v],"mousedown",stop);
		});

	},
	height:function(){
		return this.$.content.offsetHeight;
	},
	_fitUI: function(bounds){
		return;
	},
	isMaximize: function(){
		return hasClass(this.element,"webim-window-maximize");
	},
	isMinimize: function(){
		return hasClass(this.element,"webim-window-minimize");
	}
});
var winManager = (function(){
	var curWin = false;
	var deactivateCur = function(){
		curWin && curWin.deactivate();
		curWin = false;
		return true;
	};
	var activate = function(e){
		var win = this;
		win && win != curWin && deactivateCur() && (curWin = win);
	};
	var deactivate = function(e){
		var win = this;
		win && curWin == win && (curWin = false);
	};
	var register = function(win){
		if(win.active()){
			deactivateCur();
			curWin = win;
		}
		win.bind("activate", activate);
		win.bind("deactivate", deactivate);
	};
	///////////
	addEvent(document,"mousedown",function(e){
		e = target(e);
		var el;
		while(e){
			if(e.id == ":webim-window"){
				el = e;
				break;
			}
			else
				e = e.parentNode;
		}
		if(el){
			var win = el.window;
			win && win.activate();
		}else{
			deactivateCur();
		}
	});
	return function(win){
		register(win);
	}
})();
