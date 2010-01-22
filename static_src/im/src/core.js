function now() {
	return (new Date).getTime();
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
