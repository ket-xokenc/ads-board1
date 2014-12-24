/*
SQLyog Ultimate v11.52 (64 bit)
MySQL - 5.5.40-0ubuntu0.14.04.1 : Database - ads_board
*********************************************************************
*/


/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`ads_board` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `ads_board`;

/*Table structure for table `ad` */

DROP TABLE IF EXISTS `ad`;

CREATE TABLE `ad` (
  `id_ad` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `id_category` int(11) NOT NULL,
  `title` varchar(25) NOT NULL,
  `text` text,
  `phone` varchar(11) NOT NULL,
  PRIMARY KEY (`id_ad`),
  KEY `id_user` (`id_user`),
  KEY `id_category` (`id_category`),
  CONSTRAINT `ad_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`),
  CONSTRAINT `ad_ibfk_2` FOREIGN KEY (`id_category`) REFERENCES `categories` (`id_category`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Data for the table `ad` */

/*Table structure for table `categories` */

DROP TABLE IF EXISTS `categories`;

CREATE TABLE `categories` (
  `id_category` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(10) NOT NULL,
  `description` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id_category`),
  UNIQUE KEY `UNIQ_NAME` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Data for the table `categories` */

insert  into `categories`(`id_category`,`name`,`description`) values (1,'fruite','food');

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `login` varchar(50) NOT NULL,
  `name` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `status` enum('registered','confirmed','banned') NOT NULL,
  `plan` enum('free','pro','business') DEFAULT NULL,
  `role` enum('user','admin') NOT NULL,
  `email` varchar(50) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `salt` varchar(50) DEFAULT NULL,
  `guid` varchar(50) DEFAULT NULL,
  `create_time` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

/*Data for the table `users` */

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;