//
/* ui.notification:
 *
 options:
 	data [{}]
 attributes：

 methods:

 destroy()
 events: 

 */
widget("notification",{
        template: '<div id="webim-notification" class="webim-notification">\
                        <ul id=":ul"><%=list%></ul>\
                        <div id=":empty" class="webim-notification-empty"><%=empty notification%></div>\
                  </div>',
        tpl_li: '<li><a href="<%=link%>" target="<%=target%>"><%=text%></a></li>'
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
                        text: text,
                        link: link,
                        target: isExtlink ? "_blank" : ""
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
