/*
SQLyog Community v11.52 (32 bit)
MySQL - 5.5.41-0ubuntu0.14.04.1 : Database - ads_board
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
  CONSTRAINT `ads_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `ads_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Data for the table `ads` */

insert  into `ads`(`id`,`user_id`,`title`,`text`,`category_id`,`phone`,`date_create`) values (1,2,'фывфы','Советник президента Украины Юрий Бирюков заявляет, что в воскресенье утром украинские военнослужащие получили приказ и открыли огонь по позициям незаконных вооруженных формирований (НВФ) в секторе \"Б\".\r\n\"Доброе утро, страна. 08.01, уже можно сказать о том, что два часа назад вся группировка наших войск в секторе \"Б\" получила приказ и открыла массированный огонь по известным позициям сепаров\", - написал он на своей страничке в соцсети Facebook.\r\n\"Мы соблюдали перемирие, да? Мы показывали, что заинтересованы в спокойном урегулировании, да? Ну так вот, сегодня мы покажем насколько мы умеем бить по зубам\", - отметил Ю.Бирюков.',16,NULL,'2015-01-18 14:05:53'),(2,3,'Манчестер Юнайтед переигр','Не с позиций явных фаворитов подходили к очередному матчу чемпионата в этот раз подопечные Луи Ван Гаала. Манчестер Юнайтед уже давно, а именно на протяжении трех туров не может набрать в поединке премьер-лиги полноценные три очка. В прошлом туре манкунианцы и вовсе потерпели сухое поражение на Олд Траффорд от Саутгемптона. Потому обязаны были реабилитироваться сегодня Красные дьяволы. В то же время перед Харри Реднаппом стояла задача сохранения за собой места на тренерском мостике. По информации из английской прессой позиции менеджера в клубе после ряда неудачных результатов заметно пошатнулись.',1,NULL,'2015-01-18 14:06:49');

/*Table structure for table `categories` */

DROP TABLE IF EXISTS `categories`;

CREATE TABLE `categories` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(16) NOT NULL,
  `description` varchar(32) NOT NULL,
  `pid` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8;

/*Data for the table `categories` */

insert  into `categories`(`id`,`name`,`description`,`pid`) values (1,'Техника','Техника',0),(16,'новая','категория',0),(17,'Планшеты','Планшеы',1),(18,'Телефоны','Мобильные телефоны',1),(19,'asdasdasdsad','asdsadasdasdasd',16),(20,'Отдых','Принадлежности для отдыха',0),(21,'Винчестеры','Жосткие диски',1),(22,'aaaaaaaaaaa','aaaaaaaaaaaaaa',20),(30,'11111','111111',20);

/*Table structure for table `comments` */

DROP TABLE IF EXISTS `comments`;

CREATE TABLE `comments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `ad_id` int(11) unsigned DEFAULT NULL,
  `text` text,
  `date_create` timestamp NULL DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `ad_id` (`ad_id`),
  CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`ad_id`) REFERENCES `ads` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

/*Data for the table `comments` */

insert  into `comments`(`id`,`user_id`,`ad_id`,`text`,`date_create`,`status`) values (1,3,1,'Хороший товар, рэспект','2015-01-18 15:06:34',NULL),(2,3,2,'asdasdasdasdasd','2015-01-18 15:45:30',NULL),(3,2,2,'asdasdasdasdasdas','2015-01-19 17:51:42',NULL),(4,2,2,'asdasdasdadadadsasda','2015-01-19 17:51:47',NULL);

/*Table structure for table `images` */

DROP TABLE IF EXISTS `images`;

CREATE TABLE `images` (
  `id` int(11) NOT NULL,
  `ads_id` int(11) unsigned DEFAULT NULL,
  `name` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ads_id` (`ads_id`),
  CONSTRAINT `images_ibfk_1` FOREIGN KEY (`ads_id`) REFERENCES `ads` (`id`)
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Data for the table `payments` */

insert  into `payments`(`id`,`user_id`,`plan_id`,`start_date`,`end_date`,`transaction_id`) values (1,2,2,'2015-01-08 13:10:43','2015-02-07 13:10:43','4'),(2,3,2,'2015-01-18 14:50:59','2015-02-07 13:14:51','1RC36827U4880220T');

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

/*Table structure for table `properties` */

DROP TABLE IF EXISTS `properties`;

CREATE TABLE `properties` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `params` varchar(256) DEFAULT NULL,
  `type` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

/*Data for the table `properties` */

insert  into `properties`(`id`,`name`,`params`,`type`) values (3,'Тип жесткого диска','[\"111\",\"asdasdad\",\"asdasd\",\"22\",\"pp\"]','select'),(4,'Форм-фактор','[\"3.5\",\"2.5\"]','radio'),(5,'Согласен высылать за границу','[\"apply\"]','checkbox'),(6,'Скорость передачи данных','[\"Mb/s\"]','textfield'),(7,'111','[\"111\",\"111\",\"111\"]','radio'),(8,'222','[\"222\",\"222\",\"999\"]','textbox');

/*Table structure for table `property_ads` */

DROP TABLE IF EXISTS `property_ads`;

CREATE TABLE `property_ads` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ads_id` int(11) unsigned DEFAULT NULL,
  `property_id` int(11) unsigned DEFAULT NULL,
  `value` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ads_id` (`ads_id`),
  KEY `property_id` (`property_id`),
  CONSTRAINT `property_ads_ibfk_1` FOREIGN KEY (`ads_id`) REFERENCES `ads` (`id`),
  CONSTRAINT `property_ads_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `property_ads` */

/*Table structure for table `property_cats` */

DROP TABLE IF EXISTS `property_cats`;

CREATE TABLE `property_cats` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(11) unsigned DEFAULT NULL,
  `property_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `property_id` (`property_id`),
  CONSTRAINT `property_cats_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  CONSTRAINT `property_cats_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8;

/*Data for the table `property_cats` */

insert  into `property_cats`(`id`,`category_id`,`property_id`) values (2,21,4),(8,16,3),(9,16,4),(10,16,3),(11,16,4),(12,16,3),(13,16,3),(22,17,3),(24,22,4),(25,22,6),(26,22,7);

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Data for the table `users` */

insert  into `users`(`id`,`login`,`name`,`password`,`status`,`role`,`email`,`phone`,`salt`,`guid`,`date_create`,`plan_id`) values (2,'admin','admin','9cbf72dba5709b3298bbd2bbbf8b1e85','confirmed','admin','admin@ukr.net','+3806347531','54abac7224de2','JxR5SNfio1','2015-01-06 11:35:46',1),(3,'vas','vas','60d137ddc301fe95265528419e33ac27','confirmed','user','vas@vas.com','123','54b5713a425ec','XGoKVwGkVF','2015-01-13 21:25:46',1);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;