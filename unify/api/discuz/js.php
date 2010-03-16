<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: js.php 12965 2009-07-31 02:26:24Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$_SGLOBAL['supe_uid'] = $uid = isset($_GET['uid'])?intval($_GET['uid']):0;
if(empty($uid)) exit;

if($uid) {

	@include_once(S_ROOT.'./data/data_app.php');
	@include_once(S_ROOT.'./data/data_userapp.php');
	$status = isset($_GET['status']) ? intval($_GET['status']) : 0;
	
	//解码相应的权限
	$avatar = $status & 1 ? 1 : 0;
	$viewpro = $status & 2 ? 1 : 0;
	$ad = $status & 4 ? 1 : 0;
	$side = $status & 8 ? 1 : 0;
	
	$infosidestatus = isset($_GET['infosidestatus']) ? intval($_GET['infosidestatus']) : 0;
	
	print <<<END
	
function setHTML(id, val) {
	var idObj = $(id);
	if(idObj != null) {
		idObj.innerHTML = val;
	}
}

END;
	
	//取侧边栏
	if(in_array($_GET['pagetype'], array('index', 'forumdisplay', 'viewthread')) && $side && $infosidestatus == 2) {
		$spacestr = '<div class="sidebox"><h4>竞价排行</h4><ul class="avt_list avt_uname">';
		$query = $_SGLOBAL['db']->query("SELECT f.sex, main.* FROM ".tname('show')." main LEFT JOIN ".tname('spacefield')." f ON f.uid=main.uid	WHERE 1	ORDER BY main.credit DESC LIMIT 0,12");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$spacestr .= "<li><a href=\"{$siteurl}space.php?uid=$value[uid]\" target=\"_blank\">".avatar($value['uid'])."</a><p><a href=\"{$siteurl}space.php?uid=$value[uid]\" target=\"_blank\">$value[username]</a></p></li>";
		}
		$spacestr .= '</ul></div>';
		
		echo "setHTML('sidefeed', '".makeurl($spacestr)."');";
		
	}
	
	
	if($_GET['pagetype'] == 'viewthread') {
		
		$num = intval($_GET['feedpostnum']);
		$listnum = 0;
		//取贴间feed
		if($ad && $num) {
			$cachefile = S_ROOT.'./data/data_feedcache.php';
			
			$writestate = false;
			if(file_exists($cachefile)) {
				@$mktime = filemtime($cachefile);
				if($_SGLOBAL['timestamp'] - $mktime > 300) {
					$writestate = true;
				} else {
					include_once($cachefile);
				}
			} else {
				$writestate = true;
			}
			
			$listnum = isset($feed_list) ? count($feed_list) : 0;
			
			if($writestate || !$listnum) {
				$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('feed')." WHERE friend='0' ORDER BY dateline DESC LIMIT 0,$num");
				while($value = $_SGLOBAL['db']->fetch_array($query)) {
					if(ckfriend($value['uid'], $value['friend'], $value['target_ids'])) {
						realname_set($value['uid'], $value['username']);
						$value = mkfeed($value);
						$feed_list[] = makeurl($value['title_template']);
					}
				}
				include_once(S_ROOT.'./source/function_cache.php');
				cache_write('feedcache', "feed_list", $feed_list);
			}
			
			$feedstr = '"'.implode('","', $feed_list).'"';
			print <<<END
var feedArr = new Array($feedstr);
for(i=0; i<$listnum; i++) {
    var adObj = $('ad_thread1_'+i);
    if(adObj != null && adObj.innerHTML == "") {
		setHTML('ad_thread1_'+i, feedArr[i]);
	}
}
END;
		}

	}

	if(($_GET['pagetype'] == 'profile' && $viewpro) || ($_GET['pagetype'] == 'viewthread')) {
		
		$num = $_GET['pagetype'] == 'profile' ? 10 : 3;
		
		$updatetime = 0;
		$updateuid = isset($_GET['updateuid']) ? intval($_GET['updateuid']) : 0;
		
		if($updateuid) {
			$havefeed = false;
			$avatarfeedstr = '<ul>';
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('feed')." WHERE uid='$updateuid' AND friend='0' ORDER BY dateline DESC LIMIT 0,$num");
			while($value = $_SGLOBAL['db']->fetch_array($query)) {
				if(ckfriend($value['uid'], $value['friend'], $value['target_ids'])) {
					if(!$updatetime) {
						$updatetime = $_SGLOBAL['timestamp'] - $value['dateline'];
					}
					$havefeed = true;
					realname_set($value['uid'], $value['username']);
					$value = mkfeed($value);
					if(!$value['appid']) {
						$src = "http://appicon.manyou.com/icons/$value[icon]";
					} else {
						$src = $siteurl.'image/icon/'.$value['icon'].'.gif';
					}
					
					$avatarfeedstr .= '<li><img class="appicon" src="'.$src.'" /> '.makeurl($value['title_template']).'</li>';
				}
			}
			$avatarfeedstr .= '</ul>';
			if(!$havefeed)	$avatarfeedstr = '';
			if($_GET['pagetype'] == 'profile' && $viewpro) {
				
				//统计相应的数值
				$albumnum = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('album')." WHERE uid='$updateuid'"), 0);
				$doingnum = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('doing')." WHERE uid='$updateuid'"), 0);
				$blognum = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('blog')." WHERE uid='$updateuid'"), 0);
				$threadnum = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('thread')." WHERE uid='$updateuid'"), 0);
				$tagspacenum = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('tagspace')." WHERE uid='$updateuid'"), 0);
				$contstr = '';
				
				if($albumnum)	$contstr = '<li><img src="'.$siteurl.'image/icon/album.gif"><a href="'.$siteurl.'space.php?uid='.$updateuid.'&do=album&view=me" target="_blank">'.$albumnum.'个相册</a></li>';
				if($doingnum)	$contstr .= '<li><img src="'.$siteurl.'image/icon/doing.gif"> <a href="'.$siteurl.'space.php?uid='.$updateuid.'&do=doing&view=me" target="_blank">'.$doingnum.'条记录</a></li>';
				if($blognum)	$contstr .= '<li><img src="'.$siteurl.'image/icon/blog.gif"> <a href="'.$siteurl.'space.php?uid='.$updateuid.'&do=blog&view=me" target="_blank">'.$blognum.'篇日志</a></li>';
				if($threadnum)	$contstr .= '<li><img src="'.$siteurl.'image/icon/thread.gif"> <a href="'.$siteurl.'space.php?uid='.$updateuid.'&do=thread&view=me" target="_blank">'.$threadnum.'个话题</a></li>';
				if($tagspacenum)	$contstr .= '<li><img src="'.$siteurl.'image/icon/mtag.gif"> <a href="'.$siteurl.'space.php?do=mtag&uid='.$updateuid.'" target="_blank">'.$tagspacenum.'个群组</a></li>';
				
				if($contstr) {
					if(!empty($_GET['plugin'])) {
						print <<<END
$('profile_stats').innerHTML = '$contstr';
END;
					} else {
					print <<<END
var sideObj = $('profile_act');
var oUl = document.createElement("ul");
oUl.id = 'profile_stats';
sideObj.parentNode.insertBefore(oUl, sideObj);
oUl.innerHTML = '$contstr';

END;
					}
				}
				
				if($avatarfeedstr) {
					if(!empty($_GET['plugin'])) {
						print <<<END
$('uch_feed').innerHTML = '$avatarfeedstr';
$('baseprofile').style.width = '48%';
END;
					} else {
					print <<<END
var profileObj = $('profilecontent');
var baseProfile = $('baseprofile');

var feedDiv = document.createElement("div");
feedDiv.id = 'uch_feed';
feedDiv.className = 'commonlist';
feedDiv.innerHTML = '$avatarfeedstr';
$('baseprofile').style.width = '48%';
profileObj.insertBefore(feedDiv, $('baseprofile'));
END;
					}
				}
			} else {
			
				$pid = isset($_GET['pid']) ? intval($_GET['pid']) : 0;
				if($pid && $avatarfeedstr && $updatetime < 86400) {
					//验证用户是否有更新
					$prelength = strlen($_SC['cookiepre']);
					foreach($_COOKIE as $key => $val) {
						if(substr($key, 0, $prelength) == $_SC['cookiepre']) {
							$_SCOOKIE[(substr($key, $prelength))] = empty($magic_quote) ? saddslashes($val) : $val;
						}
					}
						
					if(!isset($_SCOOKIE['viewuserid']) || !in_array($updateuid, explode(',', $_SCOOKIE['viewuserid']))) {
						$_SCOOKIE['viewuserid'] = empty($_SCOOKIE['viewuserid']) ? $updateuid : $_SCOOKIE['viewuserid'].",$updateuid";
						ssetcookie('viewuserid', $_SCOOKIE['viewuserid'], $_SGLOBAL['timestamp']+43200);

						if(!empty($_GET['plugin'])) {
							print <<<END
$('authorfeed').innerHTML = '<span id="authornewfeed" onmouseover="showMenu(this.id);">有新动态</span>';
$('authornewfeed_menu').innerHTML = '<div>$avatarfeedstr</div>';
END;
						} else {
						print <<<END
var authorPostonObj = $('authorposton$pid');
if(authorPostonObj != null && typeof authorPostonObj == 'object') {
	var oSpan = document.createElement("span");
	oSpan.id = "authornewfeed"
	oSpan.innerHTML = "有新动态";
	oSpan.onmouseover = function(){showMenu(this.id);}
	authorPostonObj.parentNode.insertBefore(oSpan, authorPostonObj);
}

var userInfo = $('userinfo$pid');
var feedDiv = document.createElement("div");
var ulDiv  = document.createElement("div");
feedDiv.appendChild(ulDiv);
feedDiv.id = "authornewfeed_menu";
feedDiv.style.display = "none";
ulDiv.innerHTML = '$avatarfeedstr';
userInfo.parentNode.insertBefore(feedDiv, userInfo);
END;
						}
					}
				}
			}
		}
	}
	
	//获取通知
	$space = getspace($uid);
	$notenum = intval($space['notenum']);
	
	if($notenum) {
		print<<<END
//输出新通知标签
var noteObj = $('pm_ntc');
var oSpan=document.createElement("span");
oSpan.id="uchome_ntc";
noteObj.parentNode.insertBefore(oSpan, noteObj);
oSpan.innerHTML = '<a href="{$siteurl}space.php?do=notice" target="_blank">新通知<span>($notenum)</span></a>';
	
END;
	}
}
?>