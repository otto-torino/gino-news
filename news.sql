-- PERMISSIONS --
INSERT INTO `auth_permission` (`class`, `code`, `label`, `description`, `admin`) VALUES
('news', 'can_admin', 'Amministrazione news', 'gestione completa delle news', 1),
('news', 'can_publish', 'Pubblicazione news', 'pubblicazione e redazione dei contenuti', 1),
('news', 'can_write', 'Redazione news', 'Redazione delle news', 1),
('news', 'can_view_private', 'Visualizzazione news private', 'visualizzazione delle news con flag privata', 0);

--
-- Table structure for table `news_article`
--

CREATE TABLE IF NOT EXISTS `news_article` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `instance` int(11) NOT NULL,
  `insertion_date` datetime NOT NULL,
  `last_edit_date` datetime NOT NULL,
  `date` date NOT NULL,
  `title` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `text` text,
  `tags` varchar(255) DEFAULT NULL,
  `img` varchar(100) DEFAULT NULL,
  `attachment` varchar(100) DEFAULT NULL,
  `private` int(1) NOT NULL DEFAULT '0',
  `social` int(1) NOT NULL,
  `published` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Table structure for table `news_article_category`
--

CREATE TABLE IF NOT EXISTS `news_article_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `article_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `news_category`
--

CREATE TABLE IF NOT EXISTS `news_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `instance` int(11) NOT NULL DEFAULT '0',
  `name` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `description` text,
  `image` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `news_opt`
--

CREATE TABLE IF NOT EXISTS `news_opt` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `instance` int(11) NOT NULL,
  `last_news_number` int(11) NOT NULL,
  `list_nfp` int(2) DEFAULT NULL,
  `showcase_news_number` int(2) DEFAULT NULL,
  `showcase_auto_start` int(1) NOT NULL,
  `showcase_auto_interval` int(8) NOT NULL,
  `image_width` int(4) DEFAULT NULL,
  `newsletter_news_number` int(3) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
