DROP TABLE IF EXISTS `users`;

CREATE TABLE `shine_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nid` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `username` varchar(65) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `password` varchar(65) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `level` enum('user','admin') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'user',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
