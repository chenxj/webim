
/*ui.broadcast:
 *
 window
 history

methods:
update(info)
insert(text,isCursorPos)
focus
notice(text,timeOut)
destroy()

events:
sendCast
*/
function ieCacheSelection(e){
        document.selection && (this.caretPos = document.selection.createRange());
}
app("broadcast",{
	init:function(){
		var self = this,im = self.im,broadcast = im.broadcast,u = im.data.user;
		var model = self.broadcast =  new webim.broadcast();
		//var win = self.tabs[im.broadcastID] = new webimUI.window(null,extend());
		var widget = self.layout.panels[im.broadcastID] = 
			new webimUI.broadcast(false,
				{isadmin:im.isadmin,
				 uid:im.uid,
				 broadcastID:im.broadcastID
			});
		this.layout.addApp(widget,{
			title:i18n("broadcast"),
			icon:"broadcast",
			sticky:false,
			onlyIcon:true,
			isMinimize:true,
			widget:widget,
			model:model	
		},"setting");
		widget.bind("sendMsg",function(msg){
			msg.from = im.userid;
			msg.type = "broadcast";
			im.sendMsg(msg);
			im.history.handle(msg);
		}).bind("history",function(msg){
			this.history.clear();
			im.history.load("0");
		});
	}		
});
widget("broadcast",{
	template:'<div id=":webim-broadcast-window" class="webim-chat"> \
                       <div id=":content" class="webim-chat-content"> \
                       </div> \
                       <div id=":actions" class="webim-chat-actions"> \
                           <div id=":toolContent" class="webim-chat-tool-content"></div>\
			   			%dynContentTools% \
                               <table class="webim-chat-t" cellSpacing="0"> \
                                   <tr> \
                                       <td style="vertical-align:top;"> \
				       		%dynContentIcon% \
                                       </td> \
                                       <td style="vertical-align:top;width:100%;"> \
                                       	   <div id=":broadcastInput" class="webim-chat-input-wrap">\
					   	%dynContentInput% \
                                           </div> \
                                        </td> \
                                   </tr> \
                               </table> \
                           </div> \
                       </div>'		
		},{
    _preInit:function(){
	var self = this,options = self.options,isadmin = options.isadmin;
        if (isadmin){
		options.template = options.template.replace("%dynContentIcon%",'<em class="webim-icon webim-icon-chat"></em>');
		options.template = options.template.replace("%dynContentTools%",'<div id=":tools" class="webim-chat-tools ui-helper-clearfix ui-state-default"></div>');
		options.template = options.template.replace("%dynContentInput%",'<textarea id=":input" class="webim-chat-input webim-gray"></textarea>');
	}else{
		options.template = options.template.replace("%dynContentInput%",'');
		options.template = options.template.replace("%dynContentIcon%",'');
		options.template = options.template.replace("%dynContentTools%",'');
	}	

 },
    
    _init:function(){
        var self = this,
		element = self.element,
		options = self.options,
		win = self.window = options.window,
		info = options.info;
        var history = self.history = new webimUI.history(null,{
            user:0,
            info:options.info
	});
        self.$.content.insertBefore(history.element,self.$.content.firstChild);
        if(win){
          win.html(element);
          self._bindWindow();
        }
        self.update(options.info);
	//add history 

        history.add(options.history);
        plugin.call(self,"init",[null,self.ui()]);
       self._adjustContent();
    },
	_initEvents: function(){
		var self = this, 
			options = self.options,
			isadmin = options.isadmin, 
			$ = self.$, 
			placeholder = i18n("input notice"), 
			gray = "webim-gray", 
			input = $.input;

		self.history.bind("update", function(){
			self._adjustContent();
		}).bind("clear", function(){
			//self.notice(i18n("clear history notice"), 3000);
		});
		//输入法中，进入输入法模式时keydown,keypress触发，离开输入法模式时keyup事件发生。
		//autocomplete之类事件放入keyup，回车发送事件放入keydown,keypress

		if (isadmin){
			addEvent(input,'keyup',function(){
					ieCacheSelection.call(this);
					});
			addEvent(input,"click", ieCacheSelection);
			addEvent(input,"select", ieCacheSelection);
			addEvent(input,'focus',function(){
					removeClass(this, gray);
					if(this.value == placeholder)this.value = "";
					});
			addEvent(input,'blur',function(){
					if(this.value == ""){
					addClass(this, gray);
					this.value = placeholder;
					}
					});
			addEvent(input,'keypress',function(e){
					self._inputkeypress(e);
					});
		}
		//addEvent($.content, "click", function(e){self._onFocusInput(e)});

	},
	_inputkeypress: function(e){
		var self =  this, $ = self.$;
		if (e.keyCode == 13){
			if(e.ctrlKey){
				self.insert("\n", true);
				return true;
			}else{
				var el = target(e), val = el.value;
				if (trim(val)) {
					self._sendMsg(val);
					el.value = "";
					preventDefault(e);
				}
			}
		}
	},
	_sendMsg: function(val){
		var self = this, options = self.options, info = options.info;
		var msg = {
			type: options.msgType,
			to: options.broadcastID,
			from: options.uid,
			offline: 0,//info.presence == "online" ? 0 : 1,
			body: val,
			timestamp: (new Date()).getTime()
		};
		plugin.call(self, "send", [null, self.ui({msg: msg})]);
		self.trigger('sendMsg', msg);
		//self.sendStatus("");
	},
    update:function(info){
      var self = this;
      if(info){
        self.option("info",info);
	//add history sometime in future
        self.history.option("info",info);
        self._updateInfo(info);
      }
      plugin.call(self,"update",[null,self.ui()]);
    },
   _updateInfo:function(info){
		var self = this, $ = self.$;
		//$.userPic.setAttribute("href", info.url);
		//$.userPic.firstChild.setAttribute("defaultsrc", info.default_pic_url ? info.default_pic_url : "");
		//$.userPic.firstChild.setAttribute("src", info.pic_url);
		//$.userStatus.innerHTML = info.status;
		self.window.title(info.name);
	},
    insert:function(value,isCursorPos){
         var self = this,input = self.$.input;
         input.focus();
         if(!isCursorPos){
            input.value = value;
            return;
          }
          if(!value) value = "";
          if(input.setSelectionRange){
             var val = input.value,rangeStart = input.selectionStart,rangeEnd = input.selectionEnd,tempStr1 = val.substring(0,rangeStart),tempStr2 = val.substring(rangeEnd), len = value.length;
             input.value = tempStr1 + value + tempStr2;
             input.setSelectionRange(rangeStart+len,rangeStart+len);
          }else if (document.selection){
              var caretPos = input.caretPos;
              if(caretPos){
                caretPos.text = value;
                caretPos.collapse();
                caretPos.select();
              }else{
                input.value += value;
              }
            }else{
              input.value += value;
            }
      },
    focus:function(){
      var item = this.$.input;
      item && isFunction(item.focus) && window.setTimeout(function(){item.focus()},0);
    },
    _adjustContent: function(){
	  	var content = this.$.content;
	  	content.scrollTop = content.scrollHeight;
	  },
    _fitUI:function(e){
      var self = this, win = self.window,$ = self.$;
      self._adjustContent();
    },
    _bindWindow:function(){
      var self = this, win = self.window;
      win.bind("displayStateChange",function(type){
        if (type != "minimize"){
          window.setTimeout(function(){self.$.input.focus();},0);
          self._adjustContent();
        }
      });
    },
    _broadcast:function(val){
      var self = this,options = self.options,info = options.info;
      var msg = {
        type:"broadcast",
        body:val,
        from:options.user.id,
        timestamp:(new Date()).getTime()
      };
      plugin.call(self,"send",[null,self.ui({msg:msg})]);
      self.trigger("broadcast",msg);
    },
    destroy:function(){
      this.window.close();  
    },
    ui:function(ext){
      var self = this;
      return extend({
        self:self,
        $:self.$,
        history:self.history
      },ext);
    },
    plugins:{}
});
webimUI.broadcast.defaults.emot = true;
plugin.add("broadcast","emot",{
    init:function(e,ui){
	    if (ui.self.options.isadmin){
       		 var b = ui.self;
                 var emot = new webimUI.emot();
        	 emot.bind("select",function(alt){
          		b.focus();
          		b.insert(alt,true);
		});
	  	var tm = createElement(tpl('<a href="#chat-emot" title="<%=emot%>"><em class="webim-icon webim-icon-emot"></em></a>'));
	  	addEvent(tm,"click",function(e){
	  		preventDefault(e);
			emot.toggle();
	  	});
		ui.$.toolContent.appendChild(emot.element);
		ui.$.tools.appendChild(tm);
  	 }
   }
 });
 
