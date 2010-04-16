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

