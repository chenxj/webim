<?php
if(!defined('IN_UCHOME')) exit('Access Denied');
$_SGLOBAL['creditrule']=Array
	(
	'register' => Array
		(
		'rid' => 1,
		'rulename' => '开通空间',
		'action' => 'register',
		'cycletype' => '0',
		'cycletime' => '0',
		'rewardnum' => 1,
		'rewardtype' => 1,
		'norepeat' => '0',
		'credit' => 10,
		'experience' => '0'
		),
	'realname' => Array
		(
		'rid' => 2,
		'rulename' => '实名认证',
		'action' => 'realname',
		'cycletype' => '0',
		'cycletime' => '0',
		'rewardnum' => 1,
		'rewardtype' => 1,
		'norepeat' => '0',
		'credit' => 20,
		'experience' => 20
		),
	'realemail' => Array
		(
		'rid' => 3,
		'rulename' => '邮箱认证',
		'action' => 'realemail',
		'cycletype' => '0',
		'cycletime' => '0',
		'rewardnum' => 1,
		'rewardtype' => 1,
		'norepeat' => '0',
		'credit' => 40,
		'experience' => 40
		),
	'invitefriend' => Array
		(
		'rid' => 4,
		'rulename' => '成功邀请好友',
		'action' => 'invitefriend',
		'cycletype' => 4,
		'cycletime' => '0',
		'rewardnum' => 20,
		'rewardtype' => 1,
		'norepeat' => '0',
		'credit' => 10,
		'experience' => 10
		),
	'setavatar' => Array
		(
		'rid' => 5,
		'rulename' => '设置头像',
		'action' => 'setavatar',
		'cycletype' => '0',
		'cycletime' => '0',
		'rewardnum' => 1,
		'rewardtype' => 1,
		'norepeat' => '0',
		'credit' => 15,
		'experience' => 15
		),
	'videophoto' => Array
		(
		'rid' => 6,
		'rulename' => '视频认证',
		'action' => 'videophoto',
		'cycletype' => '0',
		'cycletime' => '0',
		'rewardnum' => 1,
		'rewardtype' => 1,
		'norepeat' => '0',
		'credit' => 40,
		'experience' => 40
		),
	'report' => Array
		(
		'rid' => 7,
		'rulename' => '成功举报',
		'action' => 'report',
		'cycletype' => 4,
		'cycletime' => '0',
		'rewardnum' => '0',
		'rewardtype' => 1,
		'norepeat' => '0',
		'credit' => 2,
		'experience' => 2
		),
	'updatemood' => Array
		(
		'rid' => 8,
		'rulename' => '更新心情',
		'action' => 'updatemood',
		'cycletype' => 1,
		'cycletime' => '0',
		'rewardnum' => 3,
		'rewardtype' => 1,
		'norepeat' => '0',
		'credit' => 3,
		'experience' => 3
		),
	'hotinfo' => Array
		(
		'rid' => 9,
		'rulename' => '热点信息',
		'action' => 'hotinfo',
		'cycletype' => 4,
		'cycletime' => '0',
		'rewardnum' => '0',
		'rewardtype' => 1,
		'norepeat' => '0',
		'credit' => 10,
		'experience' => 10
		),
	'daylogin' => Array
		(
		'rid' => 10,
		'rulename' => '每天登陆',
		'action' => 'daylogin',
		'cycletype' => 1,
		'cycletime' => '0',
		'rewardnum' => 1,
		'rewardtype' => 1,
		'norepeat' => '0',
		'credit' => 15,
		'experience' => 15
		),
	'visit' => Array
		(
		'rid' => 11,
		'rulename' => '访问别人空间',
		'action' => 'visit',
		'cycletype' => 1,
		'cycletime' => '0',
		'rewardnum' => 10,
		'rewardtype' => 1,
		'norepeat' => 2,
		'credit' => 1,
		'experience' => 1
		),
	'poke' => Array
		(
		'rid' => 12,
		'rulename' => '打招呼',
		'action' => 'poke',
		'cycletype' => 1,
		'cycletime' => '0',
		'rewardnum' => 10,
		'rewardtype' => 1,
		'norepeat' => 2,
		'credit' => 1,
		'experience' => 1
		),
	'guestbook' => Array
		(
		'rid' => 13,
		'rulename' => '留言',
		'action' => 'guestbook',
		'cycletype' => 1,
		'cycletime' => '0',
		'rewardnum' => 20,
		'rewardtype' => 1,
		'norepeat' => 2,
		'credit' => 2,
		'experience' => 2
		),
	'getguestbook' => Array
		(
		'rid' => 14,
		'rulename' => '被留言',
		'action' => 'getguestbook',
		'cycletype' => 1,
		'cycletime' => '0',
		'rewardnum' => 5,
		'rewardtype' => 1,
		'norepeat' => 2,
		'credit' => 1,
		'experience' => '0'
		),
	'doing' => Array
		(
		'rid' => 15,
		'rulename' => '发表记录',
		'action' => 'doing',
		'cycletype' => 1,
		'cycletime' => '0',
		'rewardnum' => 5,
		'rewardtype' => 1,
		'norepeat' => '0',
		'credit' => 1,
		'experience' => 1
		),
	'publishblog' => Array
		(
		'rid' => 16,
		'rulename' => '发表日志',
		'action' => 'publishblog',
		'cycletype' => 1,
		'cycletime' => '0',
		'rewardnum' => 3,
		'rewardtype' => 1,
		'norepeat' => '0',
		'credit' => 5,
		'experience' => 5
		),
	'uploadimage' => Array
		(
		'rid' => 17,
		'rulename' => '上传图片',
		'action' => 'uploadimage',
		'cycletype' => 1,
		'cycletime' => '0',
		'rewardnum' => 10,
		'rewardtype' => 1,
		'norepeat' => '0',
		'credit' => 2,
		'experience' => 2
		),
	'camera' => Array
		(
		'rid' => 18,
		'rulename' => '拍大头贴',
		'action' => 'camera',
		'cycletype' => 1,
		'cycletime' => '0',
		'rewardnum' => 5,
		'rewardtype' => 1,
		'norepeat' => '0',
		'credit' => 3,
		'experience' => 3
		),
	'publishthread' => Array
		(
		'rid' => 19,
		'rulename' => '发表话题',
		'action' => 'publishthread',
		'cycletype' => 1,
		'cycletime' => '0',
		'rewardnum' => 5,
		'rewardtype' => 1,
		'norepeat' => '0',
		'credit' => 5,
		'experience' => 5
		),
	'replythread' => Array
		(
		'rid' => 20,
		'rulename' => '回复话题',
		'action' => 'replythread',
		'cycletype' => 1,
		'cycletime' => '0',
		'rewardnum' => 10,
		'rewardtype' => 1,
		'norepeat' => 1,
		'credit' => 1,
		'experience' => 1
		),
	'createpoll' => Array
		(
		'rid' => 21,
		'rulename' => '创建投票',
		'action' => 'createpoll',
		'cycletype' => 1,
		'cycletime' => '0',
		'rewardnum' => 5,
		'rewardtype' => 1,
		'norepeat' => '0',
		'credit' => 2,
		'experience' => 2
		),
	'joinpoll' => Array
		(
		'rid' => 22,
		'rulename' => '参与投票',
		'action' => 'joinpoll',
		'cycletype' => 1,
		'cycletime' => '0',
		'rewardnum' => 10,
		'rewardtype' => 1,
		'norepeat' => 1,
		'credit' => 1,
		'experience' => 1
		),
	'createevent' => Array
		(
		'rid' => 23,
		'rulename' => '发起活动',
		'action' => 'createevent',
		'cycletype' => 1,
		'cycletime' => '0',
		'rewardnum' => 1,
		'rewardtype' => 1,
		'norepeat' => '0',
		'credit' => 3,
		'experience' => 3
		),
	'joinevent' => Array
		(
		'rid' => 24,
		'rulename' => '参与活动',
		'action' => 'joinevent',
		'cycletype' => 1,
		'cycletime' => '0',
		'rewardnum' => 1,
		'rewardtype' => 1,
		'norepeat' => 1,
		'credit' => 1,
		'experience' => 1
		),
	'recommendevent' => Array
		(
		'rid' => 25,
		'rulename' => '推荐活动',
		'action' => 'recommendevent',
		'cycletype' => 4,
		'cycletime' => '0',
		'rewardnum' => '0',
		'rewardtype' => 1,
		'norepeat' => '0',
		'credit' => 10,
		'experience' => 10
		),
	'createshare' => Array
		(
		'rid' => 26,
		'rulename' => '发起分享',
		'action' => 'createshare',
		'cycletype' => 1,
		'cycletime' => '0',
		'rewardnum' => 3,
		'rewardtype' => 1,
		'norepeat' => '0',
		'credit' => 2,
		'experience' => 2
		),
	'comment' => Array
		(
		'rid' => 27,
		'rulename' => '评论',
		'action' => 'comment',
		'cycletype' => 1,
		'cycletime' => '0',
		'rewardnum' => 40,
		'rewardtype' => 1,
		'norepeat' => 1,
		'credit' => 1,
		'experience' => 1
		),
	'getcomment' => Array
		(
		'rid' => 28,
		'rulename' => '被评论',
		'action' => 'getcomment',
		'cycletype' => 1,
		'cycletime' => '0',
		'rewardnum' => 20,
		'rewardtype' => 1,
		'norepeat' => 1,
		'credit' => 1,
		'experience' => '0'
		),
	'installapp' => Array
		(
		'rid' => 29,
		'rulename' => '安装应用',
		'action' => 'installapp',
		'cycletype' => 4,
		'cycletime' => '0',
		'rewardnum' => '0',
		'rewardtype' => 1,
		'norepeat' => 3,
		'credit' => 5,
		'experience' => 5
		),
	'useapp' => Array
		(
		'rid' => 30,
		'rulename' => '使用应用',
		'action' => 'useapp',
		'cycletype' => 1,
		'cycletime' => '0',
		'rewardnum' => 10,
		'rewardtype' => 1,
		'norepeat' => 3,
		'credit' => 1,
		'experience' => 1
		),
	'click' => Array
		(
		'rid' => 31,
		'rulename' => '信息表态',
		'action' => 'click',
		'cycletype' => 1,
		'cycletime' => '0',
		'rewardnum' => 10,
		'rewardtype' => 1,
		'norepeat' => 1,
		'credit' => 1,
		'experience' => 1
		),
	'editrealname' => Array
		(
		'rid' => 32,
		'rulename' => '修改实名',
		'action' => 'editrealname',
		'cycletype' => '0',
		'cycletime' => '0',
		'rewardnum' => 1,
		'rewardtype' => '0',
		'norepeat' => '0',
		'credit' => 5,
		'experience' => '0'
		),
	'editrealemail' => Array
		(
		'rid' => 33,
		'rulename' => '更改邮箱认证',
		'action' => 'editrealemail',
		'cycletype' => '0',
		'cycletime' => '0',
		'rewardnum' => 1,
		'rewardtype' => '0',
		'norepeat' => '0',
		'credit' => 5,
		'experience' => '0'
		),
	'delavatar' => Array
		(
		'rid' => 34,
		'rulename' => '头像被删除',
		'action' => 'delavatar',
		'cycletype' => '0',
		'cycletime' => '0',
		'rewardnum' => 1,
		'rewardtype' => '0',
		'norepeat' => '0',
		'credit' => 10,
		'experience' => 10
		),
	'invitecode' => Array
		(
		'rid' => 35,
		'rulename' => '获取邀请码',
		'action' => 'invitecode',
		'cycletype' => '0',
		'cycletime' => '0',
		'rewardnum' => 1,
		'rewardtype' => '0',
		'norepeat' => '0',
		'credit' => '0',
		'experience' => '0'
		),
	'search' => Array
		(
		'rid' => 36,
		'rulename' => '搜索一次',
		'action' => 'search',
		'cycletype' => '0',
		'cycletime' => '0',
		'rewardnum' => 1,
		'rewardtype' => '0',
		'norepeat' => '0',
		'credit' => 1,
		'experience' => '0'
		),
	'blogimport' => Array
		(
		'rid' => 37,
		'rulename' => '日志导入',
		'action' => 'blogimport',
		'cycletype' => '0',
		'cycletime' => '0',
		'rewardnum' => 1,
		'rewardtype' => '0',
		'norepeat' => '0',
		'credit' => 10,
		'experience' => '0'
		),
	'modifydomain' => Array
		(
		'rid' => 38,
		'rulename' => '修改域名',
		'action' => 'modifydomain',
		'cycletype' => '0',
		'cycletime' => '0',
		'rewardnum' => 1,
		'rewardtype' => '0',
		'norepeat' => '0',
		'credit' => 5,
		'experience' => '0'
		),
	'delblog' => Array
		(
		'rid' => 39,
		'rulename' => '日志被删除',
		'action' => 'delblog',
		'cycletype' => '0',
		'cycletime' => '0',
		'rewardnum' => 1,
		'rewardtype' => '0',
		'norepeat' => '0',
		'credit' => 10,
		'experience' => 10
		),
	'deldoing' => Array
		(
		'rid' => 40,
		'rulename' => '记录被删除',
		'action' => 'deldoing',
		'cycletype' => '0',
		'cycletime' => '0',
		'rewardnum' => 1,
		'rewardtype' => '0',
		'norepeat' => '0',
		'credit' => 2,
		'experience' => 2
		),
	'delimage' => Array
		(
		'rid' => 41,
		'rulename' => '图片被删除',
		'action' => 'delimage',
		'cycletype' => '0',
		'cycletime' => '0',
		'rewardnum' => 1,
		'rewardtype' => '0',
		'norepeat' => '0',
		'credit' => 4,
		'experience' => 4
		),
	'delpoll' => Array
		(
		'rid' => 42,
		'rulename' => '投票被删除',
		'action' => 'delpoll',
		'cycletype' => '0',
		'cycletime' => '0',
		'rewardnum' => 1,
		'rewardtype' => '0',
		'norepeat' => '0',
		'credit' => 4,
		'experience' => 4
		),
	'delthread' => Array
		(
		'rid' => 43,
		'rulename' => '话题被删除',
		'action' => 'delthread',
		'cycletype' => '0',
		'cycletime' => '0',
		'rewardnum' => 1,
		'rewardtype' => '0',
		'norepeat' => '0',
		'credit' => 4,
		'experience' => 4
		),
	'delevent' => Array
		(
		'rid' => 44,
		'rulename' => '活动被删除',
		'action' => 'delevent',
		'cycletype' => '0',
		'cycletime' => '0',
		'rewardnum' => 1,
		'rewardtype' => '0',
		'norepeat' => '0',
		'credit' => 6,
		'experience' => 6
		),
	'delshare' => Array
		(
		'rid' => 45,
		'rulename' => '分享被删除',
		'action' => 'delshare',
		'cycletype' => '0',
		'cycletime' => '0',
		'rewardnum' => 1,
		'rewardtype' => '0',
		'norepeat' => '0',
		'credit' => 4,
		'experience' => 4
		),
	'delguestbook' => Array
		(
		'rid' => 46,
		'rulename' => '留言被删除',
		'action' => 'delguestbook',
		'cycletype' => '0',
		'cycletime' => '0',
		'rewardnum' => 1,
		'rewardtype' => '0',
		'norepeat' => '0',
		'credit' => 4,
		'experience' => 4
		),
	'delcomment' => Array
		(
		'rid' => 47,
		'rulename' => '评论被删除',
		'action' => 'delcomment',
		'cycletype' => '0',
		'cycletime' => '0',
		'rewardnum' => 1,
		'rewardtype' => '0',
		'norepeat' => '0',
		'credit' => 2,
		'experience' => 2
		)
	)
?>