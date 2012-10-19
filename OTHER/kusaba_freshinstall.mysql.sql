-- --------------------------------------------------------

CREATE TABLE `PREFIX_banlist` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `expired` tinyint(1) NOT NULL DEFAULT '0',
  `type` int(11) NOT NULL,
  `ip` varchar(50) NOT NULL,
  `ipmd5` char(32) NOT NULL,
  `by` varchar(75) NOT NULL,
  `at` int(20) NOT NULL,
  `until` int(20) NOT NULL,
  `reason` text NOT NULL,
  `staffnote` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `PREFIX_loginattempts` (
  `username` varchar(255) NOT NULL,
  `ip` varchar(20) NOT NULL,
  `timestamp` int(20) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `PREFIX_modlog` (
  `entry` text NOT NULL,
  `user` varchar(255) NOT NULL,
  `category` tinyint(2) NOT NULL default '0',
  `timestamp` int(20) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `PREFIX_reports` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `cleared` tinyint(1) NOT NULL default '0',
  `board` varchar(255) NOT NULL,
  `postid` int(20) NOT NULL,
  `when` int(20) NOT NULL,
  `ip` varchar(75) NOT NULL,
  `reason` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `PREFIX_staff` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `salt` varchar(3) NOT NULL,
  `type` tinyint(1) NOT NULL default '0',
  `boards` text,
  `addedon` int(20) NOT NULL,
  `lastactive` int(20) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------