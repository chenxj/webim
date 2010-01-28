<?php
function webim_template($template){
        return $template;
        $head = file_get_content("template/webim.htm");
        $template = preg_replace("/\<body[^\>]*\>/i","<body>".$head, $template);
}
//在"//解析广告"上一行加入
//include_once(S_ROOT.'./webim/webim_template.php'); //嵌入webim
//$template = webim_template($template);
?>
