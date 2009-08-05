-- 
-- Table structure for table `sessions`
-- 

CREATE TABLE `sessions` (
  `id` varchar(255) collate utf8_unicode_ci NOT NULL,
  `data` text collate utf8_unicode_ci NOT NULL,
  `updated_on` int(10) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

-- 
-- Table structure for table `users`
-- 

CREATE TABLE `users` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(65) collate utf8_unicode_ci NOT NULL,
  `password` varchar(65) collate utf8_unicode_ci NOT NULL,
  `level` enum('user','admin') collate utf8_unicode_ci NOT NULL,
  `email` varchar(65) collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `stats`
--

CREATE TABLE IF NOT EXISTS `stats` (
  `id` int(11) NOT NULL auto_increment,
  `dt` datetime NOT NULL default '0000-00-00 00:00:00',
  `referer` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `referer_is_local` tinyint(4) NOT NULL default '0',
  `url` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `page_title` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `search_terms` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `img_search` tinyint(4) NOT NULL default '0',
  `browser_family` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `browser_version` varchar(15) collate utf8_unicode_ci NOT NULL default '',
  `os` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `os_version` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `ip` varchar(15) collate utf8_unicode_ci NOT NULL default '',
  `user_agent` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `exec_time` float NOT NULL default '0',
  `num_queries` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
