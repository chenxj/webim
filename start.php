<?php
include("config.php");
$content = <<<ZZZ
<link href="webim/static/webim.min.css" media="all"  type="text/css" rel="stylesheet"/>
<link href="webim/static/themes/{$_IMC['theme']}/ui.theme.css" media="all" type="text/css" rel="stylesheet"/>
<script src="webim/static/webim_discuz.all.js" type="text/javascript"></script>
<script src="webim/static/i18n/webim-{$_IMC['local']}.js" type="text/javascript"></script>
<script src="webim/custom.js.php?platform=discuz" type="text/javascript"></script>
ZZZ;
unset($_IMC);
echo $content;
?>
