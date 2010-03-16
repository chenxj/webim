/*
	[Discuz!] (C)2001-2009 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: calendar.js 17449 2008-12-22 08:58:53Z cnteacher $
*/

var controlid = null;
var currdate = null;
var startdate = null;
var enddate  = null;
var yy = null;
var mm = null;
var hh = null;
var ii = null;
var currday = null;
var addtime = false;
var today = new Date();
var lastcheckedyear = false;
var lastcheckedmonth = false;

function getposition(obj) {
	var r = new Array();
	r['x'] = obj.offsetLeft;
	r['y'] = obj.offsetTop;
	while(obj = obj.offsetParent) {
		r['x'] += obj.offsetLeft;
		r['y'] += obj.offsetTop;
	}
	return r;
}

function loadcalendar() {
	s = '';
	s += '<div id="calendar" style="display:none; position:absolute; z-index:100000;" onclick="doane(event)">';
	s += '<div style="width: 210px;"><table cellspacing="0" cellpadding="0" width="100%" style="text-align: center;">';
	s += '<tr align="center" id="calendar_week"><td><a href="###" onclick="refreshcalendar(yy, mm-1)" title="上一月">《</a></td><td colspan="5" style="text-align: center"><a href="###" onclick="showdiv(\'year\');doane(event)" class="dropmenu" title="点击选择年份" id="year"></a>&nbsp; - &nbsp;<a id="month" class="dropmenu" title="点击选择月份" href="###" onclick="showdiv(\'month\');doane(event)"></a></td><td><A href="###" onclick="refreshcalendar(yy, mm+1)" title="下一月">》</A></td></tr>';
	s += '<tr id="calendar_header"><td>日</td><td>一</td><td>二</td><td>三</td><td>四</td><td>五</td><td>六</td></tr>';
	for(var i = 0; i < 6; i++) {
		s += '<tr>';
		for(var j = 1; j <= 7; j++)
			s += "<td id=d" + (i * 7 + j) + " height=\"19\">0</td>";
		s += "</tr>";
	}
	s += '<tr id="hourminute"><td colspan="7" align="center"><input type="text" size="2" value="" id="hour" class="txt" onKeyUp=\'this.value=this.value > 23 ? 23 : zerofill(this.value);controlid.value=controlid.value.replace(/\\d+(\:\\d+)/ig, this.value+"$1")\'> 点 <input type="text" size="2" value="" id="minute" class="txt" onKeyUp=\'this.value=this.value > 59 ? 59 : zerofill(this.value);controlid.value=controlid.value.replace(/(\\d+\:)\\d+/ig, "$1"+this.value)\'> 分</td></tr>';
	s += '</table></div></div>';
	s += '<div id="calendar_year" onclick="doane(event)" style="display: none;z-index:100001;"><div class="col">';
	for(var k = 2020; k >= 1931; k--) {
		s += k != 2020 && k % 10 == 0 ? '</div><div class="col">' : '';
		s += '<a href="###" onclick="refreshcalendar(' + k + ', mm);$(\'calendar_year\').style.display=\'none\'"><span' + (today.getFullYear() == k ? ' class="calendar_today"' : '') + ' id="calendar_year_' + k + '">' + k + '</span></a><br />';
	}
	s += '</div></div>';
	s += '<div id="calendar_month" onclick="doane(event)" style="display: none;z-index:100001;">';
	for(var k = 1; k <= 12; k++) {
		s += '<a href="###" onclick="refreshcalendar(yy, ' + (k - 1) + ');$(\'calendar_month\').style.display=\'none\'"><span' + (today.getMonth()+1 == k ? ' class="calendar_today"' : '') + ' id="calendar_month_' + k + '">' + k + ( k < 10 ? '&nbsp;' : '') + ' 月</span></a><br />';
	}
	s += '</div>';
	if(is_ie && is_ie < 7) {
		s += '<iframe id="calendariframe" frameborder="0" style="display:none;position:absolute;filter:progid:DXImageTransform.Microsoft.Alpha(style=0,opacity=0)"></iframe>';
		s += '<iframe id="calendariframe_year" frameborder="0" style="display:none;position:absolute;filter:progid:DXImageTransform.Microsoft.Alpha(style=0,opacity=0)"></iframe>';
		s += '<iframe id="calendariframe_month" frameborder="0" style="display:none;position:absolute;filter:progid:DXImageTransform.Microsoft.Alpha(style=0,opacity=0)"></iframe>';
	}

	var div = document.createElement('div');
	div.innerHTML = s;
	$('append_parent').appendChild(div);
	document.onclick = function(event) {
		$('calendar').style.display = 'none';
		$('calendar_year').style.display = 'none';
		$('calendar_month').style.display = 'none';
		if(is_ie && is_ie < 7) {
			$('calendariframe').style.display = 'none';
			$('calendariframe_year').style.display = 'none';
			$('calendariframe_month').style.display = 'none';
		}
	}
	$('calendar').onclick = function(event) {
		doane(event);
		$('calendar_year').style.display = 'none';
		$('calendar_month').style.display = 'none';
		if(is_ie && is_ie < 7) {
			$('calendariframe_year').style.display = 'none';
			$('calendariframe_month').style.display = 'none';
		}
	}

}

function parsedate(s) {
	/(\d+)\-(\d+)\-(\d+)\s*(\d*):?(\d*)/.exec(s);
	var m1 = (RegExp.$1 && RegExp.$1 > 1899 && RegExp.$1 < 2101) ? parseFloat(RegExp.$1) : today.getFullYear();
	var m2 = (RegExp.$2 && (RegExp.$2 > 0 && RegExp.$2 < 13)) ? parseFloat(RegExp.$2) : today.getMonth() + 1;
	var m3 = (RegExp.$3 && (RegExp.$3 > 0 && RegExp.$3 < 32)) ? parseFloat(RegExp.$3) : today.getDate();
	var m4 = (RegExp.$4 && (RegExp.$4 > -1 && RegExp.$4 < 24)) ? parseFloat(RegExp.$4) : 0;
	var m5 = (RegExp.$5 && (RegExp.$5 > -1 && RegExp.$5 < 60)) ? parseFloat(RegExp.$5) : 0;
	/(\d+)\-(\d+)\-(\d+)\s*(\d*):?(\d*)/.exec("0000-00-00 00\:00");
	return new Date(m1, m2 - 1, m3, m4, m5);
}

function settime(d) {
	$('calendar').style.display = 'none';
	$('calendar_month').style.display = 'none';
	if(is_ie && is_ie < 7) {
		$('calendariframe').style.display = 'none';
	}
	controlid.value = yy + "-" + zerofill(mm + 1) + "-" + zerofill(d) + (addtime ? ' ' + zerofill($('hour').value) + ':' + zerofill($('minute').value) : '');
}

function showcalendar(event, controlid1, addtime1, startdate1, enddate1) {
	controlid = controlid1;
	addtime = addtime1;
	startdate = startdate1 ? parsedate(startdate1) : false;
	enddate = enddate1 ? parsedate(enddate1) : false;
	currday = controlid.value ? parsedate(controlid.value) : today;
	hh = currday.getHours();
	ii = currday.getMinutes();
	var p = getposition(controlid);
	$('calendar').style.display = 'block';
	$('calendar').style.left = p['x']+'px';
	$('calendar').style.top	= (p['y'] + 20)+'px';
	doane(event);
	refreshcalendar(currday.getFullYear(), currday.getMonth());
	if(lastcheckedyear != false) {
		$('calendar_year_' + lastcheckedyear).className = 'calendar_default';
		$('calendar_year_' + today.getFullYear()).className = 'calendar_today';
	}
	if(lastcheckedmonth != false) {
		$('calendar_month_' + lastcheckedmonth).className = 'calendar_default';
		$('calendar_month_' + (today.getMonth() + 1)).className = 'calendar_today';
	}
	$('calendar_year_' + currday.getFullYear()).className = 'calendar_checked';
	$('calendar_month_' + (currday.getMonth() + 1)).className = 'calendar_checked';
	$('hourminute').style.display = addtime ? '' : 'none';
	lastcheckedyear = currday.getFullYear();
	lastcheckedmonth = currday.getMonth() + 1;
	if(is_ie && is_ie < 7) {
		$('calendariframe').style.top = $('calendar').style.top;
		$('calendariframe').style.left = $('calendar').style.left;
		$('calendariframe').style.width = $('calendar').offsetWidth;
		$('calendariframe').style.height = $('calendar').offsetHeight;
		$('calendariframe').style.display = 'block';
	}
}

function refreshcalendar(y, m) {
	var x = new Date(y, m, 1);
	var mv = x.getDay();
	var d = x.getDate();
	var dd = null;
	yy = x.getFullYear();
	mm = x.getMonth();
	$("year").innerHTML = yy;
	$("month").innerHTML = mm + 1 > 9  ? (mm + 1) : '0' + (mm + 1);

	for(var i = 1; i <= mv; i++) {
		dd = $("d" + i);
		dd.innerHTML = "&nbsp;";
		dd.className = "";
	}

	while(x.getMonth() == mm) {
		dd = $("d" + (d + mv));
		dd.innerHTML = '<a href="###" onclick="settime(' + d + ');return false">' + d + '</a>';
		if((enddate && x.getTime() > enddate.getTime()) || (startdate && x.getTime() < startdate.getTime())) {
			dd.className = 'calendar_expire';
		} else {
			dd.className = 'calendar_default';
		}
		if(x.getFullYear() == today.getFullYear() && x.getMonth() == today.getMonth() && x.getDate() == today.getDate()) {
			dd.className = 'calendar_today';
			dd.firstChild.title = '今天';
		}
		if(x.getFullYear() == currday.getFullYear() && x.getMonth() == currday.getMonth() && x.getDate() == currday.getDate()) {
			dd.className = 'calendar_checked';
		}
		x.setDate(++d);
	}

	while(d + mv <= 42) {
		dd = $("d" + (d + mv));
		dd.innerHTML = "&nbsp;";
		d++;
	}

	if(addtime) {
		$('hour').value = zerofill(hh);
		$('minute').value = zerofill(ii);
	}
}

function showdiv(id) {
	var p = getposition($(id));
	$('calendar_' + id).style.left = p['x']+'px';
	$('calendar_' + id).style.top = (p['y'] + 16)+'px';
	$('calendar_' + id).style.display = 'block';
	if(is_ie && is_ie < 7) {
		$('calendariframe_' + id).style.top = $('calendar_' + id).style.top;
		$('calendariframe_' + id).style.left = $('calendar_' + id).style.left;
		$('calendariframe_' + id).style.width = $('calendar_' + id).offsetWidth;
		$('calendariframe_' + id ).style.height = $('calendar_' + id).offsetHeight;
		$('calendariframe_' + id).style.display = 'block';
	}
}

function zerofill(s) {
	var s = parseFloat(s.toString().replace(/(^[\s0]+)|(\s+$)/g, ''));
	s = isNaN(s) ? 0 : s;
	return (s < 10 ? '0' : '') + s.toString();
}

/*  加载样式  */
var calendarstyle = "#calendar{padding:5px;text-align:left;border:1px solid #7FCAE2;background:#FFF;margin-bottom:0.8em;}#calendar td{padding:2px;font-weight:bold;}#calendar_week td{height:2em;line-height:2em;border-bottom:1px solid #E3EDF5;}#hourminute td{padding:4px 2px;border-top:1px solid #E3EDF5;}.calendar_expire,.calendar_expire a:link,.calendar_expire a:visited{color:#666;font-weight:normal;}.calendar_default,.calendar_default a:link,.calendar_default a:visited{color:#09C;}.calendar_checked,.calendar_checked a:link,.calendar_checked a:visited{color:#F60;font-weight:bold;}td.calendar_checked,span.calendar_checked{background:#E3EDF5;}.calendar_today,.calendar_today a:link,.calendar_today a:visited{color:#444;font-weight:bold;}#calendar_header td{width:30px;height:20px;border-bottom:1px solid #E3EDF5;font-weight:normal;}#calendar_year{display:none;line-height:130%;background:#FFF;position:absolute;z-index:10;}#calendar_year .col{float:left;background:#FFF;margin-left:1px;border:1px solid #E3EDF5;padding:4px;}#calendar_month{display:none;background:#FFF;line-height:130%;border:1px solid #DDD;padding:4px;position:absolute;z-index:11;}";
if(is_ie){// IE
	var el = document.createStyleSheet();
	el.cssText = calendarstyle;
} else {
	var el = document.createElement("style");
	el.type = "text/css";
	el.appendChild(document.createTextNode(calendarstyle));
	document.getElementsByTagName("head")[0].appendChild(el);
}

loadcalendar();
