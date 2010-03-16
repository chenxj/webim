var ns = (navigator.appName.indexOf("Netscape") != -1);
var d = document;
function lsfloatdiv(id, sx, sy, floatid, bottom) {
	var adobj = $(id);
	if(!Cookie.get("closead")) {
		adobj.style.display = "";
	}
	
	adobj.style.width = (document.body.clientWidth - (typeof(sx) == 'string' ? eval(sx) : sx)*2) + 'px';
	adobj.style.left = (typeof(sx) == 'string' ? eval(sx) : sx) + 'px';
	adobj.style.top = (typeof(sy) == 'string'? eval(sy) : sy) + 'px';
	
	//设置两个紧贴主体两测
	//$('couplet_left').style.left = ($('wrap').offsetLeft - 0) + 'px';
	//$('couplet_rigth').style.left = ($('wrap').offsetLeft + $('wrap').offsetWidth+2) + 'px';
	
	if(floatid != "" && bottom != 0) {
		document.getElementById(floatid).style.bottom = bottom + 'px';
	}
	var el = d.getElementById?d.getElementById(id):d.all?d.all[id]:d.layers[id];
	var px = document.layers ? "" : "px";
	window[id + "_obj"] = el;
	if(d.layers)el.style = el;
	el.cx = el.sx = sx;el.cy = el.sy = sy;
	el.sP = function(x, y) { this.style.left=x+px;this.style.top=y+px; };

	el.floatIt = function() {
		var pX, pY;
		pX = (this.sx >= -4) ? 0 : ns ? innerWidth : 
		document.documentElement && document.documentElement.clientWidth ? 
		document.documentElement.clientWidth : document.body.clientWidth;
		pY = ns ? pageYOffset : document.documentElement && document.documentElement.scrollTop ? 
		document.documentElement.scrollTop : document.body.scrollTop;
		if(this.sy<0) 
		pY += ns ? innerHeight : document.documentElement && document.documentElement.clientHeight ? 
		document.documentElement.clientHeight : document.body.clientHeight;
		this.cx += (pX + this.sx - this.cx)/8;this.cy += (pY + this.sy - this.cy)/8;
		this.sP(this.cx, this.cy);
		setTimeout(this.id + "_obj.floatIt()", 40);
	}
	//分辨率小于800*600隐藏浮动广告
	var lengthobj = getWindowSize();
	if(lengthobj.winWidth < 800) {
		closeBanner(id);
	}
	return el;
}

/**
 * 获取窗口的高度与宽度
 */
function getWindowSize() {
  var winWidth = 0, winHeight = 0;
  if( typeof( window.innerWidth ) == 'number' ) {
    //Non-IE
    winWidth = window.innerWidth;
    winHeight = window.innerHeight;
  } else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
    //IE 6+ in 'standards compliant mode'
    winWidth = document.documentElement.clientWidth;
    winHeight = document.documentElement.clientHeight;
  } else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
    //IE 4 compatible
    winWidth = document.body.clientWidth;
    winHeight = document.body.clientHeight;
  }
  return {winWidth:winWidth,winHeight:winHeight}
}

function closeBanner(id) {
	if(typeof($(id)) == 'object') {
		$(id).style.display = 'none';
		Cookie.set("closead", 1);
	}
}