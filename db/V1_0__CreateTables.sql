#SET FOREIGN_KEY_CHECKS=0;

CREATE DATABASE IF NOT EXISTS restaurant;

USE restaurant;

CREATE TABLE IF NOT EXISTS `customer`
(
  `id`         int(11)      NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) NOT NULL,
  `last_name`  varchar(255) NOT NULL,
  PRIMARY KEY (id)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `restaurant`
(
  `id`         int(11)      NOT NULL AUTO_INCREMENT,
  `restaurant` varchar(255) NOT NULL,
  `cuisine`    varchar(255) NOT NULL,
  PRIMARY KEY (id)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `menu`
(
  `id`            int(11)      NOT NULL AUTO_INCREMENT,
  `restaurant_id` int(11)      NOT NULL,
  `item`          varchar(255) NOT NULL,
  `price`         decimal(5,2) NOT NULL,
  `available`     bool DEFAULT false,
  PRIMARY KEY (id)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `order`
(
  `id`          int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  PRIMARY KEY (id)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `ordered`
(
  `id`            int(11) NOT NULL AUTO_INCREMENT,
  `order_id`      int(11) NOT NULL,
  `item_id`       int(11) NOT NULL,
  `price_charged` decimal(5,2) DEFAULT 0.0,
  `discount`      decimal(5,2) DEFAULT 0.0,
  PRIMARY KEY (id)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `transaction`
(
  `id`              int(11) NOT NULL AUTO_INCREMENT,
  `customer_id`     int(11) NOT NULL,
  `ordered_id`      int(11) NOT NULL,
  `tip`             bool DEFAULT false,
  `paid`            decimal(5,2) DEFAULT 0.0,
  PRIMARY KEY (id)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;