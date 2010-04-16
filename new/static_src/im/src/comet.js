/*连接connection 
* Depends:
* 	core.js
* 	ajax.js
*
 只负责长连接. 不处理数据 不自动重连 无数据发送成功事件 只实现功能 不负责业务处理
 connection:
 attributes：

 connected //是否连接中 readonly

 methods:
 connect(options) //开始连接 成功后触发connect事件 错误触发error
 close() 关闭连接 不触发close事件
 send(msg) 发送数据 错误则触发sendError事件

 events: //
 //ready
 data //接收数据数据
 connect //连接成功
 close //连接关闭(曾经连接成功)    服务器关闭触发此事件  本地调用close()不触发此事件,连接中途出错 超时等等 需重新建立连接 调用connect(options)
 error //不能连接 缺少配置，安全限制等等
 (event,text:'')
 sendError //发送消息出错
 sendSuccess //发送消息成功

 */
/* comet */
function comet(element, options){
        var self = this;
        self._setting();
        self.options = {
                jsonp: false,
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
}
extend(comet.prototype, objectExtend, {
        _setting: function(){
                var self = this;
                self.connected = false;//是否已连接 只读属性
                self._connecting = false; //设置连接开关避免重复连接
                self._onPolling = false; //避免重复polling
                self._pollTimer = null;
                self._pollingTimes = 0; //polling次数 第一次成功后 connected = true; 
                self._failTimes = 0;//polling失败累加2次判定服务器关闭连接
        },
        connect: function(options){
                //连接
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

                if (!self._onPolling){
                        window.setTimeout(function(){
                                self._startPolling();
                        }, 300);
                }
                return self;
        },
        close: function(){
                var self = this;
                if (self._pollTimer) 
                clearTimeout(self._pollTimer);
                self._setting();
                return self;
        },
        _onConnect: function(){
                var self = this;
                self.connected = true;
                self.trigger('connect','success');
        },
        _onClose: function(m){
                var self = this;
                self._setting();
                self.trigger('close',[m]);
        },
        _onData: function(data){
                var self = this;
                self.trigger('data', data);
        },
        _onError: function(text){
                var self = this;
                self._setting();
                self.trigger('error', text);
        },
        _startPolling: function(){

                var self = this, options = self.options;
                self._onPolling = true;
                self._pollingTimes++;
                var url = options.server + '/packets';
                var data = {
                //        callback: "airtest", //fortest
                        domain: options.domain,
                        ticket: options.ticket
                };
                var o = {
                        url: url,
                        data: data,
                        //dataType: 'json', //fortest need show
                        timeout: 40000,
                        cache: false,
                        context: self,
                        success: self._onPollSuccess,
                        error: self._onPollError
                };
                if(options.jsonp){
                	extend(o,{
                	        timeout: 40000,
                	        dataType: 'jsonp',
                	        jsonp: 'callback'
                	});
			jsonp(o);
		}
		else
                ajax(o);
        },

        _onPollSuccess: function(d){
                var self = this;
                self._onPolling = false;
                if (!self._connecting) 
                return;//已断开连接
                //d = window["eval"](d.replace("airtest","")); //fortest
                if (self._pollingTimes == 1) 
                self._onConnect();
                self._onData(d);
                self._failTimes = 0;//连接成功 失败累加清零
                self._pollTimer = window.setTimeout(function(){
                        self._startPolling();
                }, 200);
        },
        _onPollError: function(m){
                var self = this;
                self._onPolling = false;
                if (!self._connecting) 
                return;//已断开连接
                self._failTimes++;
                if (self._pollingTimes == 1) 
                self._onError('can not connect.');
                else{
                        if (self._failTimes > 1) {
                                //服务器关闭连接
                                self._onClose(m);
                        }
                        else {
                                self._pollTimer = window.setTimeout(function(){
                                        self._startPolling();
                                }, 200);
                        }
                }
        }
});
