<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title>webim - test</title>
<script type="text/javascript">function webim_css_link(href){
        document.write('<link href="' + href + '" media="all" type="text/css" rel="stylesheet"/>');
}
function webim_js_include(src){
        document.write('<scr'+'ipt type="text/javascript" src="'+ src +'"></scr'+'ipt>');
}
(function(){
        var folder = "";
        webim_css_link(folder + "static/css/webim.css");
        webim_js_include(folder + "static/js/jquery.min.js");
        webim_js_include(folder + "static/js/swfobject.js");
        webim_js_include(folder + "static/js/core.js");
        webim_js_include(folder + "static/js/webim.js");
        webim_js_include(folder + "theme.js.php?logable=true&folder="+encodeURIComponent(folder));
})();
</script>
</head>
<body>
  <div id = "webim-log"></div>
</body>
</html>

