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

var isReady = false,isIfmReady = false, readyList = [];
function triggerReady() {
	if (crossdomain && !isIfmReady){
		var ibridgedoc = document.getElementById("webim_bridge").contentWindow.document;
		isIfmReady = true;
		if (ibridgedoc.readyState === "complete"){
			triggerReady();
		}else{
			if (ibridgedoc.addEventListener){
				ibridgedoc.addEventListener("DOMContentLoaded",function(){
						ibridgedoc.removeEventListener("DOMContentLoaded",arguments.callee,false);
						triggerReady();
				},false);
			}else if (ibridgedoc.attachEvent){
				
				ibridgedoc.attachEvent("onreadyStatechange",function(){
					if (ibridgedoc.readyState === "complete"){
						ibridgedoc.detachEvent("onreadyStatechange",
							arguments.callee);

						triggerReady();		
					}
				});
				if ( document.documentElement.doScroll 
						&& window == window.top ) 
					(function() {
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
						triggerReady();
					})();
			}
		}
		return;
	}
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

function buildBridge(){
	if (crossdomain){
	
	}else{
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
webim.hide = hide;
webim.show = show;
