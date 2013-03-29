CREATE TABLE `notes` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `position` int(8) NOT NULL,
  `text` text COLLATE utf8_unicode_ci NOT NULL,
  `date` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=90 ;