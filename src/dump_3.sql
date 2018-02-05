START TRANSACTION;
/*!40101 SET NAMES utf8, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET UNIQUE_CHECKS=0, FOREIGN_KEY_CHECKS=0 */;
/*!40111 SET SQL_NOTES=0 */;
CREATE TABLE `xlite_product_tab_translations` (
  `label_id` int(11) NOT NULL AUTO_INCREMENT,
  `id` int(10) unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `brief_info` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8_unicode_ci NOT NULL,
  `code` char(2) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`label_id`),
  KEY `IDX_D669DEC9BF396750` (`id`),
  CONSTRAINT `FK_D669DEC9BF396750` FOREIGN KEY (`id`) REFERENCES `xlite_product_tabs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT INTO `xlite_product_tab_translations` VALUES (1,190,'Privacy','','<h1 class=\"hero-headline\">Apple’s commitment to your privacy</h1>\n<p>At Apple, your trust means everything to us. That’s why we respect your privacy and protect it with strong encryption, plus strict policies that govern how all data is handled.</p>\n<p>Security and privacy are fundamental to the design of all our hardware, software, and services, including iCloud and new services like Apple Pay. And we continue to make improvements. Two-step verification, which we encourage all our customers to use, in addition to protecting your Apple ID account information, now also protects all of the data you store and keep up to date with iCloud.</p>\n<p>We believe in telling you up front exactly what’s going to happen to your personal information and asking for your permission before you share it with us. And if you change your mind later, we make it easy to stop sharing with us. Every Apple product is designed around those principles. When we do ask to use your data, it’s to provide you with a better user experience.</p>\n<p>We’re publishing this website to explain how we handle your personal information, what we do and don’t collect, and why. We’re going to make sure you get updates here about privacy at Apple at least once a year and whenever there are significant changes to our policies.</p>\n<p>A few years ago, users of Internet services began to realize that when an online service is free, you’re not the customer. You’re the product. But at Apple, we believe a great customer experience shouldn’t come at the expense of your privacy.</p>\n<p>Our business model is very straightforward: We sell great products. We don’t build a profile based on your email content or web browsing habits to sell to advertisers. We don’t “monetize” the information you store on your iPhone or in iCloud. And we don’t read your email or your messages to get information to market to you. Our software and services are designed to make our devices better. Plain and simple.</p>\n<p>One very small part of our business does serve advertisers, and that’s iAd. We built an advertising network because some app developers depend on that business model, and we want to support them as well as a free iTunes Radio service. iAd sticks to the same privacy policy that applies to every other Apple product. It doesn’t get data from Health and HomeKit, Maps, Siri, iMessage, your call history, or any iCloud service like Contacts or Mail, and you can always just opt out altogether.</p>\n<p>Finally, I want to be absolutely clear that we have never worked with any government agency from any country to create a backdoor in any of our products or services. We have also never allowed access to our servers. And we never will.</p>\n<p>Our commitment to protecting your privacy comes from a deep respect for our customers. We know that your trust doesn’t come easy. That’s why we have and always will work as hard as we can to earn and keep it.</p>\n<p><strong>Tim Cook</strong><br /><span class=\"attribution-title\">CEO, Apple Inc.</span></p>','en'),(2,190,'Конфиденциальность','','<h1 class=\"hero-headline\">Политика конфиденциальности Apple</h1>\n<p>Компания Apple очень дорожит вашим доверием. И мы уважаем ваше право на защиту личных данных. Чтобы обеспечить конфиденциальность, мы используем надёжное шифрование и выполняем строгие правила, которые применяются при обработке любой информации.</p>\n<p>Безопасность и конфиденциальность лежат в основе всех наших устройств, приложений и служб, включая iCloud и новые сервисы, такие как Apple Pay. И мы продолжаем совершенствовать средства защиты. Так, все данные, которые хранятся в iCloud или синхронизируются через него, теперь защищаются при помощи проверки подлинности в два этапа. Мы всем рекомендуем пользоваться ей и обязательно хранить в тайне Apple ID и пароль.</p>\n<p>Мы считаем своей обязанностью заранее предупреждать вас о том, что может случиться с вашей личной информацией, и получать согласие на предоставление этих данных. А если вы передумаете, то в любой момент сможете отключить передачу личных данных. По этому принципу работает каждый продукт Apple. Если мы запрашиваем ваши данные, то только для того, чтобы вам было проще и удобнее пользоваться нашей продукцией.</p>\n<p>Мы создали этот веб-сайт, чтобы объяснить вам, как и почему мы используем личную информацию, какие сведения мы собираем, а какие нет. Мы обязательно будем обновлять текст Политики конфиденциальности на этом сайте как минимум раз в год, а также при внесении любых существенных изменений.</p>\n<p>Несколько лет назад пользователи некоторых интернет-сервисов стали замечать, что из клиентов они превращаются в товар. Мы в Apple убеждены, что высокое качество обслуживания не должно достигаться в ущерб конфиденциальности.</p>\n<p>Модель нашего бизнеса крайне проста: мы продаём отличные продукты. Мы не создаём досье на основе переписки, истории просмотра веб-сайтов и других действий, и мы никогда не выставим на продажу данные своих пользователей. Мы не пытаемся заработать на информации, которую вы храните на iPad и в iCloud. И мы не читаем вашу почту и сообщения, чтобы показывать вам рекламу. Наше программное обеспечение и услуги созданы для вашего удобства. И ни для чего другого.</p>\n<p>Единственное, что мы делаем для рекламодателей — это iAd. Мы создали рекламную сеть, потому что некоторые разработчики зависят от такой модели ведения бизнеса, и нам хотелось бы их поддержать. То же самое можно сказать про бесплатную службу iTunes Radio. iAd подчиняется той же политике безопасности, что и все остальные решения Apple. Она не получает информацию от приложений Здоровье, HomeKit, Карты, Siri и iMessage, не имеет доступа к истории звонков, не подключается к таким службам iCloud, как Контакты или Mail, и так далее. И вы можете полностью от неё отказаться в любой момент.</p>\n<p>Наконец, я хотел бы прямо заявить, что мы никогда не сотрудничали с государственными органами каких бы то ни было стран на предмет создания «лазеек» в наших продуктах и службах. Мы никогда не давали доступа к нашим серверам. И обязуемся не делать этого в будущем.</p>\n<p>Мы защищаем вашу личную информацию, потому что уважаем вас. Мы знаем, как непросто заслужить доверие. И именно поэтому мы всегда будем делать всё возможное, чтобы не утратить его.</p>\n<p><strong>Тим Кук</strong><br /><span class=\"attribution-title\">Генеральный директор Apple Inc.</span></p>','ru'),(3,191,'Конфиденциальность','','<h1 class=\"hero-headline\">Политика конфиденциальности Apple</h1>\n<p>Компания Apple очень дорожит вашим доверием. И мы уважаем ваше право на защиту личных данных. Чтобы обеспечить конфиденциальность, мы используем надёжное шифрование и выполняем строгие правила, которые применяются при обработке любой информации.</p>\n<p>Безопасность и конфиденциальность лежат в основе всех наших устройств, приложений и служб, включая iCloud и новые сервисы, такие как Apple Pay. И мы продолжаем совершенствовать средства защиты. Так, все данные, которые хранятся в iCloud или синхронизируются через него, теперь защищаются при помощи проверки подлинности в два этапа. Мы всем рекомендуем пользоваться ей и обязательно хранить в тайне Apple ID и пароль.</p>\n<p>Мы считаем своей обязанностью заранее предупреждать вас о том, что может случиться с вашей личной информацией, и получать согласие на предоставление этих данных. А если вы передумаете, то в любой момент сможете отключить передачу личных данных. По этому принципу работает каждый продукт Apple. Если мы запрашиваем ваши данные, то только для того, чтобы вам было проще и удобнее пользоваться нашей продукцией.</p>\n<p>Мы создали этот веб-сайт, чтобы объяснить вам, как и почему мы используем личную информацию, какие сведения мы собираем, а какие нет. Мы обязательно будем обновлять текст Политики конфиденциальности на этом сайте как минимум раз в год, а также при внесении любых существенных изменений.</p>\n<p>Несколько лет назад пользователи некоторых интернет-сервисов стали замечать, что из клиентов они превращаются в товар. Мы в Apple убеждены, что высокое качество обслуживания не должно достигаться в ущерб конфиденциальности.</p>\n<p>Модель нашего бизнеса крайне проста: мы продаём отличные продукты. Мы не создаём досье на основе переписки, истории просмотра веб-сайтов и других действий, и мы никогда не выставим на продажу данные своих пользователей. Мы не пытаемся заработать на информации, которую вы храните на iPad и в iCloud. И мы не читаем вашу почту и сообщения, чтобы показывать вам рекламу. Наше программное обеспечение и услуги созданы для вашего удобства. И ни для чего другого.</p>\n<p>Единственное, что мы делаем для рекламодателей — это iAd. Мы создали рекламную сеть, потому что некоторые разработчики зависят от такой модели ведения бизнеса, и нам хотелось бы их поддержать. То же самое можно сказать про бесплатную службу iTunes Radio. iAd подчиняется той же политике безопасности, что и все остальные решения Apple. Она не получает информацию от приложений Здоровье, HomeKit, Карты, Siri и iMessage, не имеет доступа к истории звонков, не подключается к таким службам iCloud, как Контакты или Mail, и так далее. И вы можете полностью от неё отказаться в любой момент.</p>\n<p>Наконец, я хотел бы прямо заявить, что мы никогда не сотрудничали с государственными органами каких бы то ни было стран на предмет создания «лазеек» в наших продуктах и службах. Мы никогда не давали доступа к нашим серверам. И обязуемся не делать этого в будущем.</p>\n<p>Мы защищаем вашу личную информацию, потому что уважаем вас. Мы знаем, как непросто заслужить доверие. И именно поэтому мы всегда будем делать всё возможное, чтобы не утратить его.</p>\n<p><strong>Тим Кук</strong><br /><span class=\"attribution-title\">Генеральный директор Apple Inc.</span></p>','ru'),(4,191,'Privacy','','<h1 class=\"hero-headline\">Apple’s commitment to your privacy</h1>\n<p>At Apple, your trust means everything to us. That’s why we respect your privacy and protect it with strong encryption, plus strict policies that govern how all data is handled.</p>\n<p>Security and privacy are fundamental to the design of all our hardware, software, and services, including iCloud and new services like Apple Pay. And we continue to make improvements. Two-step verification, which we encourage all our customers to use, in addition to protecting your Apple ID account information, now also protects all of the data you store and keep up to date with iCloud.</p>\n<p>We believe in telling you up front exactly what’s going to happen to your personal information and asking for your permission before you share it with us. And if you change your mind later, we make it easy to stop sharing with us. Every Apple product is designed around those principles. When we do ask to use your data, it’s to provide you with a better user experience.</p>\n<p>We’re publishing this website to explain how we handle your personal information, what we do and don’t collect, and why. We’re going to make sure you get updates here about privacy at Apple at least once a year and whenever there are significant changes to our policies.</p>\n<p>A few years ago, users of Internet services began to realize that when an online service is free, you’re not the customer. You’re the product. But at Apple, we believe a great customer experience shouldn’t come at the expense of your privacy.</p>\n<p>Our business model is very straightforward: We sell great products. We don’t build a profile based on your email content or web browsing habits to sell to advertisers. We don’t “monetize” the information you store on your iPhone or in iCloud. And we don’t read your email or your messages to get information to market to you. Our software and services are designed to make our devices better. Plain and simple.</p>\n<p>One very small part of our business does serve advertisers, and that’s iAd. We built an advertising network because some app developers depend on that business model, and we want to support them as well as a free iTunes Radio service. iAd sticks to the same privacy policy that applies to every other Apple product. It doesn’t get data from Health and HomeKit, Maps, Siri, iMessage, your call history, or any iCloud service like Contacts or Mail, and you can always just opt out altogether.</p>\n<p>Finally, I want to be absolutely clear that we have never worked with any government agency from any country to create a backdoor in any of our products or services. We have also never allowed access to our servers. And we never will.</p>\n<p>Our commitment to protecting your privacy comes from a deep respect for our customers. We know that your trust doesn’t come easy. That’s why we have and always will work as hard as we can to earn and keep it.</p>\n<p><strong>Tim Cook</strong><br /><span class=\"attribution-title\">CEO, Apple Inc.</span></p>','en');
CREATE TABLE `xlite_mailchimp_profile_interests` (
  `group_name_id` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `profile_id` int(11) NOT NULL,
  PRIMARY KEY (`group_name_id`,`profile_id`),
  KEY `IDX_BA74923AF717C8DA` (`group_name_id`),
  KEY `IDX_BA74923ACCFA12B8` (`profile_id`),
  CONSTRAINT `FK_BA74923ACCFA12B8` FOREIGN KEY (`profile_id`) REFERENCES `xlite_profiles` (`profile_id`) ON DELETE CASCADE,
  CONSTRAINT `FK_BA74923AF717C8DA` FOREIGN KEY (`group_name_id`) REFERENCES `xlite_mailchimp_list_group_name` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE TABLE `xlite_order_surcharges` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(11) DEFAULT NULL,
  `type` char(8) COLLATE utf8_unicode_ci NOT NULL,
  `code` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `class` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `include` tinyint(1) NOT NULL,
  `available` tinyint(1) NOT NULL,
  `value` decimal(14,4) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `weight` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_3583A1698D9F6D38` (`order_id`),
  CONSTRAINT `FK_3583A1698D9F6D38` FOREIGN KEY (`order_id`) REFERENCES `xlite_orders` (`order_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT INTO `xlite_order_surcharges` VALUES (1,1,'shipping','SHIPPING','\\XLite\\Logic\\Order\\Modifier\\Shipping',0,1,1.5000,'Shipping cost',0),(2,1,'discount','DISCOUNT','\\XLite\\Module\\CDev\\VolumeDiscounts\\Logic\\Order\\Modifier\\Discount',0,1,-3.0000,'Discount',0),(3,2,'shipping','SHIPPING','\\XLite\\Logic\\Order\\Modifier\\Shipping',0,1,3.7200,'Shipping cost',0),(4,3,'shipping','SHIPPING','\\XLite\\Logic\\Order\\Modifier\\Shipping',0,1,0.0000,'Shipping cost',0),(5,4,'shipping','SHIPPING','\\XLite\\Logic\\Order\\Modifier\\Shipping',0,1,3.0200,'Shipping cost',0),(6,5,'shipping','SHIPPING','\\XLite\\Logic\\Order\\Modifier\\Shipping',0,1,1.5000,'Shipping cost',0);
CREATE TABLE `xlite_attribute_values_checkbox_translations` (
  `label_id` int(11) NOT NULL AUTO_INCREMENT,
  `id` int(10) unsigned DEFAULT NULL,
  `code` char(2) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`label_id`),
  KEY `ci` (`code`,`id`),
  KEY `id` (`id`),
  CONSTRAINT `FK_5A770E51BF396750` FOREIGN KEY (`id`) REFERENCES `xlite_attribute_values_checkbox` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE TABLE `xlite_quick_data` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned DEFAULT NULL,
  `membership_id` int(10) unsigned DEFAULT NULL,
  `price` decimal(14,4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_C6F6D1AD4584665A` (`product_id`),
  KEY `IDX_C6F6D1AD1FB354CD` (`membership_id`),
  KEY `customerArea` (`membership_id`,`product_id`),
  CONSTRAINT `FK_C6F6D1AD1FB354CD` FOREIGN KEY (`membership_id`) REFERENCES `xlite_memberships` (`membership_id`) ON DELETE CASCADE,
  CONSTRAINT `FK_C6F6D1AD4584665A` FOREIGN KEY (`product_id`) REFERENCES `xlite_products` (`product_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT INTO `xlite_quick_data` VALUES (1,1,1,19.9900),(2,1,NULL,19.9900),(3,2,1,49.9900),(4,2,NULL,49.9900),(5,3,1,19.9900),(6,3,NULL,19.9900),(7,4,1,24.9900),(8,4,NULL,24.9900),(9,5,1,19.9900),(10,5,NULL,19.9900),(11,6,1,79.9900),(12,6,NULL,47.9900),(13,7,1,29.9900),(14,7,NULL,29.9900),(15,8,1,19.9900),(16,8,NULL,14.9900),(17,9,1,19.9900),(18,9,NULL,19.9900),(19,10,1,34.9900),(20,10,NULL,34.9900),(21,11,1,14.9900),(22,11,NULL,14.9900),(23,12,1,9.9900),(24,12,NULL,9.9900),(25,13,1,19.9900),(26,13,NULL,19.9900),(27,14,1,39.9900),(28,14,NULL,39.9900),(29,15,1,29.9900),(30,15,NULL,25.4900),(31,16,1,19.9900),(32,16,NULL,14.9900),(33,17,1,99.0000),(34,17,NULL,99.0000),(35,18,1,49.9900),(36,18,NULL,49.9900),(37,19,1,19.9900),(38,19,NULL,19.9900),(39,20,1,9.9900),(40,20,NULL,9.9900),(41,21,1,9.9900),(42,21,NULL,9.9900),(43,22,1,29.9900),(44,22,NULL,29.9900),(45,23,1,19.9900),(46,23,NULL,19.9900),(47,24,1,29.9900),(48,24,NULL,29.9900),(49,25,1,14.9900),(50,25,NULL,14.9900),(51,26,1,19.9900),(52,26,NULL,19.9900),(53,27,1,12.9900),(54,27,NULL,6.4900),(55,28,1,69.9900),(56,28,NULL,69.9900),(57,29,1,79.9900),(58,29,NULL,79.9900),(59,30,1,299.9900),(60,30,NULL,299.9900),(61,31,1,49.9900),(62,31,NULL,19.9900),(63,32,1,59.9900),(64,32,NULL,44.9900),(65,33,1,11.9900),(66,33,NULL,11.9900),(67,34,1,139.9900),(68,34,NULL,139.9900),(69,35,1,49.9900),(70,35,NULL,39.9900),(71,36,1,34.9900),(72,36,NULL,34.9900),(73,37,1,650.0000),(74,37,NULL,650.0000),(75,38,1,817.5900),(76,38,NULL,817.5900),(77,39,1,399.0000),(78,39,NULL,399.0000),(79,40,1,199.9900),(80,40,NULL,199.9900),(81,41,1,850.0000),(82,41,NULL,850.0000),(83,42,1,299.0000),(84,42,NULL,299.0000);
CREATE TABLE `xlite_menus` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `link` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lpos` int(11) NOT NULL,
  `rpos` int(11) NOT NULL,
  `depth` int(11) NOT NULL,
  `type` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `visibleFor` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_AC666883727ACA70` (`parent_id`),
  KEY `enabled` (`enabled`,`type`),
  KEY `position` (`position`),
  CONSTRAINT `FK_AC666883727ACA70` FOREIGN KEY (`parent_id`) REFERENCES `xlite_menus` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT INTO `xlite_menus` VALUES (1,2,'{home}',2,3,0,'P',100,1,'AL'),(2,NULL,'link',1,64,-1,'P',0,1,'AL'),(3,2,'{my account}',4,9,0,'P',600,1,'L'),(4,3,'?target=order_list',5,6,1,'P',100,1,'L'),(5,3,'?target=address_book',7,8,1,'P',200,1,'L'),(6,2,'shipping.html',10,11,0,'P',200,1,'AL'),(7,2,'{new arrivals}',12,13,0,'P',400,1,'AL'),(8,2,'{coming soon}',14,15,0,'P',500,1,'AL'),(9,2,NULL,16,23,0,'P',150,1,'AL'),(10,9,'{sale}',17,18,1,'P',100,1,'AL'),(11,9,'{bestsellers}',19,20,1,'P',200,1,'AL'),(12,9,'{special offers}',21,22,1,'P',300,0,'AL'),(13,2,'{contact us}',24,25,0,'P',700,1,'AL'),(14,2,NULL,26,33,0,'F',100,1,'AL'),(15,14,'apparel',27,28,1,'F',100,1,'AL'),(16,14,'igoods',29,30,1,'F',200,1,'AL'),(17,14,'toys',31,32,1,'F',300,1,'AL'),(18,2,NULL,34,41,0,'F',200,1,'AL'),(19,18,'{sale}',35,36,1,'F',100,1,'AL'),(20,18,'{coming soon}',37,38,1,'F',200,1,'AL'),(21,18,'{new arrivals}',39,40,1,'F',300,1,'AL'),(22,2,NULL,42,49,0,'F',300,1,'AL'),(23,22,'shipping.html',43,44,1,'F',100,1,'AL'),(24,22,'?target=map',45,46,1,'F',200,1,'AL'),(25,22,'terms-and-conditions.html',47,48,1,'F',300,1,'AL'),(26,2,NULL,50,55,0,'F',400,1,'AL'),(27,26,'shipping.html',51,52,1,'F',100,1,'AL'),(28,26,'{contact us}',53,54,1,'F',200,1,'AL'),(29,2,NULL,56,63,0,'F',500,1,'L'),(30,29,'?target=address_book',57,58,1,'F',100,1,'L'),(31,29,'?target=order_list',59,60,1,'F',200,1,'L'),(32,29,'?target=profile',61,62,1,'F',300,1,'L');
CREATE TABLE `xlite_category_banners` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(10) unsigned DEFAULT NULL,
  `alt` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `width` int(11) NOT NULL,
  `height` int(11) NOT NULL,
  `hash` char(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `needProcess` tinyint(1) NOT NULL,
  `path` varchar(512) COLLATE utf8_unicode_ci NOT NULL,
  `fileName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `mime` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `storageType` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
  `size` int(10) unsigned NOT NULL,
  `date` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_69B8F0D412469DE2` (`category_id`),
  CONSTRAINT `FK_69B8F0D412469DE2` FOREIGN KEY (`category_id`) REFERENCES `xlite_categories` (`category_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT INTO `xlite_category_banners` VALUES (1,2,'',2720,800,NULL,1,'banner_igoods_clean.jpg','','image/jpeg','r',125253,1394109447),(2,3,'',2720,800,NULL,1,'banner_igoods_clean.jpg','','image/jpeg','r',125253,1394109447),(3,4,'',2720,800,NULL,1,'banner_apparel_clean.jpg','','image/jpeg','r',122998,1394109447),(4,5,'',2720,800,NULL,1,'banner_toys_clean.jpg','','image/jpeg','r',124298,1394109447);
CREATE TABLE `xlite_special_offer_type_translations` (
  `label_id` int(11) NOT NULL AUTO_INCREMENT,
  `id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `code` char(2) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`label_id`),
  KEY `ci` (`code`,`id`),
  KEY `id` (`id`),
  KEY `name` (`name`),
  CONSTRAINT `FK_E3B2A258BF396750` FOREIGN KEY (`id`) REFERENCES `xlite_special_offer_types` (`type_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE TABLE `xlite_banner_rotation_slide` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `enabled` tinyint(1) NOT NULL,
  `link` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT INTO `xlite_banner_rotation_slide` VALUES (1,1,'cart.php?target=category&category_id=4',20),(2,1,'cart.php?target=category&category_id=5',10);
COMMIT;