# Dump of table sessions
# ------------------------------------------------------------

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `data` text COLLATE utf8_unicode_ci NOT NULL,
  `updated_on` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table users
# ------------------------------------------------------------

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nid` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `username` varchar(65) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `password` varchar(65) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `level` enum('user','admin') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'user',
  `twostep` varchar(6) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
