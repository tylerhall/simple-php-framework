CREATE TABLE `sessions` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `data` text COLLATE utf8_unicode_ci NOT NULL,
  `updated_on` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nid` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `username` varchar(65) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `password` varchar(65) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `level` enum('user','admin') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'user',
  `email` varchar(65) COLLATE utf8_unicode_ci DEFAULT NULL,
  `twostep` varchar(6) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `url_cache` (
  `url` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `dt_refreshed` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `dt_expires` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `data` text COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `url` (`url`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

