/*
SQLyog Ultimate v9.50 
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

/*Table structure for table `ads` */

DROP TABLE IF EXISTS `ads`;

CREATE TABLE `ads` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `title` varchar(25) NOT NULL,
  `text` text,
  `category_id` int(11) unsigned NOT NULL,
  `phone` varchar(11) DEFAULT NULL,
  `date_create` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `ads_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  CONSTRAINT `ads_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*Data for the table `ads` */

insert  into `ads`(`id`,`user_id`,`title`,`text`,`category_id`,`phone`,`date_create`) values (4,2,'aaaaaaaaaaaaaaaaaaaaaaaaa','eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeыаа',1,NULL,'2015-01-08 16:45:39'),(5,2,'продампродам','продампродампродампродампродампродампродампродампродампродампродам\r\n',1,NULL,'2015-01-08 16:44:31');

/*Table structure for table `categories` */

DROP TABLE IF EXISTS `categories`;

CREATE TABLE `categories` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(16) DEFAULT NULL,
  `description` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Data for the table `categories` */

insert  into `categories`(`id`,`name`,`description`) values (1,'Отдых','лоивраымпбвьафыоим');

/*Table structure for table `images` */

DROP TABLE IF EXISTS `images`;

CREATE TABLE `images` (
  `id` int(11) NOT NULL,
  `ads_id` int(11) DEFAULT NULL,
  `name` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `images` */

/*Table structure for table `payments` */

DROP TABLE IF EXISTS `payments`;

CREATE TABLE `payments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `plan_id` int(11) unsigned NOT NULL,
  `start_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `end_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `transaction_id` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `plan_id` (`plan_id`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

/*Data for the table `payments` */

insert  into `payments`(`id`,`user_id`,`plan_id`,`start_date`,`end_date`,`transaction_id`) values (1,2,2,'2015-01-08 13:10:43','2015-02-07 13:10:43','4'),(2,2,2,'2015-01-08 13:14:51','2015-02-07 13:14:51','1RC36827U4880220T'),(3,2,2,'2015-01-08 13:21:19','2015-02-07 13:21:19','1RC36827U4880220T'),(4,2,2,'2015-01-08 13:21:29','2015-02-07 13:21:29','1RC36827U4880220T'),(5,2,3,'2015-01-08 13:28:51','2015-02-07 13:28:51','42S2530886168363U'),(6,2,3,'2015-01-08 13:30:17','2015-02-07 13:30:17','42S2530886168363U'),(7,2,3,'2015-01-08 15:17:38','2015-02-07 15:17:38','4WN70612S75780039'),(8,2,1,'2015-01-08 16:06:26','2015-02-07 16:06:26','0'),(9,2,1,'2015-01-08 16:06:37','2015-02-07 16:06:37','0'),(10,2,1,'2015-01-08 16:12:18','2015-02-07 16:12:18','0');

/*Table structure for table `plans` */

DROP TABLE IF EXISTS `plans`;

CREATE TABLE `plans` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(8) DEFAULT 'free',
  `price` int(3) unsigned DEFAULT '0',
  `count_ads` int(3) DEFAULT '10',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Data for the table `plans` */

insert  into `plans`(`id`,`name`,`price`,`count_ads`) values (1,'free',0,10),(2,'pro',99,1000),(3,'business',199,-1);

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `login` varchar(32) NOT NULL,
  `name` varchar(32) NOT NULL,
  `password` varchar(32) NOT NULL,
  `status` enum('registered','confirmed','banned') NOT NULL,
  `role` enum('user','admin') NOT NULL,
  `email` varchar(256) NOT NULL,
  `phone` varchar(14) DEFAULT NULL,
  `salt` varchar(32) DEFAULT NULL,
  `guid` varchar(32) DEFAULT NULL,
  `date_create` timestamp NULL DEFAULT NULL,
  `plan_id` int(11) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `plan_id` (`plan_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Data for the table `users` */

insert  into `users`(`id`,`login`,`name`,`password`,`status`,`role`,`email`,`phone`,`salt`,`guid`,`date_create`,`plan_id`) values (2,'admin','admin','9cbf72dba5709b3298bbd2bbbf8b1e85','confirmed','user','admin@ukr.net','+3806347531','54abac7224de2','mSL8dToiqW','2015-01-06 11:35:46',1);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
