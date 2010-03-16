function sAutoComplete(objName, showID, ulID, valID, series, func) {
	this.items = [];
	this.showObj = $(showID);
	this.ulObj = $(ulID);
	this.valObj = $(valID);
	if (!this.showObj) return;
	this.obj = objName;
	this.values = false;
	this.sVal = null;
	this.currently = -1;
	this.series = isUndefined(series) ? 1 : series;
	this.func = isUndefined(func) ? '': func;
	this.showObj.childNodes[0].scrollTop = 0;
	return this;
};

sAutoComplete.prototype.addItem = function(items) {
	if(items.indexOf(",") > 0) {
		var itemArr = items.split(",");
		for(var i = 0; i < itemArr.length; i++) {
			this.items.push(itemArr[i]);
		}
	} else {
		this.items.push(items);
	}
	this.items.sort();
};
sAutoComplete.prototype.doClick = function(vObj) {
	with(this) {
		if(typeof vObj == 'undefined') return false; 
		var val = valObj.value;
		instance = eval(obj);
		if(!this.series) {
			valObj.value = vObj.val;
		}else if(values) {
			if(valObj.value.lastIndexOf(",") != valObj.value.length-1) {
				valObj.value = valObj.value.substring(0, valObj.value.lastIndexOf(",")+1);
			}
			valObj.value += vObj.val + ",";
		} else {
			instance.values = true;
			valObj.value = vObj.val + ",";
		}
		if(this.func != '') {
			this.func();
		}
		valObj.focus();
		showObj.style.display = "none";
	}
};
sAutoComplete.prototype.directionKeyDown = function(event) {
	with(this) {
		var e = event.keyCode ? event.keyCode : event.which;
		var allChild = ulObj.childNodes.length;

		if(e == 40) {
			if(currently+1 >= allChild) currently = allChild - 2;
			currently++
			if(currently != 0) showObj.childNodes[0].scrollTop += 16;
			ulObj.childNodes[currently].childNodes[0].style.cssText = "background: #2782D6; color: #FFF; text-decoration: none;";
		} else if(e == 38) {
			if(currently - 1 <= -1) currently = 1;
			currently--;
			showObj.childNodes[0].scrollTop -= 16;
			ulObj.childNodes[currently].childNodes[0].style.cssText = "background: #2782D6; color: #FFF; text-decoration: none;";
		} else if(e == 13) {
			instance = eval(obj);
			instance.doClick(ulObj.childNodes[currently]);
		}
		
	}
};
sAutoComplete.prototype.append = function(item, filtrate) {
	with(this) {
		instance = eval(obj);
	 	var liObj = document.createElement("li");
		liObj.onclick = function(){instance.doClick(this)};
		liObj.val = item;
		if(filtrate) {
			var reg  = new RegExp("(" + sVal + ")","ig");
			if(sVal) liObj.innerHTML = '<a href="###">' + item.replace(reg , "<strong>$1</strong>") + '</a>';
		} else {
			liObj.innerHTML = '<a href="###">' + item + '</a>';
		}
		ulObj.appendChild(liObj);
	}
};
sAutoComplete.prototype.handleEvent = function(searchVal, event) {
	with(this) {
		var hidden = true;
		var allVal = 0;
		var strArr = new Array();
		var e = event.keyCode ? event.keyCode : event.which;
		ulObj.innerHTML = "";
		showObj.style.display = "block";
		instance = eval(obj);
		if(searchVal.indexOf(",") > 0) {
			strArr = searchVal.split(",");
			allVal = strArr.length;
			if(strArr[strArr.length-1] != "") {
				searchVal = strArr[strArr.length-1];
			} else {
				searchVal = "";
			}
		}
		if(searchVal != "") {
			searchVal = addslashes(searchVal);
			sVal = searchVal;
			var reg = new RegExp(searchVal, "ig");
			var itemstr = '';
			for(var i = 0; i < items.length; i++) {
				var itemstr = items[i];
				if(itemstr.match(reg)) {
					instance.append(itemstr, 1);
					hidden = false;
				}
			}
		} else {
			for(var i = 0; i < items.length; i++) {
				instance.append(items[i], 0);
				hidden = false;
			}
			if(allVal == 0) instance.values = false;
		}
		if(hidden) {
			showObj.style.display = "none";
		} else if(e == 38 || e == 40 || e == 13) {
			instance.directionKeyDown(event);
		}
	}
};
function addslashes(str) {
	return preg_replace(['\\\\', '\\\'', '\\\/', '\\\(', '\\\)', '\\\[', '\\\]', '\\\{', '\\\}', '\\\^', '\\\$', '\\\?', '\\\.', '\\\*', '\\\+', '\\\|'], ['\\\\', '\\\'', '\\/', '\\(', '\\)', '\\[', '\\]', '\\{', '\\}', '\\^', '\\$', '\\?', '\\.', '\\*', '\\+', '\\|'], str);
}
function preg_replace(search, replace, str) {
	var len = search.length;
	for(var i = 0; i < len; i++) {
		re = new RegExp(search[i], "ig");
		str = str.replace(re, typeof replace == 'string' ? replace : (replace[i] ? replace[i] : replace[0]));
	}
	return str;
}
