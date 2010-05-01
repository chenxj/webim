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
	}else{
		script.charset = "UTF-8";
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
