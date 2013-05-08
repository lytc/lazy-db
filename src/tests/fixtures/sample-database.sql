CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_time` datetime NOT NULL,
  `modified_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

CREATE TABLE `products` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `price` float(30,0) NOT NULL,
  `vat` float(30,0) NOT NULL DEFAULT '0',
  `created_time` datetime NOT NULL,
  `modified_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

CREATE TABLE `orders` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `product_id` int(11) unsigned NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `created_time` datetime NOT NULL,
  `modified_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

CREATE TABLE `permissions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

CREATE TABLE `user_permissions` (
  `user_id` int(11) unsigned NOT NULL,
  `permission_id` int(11) unsigned NOT NULL,
  KEY `user_id` (`user_id`),
  KEY `permission_id` (`permission_id`),
  CONSTRAINT `user_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `user_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `posts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_time` datetime NOT NULL,
  `modified_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

INSERT INTO `users` VALUES ('1', 'name1', 'username1', 'password1', 'phone1', '2013-05-02 23:47:07', '2013-05-02 23:47:07'), ('2', 'name2', 'username2', 'password2', 'phone2', '2013-05-02 23:47:07', '2013-05-02 23:47:07'), ('3', 'name3', 'username3', 'password3', null, '2013-05-02 23:47:07', '2013-05-02 23:47:16'), ('4', 'name4', 'username4', 'password4', null, '2013-05-02 23:47:07', '2013-05-02 23:47:22');
INSERT INTO `products` VALUES ('1', 'name1', '10', '0', '2013-05-02 23:49:20', '2013-05-02 23:49:20'), ('2', 'name2', '20', '10', '2013-05-02 23:49:20', '2013-05-02 23:49:20'), ('3', 'name3', '30', '20', '2013-05-02 23:49:20', '2013-05-02 23:49:20'), ('4', 'name4', '40', '30', '2013-05-02 23:49:20', '2013-05-02 23:49:20');
INSERT INTO `orders` VALUES ('1', '1', '1', '0', '2013-05-03 00:25:05', '2013-05-03 00:25:05'), ('2', '1', '2', '1', '2013-05-03 00:25:05', '2013-05-03 00:25:05'), ('3', '1', '3', '0', '2013-05-03 00:25:05', '2013-05-03 00:25:05'), ('4', '1', '1', '1', '2013-05-03 00:25:05', '2013-05-03 00:25:05'), ('5', '2', '1', '0', '2013-05-03 00:25:05', '2013-05-03 00:25:05'), ('6', '2', '2', '1', '2013-05-03 00:25:05', '2013-05-03 00:25:05'), ('7', '2', '3', '0', '2013-05-03 00:25:05', '2013-05-03 00:25:05'), ('8', '2', '1', '1', '2013-05-03 00:25:05', '2013-05-03 00:25:05');
INSERT INTO `permissions` VALUES ('1', 'name1'), ('2', 'name2'), ('3', 'name3'), ('4', 'name4');
INSERT INTO `user_permissions` VALUES ('1', '1'), ('1', '2'), ('2', '3'), ('2', '4');
INSERT INTO `posts` VALUES ('1', '1', 'name1', 'content1', '2013-05-03 21:57:06', '2013-05-03 21:57:06'), ('2', '1', 'name2', 'content2', '2013-05-03 21:57:06', '2013-05-03 21:57:06'), ('3', '1', 'name3', 'content3', '2013-05-03 21:57:06', '2013-05-03 21:57:06'), ('4', '1', 'name4', 'content4', '2013-05-03 21:57:06', '2013-05-03 21:57:06'), ('5', '2', 'name5', 'content5', '2013-05-03 21:57:06', '2013-05-03 21:57:06'), ('6', '2', 'name6', 'content6', '2013-05-03 21:57:06', '2013-05-03 21:57:06'), ('7', '2', 'name7', 'content7', '2013-05-03 21:57:06', '2013-05-03 21:57:06'), ('8', '2', 'name8', 'content8', '2013-05-03 21:57:06', '2013-05-03 21:57:06');
