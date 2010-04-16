(function(){
	var str = '{"bb":"sdf","cc":["wer","twe"],"dd":{"a":5},"g":false}';
	var obj = webim.JSON.decode(str);
	console.log(obj);
	console.log(webim.JSON.encode(obj));
})();
