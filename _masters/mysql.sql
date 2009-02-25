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
