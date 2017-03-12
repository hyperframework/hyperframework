CREATE DATABASE IF NOT EXISTS `test`;
USE `test`;
DROP TABLE IF EXISTS `Document`;
CREATE TABLE `Document` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  `decimal` decimal(8,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
