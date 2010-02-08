<?php 
$platform = $_GET['platform'];
switch($platform){
	case 'discuz':
	include_once('common_discuz.php');
	break;
	case 'uchome':
		include_once('common_uchome.php');
		break;
}

$menu = array(
	array("name" => $lang['menu_doing'],"icon" =>"image/app/doing.gif","link" => "space.php?do=doing","shortcut" => true ),
	array("name" => $lang['menu_album'],"icon" =>"image/app/album.gif","link" => "space.php?do=album","shortcut" => true ),
	array("name" => $lang['menu_blog'],"icon" =>"image/app/blog.gif","link" => "space.php?do=blog","shortcut" => true ),
	array("name" => $lang['menu_thread'],"icon" =>"image/app/mtag.gif","link" => "space.php?do=thread","shortcut" => true ),
	array("name" => $lang['menu_share'],"icon" =>"image/app/share.gif","link" => "space.php?do=share","shortcut" => true )
);
if($_SCONFIG['my_status']) {
	if(is_array($_SGLOBAL['userapp'])) { 
		foreach($_SGLOBAL['userapp'] as $value) { 
			$menu[] = array("name" => $value['appname'],"icon" =>"http://appicon.manyou.com/icons/".$value['appid'],"link" => "userapp.php?id=".$value['appid'],"shortcut" => false );
		}
	}
}
header("Content-type: application/javascript");
$cookie = $_COOKIE['_webim'];
$is_mini = false;
if(!empty($cookie)){
        $cookie = stripslashes($cookie);
        $cookie=json_decode($cookie);
        if($cookie->m===true){
                $is_mini = true;
        }
}
ob_start();?>
<div>
            <div class="webim-preload">
                <div id="webim-notice-sound-c">
                </div>
    			<div id="webim-fsc-c">
                </div>
            </div>
<div id="webim" class="webim <?php
if(empty($_SCOOKIE['webim_logout']))
        echo 'webim-online';
else
        echo 'webim-offline';
if($is_mini)
	echo ' webim-mini';
?>">

            <div class="webim-sectionManage webim-c">
            	<div class="webim-bg"></div>
                <div class="webim-bar-l">
                     <div class="webim-section webim-menu-section">
                        <div class="webim-tab">
                            <div class="webim-titletip">
                                <strong>nextim</strong>
                            </div>
                            <div class="webim-tab-title">
                                <div class="webim-tab-inner">
                                    <div class="webim-count">
                                        0
                                    </div>
                                    <h4  id="webim-logo"><?php echo $lang['ucim_logo'] ?></h4>
                                </div>
                            </div>
                        </div>
                        <div class="webim-window">
                            <div class="webim-window-inner">
                                <div class="webim-window-actions">
                                    <a title="<?php echo $lang['minbox'] ?>" class="webim-min" href="#"><?php echo $lang['minbox'] ?></a>
                                </div>
                                <div class="webim-window-header">
                                    <h4 class="webim-window-title"><?php echo $lang['ucim_logo'] ?></h4>
                                </div>
                                <div class="webim-window-container webim-menu-container"> 
                                <ul>
                					<?php foreach($menu as $m){ ?>
                              		<li><a href="<?php echo $m["link"]?>"><img src="<?php echo $m["icon"]?>"/><span><?php echo $m["name"]?></span></a></li>
                              		<?php }?>
                                </ul>
                                <div class="webim-menu-empty">
                				</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php foreach($menu as $m){ 
                    if($m["shortcut"]){
                    ?>
                    <div class="webim-section webim-shortcut-section">
                        <div class="webim-tab">
                            <div class="webim-titletip">
                                <strong><?php echo $m["name"]?></strong>
                            </div>
                            <div class="webim-tab-title">
                                <div class="webim-tab-inner">
                                    <h4><a href="<?php echo $m["link"]?>"><img src="<?php echo $m["icon"]?>"/></a></h4>
                                </div>
                            </div>
                        </div>
                    </div>  
                    <?php 
                    }} ?>
                </div>
                <div class="webim-bar-r">
    		     <div class="webim-section">
                        <div class="webim-tabs-mini webim-tabs-collapse webim-icon" title="">
                            left
                        </div>
                    </div>
                    <div class="webim-section">
                        <div class="webim-tabs-mini webim-tabs-expand webim-icon" title="">
                            right
                        </div>
                    </div>

                    <div class="webim-section webim-buddy-section">
                        <div class="webim-tab">
                            <div class="webim-titletip">
                                <strong><?php echo $lang['buddy_notice'] ?></strong>
                            </div>
                            <div class="webim-tab-title">
                                <div class="webim-tab-inner">
                                    <h4>(<span>0</span>)<?php echo $lang['online_buddies'] ?></h4>
                                </div>
                            </div>
                        </div>
                        <div class="webim-window">
                            <div class="webim-window-inner">
                                <div class="webim-window-actions">
                                    <a title="<?php echo $lang['minbox'] ?>" class="webim-min" href="#"><?php echo $lang['minbox'] ?></a>
                                </div>
                                <div class="webim-window-header">
                                    <h4 class="webim-window-title"><?php echo $lang['online_buddies'] ?></h4>
                                </div>
                                <div class="webim-window-container webim-buddy-container">
                                    <div class="webim-buddy-search">
                                        <input type="text" title="<?php echo $lang['search_buddies'] ?>" value="<?php echo $lang['search_buddies'] ?>"/>
                                    </div>
                                    <div class="webim-buddy-list">
                                        <ul>
                                        </ul>
                                    </div>
                                    <div class="webim-buddy-empty">
                                       <?php echo $lang['no_buddies'] ?> 
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

<?php 
if(true) {
	$lang_search_group = $lang['search_groups'];
	$lang_no_joined = $lang['no_joined_group'];
	$lang_search_group2 = $lang['search_groups2'];
	print<<<EOF
                    <div class="webim-section webim-buddy-section  webim-group-section">
                        <div class="webim-tab">
                            
                            <div class="webim-tab-title">
                                <div class="webim-tab-inner">
                                    <h4>$lang[group]</h4>
                                </div>
                            </div>
                        </div>
                        <div class="webim-window">
                            <div class="webim-window-inner">
                                <div class="webim-window-actions">
                                    <a title="$lang[minbox]" class="webim-min" href="#">$lang[minbox]</a>
                                </div>
                                <div class="webim-window-header">
                                    <h4 class="webim-window-title">$lang[group]</h4>
                                </div>
                                <div class="webim-window-container webim-buddy-container">
                                    <div class="webim-buddy-search">
                                        <input type="text" title="$search_group" value="$search_group"/>
                                    </div>
                                    <div class="webim-buddy-list">
                                        <ul>
                                        </ul>
                                    </div>
                                    <div class="webim-buddy-empty">
                                        $lang_no_joined,<a href="network.php?ac=mtag&view=hot">$lang_search_group2</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
EOF;
}
?>
                     <div class="webim-section webim-notification-section">
                        <div class="webim-tab webim-tab-button">
                            <div class="webim-titletip">
                                <strong><?php echo $lang['notice'] ?></strong>
                            </div>
                            <div class="webim-tab-title">
                                <div class="webim-tab-inner">
                                    <div class="webim-count">
                                        0
                                    </div>
                                    <h4><?php echo $lang['notice'] ?></h4>
                                </div>
                            </div>
                        </div>
                        <div class="webim-window">
                            <div class="webim-window-inner">
                                <div class="webim-window-actions">
                                    <a title="<?php echo $lang['minbox'] ?>" class="webim-min" href="#"><?php echo $lang['minbox'] ?></a>
                                </div>
                                <div class="webim-window-header">
                                    <h4 class="webim-window-title"><?php echo $lang['notice'] ?></h4>
                                </div>
                                <div class="webim-window-container webim-notification-container"> 
                                <ul>
                                </ul>
                                <div class="webim-notification-empty">
									<a href="space.php?do=pm&filter=newpm"><?php echo $lang['notice_content'] ?></a>
                                </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="webim-section webim-config-section webim-section-last">
                        <div class="webim-tab webim-tab-button">
                            <div class="webim-titletip">
                                <strong><?php echo $lang['chat_setting'] ?></strong>
                            </div>
                            <div class="webim-tab-title">
                                <div class="webim-tab-inner">
                                    <div class="webim-count">
                                        0
                                    </div>
                                    <h4><?php echo $lang['chat_setting'] ?></h4>
                                </div>
                            </div>
                        </div>
                        <div class="webim-window">
                            <div class="webim-window-inner">
                                <div class="webim-window-actions">
                                    <a title="<?php echo $lang['minbox'] ?>" class="webim-min" href="#"><?php echo $lang['minbox'] ?></a>
                                </div>
                                <div class="webim-window-header">
                                    <h4 class="webim-window-title"><?php echo $lang['chat_setting'] ?></h4>
                                </div>
                                <div class="webim-window-container webim-config-container">
                                    <ul class="webim-setting">
                                        <form action="#" method="post">
                                            <li>
                                                <input type="checkbox" name="sound" id="webim-setting-sound"/>
                                                <label for="webim-setting-sound">
													<?php echo $lang['sound_set'] ?>
                                                </label>
                                            </li>
                                            <li>
                                                <input type="checkbox" name="sticky_buddylist" id="webim-setting-sticky-buddylist"/>
                                                <label for="webim-setting-sticky-buddylist">
													<?php echo $lang['buddywin_set'] ?>
                                                </label>
                                            </li>
                                                                                        <li>
                                                <input type="checkbox" name="auto_pop_msg" id="webim-setting-auto-pop-msg"/>
                                                <label for="webim-setting-auto-pop-msg">
													<?php echo $lang['autopop_set'] ?>
                                                </label>
                                            </li>
                                            <li style="display:none">
                                                <input type="checkbox" name="auto_login" id="webim-setting-auto-login"/>
                                                <label for="webim-setting-auto-login">
													<?php echo $lang['autologin'] ?>
                                                </label>
                                            </li>
                                        </form>
                                    </ul>
                                    <ul class="webim-status">
                                        <li class="webim-status-offline">
											<?php echo $lang['online_status'] ?>&nbsp;&nbsp;&nbsp;&nbsp;<a href="#"><?php echo $lang['will_leave'] ?></a>
                                        </li>
                                        <li class="webim-status-online">
                                            <?php echo $lang['offline_status'] ?>&nbsp;&nbsp;&nbsp;&nbsp;<a href="#"><?php echo $lang['will_login'] ?></a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="webim-bar-c">
                    <div class="webim-section">
                        <div class="webim-tabs-next">
                            <span class="webim-tabs-next-count">0</span>
                            <div class="webim-count">
                                0
                            </div>
                        </div>
                    </div>
                    <div class="webim-tabs-wrap">
                        <div class="webim-tabs">
                        </div>
                    </div>
                    <div class="webim-section">
                        <div class="webim-tabs-prev">
                            <span class="webim-tabs-prev-count">0</span>
                            <div class="webim-count">
                                0
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
<?php 
$template = ob_get_clean();
$template = preg_replace("/>\s+?</i", "><",$template);
$template = preg_replace("/\r\n\s*/i", "",$template);
$template = to_unicode(to_utf8($template));
?>
<?php 
$emot = array(
	"dir"=>urldecode($_GET['folder'])."static/images/emot/".$_IMC['emot']."/",
	"emots"=>array(
array("t"=>"smile","src"=>"smile.png","q"=>array(":)")), array("t"=>"smile_big","src"=>"smile-big.png","q"=>array(":d",":-d",":D",":-D")), array("t"=>"sad","src"=>"sad.png","q"=>array(":(",":-(")), array("t"=>"wink","src"=>"wink.png","q"=>array(";)",";-)")), array("t"=>"tongue","src"=>"tongue.png","q"=>array(":p",":-p",":P",":-P")), array("t"=>"shock","src"=>"shock.png","q"=>array("=-O","=-o")), array("t"=>"kiss","src"=>"kiss.png","q"=>array(":-*")), array("t"=>"glasses_cool","src"=>"glasses-cool.png","q"=>array("8-)")), array("t"=>"embarrassed","src"=>"embarrassed.png","q"=>array(":-[")), array("t"=>"crying","src"=>"crying.png","q"=>array(":'(")), array("t"=>"thinking","src"=>"thinking.png","q"=>array(":-/",":-\\")), array("t"=>"angel","src"=>"angel.png","q"=>array("O:-)","o:-)")), array("t"=>"shut_mouth","src"=>"shut-mouth.png","q"=>array(":-X",":-x")), array("t"=>"moneymouth","src"=>"moneymouth.png","q"=>array(":-$")), array("t"=>"foot_in_mouth","src"=>"foot-in-mouth.png","q"=>array(":-!")), array("t"=>"shout","src"=>"shout.png","q"=>array(">:o",">:O"))
));
$emot = to_unicode($emot);
?>
(function($){
   $ && $.noConflict();
   <?php if(empty($_SGLOBAL['supe_uid'])) echo "return;" ?>
   var folder = "<?php echo urldecode($_GET['folder']);?>";
   webim_css_link(folder + "static/css/theme_<?php echo $_IMC['theme'];?>.css");
   webim_js_include(folder + "static/js/i18n/webim-<?php echo $_IMC['lang'];?>.js");
			//init emot
         webim.emot.init(<?php echo $emot?>);
        $.date.init('<?php echo microtime(true)*1000 ?>');
        if ($.browser.msie&&$.browser.version<7) {
               document.write("<style>html{background:url(no) fixed;}.webim{position: absolute; bottom: auto;clear: both;top:expression(eval(document.compatMode &&document.compatMode=='CSS1Compat') ? documentElement.scrollTop+(documentElement.clientHeight-this.clientHeight) : document.body.scrollTop +(document.body.clientHeight-this.clientHeight));}</style>");
        }
        $(function(){
               var body = $(document.body);
               body.append($('<?php echo $template;?>'));
               var im = new webim('#webim', {
                  site:folder,
                  <?php echo empty($_SCOOKIE['webim_logout'])?'online:true,':'';?>
                  //channelId:tid?tid:0,
                 logable:<?php echo $_GET['logable']?'true':'false';?>
               });
                 
<?php if($_IMC["opacity"]&&$_IMC["opacity"]!=80){ echo "$('.webim-bg').css('opacity','".($_IMC["opacity"]/100)."');";}?>
$('.webim-buddy-section .webim-titletip').mouseover();
setTimeout(function(){$('.webim-buddy-section .webim-titletip').mouseout();},5000);
        });
})(jQuery);
