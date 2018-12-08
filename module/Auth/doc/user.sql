CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `access` int(11) NOT NULL,
  `lastconnection` datetime DEFAULT NULL,
  `logonName` varchar(50) CHARACTER SET utf8 NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(50) CHARACTER SET utf8 NOT NULL,
  `role` int(11) NOT NULL DEFAULT '0',
  `status` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
