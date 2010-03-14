//
/* ui.hotpost:
 *
 options:
 	data [{}]
 attributes：
	template
	tpl_li
 methods:
	template
	_li_tpl
	_fitUI
	add
 destroy()
 events: 

 */
app("hotpost",{
	init: function(){
		//hotpost start
		var model = new webim.hotpost();
		var widget = new webimUI.hotpost();
		this.layout.addApp(widget, {
			title: i18n("hotpost"),
			icon: "hotpost",
			sticky: false,
			onlyIcon: true,
			isMinimize: true
		}, "setting");
		model.bind("data",function( data){
			widget.$.ul.innerHTML = "";
			widget.add(data);
		});
		setTimeout(function(){
			model.load();
		}, 2000);
		//hotpost end
	}
});
widget("hotpost",{
        template: '<div id="webim-hotpost" class="webim-hotpost">\
                        <ul id=":ul"><%=list%></ul>\
                        <div id=":empty" class="webim-hotpost-empty"><%=empty hotpost%></div>\
                  </div>',
        tpl_li: '<li><a href="<%=link%>" target="<%=target%>"><%=text%></a></li>'
},{
        _init: function(){
                var self = this, element = self.element, options = self.options;
                var win = options.window;
		options.data && options.data.length && hide(self.$.empty);
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
                        text: data.text,
                        link: data.link,
                        target: "" 
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
