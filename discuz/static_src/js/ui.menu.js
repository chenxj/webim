//
/* ui.menu:
 *
 options:
 attributes

 methods:
 	add

 destroy()
 events: 

 */
widget("menu",{
        template: '<div id="webim-menu" class="webim-menu">\
                        <ul id=":ul"><%=list%></ul>\
                        <div id=":empty" class="webim-menu-empty"><%=empty menu%></div>\
                  </div>',
        tpl_li: '<li><a href="<%=link%>" target="<%=target%>"><img src="<%=icon%>"/><span><%=title%></span></a></li>'
},{
	_init: function(){
		var self = this, element = self.element, options = self.options;
		var win = options.window;
		options.data && options.data.length && hide(self.$.empty);
		//self._initEvents();
	},
	template: function(){
		var self = this, temp = [], data = self.options.data;
		data && each(data, function(i, val){
			temp.push(self._li_tpl(val));
		});
		return tpl(self.options.template,{
		   list:temp.join("")
		});
	},
	_li_tpl: function(data){
		return tpl(this.options.tpl_li, {
			title: i18n(data.title),
			icon: data.icon,
			link: data.link,
			target: data.isExtlink ? "_blank" : ""
		});
	},
	_fitUI:function(){
		var el = this.element;
		if(el.clientHeight > 300)
			el.style.height = 300 + "px";
	},
	add: function(data){
		var self = this;
		if(isArray(data)){
			each(data, function(i,val){
				self.add(val);
			});
			return;
		}
                var $ = self.$;
		hide($.empty);
		$.ul.appendChild(createElement(self._li_tpl(data)));
	},
	destroy: function(){
	}
});
