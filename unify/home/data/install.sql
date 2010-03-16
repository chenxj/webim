--
-- ucenter home 数据库SQL
-- 生成日期: 2008 年 1 月 1 日 00:00
--

--
-- 数据库: 'uchome'
--

-- --------------------------------------------------------

--
-- 表的结构 'uchome_ad'
--

CREATE TABLE uchome_ad (
  adid smallint(6) unsigned NOT NULL auto_increment,
  available tinyint(1) NOT NULL default '1',
  title varchar(50) NOT NULL default '',
  pagetype varchar(20) NOT NULL default '',
  adcode text NOT NULL,
  system tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (adid)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_adminsession'
--

CREATE TABLE uchome_adminsession (
  uid mediumint(8) unsigned NOT NULL default '0',
  ip char(15) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  errorcount tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (uid)
) ENGINE=MEMORY;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_album'
--

CREATE TABLE uchome_album (
  albumid mediumint(8) unsigned NOT NULL auto_increment,
  albumname varchar(50) NOT NULL default '',
  uid mediumint(8) unsigned NOT NULL default '0',
  username varchar(15) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  updatetime int(10) unsigned NOT NULL default '0',
  picnum smallint(6) unsigned NOT NULL default '0',
  pic varchar(60) NOT NULL default '',
  picflag tinyint(1) NOT NULL default '0',
  friend tinyint(1) NOT NULL default '0',
  `password` varchar(10) NOT NULL default '',
  target_ids text NOT NULL,
  PRIMARY KEY  (albumid),
  KEY uid (uid,updatetime),
  KEY updatetime (updatetime)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_appcreditlog'
--

CREATE TABLE uchome_appcreditlog (
  logid mediumint(8) unsigned NOT NULL auto_increment,
  uid mediumint(8) unsigned NOT NULL default '0',
  appid mediumint(8) unsigned NOT NULL default '0',
  appname varchar(60) NOT NULL default '',
  `type` tinyint(1) NOT NULL default '0',
  credit mediumint(8) unsigned NOT NULL default '0',
  note text NOT NULL,
  dateline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (logid),
  KEY uid (uid,dateline),
  KEY appid (appid)
) ENGINE=MyISAM;
-- --------------------------------------------------------

--
-- 表的结构 'uchome_blacklist'
--

CREATE TABLE uchome_blacklist (
  uid mediumint(8) unsigned NOT NULL default '0',
  buid mediumint(8) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (uid,buid),
  KEY uid (uid,dateline)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_block'
--

CREATE TABLE uchome_block (
  bid smallint(6) unsigned NOT NULL auto_increment,
  blockname varchar(40) NOT NULL default '',
  blocksql text NOT NULL,
  cachename varchar(30) NOT NULL default '',
  cachetime smallint(6) unsigned NOT NULL default '0',
  startnum tinyint(3) unsigned NOT NULL default '0',
  num tinyint(3) unsigned NOT NULL default '0',
  perpage tinyint(3) unsigned NOT NULL default '0',
  htmlcode text NOT NULL,
  PRIMARY KEY  (bid)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_blog'
--

CREATE TABLE uchome_blog (
  blogid mediumint(8) unsigned NOT NULL auto_increment,
  topicid mediumint(8) unsigned NOT NULL default '0',
  uid mediumint(8) unsigned NOT NULL default '0',
  username char(15) NOT NULL default '',
  `subject` char(80) NOT NULL default '',
  classid smallint(6) unsigned NOT NULL default '0',
  viewnum mediumint(8) unsigned NOT NULL default '0',
  replynum mediumint(8) unsigned NOT NULL default '0',
  hot mediumint(8) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  pic char(120) NOT NULL default '',
  picflag tinyint(1) NOT NULL default '0',
  noreply tinyint(1) NOT NULL default '0',
  friend tinyint(1) NOT NULL default '0',
  `password` char(10) NOT NULL default '',
  click_1 smallint(6) unsigned NOT NULL default '0',
  click_2 smallint(6) unsigned NOT NULL default '0',
  click_3 smallint(6) unsigned NOT NULL default '0',
  click_4 smallint(6) unsigned NOT NULL default '0',
  click_5 smallint(6) unsigned NOT NULL default '0',
  PRIMARY KEY  (blogid),
  KEY uid (uid,dateline),
  KEY topicid (topicid,dateline),
  KEY dateline (dateline)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_blogfield'
--

CREATE TABLE uchome_blogfield (
  blogid mediumint(8) unsigned NOT NULL default '0',
  uid mediumint(8) unsigned NOT NULL default '0',
  tag varchar(255) NOT NULL default '',
  message mediumtext NOT NULL,
  postip varchar(20) NOT NULL default '',
  related text NOT NULL,
  relatedtime int(10) unsigned NOT NULL default '0',
  target_ids text NOT NULL,
  hotuser text NOT NULL,
  magiccolor tinyint(6) NOT NULL default '0',
  magicpaper tinyint(6) NOT NULL default '0',
  magiccall tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (blogid)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_cache'
--

CREATE TABLE uchome_cache (
  cachekey varchar(16) NOT NULL default '',
  `value` mediumtext NOT NULL,
  mtime int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (cachekey)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_class'
--

CREATE TABLE uchome_class (
  classid mediumint(8) unsigned NOT NULL auto_increment,
  classname char(40) NOT NULL default '',
  uid mediumint(8) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (classid),
  KEY uid (uid)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_click'
--

CREATE TABLE uchome_click (
  clickid smallint(6) unsigned NOT NULL auto_increment,
  `name` varchar(50) NOT NULL default '',
  icon varchar(100) NOT NULL default '',
  idtype varchar(15) NOT NULL default '',
  displayorder tinyint(6) unsigned NOT NULL default '0',
  PRIMARY KEY  (clickid),
  KEY idtype (idtype,displayorder)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_clickuser'
--

CREATE TABLE uchome_clickuser (
  uid mediumint(8) unsigned NOT NULL default '0',
  username varchar(15) NOT NULL default '',
  id mediumint(8) unsigned NOT NULL default '0',
  idtype varchar(15) NOT NULL default '',
  clickid smallint(6) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  KEY id (id,idtype,dateline),
  KEY uid (uid,idtype,dateline)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_comment'
--

CREATE TABLE uchome_comment (
  cid mediumint(8) unsigned NOT NULL auto_increment,
  uid mediumint(8) unsigned NOT NULL default '0',
  id mediumint(8) unsigned NOT NULL default '0',
  idtype varchar(20) NOT NULL default '',
  authorid mediumint(8) unsigned NOT NULL default '0',
  author varchar(15) NOT NULL default '',
  ip varchar(20) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  message text NOT NULL,
  magicflicker tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (cid),
  KEY authorid (authorid, idtype),
  KEY id (id, idtype, dateline)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_config'
--

CREATE TABLE uchome_config (
  var varchar(30) NOT NULL default '',
  datavalue text NOT NULL,
  PRIMARY KEY  (var)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_cron'
--

CREATE TABLE uchome_cron (
  cronid smallint(6) unsigned NOT NULL auto_increment,
  available tinyint(1) NOT NULL default '0',
  `type` enum('user','system') NOT NULL default 'user',
  `name` char(50) NOT NULL default '',
  filename char(50) NOT NULL default '',
  lastrun int(10) unsigned NOT NULL default '0',
  nextrun int(10) unsigned NOT NULL default '0',
  weekday tinyint(1) NOT NULL default '0',
  `day` tinyint(2) NOT NULL default '0',
  `hour` tinyint(2) NOT NULL default '0',
  `minute` char(36) NOT NULL default '',
  PRIMARY KEY  (cronid),
  KEY nextrun (available,nextrun)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_creditrule'
--

CREATE TABLE uchome_creditrule (
  rid mediumint(8) unsigned NOT NULL auto_increment,
  rulename char(20) NOT NULL default '',
  `action` char(20) NOT NULL default '',
  cycletype tinyint(1) NOT NULL default '0',
  cycletime int(10) NOT NULL default '0',
  rewardnum tinyint(2) NOT NULL default '1',
  rewardtype tinyint(1) NOT NULL default '1',
  norepeat tinyint(1) NOT NULL default '0',
  credit mediumint(8) unsigned NOT NULL default '0',
  experience mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (rid),
  KEY `action` (`action`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_creditlog'
--

CREATE TABLE uchome_creditlog (
  clid mediumint(8) unsigned NOT NULL auto_increment,
  uid mediumint(8) unsigned NOT NULL default '0',
  rid mediumint(8) unsigned NOT NULL default '0',
  total mediumint(8) unsigned NOT NULL default '0',
  cyclenum mediumint(8) unsigned NOT NULL default '0',
  credit mediumint(8) unsigned NOT NULL default '0',
  experience mediumint(8) unsigned NOT NULL default '0',
  starttime int(10) unsigned NOT NULL default '0',
  info text NOT NULL,
  `user` text NOT NULL,
  app text NOT NULL,
  dateline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (clid),
  KEY uid (uid, rid),
  KEY dateline (dateline)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_data'
--

CREATE TABLE uchome_data (
  var varchar(20) NOT NULL default '',
  datavalue text NOT NULL,
  dateline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (var)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_docomment'
--

CREATE TABLE uchome_docomment (
  id int(10) unsigned NOT NULL auto_increment,
  upid int(10) unsigned NOT NULL default '0',
  doid mediumint(8) unsigned NOT NULL default '0',
  uid mediumint(8) unsigned NOT NULL default '0',
  username varchar(15) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  message text NOT NULL,
  ip varchar(20) NOT NULL default '',
  grade smallint(6) unsigned NOT NULL default '0',
  PRIMARY KEY (id),
  KEY doid (doid,dateline),
  KEY dateline (dateline)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_doing'
--

CREATE TABLE uchome_doing (
  doid mediumint(8) unsigned NOT NULL auto_increment,
  uid mediumint(8) unsigned NOT NULL default '0',
  username varchar(15) NOT NULL default '',
  `from` varchar(20) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  message text NOT NULL,
  ip varchar(20) NOT NULL default '',
  replynum int(10) unsigned NOT NULL default '0',
  mood smallint(6) NOT NULL default '0',
  PRIMARY KEY  (doid),
  KEY uid (uid,dateline),
  KEY dateline (dateline)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_event'
--

CREATE TABLE uchome_event (
  eventid mediumint(8) unsigned NOT NULL auto_increment,
  topicid mediumint(8) unsigned NOT NULL default '0',
  uid mediumint(8) unsigned NOT NULL default '0',
  username varchar(15) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  title varchar(80) NOT NULL default '',
  classid smallint(6) unsigned NOT NULL default '0',
  province varchar(20) NOT NULL default '',
  city varchar(20) NOT NULL default '',
  location varchar(80) NOT NULL default '',
  poster varchar(60) NOT NULL default '',
  thumb tinyint(1) NOT NULL default '0',
  remote tinyint(1) NOT NULL default '0',
  deadline int(10) unsigned NOT NULL default '0',
  starttime int(10) unsigned NOT NULL default '0',
  endtime int(10) unsigned NOT NULL default '0',
  public tinyint(3) NOT NULL default '0',
  membernum mediumint(8) unsigned NOT NULL default '0',
  follownum mediumint(8) unsigned NOT NULL default '0',
  viewnum mediumint(8) unsigned NOT NULL default '0',
  grade tinyint(3) NOT NULL default '0',
  recommendtime int(10) unsigned NOT NULL default '0',
  tagid mediumint(8) unsigned NOT NULL default '0',
  picnum mediumint(8) unsigned NOT NULL default '0',
  threadnum mediumint(8) unsigned NOT NULL default '0',
  updatetime int(10) unsigned NOT NULL default '0',
  hot mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (eventid),
  KEY grade (grade,recommendtime),
  KEY membernum (membernum),
  KEY uid (uid,eventid),
  KEY tagid (tagid,eventid),
  KEY topicid (topicid,dateline)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_eventclass'
--

CREATE TABLE uchome_eventclass (
  classid smallint(6) unsigned NOT NULL auto_increment,
  classname varchar(80) NOT NULL default '',
  poster tinyint(1) NOT NULL default '0',
  template text NOT NULL,
  displayorder mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (classid),
  UNIQUE KEY classname (classname)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_eventfield'
--

CREATE TABLE uchome_eventfield (
  eventid mediumint(8) unsigned NOT NULL auto_increment,
  detail text NOT NULL,
  template varchar(255) NOT NULL default '',
  limitnum mediumint(8) unsigned NOT NULL default '0',
  verify tinyint(1) NOT NULL default '0',
  allowpic tinyint(1) NOT NULL default '0',
  allowpost tinyint(1) NOT NULL default '0',
  allowinvite tinyint(1) NOT NULL default '0',
  allowfellow tinyint(1) NOT NULL default '0',
  hotuser text NOT NULL,
  PRIMARY KEY  (eventid)
) ENGINE=MyISAM;


-- --------------------------------------------------------

--
-- 表的结构 'uchome_eventinvite'
--

CREATE TABLE uchome_eventinvite (
  eventid mediumint(8) unsigned NOT NULL default '0',
  uid mediumint(8) unsigned NOT NULL default '0',
  username varchar(15) NOT NULL default '',
  touid mediumint(8) unsigned NOT NULL default '0',
  tousername char(15) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (eventid,touid)
) ENGINE=MyISAM;


-- --------------------------------------------------------

--
-- 表的结构 'uchome_eventpic'
--

CREATE TABLE uchome_eventpic (
  picid mediumint(8) unsigned NOT NULL default '0',
  eventid mediumint(8) unsigned NOT NULL default '0',
  uid mediumint(8) unsigned NOT NULL default '0',
  username char(15) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (picid),
  KEY eventid (eventid,picid)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_feed'
--

CREATE TABLE uchome_feed (
  feedid int(10) unsigned NOT NULL auto_increment,
  appid smallint(6) unsigned NOT NULL default '0',
  icon varchar(30) NOT NULL default '',
  uid mediumint(8) unsigned NOT NULL default '0',
  username varchar(15) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  friend tinyint(1) NOT NULL default '0',
  hash_template varchar(32) NOT NULL default '',
  hash_data varchar(32) NOT NULL default '',
  title_template text NOT NULL,
  title_data text NOT NULL,
  body_template text NOT NULL,
  body_data text NOT NULL,
  body_general text NOT NULL,
  image_1 varchar(255) NOT NULL default '',
  image_1_link varchar(255) NOT NULL default '',
  image_2 varchar(255) NOT NULL default '',
  image_2_link varchar(255) NOT NULL default '',
  image_3 varchar(255) NOT NULL default '',
  image_3_link varchar(255) NOT NULL default '',
  image_4 varchar(255) NOT NULL default '',
  image_4_link varchar(255) NOT NULL default '',
  target_ids text NOT NULL,
  id mediumint(8) unsigned NOT NULL default '0',
  idtype varchar(15) NOT NULL default '',
  hot mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (feedid),
  KEY uid (uid,dateline),
  KEY dateline (dateline),
  KEY hot (hot),
  KEY id (id,idtype)
) ENGINE=MyISAM;

-- --------------------------------------------------------
--
-- 表的结构 'uchome_friend'
--

CREATE TABLE uchome_friend (
  uid mediumint(8) unsigned NOT NULL default '0',
  fuid mediumint(8) unsigned NOT NULL default '0',
  fusername varchar(15) NOT NULL default '',
  status tinyint(1) NOT NULL default '0',
  gid smallint(6) unsigned NOT NULL default '0',
  note varchar(50) NOT NULL default '',
  num mediumint(8) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (uid,fuid),
  KEY fuid (fuid),
  KEY status (uid, status, num, dateline)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_friendguide'
--

CREATE TABLE uchome_friendguide (
  uid mediumint(8) unsigned NOT NULL default '0',
  fuid mediumint(8) unsigned NOT NULL default '0',
  fusername char(15) NOT NULL default '',
  num smallint(6) unsigned NOT NULL default '0',
  PRIMARY KEY  (uid,fuid),
  KEY uid (uid,num)
) ENGINE=MyISAM;

-- --------------------------------------------------------
--
-- 表的结构 'uchome_friendlog'
--

CREATE TABLE uchome_friendlog (
  uid mediumint(8) unsigned NOT NULL default '0',
  fuid mediumint(8) unsigned NOT NULL default '0',
  action varchar(10) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (uid,fuid)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_invite'
--

CREATE TABLE uchome_invite (
  id mediumint(8) unsigned NOT NULL auto_increment,
  uid mediumint(8) unsigned NOT NULL default '0',
  code varchar(20) NOT NULL default '',
  fuid mediumint(8) unsigned NOT NULL default '0',
  fusername varchar(15) NOT NULL default '',
  `type` tinyint(1) NOT NULL default '0',
  email varchar(100) NOT NULL default '',
  appid mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY uid (uid)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_log'
--

CREATE TABLE uchome_log (
  logid mediumint(8) unsigned NOT NULL auto_increment,
  id mediumint(8) unsigned NOT NULL default '0',
  idtype char(20) NOT NULL default '',
  PRIMARY KEY  (logid)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_magic'
--

CREATE TABLE uchome_magic (
  mid varchar(15) NOT NULL default '',
  `name` varchar(30) NOT NULL default '',
  description text NOT NULL default '',
  forbiddengid text NOT NULL default '',
  charge smallint(6) unsigned NOT NULL default '0',
  experience smallint(6) unsigned NOT NULL default '0',
  provideperoid int(10) unsigned NOT NULL default '0',
  providecount smallint(6) unsigned NOT NULL default '0',
  useperoid int(10) unsigned NOT NULL default '0',
  usecount smallint(6) unsigned NOT NULL default '0',
  displayorder smallint(6) unsigned NOT NULL default '0',
  custom text NOT NULL default '',
  `close` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (mid)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_magicinlog'
--

CREATE TABLE uchome_magicinlog (
  logid mediumint(8) unsigned NOT NULL auto_increment,
  uid mediumint(8) unsigned NOT NULL default '0',
  username varchar(15) NOT NULL default '',
  mid varchar(15) NOT NULL default '',
  count smallint(6) unsigned NOT NULL default '0',
  `type` tinyint(3) unsigned NOT NULL default '0',
  fromid mediumint(8) unsigned NOT NULL default '0',
  credit smallint(6) unsigned NOT NULL default '0',
  dateline int(10) NOT NULL default '0',
  PRIMARY KEY  (logid),
  KEY uid (uid,dateline),
  KEY `type` (`type`,fromid,dateline)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uch_magicstore'
--

CREATE TABLE uchome_magicstore (
  mid varchar(15) NOT NULL default '',
  `storage` smallint(6) unsigned NOT NULL default '0',
  lastprovide int(10) unsigned NOT NULL default '0',
  sellcount int(8) unsigned NOT NULL default '0',
  sellcredit int(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (mid)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_magicuselog'
--

CREATE TABLE uchome_magicuselog (
  logid mediumint(8) unsigned NOT NULL auto_increment,
  uid mediumint(8) unsigned NOT NULL default '0',
  username varchar(15) NOT NULL default '',
  mid varchar(15) NOT NULL default '',
  id mediumint(8) unsigned NOT NULL default '0',
  idtype varchar(20) NOT NULL default '',
  count mediumint(8) unsigned NOT NULL default '0',
  `data` text NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  expire int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (logid),
  KEY uid (uid,mid),
  KEY id (id,idtype)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_mailqueue'
--

CREATE TABLE uchome_mailqueue (
  qid mediumint(8) unsigned NOT NULL auto_increment,
  cid mediumint(8) unsigned NOT NULL default '0',
  subject text NOT NULL,
  message text NOT NULL,
  dateline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (qid),
  KEY mcid (cid,dateline)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_mailcron'
--

CREATE TABLE uchome_mailcron (
  cid mediumint(8) unsigned NOT NULL auto_increment,
  touid mediumint(8) unsigned NOT NULL default '0',
  email varchar(100) NOT NULL default '',
  sendtime int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (cid),
  KEY sendtime (sendtime)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_member'
--

CREATE TABLE uchome_member (
  uid mediumint(8) unsigned NOT NULL auto_increment,
  username char(15) NOT NULL default '',
  `password` char(32) NOT NULL default '',
  PRIMARY KEY  (uid)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_mtag'
--

CREATE TABLE uchome_mtag (
  tagid mediumint(8) unsigned NOT NULL auto_increment,
  tagname varchar(40) NOT NULL default '',
  fieldid smallint(6) NOT NULL default '0',
  membernum mediumint(8) unsigned NOT NULL default '0',
  threadnum mediumint(8) unsigned NOT NULL default '0',
  postnum mediumint(8) unsigned NOT NULL default '0',
  `close` tinyint(1) NOT NULL default '0',
  announcement text NOT NULL,
  pic varchar(150) NOT NULL default '',
  closeapply tinyint(1) NOT NULL default '0',
  joinperm tinyint(1) NOT NULL default '0',
  viewperm tinyint(1) NOT NULL default '0',
  threadperm tinyint(1) NOT NULL default '0',
  postperm tinyint(1) NOT NULL default '0',
  recommend tinyint(1) NOT NULL default '0',
  moderator varchar(255) NOT NULL default '',
  PRIMARY KEY  (tagid),
  KEY tagname (tagname),
  KEY threadnum (threadnum)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_mtaginvite'
--

CREATE TABLE uchome_mtaginvite (
  uid mediumint(8) unsigned NOT NULL default '0',
  tagid mediumint(8) unsigned NOT NULL default '0',
  fromuid mediumint(8) unsigned NOT NULL default '0',
  fromusername char(15) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (uid,tagid)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_myapp'
--

CREATE TABLE uchome_myapp (
  appid mediumint(8) unsigned NOT NULL default '0',
  appname varchar(60) NOT NULL default '',
  narrow tinyint(1) NOT NULL default '0',
  flag tinyint(1) NOT NULL default '0',
  version mediumint(8) unsigned NOT NULL default '0',
  displaymethod tinyint(1) NOT NULL default '0',
  displayorder smallint(6) unsigned NOT NULL default '0',
  PRIMARY KEY (appid),
  KEY flag (flag, displayorder)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_myinvite'
--

CREATE TABLE uchome_myinvite (
  id mediumint(8) unsigned NOT NULL auto_increment,
  typename varchar(100) NOT NULL default '',
  appid mediumint(8) NOT NULL default '0',
  type tinyint(1) NOT NULL default '0',
  fromuid mediumint(8) unsigned NOT NULL default '0',
  touid mediumint(8) unsigned NOT NULL default '0',
  myml text NOT NULL,
  dateline int(10) unsigned NOT NULL default '0',
  hash int(10) unsigned NOT NULL default '0',
  PRIMARY KEY (id),
  KEY hash (hash),
  KEY uid (touid, dateline)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_notification'
--

CREATE TABLE uchome_notification (
  id mediumint(8) unsigned NOT NULL auto_increment,
  uid mediumint(8) unsigned NOT NULL default '0',
  `type` varchar(20) NOT NULL default '',
  `new` tinyint(1) NOT NULL default '0',
  authorid mediumint(8) unsigned NOT NULL default '0',
  author varchar(15) NOT NULL default '',
  note text NOT NULL,
  dateline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY uid (uid,new,dateline)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_pic'
--

CREATE TABLE uchome_pic (
  picid mediumint(8) NOT NULL auto_increment,
  albumid mediumint(8) unsigned NOT NULL default '0',
  topicid mediumint(8) unsigned NOT NULL default '0',
  uid mediumint(8) unsigned NOT NULL default '0',
  username varchar(15) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  postip varchar(20) NOT NULL default '',
  filename varchar(100) NOT NULL default '',
  title varchar(255) NOT NULL default '',
  `type` varchar(20) NOT NULL default '',
  size int(10) unsigned NOT NULL default '0',
  filepath varchar(60) NOT NULL default '',
  thumb tinyint(1) NOT NULL default '0',
  remote tinyint(1) NOT NULL default '0',
  hot mediumint(8) unsigned NOT NULL default '0',
  click_6 smallint(6) unsigned NOT NULL default '0',
  click_7 smallint(6) unsigned NOT NULL default '0',
  click_8 smallint(6) unsigned NOT NULL default '0',
  click_9 smallint(6) unsigned NOT NULL default '0',
  click_10 smallint(6) unsigned NOT NULL default '0',
  magicframe tinyint(6) NOT NULL default '0',
  PRIMARY KEY  (picid),
  KEY albumid (albumid,dateline),
  KEY topicid (topicid,dateline)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_picfield'
--

CREATE TABLE uchome_picfield (
  picid mediumint(8) unsigned NOT NULL default '0',
  hotuser text NOT NULL,
  PRIMARY KEY  (picid)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_poke'
--

CREATE TABLE uchome_poke (
  uid mediumint(8) unsigned NOT NULL default '0',
  fromuid mediumint(8) unsigned NOT NULL default '0',
  fromusername varchar(15) NOT NULL default '',
  note varchar(255) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  iconid smallint(6) unsigned NOT NULL default '0',
  PRIMARY KEY  (uid,fromuid),
  KEY uid (uid,dateline)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_poll'
--

CREATE TABLE uchome_poll (
  pid mediumint(8) unsigned NOT NULL auto_increment,
  topicid mediumint(8) unsigned NOT NULL default '0',
  uid mediumint(8) unsigned NOT NULL default '0',
  username char(15) NOT NULL default '',
  subject char(80) NOT NULL default '',
  voternum mediumint(8) unsigned NOT NULL default '0',
  replynum mediumint(8) unsigned NOT NULL default '0',
  multiple tinyint(1) NOT NULL default '0',
  maxchoice tinyint(3) NOT NULL default '0',
  sex tinyint(1) NOT NULL default '0',
  noreply tinyint(1) NOT NULL default '0',
  credit mediumint(8) unsigned NOT NULL default '0',
  percredit mediumint(8) unsigned NOT NULL default '0',
  expiration int(10) unsigned NOT NULL default '0',
  lastvote int(10) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  hot mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (pid),
  KEY uid (uid,dateline),
  KEY topicid (topicid,dateline),
  KEY voternum (voternum),
  KEY dateline (dateline),
  KEY lastvote (lastvote),
  KEY hot (hot),
  KEY percredit (percredit)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_pollfield'
--

CREATE TABLE uchome_pollfield (
  pid mediumint(8) unsigned NOT NULL default '0',
  notify tinyint(1) NOT NULL default '0',
  message text NOT NULL,
  summary text NOT NULL,
  `option` text NOT NULL,
  invite text NOT NULL,
  hotuser text NOT NULL,
  PRIMARY KEY  (pid)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_polloption'
--

CREATE TABLE uchome_polloption (
  oid mediumint(8) unsigned NOT NULL auto_increment,
  pid mediumint(8) unsigned NOT NULL default '0',
  votenum mediumint(8) unsigned NOT NULL default '0',
  `option` varchar(100) NOT NULL default '',
  PRIMARY KEY (oid),
  KEY pid (pid)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_polluser'
--

CREATE TABLE uchome_polluser (
  uid mediumint(8) unsigned NOT NULL default '0',
  username varchar(15) NOT NULL default '',
  pid mediumint(8) unsigned NOT NULL default '0',
  `option` text NOT NULL,
  dateline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY (uid, pid),
  KEY pid (pid, dateline),
  KEY uid (uid, dateline)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_post'
--

CREATE TABLE uchome_post (
  pid int(10) unsigned NOT NULL auto_increment,
  tagid mediumint(8) unsigned NOT NULL default '0',
  tid mediumint(8) unsigned NOT NULL default '0',
  uid mediumint(8) unsigned NOT NULL default '0',
  username varchar(15) NOT NULL default '',
  ip varchar(20) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  message text NOT NULL,
  pic varchar(255) NOT NULL default '',
  isthread tinyint(1) NOT NULL default '0',
  hotuser text NOT NULL,
  PRIMARY KEY  (pid),
  KEY tid (tid,dateline)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_profield'
--

CREATE TABLE uchome_profield (
  fieldid smallint(6) unsigned NOT NULL auto_increment,
  title varchar(80) NOT NULL default '',
  note varchar(255) NOT NULL default '',
  formtype varchar(20) NOT NULL default '0',
  inputnum smallint(3) unsigned NOT NULL default '0',
  choice text NOT NULL,
  mtagminnum smallint(6) unsigned NOT NULL default '0',
  manualmoderator tinyint(1) NOT NULL default '0',
  manualmember tinyint(1) NOT NULL default '0',
  displayorder tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (fieldid)
) ENGINE=MyISAM;


-- --------------------------------------------------------

--
-- 表的结构 'uchome_profilefield'
--

CREATE TABLE uchome_profilefield (
  fieldid smallint(6) unsigned NOT NULL auto_increment,
  title varchar(80) NOT NULL default '',
  note varchar(255) NOT NULL default '',
  formtype varchar(20) NOT NULL default '0',
  maxsize tinyint(3) unsigned NOT NULL default '0',
  required tinyint(1) NOT NULL default '0',
  invisible tinyint(1) NOT NULL default '0',
  allowsearch tinyint(1) NOT NULL default '0',
  choice text NOT NULL,
  displayorder tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (fieldid)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_report'
--

CREATE TABLE uchome_report (
  rid mediumint(8) unsigned NOT NULL auto_increment,
  id mediumint(8) unsigned NOT NULL default '0',
  idtype varchar(15) NOT NULL default '',
  `new` tinyint(1) NOT NULL default '0',
  num smallint(6) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  reason text NOT NULL,
  uids text NOT NULL,
  PRIMARY KEY  (rid),
  KEY id (id,idtype,num,dateline),
  KEY `new` (new,dateline)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_session'
--

CREATE TABLE uchome_session (
  uid mediumint(8) unsigned NOT NULL default '0',
  username char(15) NOT NULL default '',
  `password` char(32) NOT NULL default '',
  lastactivity int(10) unsigned NOT NULL default '0',
  ip int(10) unsigned NOT NULL default '0',
  magichidden tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (uid),
  KEY lastactivity (lastactivity),
  KEY ip (ip)
) ENGINE=MEMORY;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_share'
--

CREATE TABLE uchome_share (
  sid mediumint(8) unsigned NOT NULL auto_increment,
  topicid mediumint(8) unsigned NOT NULL default '0',
  type varchar(30) NOT NULL default '',
  uid mediumint(8) unsigned NOT NULL default '0',
  username varchar(15) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  title_template text NOT NULL,
  body_template text NOT NULL,
  body_data text NOT NULL,
  body_general text NOT NULL,
  image varchar(255) NOT NULL default '',
  image_link varchar(255) NOT NULL default '',
  hot mediumint(8) unsigned NOT NULL default '0',
  hotuser text NOT NULL,
  PRIMARY KEY  (sid),
  KEY uid (uid,dateline),
  KEY topicid (topicid,dateline),
  KEY hot (hot),
  KEY dateline (dateline)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_show'
--

CREATE TABLE uchome_show (
  uid mediumint(8) unsigned NOT NULL default '0',
  username varchar(15) NOT NULL default '',
  credit int(10) unsigned NOT NULL default '0',
  note varchar(100) NOT NULL default '',
  PRIMARY KEY  (uid),
  KEY credit (credit)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_space'
--

CREATE TABLE uchome_space (
  uid mediumint(8) unsigned NOT NULL default '0',
  groupid smallint(6) unsigned NOT NULL default '0',
  credit int(10) NOT NULL default '0',
  experience int(10) NOT NULL default '0',
  username char(15) NOT NULL default '',
  `name` char(20) NOT NULL default '',
  namestatus tinyint(1) NOT NULL default '0',
  videostatus tinyint(1) NOT NULL default '0',
  domain char(15) NOT NULL default '',
  friendnum int(10) unsigned NOT NULL default '0',
  viewnum int(10) unsigned NOT NULL default '0',
  notenum int(10) unsigned NOT NULL default '0',
  addfriendnum smallint(6) unsigned NOT NULL default '0',
  mtaginvitenum smallint(6) unsigned NOT NULL default '0',
  eventinvitenum smallint(6) unsigned NOT NULL default '0',
  myinvitenum smallint(6) unsigned NOT NULL default '0',
  pokenum smallint(6) unsigned NOT NULL default '0',
  doingnum smallint(6) unsigned NOT NULL default '0',
  blognum smallint(6) unsigned NOT NULL default '0',
  albumnum smallint(6) unsigned NOT NULL default '0',
  threadnum smallint(6) unsigned NOT NULL default '0',
  pollnum smallint(6) unsigned NOT NULL default '0',
  eventnum smallint(6) unsigned NOT NULL default '0',
  sharenum smallint(6) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  updatetime int(10) unsigned NOT NULL default '0',
  lastsearch int(10) unsigned NOT NULL default '0',
  lastpost int(10) unsigned NOT NULL default '0',
  lastlogin int(10) unsigned NOT NULL default '0',
  lastsend int(10) unsigned NOT NULL default '0',
  attachsize int(10) unsigned NOT NULL default '0',
  addsize int(10) unsigned NOT NULL default '0',
  addfriend smallint(6) unsigned NOT NULL default '0',
  flag tinyint(1) NOT NULL default '0',
  newpm smallint(6) unsigned NOT NULL default '0',
  avatar tinyint(1) NOT NULL default '0',
  regip char(15) NOT NULL default '',
  ip int(10) unsigned NOT NULL default '0',
  mood smallint(6) unsigned NOT NULL default '0',
  PRIMARY KEY  (uid),
  KEY username (username),
  KEY domain (domain),
  KEY ip (ip),
  KEY updatetime (updatetime),
  KEY mood (mood)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_spacefield'
--

CREATE TABLE uchome_spacefield (
  uid mediumint(8) unsigned NOT NULL default '0',
  sex tinyint(1) NOT NULL default '0',
  email varchar(100) NOT NULL default '',
  newemail varchar(100) NOT NULL default '',
  emailcheck tinyint(1) NOT NULL default '0',
  mobile varchar(40) NOT NULL default '',
  qq varchar(20) NOT NULL default '',
  msn varchar(80) NOT NULL default '',
  msnrobot varchar(15) NOT NULL default '',
  msncstatus tinyint(1) NOT NULL default '0',
  videopic varchar(32) NOT NULL default '',
  birthyear smallint(6) unsigned NOT NULL default '0',
  birthmonth tinyint(3) unsigned NOT NULL default '0',
  birthday tinyint(3) unsigned NOT NULL default '0',
  blood varchar(5) NOT NULL default '',
  marry tinyint(1) NOT NULL default '0',
  birthprovince varchar(20) NOT NULL default '',
  birthcity varchar(20) NOT NULL default '',
  resideprovince varchar(20) NOT NULL default '',
  residecity varchar(20) NOT NULL default '',
  note text NOT NULL,
  spacenote text NOT NULL,
  authstr varchar(20) NOT NULL default '',
  theme varchar(20) NOT NULL default '',
  nocss tinyint(1) NOT NULL default '0',
  menunum smallint(6) unsigned NOT NULL default '0',
  css text NOT NULL,
  privacy text NOT NULL,
  friend mediumtext NOT NULL,
  feedfriend mediumtext NOT NULL,
  sendmail text NOT NULL,
  magicstar tinyint(1) NOT NULL default '0',
  magicexpire int(10) unsigned NOT NULL default '0',
  timeoffset varchar(20) NOT NULL default '',
  PRIMARY KEY  (uid)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_spaceinfo'
--

CREATE TABLE uchome_spaceinfo (
  infoid mediumint(8) unsigned NOT NULL auto_increment,
  uid mediumint(8) unsigned NOT NULL default '0',
  type varchar(20) NOT NULL default '',
  subtype varchar(20) NOT NULL default '',
  title text NOT NULL,
  subtitle varchar(255) NOT NULL default '',
  friend tinyint(1) NOT NULL default '0',
  startyear smallint(6) unsigned NOT NULL default '0',
  endyear smallint(6) unsigned NOT NULL default '0',
  startmonth smallint(6) unsigned NOT NULL default '0',
  endmonth smallint(6) unsigned NOT NULL default '0',
  PRIMARY KEY  (infoid),
  KEY uid (uid)
) ENGINE=MyISAM;;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_spacelog'
--

CREATE TABLE uchome_spacelog (
  uid mediumint(8) unsigned NOT NULL default '0',
  username char(15) NOT NULL default '',
  opuid mediumint(8) unsigned NOT NULL default '0',
  opusername char(15) NOT NULL default '',
  flag tinyint(1) NOT NULL default '0',
  expiration int(10) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (uid),
  KEY flag (flag)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_stat'
--

CREATE TABLE uchome_stat (
  daytime int(10) unsigned NOT NULL default '0',
  login smallint(6) unsigned NOT NULL default '0',
  register smallint(6) unsigned NOT NULL default '0',
  invite smallint(6) unsigned NOT NULL default '0',
  appinvite smallint(6) unsigned NOT NULL default '0',
  doing smallint(6) unsigned NOT NULL default '0',
  blog smallint(6) unsigned NOT NULL default '0',
  pic smallint(6) unsigned NOT NULL default '0',
  poll smallint(6) unsigned NOT NULL default '0',
  event smallint(6) unsigned NOT NULL default '0',
  `share` smallint(6) unsigned NOT NULL default '0',
  thread smallint(6) unsigned NOT NULL default '0',
  docomment smallint(6) unsigned NOT NULL default '0',
  blogcomment smallint(6) unsigned NOT NULL default '0',
  piccomment smallint(6) unsigned NOT NULL default '0',
  pollcomment smallint(6) unsigned NOT NULL default '0',
  pollvote smallint(6) unsigned NOT NULL default '0',
  eventcomment smallint(6) unsigned NOT NULL default '0',
  eventjoin smallint(6) unsigned NOT NULL default '0',
  sharecomment smallint(6) unsigned NOT NULL default '0',
  post smallint(6) unsigned NOT NULL default '0',
  wall smallint(6) unsigned NOT NULL default '0',
  poke smallint(6) unsigned NOT NULL default '0',
  click smallint(6) unsigned NOT NULL default '0',
  PRIMARY KEY  (daytime)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_statuser'
--

CREATE TABLE uchome_statuser (
  uid mediumint(8) unsigned NOT NULL default '0',
  daytime int(10) unsigned NOT NULL default '0',
  `type` char(20) NOT NULL default '',
  KEY uid (uid)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_tag'
--

CREATE TABLE uchome_tag (
  tagid mediumint(8) unsigned NOT NULL auto_increment,
  tagname char(30) NOT NULL default '',
  uid mediumint(8) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  blognum smallint(6) unsigned NOT NULL default '0',
  `close` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (tagid),
  KEY tagname (tagname)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_tagblog'
--

CREATE TABLE uchome_tagblog (
  tagid mediumint(8) unsigned NOT NULL default '0',
  blogid mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (tagid,blogid)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_tagspace'
--

CREATE TABLE uchome_tagspace (
  tagid mediumint(8) unsigned NOT NULL default '0',
  uid mediumint(8) unsigned NOT NULL default '0',
  username char(15) NOT NULL default '',
  grade smallint(6) NOT NULL default '0',
  PRIMARY KEY  (tagid,uid),
  KEY grade (tagid,grade),
  KEY uid (uid,grade)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_task'
--

CREATE TABLE uchome_task (
  taskid smallint(6) unsigned NOT NULL auto_increment,
  available tinyint(1) NOT NULL default '0',
  `name` varchar(50) NOT NULL default '',
  note text NOT NULL,
  num mediumint(8) unsigned NOT NULL default '0',
  maxnum mediumint(8) unsigned NOT NULL default '0',
  image varchar(150) NOT NULL default '',
  filename varchar(50) NOT NULL default '',
  starttime int(10) unsigned NOT NULL default '0',
  endtime int(10) unsigned NOT NULL default '0',
  nexttime int(10) unsigned NOT NULL default '0',
  nexttype varchar(20) NOT NULL default '',
  credit smallint(6) NOT NULL default '0',
  displayorder smallint(6) unsigned NOT NULL default 0,
  PRIMARY KEY  (taskid),
  KEY displayorder (displayorder)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_thread'
--

CREATE TABLE uchome_thread (
  tid mediumint(8) unsigned NOT NULL auto_increment,
  topicid mediumint(8) unsigned NOT NULL default '0',
  tagid mediumint(8) unsigned NOT NULL default '0',
  eventid mediumint(8) unsigned NOT NULL default '0',
  `subject` char(80) NOT NULL default '',
  magiccolor tinyint(6) unsigned NOT NULL default '0',
  magicegg tinyint(6) unsigned NOT NULL default '0',
  uid mediumint(8) unsigned NOT NULL default '0',
  username char(15) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  viewnum mediumint(8) unsigned NOT NULL default '0',
  replynum mediumint(8) unsigned NOT NULL default '0',
  lastpost int(10) unsigned NOT NULL default '0',
  lastauthor char(15) NOT NULL default '',
  lastauthorid mediumint(8) unsigned NOT NULL default '0',
  displayorder tinyint(1) unsigned NOT NULL default '0',
  digest tinyint(1) NOT NULL default '0',
  hot mediumint(8) unsigned NOT NULL default '0',
  click_11 smallint(6) unsigned NOT NULL default '0',
  click_12 smallint(6) unsigned NOT NULL default '0',
  click_13 smallint(6) unsigned NOT NULL default '0',
  click_14 smallint(6) unsigned NOT NULL default '0',
  click_15 smallint(6) unsigned NOT NULL default '0',
  PRIMARY KEY  (tid),
  KEY tagid (tagid,displayorder,lastpost),
  KEY uid (uid,lastpost),
  KEY lastpost (lastpost),
  KEY topicid (topicid,dateline),
  KEY eventid (eventid,lastpost)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_topic'
--

CREATE TABLE uchome_topic (
  topicid mediumint(8) unsigned NOT NULL auto_increment,
  uid mediumint(8) unsigned NOT NULL default '0',
  username varchar(15) NOT NULL default '',
  `subject` varchar(80) NOT NULL default '',
  message mediumtext NOT NULL,
  jointype varchar(255) NOT NULL default '',
  joingid varchar(255) NOT NULL default '',
  pic varchar(100) NOT NULL default '',
  thumb tinyint(1) NOT NULL default '0',
  remote tinyint(1) NOT NULL default '0',
  joinnum mediumint(8) unsigned NOT NULL default '0',
  lastpost int(10) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  endtime int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (topicid),
  KEY lastpost (lastpost),
  KEY joinnum (joinnum)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_topicuser'
--

CREATE TABLE uchome_topicuser (
  id mediumint(8) unsigned NOT NULL auto_increment,
  uid mediumint(8) unsigned NOT NULL default '0',
  topicid mediumint(8) unsigned NOT NULL default '0',
  username varchar(15) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY uid (uid,dateline),
  KEY topicid (topicid,dateline)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_userapp'
--

CREATE TABLE uchome_userapp (
  uid mediumint(8) unsigned NOT NULL default '0',
  appid mediumint(8) unsigned NOT NULL default '0',
  appname varchar(60) NOT NULL default '',
  privacy tinyint(1) NOT NULL default '0',
  allowsidenav tinyint(1) NOT NULL default '0',
  allowfeed tinyint(1) NOT NULL default '0',
  allowprofilelink tinyint(1) NOT NULL default '0',
  narrow tinyint(1) NOT NULL default '0',
  menuorder smallint(6) NOT NULL default '0',
  displayorder smallint(6) NOT NULL default '0',
  KEY uid (uid,appid),
  KEY menuorder (uid,menuorder),
  KEY displayorder (uid,displayorder)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_userappfield'
--

CREATE TABLE uchome_userappfield (
  uid mediumint(8) unsigned NOT NULL default '0',
  appid mediumint(8) unsigned NOT NULL default '0',
  profilelink text NOT NULL,
  myml text NOT NULL,
  KEY uid (uid,appid)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_userevent'
--

CREATE TABLE uchome_userevent (
  eventid mediumint(8) unsigned NOT NULL default '0',
  uid mediumint(8) unsigned NOT NULL default '0',
  username varchar(15) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  status tinyint(4) NOT NULL default '0',
  fellow mediumint(8) unsigned NOT NULL default '0',
  template varchar(255) NOT NULL default '',
  PRIMARY KEY  (eventid,uid),
  KEY uid (uid,dateline),
  KEY eventid (eventid,status,dateline)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_usergroup'
--

CREATE TABLE uchome_usergroup (
  gid smallint(6) unsigned NOT NULL auto_increment,
  grouptitle varchar(20) NOT NULL default '',
  system tinyint(1) NOT NULL default '0',
  banvisit tinyint(1) NOT NULL default '0',
  explower int(10) NOT NULL default '0',
  maxfriendnum smallint(6) unsigned NOT NULL default '0',
  maxattachsize int(10) unsigned NOT NULL default '0',
  allowhtml tinyint(1) NOT NULL default '0',
  allowcomment tinyint(1) NOT NULL default '0',
  searchinterval smallint(6) unsigned NOT NULL default '0',
  searchignore tinyint(1) NOT NULL default '0',
  postinterval smallint(6) unsigned NOT NULL default '0',
  spamignore tinyint(1) NOT NULL default '0',
  videophotoignore tinyint(1) NOT NULL default '0',
  allowblog tinyint(1) NOT NULL default '0',
  allowdoing tinyint(1) NOT NULL default '0',
  allowupload tinyint(1) NOT NULL default '0',
  allowshare tinyint(1) NOT NULL default '0',
  allowmtag tinyint(1) NOT NULL default '0',
  allowthread tinyint(1) NOT NULL default '0',
  allowpost tinyint(1) NOT NULL default '0',
  allowcss tinyint(1) NOT NULL default '0',
  allowpoke tinyint(1) NOT NULL default '0',
  allowfriend tinyint(1) NOT NULL default '0',
  allowpoll tinyint(1) NOT NULL default '0',
  allowclick tinyint(1) NOT NULL default '0',
  allowevent tinyint(1) NOT NULL default '0',
  allowmagic tinyint(1) NOT NULL default '0',
  allowpm tinyint(1) NOT NULL default '0',
  allowviewvideopic tinyint(1) NOT NULL default '0',
  allowmyop tinyint(1) NOT NULL default '0',
  allowtopic tinyint(1) NOT NULL default '0',
  allowstat tinyint(1) NOT NULL default '0',
  magicdiscount tinyint(1) NOT NULL default '0',
  verifyevent tinyint(1) NOT NULL default '0',
  edittrail tinyint(1) NOT NULL default '0',
  domainlength smallint(6) unsigned NOT NULL default '0',
  closeignore tinyint(1) NOT NULL default '0',
  seccode tinyint(1) NOT NULL default '0',
  color varchar(10) NOT NULL default '',
  icon varchar(100) NOT NULL default '',
  manageconfig tinyint(1) NOT NULL default '0',
  managenetwork tinyint(1) NOT NULL default '0',
  manageprofilefield tinyint(1) NOT NULL default '0',
  manageprofield tinyint(1) NOT NULL default '0',
  manageusergroup tinyint(1) NOT NULL default '0',
  managefeed tinyint(1) NOT NULL default '0',
  manageshare tinyint(1) NOT NULL default '0',
  managedoing tinyint(1) NOT NULL default '0',
  manageblog tinyint(1) NOT NULL default '0',
  managetag tinyint(1) NOT NULL default '0',
  managetagtpl tinyint(1) NOT NULL default '0',
  managealbum tinyint(1) NOT NULL default '0',
  managecomment tinyint(1) NOT NULL default '0',
  managemtag tinyint(1) NOT NULL default '0',
  managethread tinyint(1) NOT NULL default '0',
  manageevent tinyint(1) NOT NULL default '0',
  manageeventclass tinyint(1) NOT NULL default '0',
  managecensor tinyint(1) NOT NULL default '0',
  managead tinyint(1) NOT NULL default '0',
  managesitefeed tinyint(1) NOT NULL default '0',
  managebackup tinyint(1) NOT NULL default '0',
  manageblock tinyint(1) NOT NULL default '0',
  managetemplate tinyint(1) NOT NULL default '0',
  managestat tinyint(1) NOT NULL default '0',
  managecache tinyint(1) NOT NULL default '0',
  managecredit tinyint(1) NOT NULL default '0',
  managecron tinyint(1) NOT NULL default '0',
  managename tinyint(1) NOT NULL default '0',
  manageapp tinyint(1) NOT NULL default '0',
  managetask tinyint(1) NOT NULL default '0',
  managereport tinyint(1) NOT NULL default '0',
  managepoll tinyint(1) NOT NULL default '0',
  manageclick tinyint(1) NOT NULL default '0',
  managemagic tinyint(1) NOT NULL default '0',
  managemagiclog tinyint(1) NOT NULL default '0',
  managebatch tinyint(1) NOT NULL default '0',
  managedelspace tinyint(1) NOT NULL default '0',
  managetopic tinyint(1) NOT NULL default '0',
  manageip tinyint(1) NOT NULL default '0',
  managehotuser tinyint(1) NOT NULL default '0',
  managedefaultuser tinyint(1) NOT NULL default '0',
  managespacegroup tinyint(1) NOT NULL default '0',
  managespaceinfo tinyint(1) NOT NULL default '0',
  managespacecredit tinyint(1) NOT NULL default '0',
  managespacenote tinyint(1) NOT NULL default '0',
  managevideophoto tinyint(1) NOT NULL default '0',
  managelog tinyint(1) NOT NULL default '0',
  magicaward text NOT NULL,
  PRIMARY KEY  (gid)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_userlog'
--

CREATE TABLE uchome_userlog (
  uid mediumint(8) unsigned NOT NULL default '0',
  action char(10) NOT NULL default '',
  `type` tinyint(1) NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (uid)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_usermagic'
--

CREATE TABLE uchome_usermagic (
  uid mediumint(8) unsigned NOT NULL default '0',
  username char(15) NOT NULL default '',
  mid varchar(15) NOT NULL default '',
  count smallint(6) unsigned NOT NULL default '0',
  PRIMARY KEY  (uid,mid)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_usertask'
--

CREATE TABLE uchome_usertask (
  uid mediumint(8) unsigned NOT NULL,
  username char(15) NOT NULL default '',
  taskid smallint(6) unsigned NOT NULL default '0',
  credit smallint(6) NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  isignore tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (uid,taskid),
  KEY isignore (isignore,dateline)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'uchome_visitor'
--

CREATE TABLE uchome_visitor (
  uid mediumint(8) unsigned NOT NULL default '0',
  vuid mediumint(8) unsigned NOT NULL default '0',
  vusername char(15) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (uid,vuid),
  KEY dateline (uid,dateline)
) ENGINE=MyISAM;

