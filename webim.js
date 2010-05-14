function start_nextim()
{  
    webim_template = '<link href="webim/static/webim.min.css" media="all" type="text/css" rel="stylesheet"/>';
    webim_template += '<link href="webim/static/themes/flick/ui.theme.css" media="all" type="text/css" rel="stylesheet"/>';
    webim_template += '<script src="webim/static/webim_uchome.all.min.js" type="text/javascript"></script>';
    webim_template += '<script src="webim/static/i18n/webim_zh-CN.js" type="text/javascript"></script>';
    webim_template += '<script src="webim/custom.js.php?platform=uchome" type="text/javascript"></script>';
    document.write(webim_template);
}
start_nextim();
