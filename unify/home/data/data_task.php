<?php
if(!defined('IN_UCHOME')) exit('Access Denied');
$_SGLOBAL['task']=Array
	(
	2 => Array
		(
		'taskid' => 2,
		'available' => 1,
		'name' => '将个人资料补充完整',
		'note' => '把自己的个人资料填写完整吧。<br>这样您会被更多的朋友找到的，系统也会帮您找到朋友。',
		'num' => '0',
		'maxnum' => '0',
		'image' => 'image/task/profile.gif',
		'filename' => 'profile.php',
		'starttime' => '0',
		'endtime' => '0',
		'nexttime' => '0',
		'nexttype' => 2,
		'credit' => 20,
		'displayorder' => '0'
		),
	1 => Array
		(
		'taskid' => 1,
		'available' => 1,
		'name' => '更新一下自己的头像',
		'note' => '头像就是你在这里的个人形象。<br>设置自己的头像后，会让更多的朋友记住您。',
		'num' => '0',
		'maxnum' => '0',
		'image' => 'image/task/avatar.gif',
		'filename' => 'avatar.php',
		'starttime' => '0',
		'endtime' => '0',
		'nexttime' => '0',
		'nexttype' => '',
		'credit' => 20,
		'displayorder' => 1
		),
	3 => Array
		(
		'taskid' => 3,
		'available' => 1,
		'name' => '发表自己的第一篇日志',
		'note' => '现在，就写下自己的第一篇日志吧。<br>与大家一起分享自己的生活感悟。',
		'num' => '0',
		'maxnum' => '0',
		'image' => 'image/task/blog.gif',
		'filename' => 'blog.php',
		'starttime' => '0',
		'endtime' => '0',
		'nexttime' => '0',
		'nexttype' => '',
		'credit' => 5,
		'displayorder' => 3
		),
	4 => Array
		(
		'taskid' => 4,
		'available' => 1,
		'name' => '寻找并添加五位好友',
		'note' => '有了好友，您发的日志、图片等会被好友及时看到并传播出去；<br>您也会在首页方便及时的看到好友的最新动态。',
		'num' => '0',
		'maxnum' => '0',
		'image' => 'image/task/friend.gif',
		'filename' => 'friend.php',
		'starttime' => '0',
		'endtime' => '0',
		'nexttime' => '0',
		'nexttype' => '',
		'credit' => 50,
		'displayorder' => 4
		),
	5 => Array
		(
		'taskid' => 5,
		'available' => 1,
		'name' => '验证激活自己的邮箱',
		'note' => '填写自己真实的邮箱地址并验证通过。<br>您可以在忘记密码的时候使用该邮箱取回自己的密码；<br>还可以及时接受站内的好友通知等等。',
		'num' => '0',
		'maxnum' => '0',
		'image' => 'image/task/email.gif',
		'filename' => 'email.php',
		'starttime' => '0',
		'endtime' => '0',
		'nexttime' => '0',
		'nexttype' => '',
		'credit' => 10,
		'displayorder' => 5
		),
	6 => Array
		(
		'taskid' => 6,
		'available' => 1,
		'name' => '邀请10个新朋友加入',
		'note' => '邀请一下自己的QQ好友或者邮箱联系人，让亲朋好友一起来加入我们吧。',
		'num' => '0',
		'maxnum' => '0',
		'image' => 'image/task/friend.gif',
		'filename' => 'invite.php',
		'starttime' => '0',
		'endtime' => '0',
		'nexttime' => '0',
		'nexttype' => '',
		'credit' => 100,
		'displayorder' => 6
		),
	7 => Array
		(
		'taskid' => 7,
		'available' => 1,
		'name' => '领取每日访问大礼包',
		'note' => '每天登录访问自己的主页，就可领取大礼包。',
		'num' => '0',
		'maxnum' => '0',
		'image' => 'image/task/gift.gif',
		'filename' => 'gift.php',
		'starttime' => '0',
		'endtime' => '0',
		'nexttime' => '0',
		'nexttype' => 'day',
		'credit' => 5,
		'displayorder' => 99
		)
	)
?>