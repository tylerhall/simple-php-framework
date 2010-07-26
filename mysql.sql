CREATE TABLE `sessions` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `data` text NOT NULL,
  `updated_on` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
);

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nid` varchar(32) NOT NULL DEFAULT '',
  `username` varchar(65) NOT NULL DEFAULT '',
  `password` varchar(65) NOT NULL DEFAULT '',
  `level` enum('user','admin') NOT NULL DEFAULT 'user',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
);
