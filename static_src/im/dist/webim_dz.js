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
				buddy_ids: "",
                //(self.isStrangerOn == "on")?buddy_ids.join(","):"",
				stranger_ids: "",
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

