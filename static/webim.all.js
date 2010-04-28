/*!
 * Webim v1.0.0pre
 * http://www.nextim.cn/
 *
 * Copyright (c) 2009 Hidden
 *
 * Date:   Wed Apr 28 19:54:26 2010 +0800
 * Revision: 
 */
(function(window, document, undefined){

function now() {
	return (new Date).getTime();
}
function getTid(roomIdendify){
	var url = location;
	var reg = new RegExp("(^|&|\\?)"+roomIdendify+"=(\\d*)(&|$)","i");
	if (reg.test(url)){
		return RegExp.$1;
	}
	return "";
}
var _toString = Object.prototype.toString;
function isFunction( obj ){
	return _toString.call(obj) === "[object Function]";
}

function isArray( obj ){
	return _toString.call(obj) === "[object Array]";
}
function isObject( obj ){
	return obj && _toString.call(obj) === "[object Object]";
}

function trim( text ) {
	return (text || "").replace( /^\s+|\s+$/g, "" );
}

function checkUpdate (old, add){
	var added = false;
	if (isObject(add)) {
		old = old || {};
		for (var key in add) {
			var val = add[key];
			if (old[key] != val) {
				added = added || {};
				added[key] = val;
			}
		}
	}
	return added;
}
function makeArray( array ){
	var ret = [];
	if( array != null ){
		var i = array.length;
		// The window, strings (and functions) also have 'length'
		if( i == null || typeof array === "string" || isFunction(array) || array.setInterval )
			ret[0] = array;
		else
			while( i )
				ret[--i] = array[i];
	}
	return ret;
}

function extend() {
	// copy reference to target object
	var target = arguments[0] || {}, i = 1, length = arguments.length, deep = false, options;

	// Handle a deep copy situation
	if ( typeof target === "boolean" ) {
		deep = target;
		target = arguments[1] || {};
		// skip the boolean and the target
		i = 2;
	}

	// Handle case when target is a string or something (possible in deep copy)
	if ( typeof target !== "object" && !isFunction(target) )
		target = {};
	for ( ; i < length; i++ )
		// Only deal with non-null/undefined values
		if ( (options = arguments[ i ]) != null )
			// Extend the base object
			for ( var name in options ) {
				var src = target[ name ], copy = options[ name ];

				// Prevent never-ending loop
				if ( target === copy )
					continue;

				// Recurse if we're merging object values
				if ( deep && copy && typeof copy === "object" && !copy.nodeType )
					target[ name ] = extend( deep, 
							// Never move original objects, clone them
							src || ( copy.length != null ? [ ] : { } )
							, copy );

				// Don't bring in undefined values
				else if ( copy !== undefined )
					target[ name ] = copy;

			}

	// Return the modified object
	return target;
}

function each( object, callback, args ) {
	var name, i = 0,
	    length = object.length,
	    isObj = length === undefined || isFunction(object);

	if ( args ) {
		if ( isObj ) {
			for ( name in object ) {
				if ( callback.apply( object[ name ], args ) === false ) {
					break;
				}
			}
		} else {
			for ( ; i < length; ) {
				if ( callback.apply( object[ i++ ], args ) === false ) {
					break;
				}
			}
		}

		// A special, fast, case for the most common use of each
	} else {
		if ( isObj ) {
			for ( name in object ) {
				if ( callback.call( object[ name ], name, object[ name ] ) === false ) {
					break;
				}
			}
		} else {
			for ( var value = object[0];
					i < length && callback.call( value, i, value ) !== false; value = object[++i] ) {}
		}
	}

	return object;
}


function inArray( elem, array ) {
	for ( var i = 0, length = array.length; i < length; i++ ) {
		if ( array[ i ] === elem ) {
			return i;
		}
	}

	return -1;
}


function grep( elems, callback, inv ) {
	var ret = [];

	// Go through the array, only saving the items
	// that pass the validator function
	for ( var i = 0, length = elems.length; i < length; i++ ) {
		if ( !inv !== !callback( elems[ i ], i ) ) {
			ret.push( elems[ i ] );
		}
	}

	return ret;
}

function map( elems, callback ) {
	var ret = [], value;

	// Go through the array, translating each of the items to their
	// new value (or values).
	for ( var i = 0, length = elems.length; i < length; i++ ) {
		value = callback( elems[ i ], i );

		if ( value != null ) {
			ret[ ret.length ] = value;
		}
	}

	return ret.concat.apply( [], ret );
}
var objectExtend = {
	option: function(key, value) {
		var options = key, self = this;
		self.options = self.options || {};
		if (typeof key == "string") {
			if (value === undefined) {
				return self.options[key];
			}
			options = {};
			options[key] = value;
		}
		extend(self.options, options);
		return self;
	},

	bind: function(type, fn){
		var self = this, _events = self._events = self._events || {};
		if (isFunction(fn)){
			_events[type] = _events[type] || [];
			_events[type].push(fn);
		}
		return this;
	},

	trigger: function(type, args){
		var self = this, _events = self._events = self._events || {}, fns = _events[type];
		if (!fns) return this;
		args = isArray(args) ? args : makeArray(args);
		for (var i = 0, l = fns.length; i < l; i++){
			fns[i].apply(this, args);
		}
		return this;
	},

	unbind: function(type, fn){
		var self = this, _events = self._events = self._events || {};
		if (!_events[type]) return this;
		if (isFunction(fn)){
			var _e = _events[type];
			for (var i = _e.length; i--; i){
				if (_e[i] === fn || _e[i] === fn._proxy) _e.splice(i, 1);
			}
		} else {
			delete _events[type];
		}
		return this;
	},
	one: function(type, fn){
		if (!isFunction(fn)) return this;
		var self = this,
		one = fn._proxy = fun._proxy || function(){
			self.unbind(type, one);
			return fn.apply(this, arguments);
		};
		self.bind(type, one);
	}
};
/*
* Depends:
* 	core.js
*
*/

// key/values into a query string
function param( a ) {
	var s = [];
	if ( typeof a == "object"){
		for (var key in a) {
			s[ s.length ] = encodeURIComponent(key) + '=' + encodeURIComponent(a[key]);
		}
		// Return the resulting serialization
		return s.join("&").replace(r20, "+");
	}
	return a;
}

var jsc = now(),
	rquery = /\?/,
	rts = /(\?|&)_=.*?(&|$)/,
	r20 = /%20/g,
	ajaxSettings = {
		url: location.href,
		global: true,
		type: "GET",
		contentType: "application/x-www-form-urlencoded",
		processData: true,
		async: true,
		/*
		timeout: 0,
		data: null,
		username: null,
		password: null,
		*/
		// Create the request object; Microsoft failed to properly
		// implement the XMLHttpRequest in IE7, so we use the ActiveXObject when it is available
		// This function can be overriden by calling ajaxSetup
		xhr: function(){
			return window.ActiveXObject ?
				new ActiveXObject("Microsoft.XMLHTTP") :
				new XMLHttpRequest();
		},
		accepts: {
			xml: "application/xml, text/xml",
			html: "text/html",
			script: "text/javascript, application/javascript",
			json: "application/json, text/javascript",
			text: "text/plain",
			_default: "*/*"
		}
	},
	// Last-Modified header cache for next request
	lastModified = {},
	etag = {};

function handleError( s, xhr, status, e ) {
	// If a local callback was specified, fire it
	if ( s.error ) {
		s.error.call( s.context || window, xhr, status, e );
	}
}
// Determines if an XMLHttpRequest was successful or not
function httpSuccess( xhr ) {
	try {
		// IE error sometimes returns 1223 when it should be 204 so treat it as success, see #1450
		return !xhr.status && location.protocol === "file:" ||
			// Opera returns 0 when status is 304
			( xhr.status >= 200 && xhr.status < 300 ) ||
			xhr.status === 304 || xhr.status === 1223 || xhr.status === 0;
	} catch(e){}
	return false;
}

// Determines if an XMLHttpRequest returns NotModified
function httpNotModified( xhr, url ) {
	var _lastModified = xhr.getResponseHeader("Last-Modified"),
		_etag = xhr.getResponseHeader("Etag");

	if ( _lastModified ) {
		lastModified[url] = _lastModified;
	}
	if ( _etag ) {
		etag[url] = _etag;
	}
	// Opera returns 0 when status is 304
	return xhr.status === 304 || xhr.status === 0;
}

function httpData( xhr, type, s ) {
	var ct = xhr.getResponseHeader("content-type"),
		xml = type === "xml" || !type && ct && ct.indexOf("xml") >= 0,
		data = xml ? xhr.responseXML : xhr.responseText;

	if ( xml && data.documentElement.nodeName === "parsererror" ) {
		throw "parsererror";
	}
	// Allow a pre-filtering function to sanitize the response
	// s is checked to keep backwards compatibility
	if ( s && s.dataFilter ) {
		data = s.dataFilter( data, type );
	}

	// The filter can actually parse the response
	if ( typeof data === "string" ) {
		// Get the JavaScript object, if JSON is used.
		if ( type === "json" ) {
			if ( typeof JSON === "object" && JSON.parse ) {
				data = JSON.parse( data );
			} else {
				data = (new Function("return " + data))();
			}
		}
	}

	return data;
}


function ajaxSetup( settings ) {
	extend( ajaxSettings, settings );
}
function ajax( s ) {
	// Extend the settings, but re-extend 's' so that it can be
	// checked again later (in the test suite, specifically)
	s = extend(true, s, extend(true, {}, ajaxSettings, s));
	
	var status, data,
		callbackContext = s.context || window,
		type = s.type.toUpperCase();

	// convert data if not already a string
	if ( s.data && s.processData && typeof s.data !== "string" ) {
		s.data = param(s.data);
	}
	if ( s.cache === false && type === "GET" ) {
		var ts = now();

		// try replacing _= if it is there
		var ret = s.url.replace(rts, "$1_=" + ts + "$2");

		// if nothing was replaced, add timestamp to the end
		s.url = ret + ((ret === s.url) ? (rquery.test(s.url) ? "&" : "?") + "_=" + ts : "");
	}

	// If data is available, append data to url for get requests
	if ( s.data && type === "GET" ) {
		s.url += (rquery.test(s.url) ? "&" : "?") + s.data;
	}

	var requestDone = false;

	// Create the request object
	var xhr = s.xhr();

	// Open the socket
	// Passing null username, generates a login popup on Opera (#2865)
	if ( s.username ) {
		xhr.open(type, s.url, s.async, s.username, s.password);
	} else {
		xhr.open(type, s.url, s.async);
	}

	// Need an extra try/catch for cross domain requests in Firefox 3
	try {
		// Set the correct header, if data is being sent
		if ( s.data ) {
			xhr.setRequestHeader("Content-Type", s.contentType);
		}

			// Set the If-Modified-Since and/or If-None-Match header, if in ifModified mode.
			if ( s.ifModified ) {
				if ( lastModified[s.url] ) {
					xhr.setRequestHeader("If-Modified-Since", lastModified[s.url]);
				}

				if ( etag[s.url] ) {
					xhr.setRequestHeader("If-None-Match", etag[s.url]);
				}
			}

		// Set header so the called script knows that it's an XMLHttpRequest
		xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");

		// Set the Accepts header for the server, depending on the dataType
		xhr.setRequestHeader("Accept", s.dataType && s.accepts[ s.dataType ] ?
			s.accepts[ s.dataType ] + ", */*" :
			s.accepts._default );
	} catch(e){}

	// Allow custom headers/mimetypes and early abort
	if ( s.beforeSend && s.beforeSend.call(callbackContext, xhr, s) === false ) {
		// close opended socket
		xhr.abort();
		return false;
	}
	// Wait for a response to come back
	var onreadystatechange = function(isTimeout){
		// The request was aborted, clear the interval
		if ( !xhr || xhr.readyState === 0 ) {
			if ( ival ) {
				// clear poll interval
				clearInterval( ival );
				ival = null;
			}

		// The transfer is complete and the data is available, or the request timed out
		} else if ( !requestDone && xhr && (xhr.readyState === 4 || isTimeout === "timeout") ) {
			requestDone = true;

			// clear poll interval
			if (ival) {
				clearInterval(ival);
				ival = null;
			}

			status = isTimeout === "timeout" ?
				"timeout" :
				!httpSuccess( xhr ) ?
					"error" :
					s.ifModified && httpNotModified( xhr, s.url ) ?
						"notmodified" :
						"success";

			if ( status === "success" ) {
				// Watch for, and catch, XML document parse errors
				try {
					// process the data (runs the xml through httpData regardless of callback)
					data = httpData( xhr, s.dataType, s );
				} catch(e) {
					status = "parsererror";
				}
			}

			// Make sure that the request was successful or notmodified
			if ( status === "success" || status === "notmodified" ) {
				success();
			} else {
				handleError(s, xhr, status);
			}

			// Fire the complete handlers
			complete();

			if ( isTimeout ) {
				xhr.abort();
			}

			// Stop memory leaks
			if ( s.async ) {
				xhr = null;
			}
		}
	};

	if ( s.async ) {
		// don't attach the handler to the request, just poll it instead
		var ival = setInterval(onreadystatechange, 13);

		// Timeout checker
		if ( s.timeout > 0 ) {
			setTimeout(function(){
				// Check to see if the request is still happening
				if ( xhr && !requestDone ) {
					onreadystatechange( "timeout" );
				}
			}, s.timeout);
		}
	}

	// Send the data
	try {
		xhr.send( type === "POST" || type === "PUT" ? s.data : null );
	} catch(e) {
		handleError(s, xhr, null, e);
	}

	// firefox 1.5 doesn't fire statechange for sync requests
	if ( !s.async ) {
		onreadystatechange();
	}

	function success(){
		// If a local callback was specified, fire it and pass it the data
		if ( s.success ) {
			s.success.call( callbackContext, data, status );
		}
	}

	function complete(){
		// Process result
		if ( s.complete ) {
			s.complete.call( callbackContext, xhr, status);
		}
	}
	// return XMLHttpRequest to allow aborting the request etc.
	return xhr;
}

//var jsonpSettings = {
//	url: location.href,
//	timeout: 30,
//	jsonp:"callback",
//	success:function(data){},
//	error:function(s){}
//};
function emptyFunction(){}
function jsonp(s){
	s = extend({}, s);
	var data = "" + param(s.data),
	callbackContext = s.context || window,
	jsonp = "jsonp" + jsc++,
	head = document.getElementsByTagName("head")[0] || document.documentElement,
	script = document.createElement("script");
	data = (data ? (data + "&") : "") + (s.jsonp || "callback") + "=" + jsonp;
	s.url += (rquery.test( s.url ) ? "&" : "?") + data;
	script.src = s.url;
	if ( s.scriptCharset ) {
		script.charset = s.scriptCharset;
	}
	// Handle Script loading
	var done = false;
	window[ jsonp ] = function(tmp){
		s.success && s.success.call( callbackContext, tmp, "success" );
		destroy();
	};
	// Attach handlers for all browsers
	script.onload = script.onreadystatechange = function(){
		if(!done && (!this.readyState || this.readyState === "loaded" || this.readyState === "complete")){
			//error
			error("error");
			destroy();
		}
	};
	if ( s.timeout > 0 ) {
		setTimeout(function(){
			if (!done){
				error("timeout");
				destroy();
				// The script may be loading.
				window[ jsonp ] = emptyFunction;
			}
		}, s.timeout);
	}
	// Use insertBefore instead of appendChild  to circumvent an IE6 bug.
	head.insertBefore( script, head.firstChild );
	// We handle everything using the script element injection
	return undefined;
	function destroy(){
		done = true;
		// Garbage collect
		window[ jsonp ] = undefined;
		try{ delete window[ jsonp ]; } catch(e){}
		// Handle memory leak in IE
		script.onload = script.onreadystatechange = null;
		//if ( head ) {
		if ( head && script.parentNode ) {
			head.removeChild( script );
		}
	}
	function error(status){
		s.error && s.error.call( callbackContext, status );
	}
}
var JSON = (function(){
	var chars = {'\b': '\\b', '\t': '\\t', '\n': '\\n', '\f': '\\f', '\r': '\\r', '"' : '\\"', '\\': '\\\\'};
	function rChars(chr){
		return chars[chr] || '\\u00' + Math.floor(chr.charCodeAt() / 16).toString(16) + (chr.charCodeAt() % 16).toString(16);
	}
	function encode(obj){
		switch (Object.prototype.toString.call(obj)){
			case '[object String]':
				return '"' + obj.replace(/[\x00-\x1f\\"]/g, rChars) + '"';
			case '[object Array]':
				var string = [], l = obj.length;
			for(var i = 0; i < l; i++){
				string.push(encode(obj[i]));
			}
			return '[' + string.join(",") + ']';
			case '[object Object]':
				var string = [];
			for(var key in obj){
				var json = encode(obj[key]);
				if(json) string.push(encode(key) + ':' + json);

			}
			return '{' + string + '}';
			case '[object Number]': case '[object Boolean]': return String(obj);
			case false: return 'null';
		}
		return null;
	}
	return {
		encode: encode,
		decode: function(string){
			if(!string || !string.length)return null;
			return (new Function("return " + string))();
			//if (secure && !(/^[,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t]*$/).test(string.replace(/\\./g, '@').replace(/"[^"\\\n\r]*"/g, ''))) return null;
		}
	}
})();

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
/**
 * Cookie plugin
 *
 * Copyright (c) 2006 Klaus Hartl (stilbuero.de)
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

/**
 * Create a cookie with the given name and value and other optional parameters.
 *
 * @example $.cookie('the_cookie', 'the_value');
 * @desc Set the value of a cookie.
 * @example $.cookie('the_cookie', 'the_value', { expires: 7, path: '/', domain: 'jquery.com', secure: true });
 * @desc Create a cookie with all available options.
 * @example $.cookie('the_cookie', 'the_value');
 * @desc Create a session cookie.
 * @example $.cookie('the_cookie', null);
 * @desc Delete a cookie by passing null as value. Keep in mind that you have to use the same path and domain
 *       used when the cookie was set.
 *
 * @param String name The name of the cookie.
 * @param String value The value of the cookie.
 * @param Object options An object literal containing key/value pairs to provide optional cookie attributes.
 * @option Number|Date expires Either an integer specifying the expiration date from now on in days or a Date object.
 *                             If a negative value is specified (e.g. a date in the past), the cookie will be deleted.
 *                             If set to null or omitted, the cookie will be a session cookie and will not be retained
 *                             when the the browser exits.
 * @option String path The value of the path atribute of the cookie (default: path of page that created the cookie).
 * @option String domain The value of the domain attribute of the cookie (default: domain of page that created the cookie).
 * @option Boolean secure If true, the secure attribute of the cookie will be set and the cookie transmission will
 *                        require a secure protocol (like HTTPS).
 * @type undefined
 *
 * @name $.cookie
 * @cat Plugins/Cookie
 * @author Klaus Hartl/klaus.hartl@stilbuero.de
 */

/**
 * Get the value of a cookie with the given name.
 *
 * @example $.cookie('the_cookie');
 * @desc Get the value of a cookie.
 *
 * @param String name The name of the cookie.
 * @return The value of the cookie.
 * @type String
 *
 * @name $.cookie
 * @cat Plugins/Cookie
 * @author Klaus Hartl/klaus.hartl@stilbuero.de
 */
function cookie(name, value, options) {
    if (typeof value != 'undefined') { // name and value given, set cookie
        options = options || {};
        if (value === null) {
            value = '';
            options = extend({}, options); // clone object since it's unexpected behavior if the expired property were changed
            options.expires = -1;
        }
        var expires = '';
        if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
            var date;
            if (typeof options.expires == 'number') {
                date = new Date();
                date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
            } else {
                date = options.expires;
            }
            expires = '; expires=' + date.toUTCString(); // use expires attribute, max-age is not supported by IE
        }
        // NOTE Needed to parenthesize options.path and options.domain
        // in the following expressions, otherwise they evaluate to undefined
        // in the packed version for some reason...
        var path = options.path ? '; path=' + (options.path) : '';
        var domain = options.domain ? '; domain=' + (options.domain) : '';
        var secure = options.secure ? '; secure' : '';
        document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
    } else { // only name given, get cookie
        var cookieValue = null;
        if (document.cookie && document.cookie != '') {
            var cookies = document.cookie.split(';');
            for (var i = 0; i < cookies.length; i++) {
                var cookie = trim(cookies[i]);
                // Does this cookie string begin with the name we want?
                if (cookie.substring(0, name.length + 1) == (name + '=')) {
                    cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                    break;
                }
            }
        }
        return cookieValue;
    }
}
/*
*
* Depends:
* 	core.js
*
* options:
*
* attributes:
* 	data
* 	status
* 	setting
* 	history
* 	buddy
* 	connection
*
*
* methods:
* 	online
* 	offline
* 	autoOnline
* 	sendMsg
* 	sendStatus
* 	setStranger
*
* events:
* 	ready
* 	go
* 	stop
*
* 	message
* 	presence
* 	status
*/


function webim(element, options){
	var self = this;
	self.options = extend({}, webim.defaults, options);
	this._init(element, options);
}

extend(webim.prototype, objectExtend,{
	_init:function(){
		var self = this;
		self.data = {user:{}};
		self.status = new webim.status();
		self.setting = new webim.setting();
		self.buddy = new webim.buddy();
		self.room = new webim.room();
		self.history = new webim.history();
		//self.notification = new webim.notification();
                //self.hotpost= new webim.hotpost();
		self.connection = new comet(null,{jsonp:true});
		self._initEvents();
		//self.online();
	},
	ready: function(){
		var self = this;
		self._unloadFun = window.onbeforeunload;
		window.onbeforeunload = function(){
			//self.refresh();
		};
		self.trigger("ready");
	},
	go: function(){
		var self = this, data = self.data, history = self.history, buddy = self.buddy, room = self.room;
		self.connection.connect(data.connection);
		history.option("userInfo", data.user);
		history.init(data.histories);
		buddy.handle(data.buddies);
		//buddy load delay
		buddy.online(data.buddy_online_ids, true);
		//rooms
		//blocked rooms
		var b = self.setting.get("block_list"), roomData = data.rooms;
		isArray(b) && roomData && each(b,function(n,v){
			roomData[v] && (roomData[v].blocked = true);
		});
		room.handle(roomData);
		room.options.ticket = data.connection.ticket;
		//handle new messages
		var n_msg = data.new_messages;
		if(n_msg && n_msg.length)
			self.trigger("message",[n_msg]);

		self.trigger("go",[data]);
	},
	stop: function(msg){
		var self = this;
		window.onbeforeunload = self._unloadFun;
		self.data.user.presence = "offline";
		self.buddy.clear();
		self.trigger("stop", msg);

	},
	autoOnline: function(){
		return !this.status.get("o");
	},
	_initEvents: function(){
		var self = this, status = self.status, setting = self.setting, history = self.history, connection = self.connection;
                connection.bind("connect",function(e, data){
                }).bind("data",function(data){
                        self.handle(data);
                }).bind("error",function(data){
                        self.stop("connect error");
                }).bind("close",function(data){
                        self.stop("disconnect");
                });
	},
	handle:function(data){
		var self = this;
		data.messages && data.messages.length && self.trigger("message",[data.messages]);
		data.presences && data.presences.length && self.trigger("presence",[data.presences]);
		data.statuses && data.statuses.length && self.trigger("status",[data.statuses]);
	},
	sendMsg: function(msg){
		var self = this;
		msg.ticket = self.data.connection.ticket;
		ajax({
			type: 'post',
			url: self.options.urls.message,
			type: 'post',
			cache: false,
			data: msg
		});
	},
	sendStatus: function(msg){
		var self = this;
		msg.ticket = self.data.connection.ticket;
		ajax({
			type: 'post',
			url: self.options.urls.status,
			type: 'post',
			cache: false,
			data: msg
		});
	},
	//        online_list:function(){
	//                var self = this;
	//                ajax({
	//                        type:"post",
	//                        dataType: "json",
	//                        url: self.options.urls.online_list,
	//                        success: function(data){
	//                                self.trigger("online_list", [data]);
	//                        },
	//                        error: function(data){
	//                                log(data, "online:error");
	//                        }
	//                });
	//
	//        },
	setStranger: function(ids){
		this.stranger_ids = idsArray(ids);
	},
	stranger_ids:[],
	online:function(){
		var self = this, status = self.status, buddy_ids = [], tabs = status.get("tabs"), tabIds = status.get("tabIds");
		//set auto open true
		status.set("o", false);
		self.ready();
		tabIds && tabIds.length && tabs && each(tabs, function(k,v){
			v["t"] == "buddy" && buddy_ids.push(k);
		});
		ajax({
			type:"post",
			dataType: "json",
			data:{                                
				buddy_ids: buddy_ids.join(","),
                //(self.isStrangerOn == "on")?buddy_ids.join(","):"",
				stranger_ids: self.stranger_ids.join(","),
                //(self.isStrangerOn == "on")?self.stranger_ids.join(","):"",
				room_ids:getTid(self.roomIdendify),
				timestamp: parseInt(new Date().getTime()/1000)
			},
			url: self.options.urls.online,
			success: function(data){
				if(!data || !data.user || !data.connection){
					self.stop("online error");
				}else{
					data.user = extend(self.data.user, data.user);
					self.data = data;
					self.go();
				}
			},
			error: function(data){
				self.stop("online error");
			}
		});

	},
	offline:function(){
		var self = this, data = self.data;
		self.status.set("o", true);
		self.connection.close();
		self.stop("offline");
		ajax({
			type: 'post',
			url: self.options.urls.offline,
			type: 'post',
			cache: false,
			data: {
				status: 'offline',
				ticket: data.connection.ticket
			}
		});

	},
	refresh:function(){
		var self = this, data = self.data;
		if(!data || !data.connection || !data.connection.ticket) return;
		ajax({
			type: 'post',
			url: self.options.urls.refresh,
			type: 'post',
			cache: false,
			data: {
				ticket: data.connection.ticket
			}
		});
	}

});
function idsArray(ids){
	return ids && ids.split ? ids.split(",") : (isArray(ids) ? ids : (parseInt(ids) ? [parseInt(ids)] : []));
}
function model(name, defaults, proto){
	function m(data,options){
		var self = this;
		self.data = data;
		self.options = extend({}, m.defaults,options);
		isFunction(self._init) && self._init();
	}
	m.defaults = defaults;
	extend(m.prototype, objectExtend, proto);
	webim[name] = m;
}
//_webim = window.webim;
window.webim = webim;

extend(webim,{
	version:"1.0.0pre",
	defaults:{},
	//log:log,
	idsArray: idsArray,
	now: now,
	isFunction: isFunction,
	isArray: isArray,
	isObject: isObject,
	trim: trim,
	makeArray: makeArray,
	extend: extend,
	each: each,
	inArray: inArray,
	grep: grep,
	map: map,
	JSON: JSON,
	ajax: ajax,
	model: model,
	objectExtend: objectExtend
});

/*
* 配置(数据库永久存储)
* Methods:
* 	get
* 	set
*
* Events:
* 	update
* 	
*/
model("setting",{
	url:"/webim/setting",
	data:{
		play_sound:true,
		buddy_sticky:true,
		minimize_layout: true,
		msg_auto_pop:true
	}
},{
	_init:function(){
		var self = this;
		self.data = extend({}, self.options.data, self.data);
	},
	get: function(key){
		return this.data[key];
	},
	set: function(key, value){
		var self = this, options = key;
		if(!key)return;
		if (typeof key == "string") {
			options = {};
			options[key] = value;
		}
		var _old = self.data,
			up = checkUpdate(_old, options);
		if ( up ) {
			each(up,function(key,val){
				self.trigger("update",[key,val]);
			});
			var _new = extend({}, _old, options);
			self.data = _new;
			ajax({
				type: 'post',
				url: self.options.url,
				dataType: 'json',
				cache: false,
				data: {data: JSON.encode(_new)}
			});
		}
	}
});
/*
* 状态(cookie临时存储[刷新页面有效])
* webim.status.init(status);//初始化状态
* webim.status.all //所有状态
* webim.status(key);//get
* webim.status(key,value);//set
*/
//var d = {
//        tabs:{1:{n:5}}, // n -> notice count
//        tabIds:[1],
//        p:5, //tab prevCount
//        a:5, //tab activeTabId
//        b:0, //is buddy open
//        o:0 //has offline
//}
model("status",{
	key:"_webim"
},{
	_init:function(){
		var self = this, data = self.data;
		if (!data){
			var c = cookie(self.options.key);
			self.data = c ? JSON.decode(c) : {};
		}else{
			self._save(data);
		}
	},
	set: function(key, value){
		var options = key, self = this;
		if (typeof key == "string") {
			options = {};
			options[key] = value;
		}
		var old = self.data;
		if (checkUpdate(old, options)) {
			var _new = extend({}, old, options);
			self._save(_new);
		}
	},
	get: function(key){
		return this.data[key];
	},
	clear:function(){
		this._save({});
	},
	_save: function(data){
		this.data = data;
		cookie(this.options.key, JSON.encode(data), {
			path: '/',
			domain: document.domain
		});
	}
});

/**/
/*
buddy //联系人
attributes：
data []所有信息 readonly 
methods:
get(id)
handle(data) //handle data and distribute events
online(ids, loadDelay) // 
loadDelay()
offline(ids)
update(ids) 更新用户信息 有更新时触发events:update

events:
online  //  data:[]
onlineDelay
offline  //  data:[]
update 
*/

model("buddy", {
	url:"/webim/buddy"
}, {
	_init: function(){
		var self = this;
		self.data = self.data || [];
		self.dataHash = {};
		self._cacheData = {};
		self.handle(self.data);
	},
	clear:function(){
		var self =this;
		self.data = [];
		self.dataHash = {};
		self._cacheData = {};
	},
	count: function(conditions){
		var data = extend({}, this.dataHash, this._cacheData), count = 0, t;
		for(var key in data){
			if(isObject(conditions)){
				t = true;
				for(var k in conditions){
					if(conditions[k] != data[key][k]) t = false;
				}
				if(t) count++;
			}else{
				count ++;
			}
		}
		return count;
	},
	get: function(id){
		return this.dataHash[id];
	},
	online: function(ids, loadDelay){
		this.changeStatus(ids, "online", true, loadDelay);
	},
	offline: function(ids){
		this.changeStatus(ids, "offline", false);
	},
	loadDelay: function(){
		var self = this, cache = self._cacheData, cache_ids = [];
		for(var key in cache){
			cache_ids.push(key);
		}
		self.load(cache_ids);
	},
	update: function(ids){
		this.load(ids);
	},
	changeStatus:function(ids, type, needLoad, loadDelay){
		ids = idsArray(ids);
		var l = ids.length;
		if(l){
			var self = this, cache = self._cacheData, dataHash = self.dataHash, statusData = [], id, delayData = [], dd;
			for(var i = 0; i < l; i++){
				id = ids[i];
				if(dataHash[id]){
					statusData.push({id:id, presence:type});
				}
				else{
					dd = {id:id, presence:type};
					if(!cache[id] || cache[id].presence != type)delayData.push(dd);
					if(needLoad){
						cache[id] = dd;
					}else{
						if(cache[id])
							delete cache[id];
					}
				}

			}
			self.handle(statusData);
			if(needLoad && !loadDelay)self.loadDelay();
			else if(delayData.length){
				if(needLoad)self.trigger(type + "Delay", [delayData]);
				else self.trigger(type , [delayData]);
			}
		}

	},
	_loadSuccess:function(data){
		var self = this.self || this, cache = self._cacheData, l = data.length, value , id;
		//for(var i = 0; i < l; i++){
		for(var i in data){
			value = data[i];
			id = value["id"];
			if(cache[id]){
				extend(value, cache[id]);
				delete cache[id];
			}
		}
		self.handle(data);
	},
	load: function(ids){
		ids = idsArray(ids);
		if(ids.length){
			var self = this, options = self.options;
			ajax({
				url: options.url,
				cache: false,
				dataType: "json",
				data:{ ids: ids.join(",")},
				context: self,
				success: self._loadSuccess
			});
		}
	},
	handle:function(addData){
		var self = this, data = self.data, dataHash = self.dataHash, status = {};
		addData = addData || [];
		var l = addData.length , v, type, add;
		//for(var i = 0; i < l; i++){
		for(var i in addData){
			v = addData[i], id = v.id;
			if(id){
				if(!dataHash[id]){
					dataHash[id] = {};
					data.push(dataHash[id]);
				}
				add = checkUpdate(dataHash[id], v);
				if(add){
					type = add.presence || "update";
					status[type] = status[type] || [];
					extend(dataHash[id], add);
					status[type].push(dataHash[id]);
				}

			}
		}
		for (var key in status) {
			self.trigger(key, [status[key]]);
		}

	}

});
/**/
/*
* room
*attributes：
*data []所有信息 readonly 
*methods:
*	get(id)
*	handle()
*	join(id)
*	leave(id)
*	count()
*	initMember
*	addMember
*	removeMember
*	members(id)
*	member_cont(id)
*
*events:
*	join
*	leave
*	block
*	unblock
*	addMember
*	removeMember
*
*
*/
(function(){
	model("room", {
		urls:{
			join: "/webim/join.php",
			leave: "/webim/leave.php",
			member: "/webim/members.php"
		}
	},{
		_init: function(){
			var self = this;
			self.data = self.data || [];
			self.dataHash = {};
		},
		get: function(id){
			return this.dataHash[id];
		},
		block: function(id){
			var self = this, d = self.dataHash[id];
			if(d && !d.blocked){
				d.blocked = true;
				var list = [];
				each(self.dataHash,function(n,v){
					if(v.blocked) list.push(v.id);
				});
				self.trigger("block",[id, list]);
			}
		},
		unblock: function(id){
			var self = this, d = self.dataHash[id];
			if(d && d.blocked){
				d.blocked = false;
				var list = [];
				each(self.dataHash,function(n,v){
					if(v.blocked) list.push(v.id);
				});
				self.trigger("unblock",[id, list]);
			}
		},
		handle: function(d){
			var self = this, data = self.data, dataHash = self.dataHash, status = {};
			each(d,function(k,v){
				var id = v.id;
				if(id){
					v.members = v.members || [];
					v.count = v.count || 0;
					v.all_count = v.all_count || 0;
					if(!dataHash[id]){
						dataHash[id] = v;
						data.push(v);
					}
					else extend(dataHash[id], v);
					self.trigger("join",[dataHash[id]]);
				}

			});
		},
		addMember: function(room_id, info){
			var self = this;
			if(isArray(info)){
				each(info, function(k,v){
					self.addMember(room_id, v);
				});
				return;
			};
			var room = self.dataHash[room_id];
			if(room){
				var members = room.members, member;
				for (var i = members.length; i--; i){
					if (members[i].id == info.id) {
						member = members[i];
					}
				}
				if(!member){
					info.name = info.nick || info.name;
					members.push(info);
					room.members.length;
					self.trigger("addMember",[room_id, info]);
				}
			}
		},
		removeMember: function(room_id, member_id){
			var room = this.dataHash[room_id];
			if(room){
				var members = room.members, member;
				for (var i = members.length; i--; i){
					if (members[i].id == member_id) {
						member = members[i];
						members.splice(i, 1);
						room.count--;
					}
				}
				member && self.trigger("removeMember",[room_id, member]);
			}
		},
		initMember: function(id){
			var room = this.dataHash[id];
			if(room && !room.initMember){
				room.initMember = true;
				this.loadMember(id);
			}
		},
		loadMember: function(id){
			var self = this, options = self.options;
			ajax({
				cache: false,
				url: options.urls.member,
				dataType: "json",
				data: {
					ticket: options.ticket,
					id: id
				},
				success: function(data){
					each(data,function(k,v){
						self.addMember(k,v);
					});
				}
			});
		},
		join:function(id,user){
			var self = this, options = self.options;
			ajax({
				cache: false,
				url: options.urls.join,
				dataType: "json",
				data: {
					ticket: options.ticket,
					id: id,
          nick: user.name
				},
				success: function(data){
					//self.trigger("join",[data]);
					self.initMember(id);
					self.handle([data]);
				}
			});
		},
		leave: function(id,user){
			var self = this, options = self.options, d = self.dataHash[id];
			if(d){
				d.initMember = false;
				ajax({
					cache: false,
					url: options.urls.leave,
					data: {
						ticket: options.ticket,
						id: id,
            nick: user.name
					}
				});
				self.trigger("leave",[d]);
			}
		},
		clear:function(){
		}
	});
})();

/*
history // 消息历史记录
attributes：
data []所有信息 readonly 
methods:
get(id)
load(ids)
clear(ids)
init(data)
handle(data) //handle data and distribute events

events:
data //id,data
clear //
*/

model("history",{
	urls:{load:"", clear:""}
}, {
	_init:function(){
		this.data = this.data || {};
	},
	get: function(id){
		return this.data[id];
	},
	handle:function(addData){
		var self = this, data = self.data, cache = {};
		addData = makeArray(addData);
		var l = addData.length , v, id, userId = self.options.userInfo.id;
		if(!l)return;
		for(var i = 0; i < l; i++){
			//for(var i in addData){
			v = addData[i];
			id = v.to == userId ? v.from : v.to;
			if(id != undefined){
				cache[id] = cache[id] || [];
				cache[id].push(v);
			}
		}
		var ids = [];
		for (var key in cache) {
			var v = cache[key];
			if(data[key]){
				data[key] = data[key].concat(v);
				self._triggerMsg(key, v);
			}else{

				ids.push(key);
			}
		}
		self.load(ids);

	},
	_triggerMsg: function(id, data){
		//this.trigger("message." + id, [data]);
		this.trigger("data", [id, data]);
	},
	clear: function(ids){
		ids = idsArray(ids);
		var self = this, l = ids.length, options = self.options, id;
		if(l){
			for(var i = 0; i < l; i++){
				id = ids[i];
				self.data[id] = [];
				self.trigger("clear", [id]);
			}
			ajax({
				url: options.urls.clear,
				cache: false,
				dataType: "json",
				data:{ ids: ids.join(",")}
			});
		}

	},
	init:function(data){
		var self = this.self || this, v;
		for(var key in data){
			v = data[key];
			self.data[key] = v;
			self._triggerMsg(key, v);
		}
	},
	load: function(ids){
		ids = idsArray(ids);
		if(ids.length){
			var self = this, options = self.options;
			for(var i = 0; i < ids.length; i++){
				self.data[ids[i]] = [];
			}
			ajax({
				url: options.urls.load,
				cache: false,
				dataType: "json",
				data:{ ids: ids.join(",")},
				context: self,
				success: self.init
			});
		}
	}

});

})(window, document);
/*!
 * Webim UI v2.1.0pre
 * http://www.nextim.cn/
 *
 * Copyright (c) 2009 Hidden
 *
 * Date:   Wed Apr 28 19:54:26 2010 +0800
 * Revision: 
 */
(function(window,document,undefined){

var //log = webim.log,
idsArray = webim.idsArray,
now = webim.now,
isFunction = webim.isFunction,
isArray = webim.isArray,
isObject = webim.isObject,
trim = webim.trim,
makeArray = webim.makeArray,
extend = webim.extend,
each = webim.each,
inArray = webim.inArray,
grep = webim.grep,
objectExtend = webim.objectExtend,
map = webim.map;

function returnFalse(){
	return false;
}
function HTMLEnCode(str)  
{  
	var    s    =    "";  
	if    (str.length    ==    0)    return    "";  
	s    =    str.replace(/&/g,    "&gt;");  
	s    =    s.replace(/</g,        "&lt;");  
	s    =    s.replace(/>/g,        "&gt;");  
	s    =    s.replace(/    /g,        "&nbsp;");  
	s    =    s.replace(/\'/g,      "&#39;");  
	s    =    s.replace(/\"/g,      "&quot;");  
	s    =    s.replace(/\n/g,      "<br />");  
	return    s;  
}
function isUrl(str){
	return /^http:\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"])*$/.test(str);
}

function subVisibleLength(cont,start,length){
	if(!cont) return cont;
	var l = 0,a =[],c = cont.split(''),ln=c.length;
	for(var i =0;i<ln;i++){
		if(l>=length||l<start)
			break;
		else{
			if(c[i].charCodeAt(0) > 255)l+=2;
			else l++;
			a.push(c[i]);
		}

	}
	return a.join('');
}

function $(id){
	return id ? (id.nodeType ? id : document.getElementById(id)) : null;
}

function sibling(n, elem){
	var r = [];

	for ( ; n; n = n.nextSibling ) {
		if ( n.nodeType == 1 && n != elem )
			r.push( n );
	}

	return r;
}

function children(elem){
	return sibling(elem.firstChild);
}

function hasClass(obj,name){
	return obj && (new RegExp("(^|\\s+)"+name+"(\\s+|$)").test(obj.className));

}
function addClass(obj,name){
	if(!obj)return;
	if(!hasClass(obj,name)){
		obj.className+=" "+name;
	}
}
function removeClass(obj,name){
	obj && (obj.className=obj.className.replace(new RegExp("(^|\\s+)("+name.split(/\s+/).join("|")+")(\\s+|$)","g")," "));
}
function replaceClass(obj,_old, _new){
	obj && (obj.className=obj.className.replace(new RegExp("(^|\\s+)("+_old.split(/\s+/).join("|")+")(\\s+|$)","g")," ") + " " + _new);
}
function hoverClass(obj, name, toggleClass){
	addEvent(obj,"mouseover",function(){
		addClass(this, name);
		toggleClass && (removeClass(this, toggleClass));
	});
	addEvent(obj,"mouseout",function(){
		removeClass(this, name);
		toggleClass && (addClass(this, toggleClass));
	});
}
function toggleClass(obj, name, is){
	if(typeof is === "boolean")
		is ? addClass(obj, name) : removeClass(obj,name);
	else
		hasClass(obj, name) ? removeClass(obj,name) : addClass(obj, name);
}
function show(obj){
	obj && obj.style && (obj.style.display="block")
}
function hide(obj){
	obj && obj.style && (obj.style.display="none")
}
function remove(obj){
	obj && obj.parentNode && (obj.parentNode.removeChild(obj));
}
function addEvent( obj, type, fn ) {
	if ( obj.addEventListener ) {
		obj.addEventListener( type, fn, false );
	} else{
		obj['e'+type+fn] = fn;
		obj[type+fn] = function(){return obj['e'+type+fn]( window.event );}
		obj.attachEvent( 'on'+type, obj[type+fn] );
	}
}
function removeEvent( obj, type, fn ) {
	if ( obj.addEventListener ) {
		obj.removeEventListener( type, fn, false );
	} else{
		obj.detachEvent( 'on'+type, obj[type+fn] );
		obj[type+fn] = null;
	}
}
function stopPropagation(e){
	if(!e)return;
	e.stopPropagation && e.stopPropagation();
	e.cancelBubble = true;
}
function preventDefault(e){
	if(!e)return;
	e.preventDefault && e.preventDefault();
	e.returnValue = false;
}
function target(event){
	if ( !event.target ) {
		event.target = event.srcElement || document; 
		// Fixes #1925 where srcElement might not be defined either
	}
	// check if target is a textnode (safari)
	if ( event.target.nodeType === 3 ) {
		event.target = event.target.parentNode;
	}
	return event.target;
}

function enableSelection(obj) {
	obj.setAttribute("unselectable","off");
	obj.style.MozUserSelect = '';
	removeEvent(obj,'selectstart', returnFalse);
}
function disableSelection(obj) {
	obj.setAttribute("unselectable","on");
	obj.style.MozUserSelect = 'none';
	addEvent(obj,'selectstart', returnFalse);
}

//document ready
//

function ready(fn){
	// Attach the listeners
	bindReady();
	// If the DOM is already ready
	if ( isReady ) {
		// Execute the function immediately
		fn();
		// Otherwise, remember the function for later
	} else {
		// Add the function to the wait list
		readyList.push( fn );
	}

}

var isReady = false, readyList = [];
function triggerReady() {
	// Make sure that the DOM is not already loaded
	if ( !isReady ) {
		// Remember that the DOM is ready
		isReady = true;

		// If there are functions bound, to execute
		if ( readyList ) {
			// Execute all of them
			var fn, i = 0;
			while ( (fn = readyList[ i++ ]) ) {
				fn();
			}

			// Reset the list of functions
			readyList = null;
		}

	}
}

var readyBound = false;
function bindReady() {
	if ( readyBound ) return;
	readyBound = true;

	// Catch cases where $(document).ready() is called after the
	// browser event has already occurred.
	if ( document.readyState === "complete" ) {
		return triggerReady();
	}

	// Mozilla, Opera and webkit nightlies currently support this event
	if ( document.addEventListener ) {
		// Use the handy event callback
		document.addEventListener( "DOMContentLoaded", function() {
			document.removeEventListener( "DOMContentLoaded", arguments.callee, false );
			triggerReady();
		}, false );

		// If IE event model is used
	} else if ( document.attachEvent ) {
		// ensure firing before onload,
		// maybe late but safe also for iframes
		document.attachEvent("onreadystatechange", function() {
			if ( document.readyState === "complete" ) {
				document.detachEvent( "onreadystatechange", arguments.callee );
				triggerReady();
			}
		});

		// If IE and not an iframe
		// continually check to see if the document is ready
		// NOTE: DO NOT CHANGE TO ===, FAILS IN IE.
		if ( document.documentElement.doScroll && window == window.top ) (function() {
			if ( isReady ) {
				return;
			}

			try {
				// If IE is used, use the trick by Diego Perini
				// http://javascript.nwbox.com/IEContentLoaded/
				document.documentElement.doScroll("left");
			} catch( error ) {
				setTimeout( arguments.callee, 0 );
				return;
			}

			// and execute any waiting functions
			triggerReady();
		})();
	}
	// A fallback to window.onload, that will always work
	addEvent( window, "load", triggerReady );
}
//格式化时间输出，消除本地时间和服务器时间差，以计算机本地时间为准
//date.init(serverTime);设置时差
//date()
function date(time){
        var d = (new Date());
        d.setTime(time ? (parseFloat(time) + date.timeSkew) : (new Date()).getTime());
        this.date = d;
};
date.timeSkew = 0;
date.init = function(serverTime){//设置本地时间和服务器时间差
    date.timeSkew = (new Date()).getTime() - parseFloat(serverTime);
};
extend(date.prototype, {
    getTime: function(){
            var date = this.date;
        var hours = date.getHours();
        var ampm = '';
        /*ampm = 'am';
         if (hours >= 12) {
         ampm = 'pm';
         }
         if (hours == 0) {
         hours = 12;
         }
         else
         if (hours > 12) {
         hours -= 12;
         }
         */
        var minutes = date.getMinutes();
        if (minutes < 10) {
            minutes = '0' + minutes;
        }
        var timeStr = hours + ':' + minutes + ampm;
        return timeStr;
    },
    getDay: function(showRelative){
            var date = this.date;
        if (showRelative) {
            var today = new Date();
            today.setHours(0);
            today.setMinutes(0);
            today.setSeconds(0);
            today.setMilliseconds(0);
            var dayMilliseconds = 24 * 60 * 60 * 1000;
            var diff = today.getTime() - date.getTime();
            if (diff <= 0) {
                return i18n('dt:today');
            }
            else 
                if (diff < dayMilliseconds) {
                    return i18n('dt:yesterday');
                }
        }
        return i18n('dt:monthdate', {
                'month': i18n(['dt:january','dt:february','dt:march','dt:april','dt:may','dt:june','dt:july','dt:august','dt:september','dt:october','dt:november','dt:december'][date.getMonth()]),
                'date': date.getDate()
        });
    }
});
var sound = (function(){
        var playSound = true;
        var play = function(url){
            try {
                document.getElementById('webim-flashlib').playSound(url ? url : '/sound/sound.mp3');
            } 
            catch (e){
            }
        };
        var _urls = {
                lib: "sound.swf",
                msg:"sound/msg.mp3"
        };
        return {
                enable:function(){
                        playSound = true;
                },
                disable:function(){
                        playSound = false;
                },
                init: function(urls){
                        extend(_urls, urls);
			/*
                        swfobject.embedSWF(_urls.lib + "?_" + new Date().getTime(), "webim-flashlib-c", "100", "100", "9.0.0", null, null, {
                        allowscriptaccess:'always'
                        }, {
                            id: 'webim-flashlib'
                        });
			*/
			var lib_url = _urls.lib + "?_" + new Date().getTime();
			if (navigator.plugins && navigator.mimeTypes && navigator.mimeTypes.length) { // netscape plugin architecture
				var html = '<embed type="application/x-shockwave-flash" width="10" height="10" id="webim-flashlib" allowscriptaccess="always" src="'+lib_url+'" />';
			}else{
				var html = '<object width="10" height="10" id="webim-flashlib" type="application/x-shockwave-flash" data="'+ lib_url + '">\
				<param name="allowScriptAccess" value="always" />\
				<param name="movie" value="'+lib_url+'" />\
				<param name="scale" value="noscale" />\
				</object>';
			}
			try {
				document.getElementById('webim-flashlib-c').innerHTML = html;
			} 
			catch (e){
			}
		},
                play: function(type){
                        var url = isUrl(type) ? type : _urls[type];
                        playSound && play(url);
                }
        }
})();

/*
* set display frequency.
*/
var titleShow = (function(){
	var _showNoti = false;
	addEvent(window,"focus",function(){
		_showNoti = false;
	});
	addEvent(window,"blur",function(){
		_showNoti = true;
	});
	var title = document.title, t = 0, s = false, set = null;
	return  function(msg, time){
		if(!_showNoti) 
			return;
		if(set){
			clearInterval(set);
			t = 0;
			s = false;
		}

		var set = setInterval(function(){
			t++;
			s = !s;
			if (t == time || !_showNoti) {
				clearInterval(set);
				t = 0;
				s = false;
			}
			if (s) {
				document.title = "[" + msg + "]" + title;
			}
			else {
				document.title = title;
			}
		}, 1500);
	}
})();
/*
本地化
i18n.locale = 'zh-CN';//设置本地语言
i18n.store('zh-CN',{bbb:"test"});//添加
i18n(name,args);// 获取
*/
var i18nArgs = {};
var i18nRe = function(a, b){
	return i18nArgs[b] || "";
}
function i18n(name, args, options){
	options = extend({
		locale: i18n.locale
	}, options);
	var dict = i18n.dictionary[options.locale];
	if (!isObject(dict)) 
		dict = {};
	var str = dict[name] === undefined ? name : dict[name];

	if (args) {
		i18nArgs = args;
		for (var key in args) {
			str = str.replace(/\{\{(.*?)\}\}/g, i18nRe);
		}
	}
	return str;
};
i18n.locale = 'zh-CN';
i18n.dictionary = {};
i18n.store = function(locale, data){
	var dict = i18n.dictionary;
	if (!isObject(dict[locale])) 
		dict[locale] = {};
	extend(dict[locale], data);
};
/* webim UI:
*
* options:
* attributes:
* 	im
* 	layout
* 	setting
* 	buddy
* 	notification
* 	menu
*
* methods:
*
* events:
*
*/

function webimUI(element, options){
	var self = this;
	self.element = element;
	self.options = extend({}, webimUI.defaults, options);
	self._init();
}
extend(webimUI.prototype, objectExtend, {
	_init:function(){
		var self = this,
		im = self.im = new webim(),
		layout = self.layout = new webimUI.layout(null,{
			chatAutoPop: im.setting.get("msg_auto_pop")
		}),
		options = self.options;
		//self.notification = new webimUI.notification();
		var d = im.setting.data;
		self.setting = new webimUI.setting(null,{
			data: {
				"buddy_sticky": d["buddy_sticky"],
				"play_sound": d["play_sound"],
				"msg_auto_pop": d["msg_auto_pop"],
				"minimize_layout": d["minimize_layout"]
			}
		});
		self.buddy = new webimUI.buddy(null,{
		});
		var menuData = self.options.menu;
	       	self.menu = new webimUI.menu(null,{
			data: menuData
		});
					    //render start
		layout.addApp(self.menu, {
			title: i18n("menu"),
			icon: "home",
			sticky: false,
			onlyIcon: false,
			isMinimize: true
		}, null,"shortcut");
		layout.addShortcut(menuData);

		layout.addApp(self.buddy, {
			title: i18n("chat"),
			icon: "buddy",
			sticky: im.setting.get("buddy_sticky"),
			className: "webim-buddy-window",
			//       onlyIcon: true,
			isMinimize: !im.status.get("b"),
			titleVisibleLength: 19
		});
		layout.addApp(self.setting, {
			title: i18n("setting"),
			icon: "setting",
			sticky: false,
			onlyIcon: true,
			isMinimize: true
		});

	/*	layout.addApp(self.notification, {
			title: i18n("notification"),
			icon: "notification",
			sticky: false,
			onlyIcon: true,
			isMinimize: true
		});
		*/
 			im.setting.get("play_sound") ? sound.enable() : sound.disable() ;
		im.setting.get("minimize_layout") ? layout.collapse() : layout.expand(); 
		self.buddy.offline();
		//document.body.appendChild(layout.element);
		//layout.buildUI();

		//render end

		self._initEvents();
	},
	addApp: function(name){
		var e = webimUI.apps[name];
		if(!e)return;
		var self = this;
		isFunction(e.init) && e.init.apply(self, []);
		self.im.bind("ready", function(){
			isFunction(e.ready) && e.ready.apply(self, []);
		}).bind("go", function(){
			isFunction(e.go) && e.go.apply(self, []);
		}).bind("stop", function(){
			isFunction(e.stop) && e.stop.apply(self, []);
		});
	},
	initSound: function(urls){
		sound.init(urls || this.options.soundUrls);
	},
	_initEvents: function(){
		var self = this, im = self.im, buddy = im.buddy, history = im.history, status = im.status, setting = im.setting, buddyUI = self.buddy,chatlink = im.chatlink, layout = self.layout, /*notificationUI = self.notification,*/ settingUI = self.setting, room = im.room;
		//im events
		im.bind("ready",function(){
			layout.changeState("ready");
      			show(layout.app("room").window.element);
			buddyUI.online();
      			settingUI.online();
		}).bind("go",function(data){
			layout.changeState("active");
			layout.option("user", data.user);
			date.init(data.server_time);
			self._initStatus();
			!buddyUI.window.isMinimize() && buddy.loadDelay();
			buddyUI.notice("count", buddy.count({presence:"online"}));
			setting.set(data.setting);
		}).bind("stop", function(type){
			layout.changeState("stop");
		        hide(layout.app("room").window.element);
			type == "offline" && layout.removeAllChat();
			layout.updateAllChat();
			buddyUI.offline();
			type && buddyUI.notice(type);
      			settingUI.offline();
		});
		//setting events
		setting.bind("update",function(key, val){

			switch(key){
				case "buddy_sticky": buddyUI.window.option("sticky", val);
				settingUI.check_tag(key, val);
				break;
				case "play_sound": (val ? sound.enable() : sound.disable() ); 
				settingUI.check_tag(key, val);
				break;
				case "msg_auto_pop": layout.option("chatAutoPop", val); 
				settingUI.check_tag(key, val);
				break;
				case "minimize_layout": 
					settingUI.check_tag(key, val);
				(val ? layout.collapse() : layout.expand()); 
				break;
			}
		});
		settingUI.bind("change",function(key, val){
			setting.set(key, val);
		});
    //handle 
    settingUI.bind("offline",function(){
      im.trigger("stop");
      im.offline();
    });
    settingUI.bind("online",function(){
      im.trigger("ready");  
      im.online();
    });

		layout.bind("collapse", function(){
			setting.set("minimize_layout", true);
		});
		layout.bind("expand", function(){
			setting.set("minimize_layout", false);
		});

		//display status
		layout.bind("displayUpdate", function(e){
			self._updateStatus(); //save status
		});

		//buddy events

		//select a buddy
		buddyUI.bind("select", function(info){
			self.addChat(info.id, {type: "buddy"});
		}).bind("online",function(){
			im.online();
		}).bind("broadcastselect",function(e){
			self.addBroadcast(e);
		});
		buddyUI.window.bind("displayStateChange",function(type){
			if(type != "minimize")buddy.loadDelay();
		});
		//some buddies online.
		buddy.bind("online", function(data){
			buddyUI.add(data);
			layout.updateChat(data);
			buddyUI.notice("count", buddy.count({presence:"online"}));
		});
		buddy.bind("onlineDelay", function(data){
			buddyUI.notice("count", buddy.count({presence:"online"}));
		});

		//some buddies offline.
		var mapId = function(a){ return isObject(a) ? a.id : a };
		buddy.bind("offline", function(data){
			buddyUI.remove(map(data, mapId));
			layout.updateChat(data);
			buddyUI.notice("count", buddy.count({presence:"online"}));
		});
		//some information has been modified.
		buddy.bind("update", function(data){
			buddyUI.update(data);
			layout.updateChat(data);
		});

		//all ready.
		//message
		im.bind("message",function(data){
			var show = false,roomData = this.room.dataHash,
         			 l = data.length, d, uid = im.data.user.id, id, c, online_ids = [], count = "+1";
			for(var i = 0; i < l; i++){
				d = data[i];
				id = d.to == uid ? d.from : d.to;
				if(!d["new"])online_ids.push(id);
				c = layout.chat(id);
				c && isFunction(c.status) && c.status("");//clear status
				if(!c){	
				   var titlename = "";
				   if (d.type === "broadcast"){
				   	titlename = "站长广播";
				   }else{
			           	titlename = (d.type === "unicast")?d.nick:roomData[id].name;
				   }
           			   if (d.type === "unicast"){
				   	if (d.from === "0"){
						self.addBroadcast(id,"update",null,"NextIM");
					}else{
				   		self.addChat(id, null, null, titlename);
					}
          			   }else if (d.type === "broadcast"){
				   	self.addBroadcast(id,null,null,titlename);
	  			   }else{
			              self.addChat(id,{type:"room"});  
          			}
			 	c = layout.chat(id);
			  }
				c && setting.get("msg_auto_pop") && !layout.activeTabId && layout.focusChat(id);
				c.window.notifyUser("information", count);
				var p = c.window.pos;
				(p == -1) && layout.setNextMsgNum(count);
				(p == 1) && layout.setPrevMsgNum(count);
				if(d.from != uid)show = true;
			}
			if(show){
				sound.play('msg');
				titleShow(i18n("new message"), 5);
			}
			history.handle(data);
			buddy.online(online_ids, 1);
		});
		function grepOffline(msg){
			return msg.type == "offline";
		}
		function grepOnline(msg){
			return msg.type == "online";
		}
		function mapFrom(a){ return a.from; }

		im.bind("presence",function(data){
			offline = grep(data, grepOffline);
			online = grep(data, grepOnline);
			buddy.online(map(online, mapFrom), buddyUI.window.isMinimize());
			buddy.offline(map(offline, mapFrom));
			//chatlink.online(online);
			online.length && buddyUI.notice("buddyOnline", online.pop()["nick"]);
		});
		im.bind("status",function(data){
			each(data,function(n,msg){
				var userId = im.data.user.id;
				var id = msg['from'];
				if (userId != msg.to && userId != msg.from) {
					id = msg.to; //群消息
					var nick = msg.nick;
				}else{
					var c = layout.chat(id);
					c && c.status(msg['show']);
				}
			});
		});
				//for test
		history.bind("data", function( id, data){
			var c = layout.chat(id), count = "+" + data.length;
			if(c){
				c.history.add(data);
			}
			//(c ? c.history.add(data) : im.addChat(id));
		});
		history.bind("clear", function( id){
			var c = layout.chat(id);
			c && c.history.clear();
		});

		///notification
		/*
		im.notification.bind("data",function( data){
			notificationUI.window.notifyUser("information", "+" + data.length);
			notificationUI.add(data);
		});
    setTimeout(function(){
			im.notification.load();
		}, 2000);  
		*/

	},
	__status: false,
	_initStatus: function(){
		var self = this, layout = self.layout;
		if(self.__status)return layout.updateAllChat();
		// status start
		self.__status = true;
		var status = self.im.status,
		tabs = status.get("tabs"), 
		tabIds = status.get("tabIds"),
		//prev num
		p = status.get("p"), 
		//focus tab
		a = status.get("a");

		tabIds && tabIds.length && tabs && each(tabs, function(k,v){
			//broadcast
			if (k == 0 || k === "0"){
			//when the type is broadcast ,don't show
				//self.addBroadcast(k,{type:"broadcast"},{isMinimize:true});

			}
			else{
				self.addChat(k, {type: v["t"]}, { isMinimize: true});
				layout.chat(k).window.notifyUser("information", v["n"]);
			}
		});
		p && (layout.prevCount = p) && layout._fitUI();
		a && layout.focusChat(a);
		// status end
	},
	addBroadcast: function(id,type,y,title){
		var self = this,layout = self.layout,im = self.im,history = self.im.history,u = im.data.user,isadmin =self._isAdmin(u.id);
		var _info = "";

		if (layout.chat(0))return;

		var h = history.get(0);
		if(!h)history.load('0');

		if (type === "update"){
			_info = {id:0,name:"NextIM",isadmin:false};
			layout.addBroadcast(_info,extend({user:u,history:null,block:true,emot:false,clearHistory:false,member:false,msgType:"broadcast"},{name:"NextIM"}), null);
			layout.chat(0);

		}else{
			_info = {id:0,name:tpl("<%=broadcast%>"),isadmin:isadmin};
			layout.addBroadcast(_info,extend({user:u,history:h,block:true,emot:isadmin,clearHistory:true,member:false,msgType:"broadcast"},{name:tpl("<%=broadcast%>")}), null);
			var broadcast = layout.chat(0);
			broadcast.bind("sendMsg",function(msg){
				im.sendMsg(msg);		
				history.handle(msg);
			});
		}
	},
	_isAdmin:function(id){
		var self = this,im = self.im,ids = im.admins;
       		for (var i = 0; i < ids.length; i++){
			if(id == trim(ids[i])){
				return true;
			}
		}		
		return false; 
	},
	addChat: function(id, options, winOptions, name){
		var self = this, layout = self.layout, im = self.im, history = self.im.history, buddy = im.buddy, room = im.room;
		if(layout.chat(id))return;
		if(options && options.type == "room"){
			var h = history.get(id), info = room.get(id), _info = info || {id:id, name: name || id};
			_info.presence = "online";
			layout.addChat(_info, extend({history: h, block: true, emot:true, clearHistory: false, member: true, msgType: "multicast"}, options), winOptions);
			if(!h) history.load(id);
			var chat = layout.chat(id);
			chat.bind("sendMsg", function(msg){
				im.sendMsg(msg);
				history.handle(msg);
			}).bind("select", function(info){
				buddy.online(info.id, 1);//online
				self.addChat(info.id, {type: "buddy"}, null, info.name);
				layout.focusChat(info.id);
			}).bind("block", function(d){
				room.block(d.id);
			}).bind("unblock", function(d){
				room.unblock(d.id);
			}).window.bind("close",function(){
				chat.options.info.blocked && room.leave(id);
			});
			setTimeout(function(){
				if(chat.options.info.blocked)room.join(id);
				else room.initMember(id);
			}, 500);
			isArray(info.members) && each(info.members, function(n, info){
				chat.addMember(info.id, info.name, info.id == im.data.user.id);
			});

		}else{
			var h = history.get(id), info = buddy.get(id);
			var _info = info || {id:id, name: name || id};
			layout.addChat(_info, extend({history: h, block: false, emot:true, clearHistory: true, member: false, msgType: "unicast"}, options), winOptions);
			if(!info) buddy.update(id);
			if(!h) history.load(id);
			layout.chat(id).bind("sendMsg", function(msg){
				im.sendMsg(msg);
				history.handle(msg);
			}).bind("sendStatus", function(msg){
				im.sendStatus(msg);
			}).bind("clearHistory", function(info){
				history.clear(info.id);
			});
		}
	},
	_updateStatus: function(){
		var self = this, layout = self.layout, _tabs = {}, panels = layout.panels;
		each(layout.tabs, function(n, v){
			_tabs[n] = {
				n: v._count(),
				t: panels[n].options.type //type: buddy,room
			};
		});
		var d = {
			//o:0, //has offline
			tabs: _tabs, // n -> notice count
			tabIds: layout.tabIds,
			p: layout.prevCount, //tab prevCount
			a: layout.activeTabId, //tab activeTabId
			b: layout.app("buddy").window.isMinimize() ? 0 : 1 //is buddy open
		}
		self.im.status.set(d);
	}
});

var _countDisplay = function(element, count){
	if (count === undefined){
		return parseInt(element.innerHTML);
	}
	else if (count){
		count = (typeof count == "number") ? count : (parseInt(element.innerHTML) + parseInt(count));
		element.innerHTML = count.toString();
		show(element);
	}
	else {
		element.innerHTML = '0';
		hide(element);
	}
	return count;
};

function mapElements(obj){
	var elements = obj.getElementsByTagName("*"), el, id, need = {}, pre = ":", preLen = pre.length;
	for(var i = elements.length - 1; i > -1; i--){
		el = elements[i];
		id = el.id;
		if(id && id.indexOf(pre) == 0)need[id.substring(preLen, id.length)] = el;
	}
	return need;
}
function createElement(str){
	var el = document.createElement("div");
	el.innerHTML = str;
	el = el.firstChild; // release memory in IE ???
	return el;
}
var tpl = (function(){
	var dic = null, re = /\<\%\=(.*?)\%\>/ig;
	function call(a, b){
		return dic && dic[b] !=undefined ? dic[b] : i18n(b);
	}
	return function(str, hash){
		if(!str)return '';
		dic = hash;
		return str.replace(re, call);
	};
})();



var plugin = {
	add: function(module, option, set) {
		var proto = webimUI[module].prototype;
		for(var i in set){
			proto.plugins[i] = proto.plugins[i] || [];
			proto.plugins[i].push([option, set[i]]);
		}
	},
	call: function(instance, name, args) {
		var set = instance.plugins[name];
		if(!set || !instance.element.parentNode) { return; }

		for (var i = 0; i < set.length; i++) {
			if (instance.options[set[i][0]]) {
				set[i][1].apply(instance.element, args);
			}
		}
	}
};

/*
* widget
* options:
* 	template
* 	className
*
* attributes:
* 	id
* 	name
* 	className
* 	element
* 	$
*
* methods:
* 	template
*
*/
var _widgetId = 1;
function widget(name, defaults, prototype){
	function m(element, options){
		var self = this;
		self.id = _widgetId++;
		self.name = name;
		self.className = "webim-" + name;
		self.options = extend({}, m['defaults'], options);

		isFunction(self._preInit) && self._preInit();
		//template
		self.element = element || (self.template && createElement(self.template())) || ( self.options.template && createElement(tpl(self.options.template)));
		if(self.element){
			self.options.className && addClass(self.element, self.options.className);
			self.$ = mapElements(self.element);
		}
		isFunction(self._init) && self._init();
		//isFunction(self._initEvents) && setTimeout(function(){self._initEvents()}, 0);
		isFunction(self._initEvents) && self._initEvents();
	}
	m.defaults = defaults;// default options;
	// add prototype
	extend(m.prototype, objectExtend, widget.prototype, prototype);
	webimUI[name] = m;
}

extend(widget.prototype, {
	_init: function(){
	}
});
function app(name, events){
	webimUI.apps[name] = events || {};
}
extend(webimUI,{
	version: "2.1.0pre",
	widget: widget,
	app: app,
	plugin: plugin,
	i18n: i18n,
	date: date,
	ready: ready,
	createElement: createElement,
	apps:{}
});
webim.ui = webimUI;

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
		var self = this, options = self.options, $ = self.$;
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
		replaceClass(el, "webim-window-normal webim-window-maximize webim-window-minimize", "webim-window-" + className);
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
		self.trigger("close");
		remove(self.element);
	},
	_initEvents:function(){
		var self = this, element = self.element, $ = self.$, tab = $.tab;
		var stop = function(e){
			stopPropagation(e);
			preventDefault(e);
		};
		//resize
		var minimize = function(e){
			self.minimize();
		};
		addEvent($.header, "click", minimize);
		addEvent(tab, "click", function(e){
			if(self.isMinimize())self.restore();
			else self.minimize();
			stop(e);
		});
		addEvent(tab,"mouseover",function(){
			addClass(this, "ui-state-hover");
			removeClass(this, "ui-state-default");
		});
		addEvent(tab,"mouseout",function(){
			removeClass(this, "ui-state-hover");
			this.className.indexOf("ui-state-") == -1 && addClass(this, "ui-state-default");
		});
		addEvent(tab,"mousedown",stop);
		disableSelection(tab);

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
				title:"站长广播"
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
		var self = this, panels = self.panels, id = info.id, chat;
		if(!panels[id]){
			var win = self.tabs[id] = new webimUI.window(null, extend({
				isMinimize: self.activeTabId || !self.options.chatAutoPop,
				tabWidth: self.tabWidth -2
			},winOptions)).bind("close", function(){ self._onChatClose(id)}).bind("displayStateChange", function(state){ self._onChatChange(id,state)});
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
widget("emot", {
                template: '<div class="webim-emot ui-widget-content"><%=emots%></div>'
},{
        _init: function(options){
                var self = this, element = self.element;
		each(element.firstChild.childNodes, function(i,v){
			addEvent(v, "click", function(e){
				removeClass(element, "webim-emot-show");
				self.trigger('select', this.firstChild.getAttribute('alt'));
			});
		});
        },
	template: function(){
                var self = this, emots = self.emots = webim.ui.emot.emots;
                var markup = [];
                markup.push('<ul class="ui-helper-clearfix">');
                each(emots, function(n, v){
                    var src = v.src, title = v.t ? v.t : v.q[0];
                    markup.push('<li><img src="');
                    markup.push(src);
                    markup.push('" title="');
                    markup.push(title);
                    markup.push('" alt="');
                    markup.push(v.q[0]);
                    markup.push('" /></li>');
                });
                markup.push('</ul>');
		return tpl(self.options.template, { emots: markup.join('')});

	},
        toggle: function(){
                toggleClass(this.element, "webim-emot-show");
        }
});
extend(webimUI.emot, {
        emots: [
                {"t":"smile","src":"smile.png","q":[":)"]},
                {"t":"smile_big","src":"smile-big.png","q":[":d",":-d",":D",":-D"]},
                {"t":"sad","src":"sad.png","q":[":(",":-("]},
                {"t":"wink","src":"wink.png","q":[";)",";-)"]},
                {"t":"tongue","src":"tongue.png","q":[":p",":-p",":P",":-P"]},
                {"t":"shock","src":"shock.png","q":["=-O","=-o"]},
                {"t":"kiss","src":"kiss.png","q":[":-*"]},
                {"t":"glasses_cool","src":"glasses-cool.png","q":["8-)"]},
                {"t":"embarrassed","src":"embarrassed.png","q":[":-["]},
                {"t":"crying","src":"crying.png","q":[":'("]},
                {"t":"thinking","src":"thinking.png","q":[":-\/",":-\\"]},
                {"t":"angel","src":"angel.png","q":["O:-)","o:-)"]},
                {"t":"shut_mouth","src":"shut-mouth.png","q":[":-X",":-x"]},
                {"t":"moneymouth","src":"moneymouth.png","q":[":-$"]},
                {"t":"foot_in_mouth","src":"foot-in-mouth.png","q":[":-!"]},
                {"t":"shout","src":"shout.png","q":[">:o",">:O"]}
        ],
        init: function(options){
            var emot = webim.ui.emot, q = emot._q = {};
            options = extend({
                dir: 'webim/static/emot/default'
            }, options);
            if (options.emots) 
                emot.emots = options.emots;
            var dir = options.dir + "/";
            each(emot.emots, function(key, v){
                if (v && v.src) 
                    v.src = dir + v.src;
                v && v.q &&
                each(v.q, function(n, val){
                    q[val] = key;

                });

            });
        },
        parse: function(str){
            var q = webim.ui.emot._q, emots = webim.ui.emot.emots;
            q && each(q, function(n, v){
                var emot = emots[v], src = emot.src, title = emot.t ? emot.t : emot.q[0], markup = [];
                markup.push('<img src="');
                markup.push(src);
                markup.push('" title="');
                markup.push(title);
                markup.push('" alt="');
                markup.push(emot.q[0]);
                markup.push('" />');
                n = HTMLEnCode(n);
                n = n.replace(new RegExp('(\\' + '.$^*\\[]()|+?{}:<>'.split('').join('|\\') + ')', "g"), "\\$1");
                str = str.replace(new RegExp(n, "g"), markup.join(''));

            });
            return str;
        }
});
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
		var ex = /space\.php\?uid=(\d+)$|space\-(\d+)\.html$/i.exec(link);
		return ex && (ex[1] || ex[2]);
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

//
/* ui.setting:
 *
 options:
 	data

 attributes：

 methods:
 check_tag

 destroy()
 events: 
 change

 */
widget("setting",{
        template: '<div id="webim-setting" class="webim-setting">\
                        <ul id=":ul"><%=tags%></ul>\
                        <div id=":offline" class="webim-setting-offline"><a href="#offline"><%=offline%></a></div>\
                        <div id=":online" class="webim-setting-online"><a href="#online"><%=online%></a></div>\
                  </div>',
        tpl_check: '<li id=":<%=name%>"><input type="checkbox" <%=checked%> id="webim-setting-<%=name%>" name="<%=name%>"/><label for="webim-setting-<%=name%>"><%=label%></label></li>'
},{
        _init: function(){
                //this._initEvents();
        },
	template: function(){
		var self = this, temp = [], data = self.options.data;
		data && each(data, function(key, val){
			temp.push(self._check_tpl(key, val));
		});
		return tpl(self.options.template,{
		   tags:temp.join("")
		});
	},
	_initEvents:function(){
		var self = this, data = self.options.data, $ = self.$;
		data && each(data, function(key, val){
			$[key] && self._check_event($[key]);
		});
    addEvent($.offline,"click",function(e){
      self.trigger("offline");
    });
    addEvent($.online,"click",function(e){
      self.trigger("online");
    });
	},
  offline:function(){
    var $ = this.$;
      hide($.offline);//.style.display="none";
      show($.online);//.style.display="block";   
  },
  online:function(){
      var $ = this.$;
      show($.offline);//.style.display="block";
      hide($.online);//.style.display="none";   
  },
	_check_tpl: function(name, isChecked){
		return tpl(this.options.tpl_check,{
			label: i18n(name),
			name: name,
			checked: isChecked ? 'checked="checked"' : ''
		});
	},
	_check_event: function(el){
		var self = this;
		addEvent(el.firstChild, "click", function(e){
                        self._change(this.name, this.checked);
		});
	},
        check_tag: function(name, isChecked){
		var self = this;
		if(isObject(name)){
			each(name, function(key,val){
				self.check_tag(key, val);
			});
			return;
		}
                var $ = self.$, tag = $[name];
                if(tag){
                        tag.firstChild.checked = isChecked;
                        return;
                }
		var el = $[name] = createElement(self._check_tpl(name, isChecked));
		self._check_event(el);
		$.ul.appendChild(el);
        },
        _change:function(name, value){
                this.trigger("change", [name, value]);
        },
        destroy: function(){
        }
});
//
/* ui.buddy:
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

widget("buddy",{
        template: '<div id="webim-buddy" class="webim-buddy">\
                        <div id=":search" class="webim-buddy-search ui-state-default ui-corner-all"><em class="ui-icon ui-icon-search"></em><input id=":searchInput" type="text" value="" /></div>\
			<div id=":broadcast" class="webim-broadcast"><a class="ui-helper-clearfix" rel="9" href="#"><img width="25" src="webim/static/images/icons/broadcastx.png"><strong><%=broadcast%></strong><span></span></a></div>\
                        <div class="webim-buddy-content">\
                                <div id=":empty" class="webim-buddy-empty"><%=empty buddy%></div>\
                                <ul id=":ul"></ul>\
                        </div>\
                  </div>',
        tpl_group: '<li><h4 class="ui-state-default"><%=title%>(<%=count%>)</h4><ul></ul></li>',
        tpl_li: '<li title=""><a href="<%=url%>" rel="<%=id%>" class="ui-helper-clearfix"><img width="25" src="<%=pic_url%>" defaultsrc="<%=default_pic_url%>" onerror="var d=this.getAttribute(\'defaultsrc\');if(d && this.src!=d)this.src=d;" /><strong><%=name%></strong><span><%=status%></span></a></li>'
},{
	_init: function(){
		var self = this;
		self.groups = {
		};
		self.li = {
		};
		self.li_group = {
		};
		self._count = 0;
		//self._initEvents();
	},
	_initEvents: function(){
		var self = this, $ = self.$, search = $.search, broadcast = $.broadcast,input = $.searchInput, placeholder = i18n("search buddy"), activeClass = "ui-state-active";
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
    /*
		var a = $.online.firstChild;
		addEvent(a, "click", function(e){
			preventDefault(e);
			self.trigger("online");
		});
		hoverClass(a, "ui-state-hover");
		addEvent($.offline.firstChild, "click", function(e){
			preventDefault(e);
			self.trigger("offline");
		});
    */
		//broadcast 
		addEvent(broadcast.firstChild,"click",function(e){
			preventDefault(e);
			self.trigger("broadcastselect");	
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
		toggleClass(this.element, "webim-buddy-scroll", is);
	},
	_time:null,
	_titleBuddyOnline: function(name){
		var self = this, win = self.window;
		if(!name) name = "";
	//	win && win.title(subVisibleLength(name, 0, 8) + " " + i18n("online"));
		if(self._time) clearTimeout(self._time);
		self._time = setTimeout(function(){
			self._titleCount();
		}, 5000);
	},
	_title: function(type){
		var win = this.window;
		if(win){
			win.title(i18n("chat") + "[" + i18n(type) + "]");
		}
	},
	notice: function(type, nameOrCount){
		var self = this;
		switch(type){
			case "buddyOnline":
				self._titleBuddyOnline(nameOrCount);
			break;
			case "count":
				self._count = nameOrCount;
			self._titleCount();
			break;
			default:
				self._title(type);
		}
	},
	online: function(){
		var self = this, $ = self.$, win = self.window;
		self.notice("connect");
		//hide($.online);
		show($.empty);
		show($.offline);
    		show(win.element);
	},
	offline: function(){
		var self = this, $ = self.$, win = self.window;
		self.notice("offline");
		//show($.online);
		hide($.offline);
		hide($.empty);
		self.scroll(false);
		self.removeAll();
    		hide(win.element);
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

			var groups = self.groups, group_name = i18n(info["group"] || "friend"), group = groups[group_name];
			if(!group){
				var g_el = createElement(tpl(self.options.tpl_group));
				hide(g_el);
				if(group_name == i18n("stranger")) end = true;
				if(end) ul.appendChild(g_el);
				else ul.insertBefore(g_el, ul.firstChild);
				group = {
					name: group_name,
					el: g_el,
					count: 0,
					title: g_el.firstChild,
					li: g_el.lastChild
				};
				self.groups[group_name] = group;
			}
			if(group.count == 0) show(group.el);
			self.li_group[id] = group;
			group.li.appendChild(el);
			group.count++;
			group.title.innerHTML = group_name + "("+ group.count+")";
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
	add: function(data, end){
		data = makeArray(data);
		for(var i=0; i < data.length; i++){
			this._addOne(data[i], end);
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
//
/* ui.history:
 *
 options:
 attributes：

 methods:
 add(data) //
 clear

 destroy()
 events: 
 clear
 update

 */
widget("history",{
        user: {},
        info: {},
        template:'<div class="webim-history">\
                        <div id=":content" class="webim-history-content"> \
                </div></div>'
},{
	_init: function(){
		var self = this, element = self.element, options = self.options;
		plugin.call(self, "init", [null, self.ui()]);
	},
	clear:function(){
		var self = this;
		self.$.content.innerHTML = "";
		self.trigger("clear");
	},
	add: function(data){
		data = makeArray(data);
		var self = this, l = data.length, markup = [];
		if(!l)return;
		for (var i = 0; i < l; i++){
			var val = data[i];
			markup.push(self._renderMsg(val));
		}
		self.$.content.innerHTML += markup.join('');
		self.trigger("update");
	},
	_renderMsg: function(logItem){
		var self = this;
		logItem = extend({}, logItem);
		plugin.call(self, "render", [null, self.ui({msg: logItem})]);
		var  from = logItem.from, to = logItem.to, time = logItem.timestamp, msg = logItem.body, shouldTilte = true, last = self._lastLogItem, markup = [], info = self.options.info, user = self.options.user;
		var fromSelf = from == user.id;
		var other = !fromSelf && user.id != to;

		var name = other ? logItem.nick : fromSelf ? user.name : (info.name ? '<a href="' + info.url + '">' + info.name + '</a>' : info.id);
		if (last && last.to == to && last.from == from && time - last.timestamp < 60000){
			shouldTilte = false;
		}
		//markup.push(self._renderDateBreak(time));
		if (shouldTilte) {
			self._lastLogItem = logItem;
			var t = (new date(time));
			markup.push('<h4><span class="webim-gray">');
			markup.push(t.getDay(true));
			markup.push(" ");
			markup.push(t.getTime());
			markup.push('</span>');
			markup.push(name);
			markup.push('</h4>');
		}

		markup.push('<p>');
		markup.push(msg);
		markup.push('</p>');
		return markup.join("");
	},
	_renderDateBreak: function(time){
		var self = this, last = self._lastLogItem, newDate = new Date(), lastDate = new Date(), markup = [];
		newDate.setTime(time);
		last && lastDate.setTime(last.timestamp);
		if(!last || newDate.getDate() != lastDate.getDate() || newDate.getMonth() != lastDate.getMonth()){
			markup.push("<h5>");
			markup.push((new date(time)).getDay(true));
			markup.push("</h5>");
		}
		return markup.join("");
	},
	ui:function(ext){
		var self = this;
		return extend({
			element: self.element,
			$: self.$
		}, ext);
	},
	plugins:{}

});
//<p class="webim-history-actions"> \
//                                                        <a href="#"><%=clear history%></a> \
//                                                        </p> \

var autoLinkUrls = (function(){
	var attrStr;
	function filterUrl(a, b, c){
		return '<a href="' + (b=='www.' ? ('http://' + a) : a) + '"' + attrStr + '>' + a + '</a>'
	}
		function serialize(key, val){
			attrStr += ' ' + key + '="' + val + '"';
		}
		return function(str, attrs){
			attrStr = "";
			attrs && isObject(attrs) && each(attrs, serialize);
			return str.replace(/(https?:\/\/|www\.)([^\s<]+)/ig, filterUrl);
		};
})();

webimUI.history.defaults.parseMsg = true;
plugin.add("history","parseMsg",{
	render:function(e, ui){
		var msg = ui.msg.body;
		msg = HTMLEnCode(msg);
		msg = autoLinkUrls(msg, {target:"_blank"});
		ui.msg.body = msg;
	}
});

webimUI.history.defaults.emot = true;
plugin.add("history","emot",{
	render:function(e, ui){
		ui.msg.body = webimUI.emot.parse(ui.msg.body);
	}
});


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
//
/* ui.chat:
 *
 options:
 window
 history

 methods:
 update(info)
 status(type)
 insert(text, isCursorPos)
 focus
 notice(text, timeOut)
 destroy()

 events: 
 sendMsg
 sendStatus

 */
 
function ieCacheSelection(e){
        document.selection && (this.caretPos = document.selection.createRange());
}
widget("chat",{
        template:'<div class="webim-chat"> \
                                                <div id=":header" class="webim-chat-header ui-widget-subheader">  \
                                                        <div id=":user" class="webim-user"> \
                                                                <a id=":userPic" class="webim-user-pic" href="#id"><img width="50" height="50" src="about:blank" defaultsrc="" onerror="var d=this.getAttribute(\'defaultsrc\');if(d && this.src!=d)this.src=d;"></a> \
                                                                <span id=":userStatus" title="" class="webim-user-status">Hello</span> \
                                                        </div> \
                                                </div> \
                                                                                                                                        <div class="webim-chat-notice-wrap"><div id=":notice" class="webim-chat-notice ui-state-highlight"></div></div> \
                                                <div id=":content" class="webim-chat-content"> \
                                                                                                                <div id=":status" class="webim-chat-status webim-gray"></div> \
                                                </div> \
                                                <div id=":actions" class="webim-chat-actions"> \
                                                        <div id=":toolContent" class="webim-chat-tool-content"></div>\
                                                        <div id=":tools" class="webim-chat-tools ui-helper-clearfix ui-state-default"></div>\
                                                        <table class="webim-chat-t" cellSpacing="0"> \
                                                                <tr> \
                                                                        <td style="vertical-align:top;"> \
                                                                        <em class="webim-icon webim-icon-chat"></em>\
                                                                        </td> \
                                                                        <td style="vertical-align:top;width:100%;"> \
                                                                        <div class="webim-chat-input-wrap">\
                                                                                <textarea id=":input" class="webim-chat-input webim-gray"><%=input notice%></textarea> \
                                                                        </div> \
                                                                        </td> \
                                                                </tr> \
                                                        </table> \
                                                </div> \
                                        </div>'
},{
	_init: function(){
		var self = this, element = self.element, options = self.options, win = self.window = options.window;
		var history = self.history = new webimUI.history(null,{
			user: options.user,
			info: options.info
		});
		self.$.content.insertBefore(history.element, self.$.content.firstChild);
		//self._initEvents();
		if(win){
			win.html(element);
			self._bindWindow();
			//self._fitUI();
		}
		self.update(options.info);
		history.add(options.history);
		plugin.call(self, "init", [null, self.ui()]);
		self._adjustContent();
	},
	update: function(info){
		var self = this;
		if(info){
			self.option("info", info);
			self.history.option("info", info);
			self._updateInfo(info);
		}
		var userOn = self.options.user.presence == "online";
		var buddyOn = self.options.info.presence == "online";
		if(!userOn){
			self.notice(i18n("user offline notice"));
		}else if(!buddyOn){
			self.notice(i18n("buddy offline notice",{name: self.options.info.name}));
		}else{
			self.notice("");
		}
		plugin.call(self, "update", [null, self.ui()]);
	},
	focus: function(){
		//this.$.input.focus();
    //fix firefox
    var item = this.$.input;
    window.setTimeout(function(){item.focus()},0);
	},
	_noticeTime: null,
	_noticeTxt:"",
	notice: function(text, timeOut){
		var self = this, content = self.$.notice, time = self._noticeTime;
		if(time)clearTimeout(time);
		if(!text){
			self._noticeTxt = null;
			hide(content);
			return;
		}
		if(timeOut){
			content.innerHTML = text;
			show(content);
			setTimeout(function(){
				if(self._noticeTxt)
					content.innerHTML = self._noticeTxt;
				else hide(content, 500);
			}, timeOut);

		}else{
			self._noticeTxt = text;
			content.innerHTML = text;
			show(content);
		}
	},
	_adjustContent: function(){
		var content = this.$.content;
		content.scrollTop = content.scrollHeight;
	},
	_fitUI: function(e){
		var self = this, win = self.window, $ = self.$;
		self._adjustContent();

	},
	_bindWindow: function(){
		var self = this, win = self.window;
		win.bind("displayStateChange", function(type){
			if(type != "minimize"){
        //fix firefox
        window.setTimeout(function(){self.$.input.focus();},0);
				//self.$.input.focus();
				self._adjustContent();
			}
		});
		//win.bind("resize",{self: self}, self._fitUI);
	},
	_inputAutoHeight:function(){
		var el = this.$.input, scrollTop = el[0].scrollTop;
		if(scrollTop > 0){
			var h = el.height();
			if(h> 32 && h < 100) el.height(h + scrollTop);
		}
	},
	_sendMsg: function(val){
		var self = this, options = self.options, info = options.info;
		var msg = {
			type: options.msgType,
			to: info.id,
			from: options.user.id,
			stype: '',
			offline: info.presence == "online" ? 0 : 1,
			body: val,
			timestamp: (new Date()).getTime()
		};
		plugin.call(self, "send", [null, self.ui({msg: msg})]);
		self.trigger('sendMsg', msg);
		//self.sendStatus("");
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
		else self._typing();

	},
	_onFocusInput: function(e){
		var self = this, el = target(e);

		//var val = el.setSelectionRange ? el.value.substring(el.selectionStart, el.selectionEnd) : (window.getSelection ? window.getSelection().toString() : (document.selection ? document.selection.createRange().text : ""));
		var val = window.getSelection ? window.getSelection().toString() : (document.selection ? document.selection.createRange().text : "");
		if(!val){
      //self.$.input.focus();
      //fix firefox
      window.setTimeout(function(){self.$.input.focus();},0);
    }
	},
	_initEvents: function(){
		var self = this, options = self.options, $ = self.$, placeholder = i18n("input notice"), gray = "webim-gray", input = $.input;

		self.history.bind("update", function(){
			self._adjustContent();
		}).bind("clear", function(){
			self.notice(i18n("clear history notice"), 3000);
		});
		//输入法中，进入输入法模式时keydown,keypress触发，离开输入法模式时keyup事件发生。
		//autocomplete之类事件放入keyup，回车发送事件放入keydown,keypress

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
		addEvent($.content, "click", function(e){self._onFocusInput(e)});

	},
	_updateInfo:function(info){
		var self = this, $ = self.$;
		$.userPic.setAttribute("href", info.url);
		$.userPic.firstChild.setAttribute("defaultsrc", info.default_pic_url ? info.default_pic_url : "");
		$.userPic.firstChild.setAttribute("src", info.pic_url);
		$.userStatus.innerHTML = info.status;
		self.window.title(info.name);
	},
	insert:function(value, isCursorPos){
		//http://hi.baidu.com/beileyhu/blog/item/efe29910f31fd505203f2e53.html
		var self = this,input = self.$.input;
		input.focus();
		if(!isCursorPos){
			input.value = value;
			return;
		}
		if(!value) value = "";
		if(input.setSelectionRange){
			var val = input.value, rangeStart = input.selectionStart, rangeEnd = input.selectionEnd, tempStr1 = val.substring(0,rangeStart), tempStr2 = val.substring(rangeEnd), len = value.length;  
			input.value = tempStr1+value+tempStr2;  
			input.setSelectionRange(rangeStart+len,rangeStart+len);
		}else if(document.selection){
			var caretPos = input.caretPos;
			if(caretPos){
				caretPos.text = value;
				caretPos.collapse();
				caretPos.select();
			}
			else{
				input.value += value;
			}
		}else{
			input.value += value;
		}
	},
	_statusText: '',
	sendStatus: function(show){
		var self = this;
		if (!show || show == self._statusText) return;
		self._statusText = show;
		self.trigger('sendStatus', {
			to: self.options.info.id,
			show: show
		});
	},
	_checkST: false,
	_typing: function(){
		var self = this;
		self.sendStatus("typing");
		if (self._checkST) 
			clearTimeout(self._checkST);
		self._checkST = window.setTimeout(function(){
			self.sendStatus('clear');
		}, 6000);
	},
	_setST: null,
	status: function(type){
		//type ['typing']
		type = type || 'clear';
		var self = this, el = self.$.status, name = self.options.info.name, markup = '';
		markup = type == 'clear' ? '' : name + i18n(type);
		el.innerHTML = markup;
		self._adjustContent();
		if (self._setST)  clearTimeout(self._setST);
		if (markup != '') 
			self._setST = window.setTimeout(function(){
				el.innerHTML = '';
			}, 10000);
	},
	destroy: function(){
		this.window.close();
	},
	ui:function(ext){
		var self = this;
		return extend({
			self: self,
			$: self.$,
			history: self.history
		}, ext);
	},
	plugins: {}
});

webimUI.chat.defaults.emot = true;
plugin.add("chat","emot",{
	init:function(e, ui){
		var chat = ui.self;
		var emot = new webimUI.emot();
		emot.bind("select",function(alt){
     
			chat.focus();
			chat.insert(alt, true);
		});
		var trigger = createElement(tpl('<a href="#chat-emot" title="<%=emot%>"><em class="webim-icon webim-icon-emot"></em></a>'));
		addEvent(trigger,"click",function(e){
			preventDefault(e);
			emot.toggle();
		});
		ui.$.toolContent.appendChild(emot.element);
		ui.$.tools.appendChild(trigger);
	},
	send:function(e, ui){
	}
});
webimUI.chat.defaults.clearHistory = true;
plugin.add("chat","clearHistory",{
	init:function(e, ui){
		var chat = ui.self;
		var trigger = createElement(tpl('<a href="#chat-clearHistory" title="<%=clear history%>"><em class="webim-icon webim-icon-clear"></em></a>'));
		addEvent(trigger,"click",function(e){
			preventDefault(e);
			chat.trigger("clearHistory",[chat.options.info]);
		});
		ui.$.tools.appendChild(trigger);
	}
});
webimUI.chat.defaults.block = true;
plugin.add("chat","block",{
	init:function(e, ui){
		var chat = ui.self;
		var blocked = chat.options.info.blocked,
		name = chat.options.info.name,
		block = createElement('<a href="#chat-block" style="display:'+(blocked ? 'none' : '')+'" title="'+ i18n('block group',{name:name}) +'"><em class="webim-icon webim-icon-unblock"></em></a>'),
		unblock = createElement('<a href="#chat-block" style="display:'+(blocked ? '' : 'none')+'" title="'+ i18n('unblock group',{name:name}) +'"><em class="webim-icon webim-icon-block"></em></a>');
		addEvent(block,"click",function(e){
			preventDefault(e);
			hide(block);
			show(unblock);
			chat.trigger("block",[chat.options.info]);
		});
		addEvent(unblock,"click",function(e){
			preventDefault(e);
			hide(unblock);
			show(block);
			chat.trigger("unblock",[chat.options.info]);
		});
		ui.$.tools.appendChild(block);
		ui.$.tools.appendChild(unblock);
	}
});
webimUI.chat.defaults.member = true;
extend(webimUI.chat.prototype, {
	addMember: function(id, name, disable){
		var self = this, ul = self.$.member, li = self.memberLi;
		if(li[id])return;
		var el = createElement('<li><a class="'+ (disable ? 'ui-state-disabled' : '') +'" href="'+ id +'">'+ name +'</a></li>');
		addEvent(el.firstChild,"click",function(e){
			preventDefault(e);
			disable || self.trigger("select", [{id: id, name: name}]);
		});
		li[id] = el;
		self.$.member.appendChild(el);

	},
	removeMember: function(id){
		var self = this, el = self.memberLi[id];
		if(el){
			self.$.member.removeChild(el);
			delete self.memberLi[id];
		}
	}
});
plugin.add("chat","member",{
	init:function(e, ui){
		var chat = ui.self, $ = ui.$;
		chat.memberLi = {};
		var member = createElement('<div class="webim-member ui-widget-content ui-corner-left"><iframe id=":bgiframe" class="webim-bgiframe" frameborder="0" tabindex="-1" src="about:blank;" ></iframe><ul></ul></div>');
		$.member = member.lastChild;
		$.content.parentNode.insertBefore(member, $.content);
	}
});

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

widget("broadcast",{
	template:'<div class="webim-chat"> \
                      <div id=":header" class="webim-broadcast-header ui-widget-subheader">  \
                            <div id=":user" class="webim-user"> \
                                  <a id=":userPic" class="webim-user-pic" href="space.php?uid=0"><img width="50" height="50" src="webim/static/images/icons/broadcast.png" defaultsrc="" onerror="var d=this.getAttribute(\'defaultsrc\');if(d && this.src!=d)this.src=d;"></a> \
				  	<span id=":userStatus" title="" class="webim-user-status"></span>\
                             </div> \
                       </div> \
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
	var self = this,options = self.options,info = options.info,isadmin = info.isadmin;
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
        var self = this,element = self.element,options = self.options,win = self.window = options.window,info = options.info;
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
		var self = this, options = self.options,isadmin = options.info.isadmin, $ = self.$, placeholder = i18n("input notice"), gray = "webim-gray", input = $.input;

		self.history.bind("update", function(){
			self._adjustContent();
		}).bind("clear", function(){
			self.notice(i18n("clear history notice"), 3000);
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
			to: info.id,
			from: options.user.id,
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
webimUI.chat.defaults.emot = true;
plugin.add("broadcast","emot",{
    init:function(e,ui){
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
 });
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
			c && c.addMember(info.id, info.name, info.id == im.data.user.id);
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
})(window, document);
