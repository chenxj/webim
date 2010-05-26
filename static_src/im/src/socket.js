/*http://livedocs.adobe.com/flex/3_cn/langref/flash/net/Socket.html
 connect base on flash socket（socket）
 need swfobject.js
 */
var _window_loaded = false;
/*window.onload(function(){
    _window_loaded = true;
});
*/
_window_loaded = true;
socket = function(element, options){

    var self = this;
    self._loaded = true; //加载flash
    self._socket = null;
    self._setting();
    var id = self.id = "_i" + new Date().getTime();
    swfobject.embedSWF("static/socket.swf?id=chenxj.socket." + id, "webim-socket-c", "100", "100", "9.0.0", null, null, null, {
        id: 'webim-socket'
    });
    //this.element = $('#webim-socket');
    //$.log(this.element);
    
    socket[id + 'Init'] = function(){
        self._loaded = true;
    }
    each(['Error', 'Close', 'Data', 'Connect'], function(n, v){
    
        socket[id + v] = function(){
            //$.log(arguments);
            //$.log(v);
            self['_on' + v].apply(self, arguments);
        }
    });
    self.options = {
        host: null,
        port: null,
        server: null,
        ticket: null,
        domain: null,
        url: {
            send: null
        }
    };
    
    extend(self.options, options);
    
};
extend(socket.prototype, objectExtend, {
    _setting: function(){
        var self = this;
        self.connected = false;//是否已连接 只读属性
        self._connecting = false; //避免重复连接
    },
    _connect: function(back){
    
        var self = this, o = self.options;
        if (!self._socket) 
            self._socket = document.getElementById('imsocket');
        var s = self._socket;
        
        if (!s.connect) 
            return self._onError();
        
        s.connect(o.host, o.port);
    },
    connect: function(options){//连接
        var self = this;
        extend(self.options, options);
        if (self._connecting) 
            return self;
        self._connecting = true;
        var options = self.options, error = false, text = [];
        each(['server', 'ticket', 'domain'], function(n, v){
            if (!options[v]) {
                text.push(v);
                text.push(' required.');
                error = true;
            }
        });
        if (error) {
            self._onError('error', text.join(' '));
            return self;
        }
        if (self._loaded) {
            self._connect();
        }
        else 
            if (_window_loaded) {
                self._onError();
            }
            else {
                $(window).load(function(){
                
                    if (self._loaded) {
                        self._connect();
                    }
                    else {
                        self._onError();
                    }
                });
            }
        return self;
    },
    close: function(){
        var self = this;
        self._socket && self._socket.close();
        self._setting();
        return self;
    },
    _onConnect: function(){
        var self = this;
        self.connected = true;
        self.trigger('connect');
    },
    _onClose: function(){
        var self = this;
        self._setting();
        self.trigger('close');
    },
    _onData: function(data){
        var self = this;
        self.trigger('data', data);
    },
    _onError: function(text){
        var self = this;
        self._setting();
        self.trigger('error', text);
    }
});
