--
-- webim database schema
--

CREATE TABLE  webim_histories (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `send` tinyint(1) ,
  `type` varchar(20) ,
  `to` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `from` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `body` text,
  `style` varchar(150) ,
  `timestamp` double ,
  `todel` tinyint(1) NOT NULL DEFAULT '0',
  `fromdel` tinyint(1) NOT NULL DEFAULT '0',
  `uid` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`) ,
  KEY `todel` (`todel`),
  KEY `fromdel` (`fromdel`),
  KEY `timestamp` (`timestamp`),
  KEY `to` (`to`),
  KEY `from` (`from`),
  KEY `send` (`send`)
) ENGINE=MyISAM ;

CREATE TABLE  webim_setting (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `web` blob,
  `air` blob,
  `uid` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`id`) 
) ENGINE=MyISAM ;
